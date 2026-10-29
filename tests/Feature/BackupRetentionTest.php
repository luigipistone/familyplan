<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Support\BackupManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupRetentionTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->temporaryStorage = sys_get_temp_dir().'/familyplan-retention-'.bin2hex(random_bytes(8));
        $this->app->useStoragePath($this->temporaryStorage);
        File::ensureDirectoryExists(storage_path('app/backups'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->temporaryStorage);
        parent::tearDown();
    }

    private function archive(string $name, int $time): string
    {
        $path = storage_path('app/backups/'.$name);
        File::put($path, 'Synthetic backup fixture');
        touch($path, $time);

        return $path;
    }

    public function test_keeps_only_the_latest_five_automatic_archives_across_frequencies(): void
    {
        $automatic = [];
        foreach (['daily', 'weekly', 'monthly', 'daily', 'weekly', 'monthly', 'daily'] as $index => $mode) {
            $automatic[] = $this->archive('familyplan-auto-'.$mode.'-202610'.sprintf('%02d', $index + 1).'-120000.zip', 1000 + $index);
        }
        $preserved = [];
        foreach (['manual', 'pre-deploy', 'pre-restore', 'unknown'] as $mode) {
            $preserved[] = $this->archive('familyplan-'.$mode.'-20260901-120000.zip', 1);
        }
        $preserved[] = $this->archive('familyplan-auto-daily-20261001-120000.zip.pending-abc', 1);
        $preserved[] = $this->archive('unrelated-auto-archive.zip', 1);
        symlink($automatic[6], storage_path('app/backups/familyplan-auto-daily-20200101-120000.zip'));

        $manager = new BackupManager;
        $this->assertSame(2, $manager->pruneAutomaticBackups());
        foreach ($automatic as $index => $path) {
            $this->assertSame($index >= 2, is_file($path), $path);
        }
        foreach ($preserved as $path) {
            $this->assertFileExists($path);
        }
        $this->assertTrue(is_link(storage_path('app/backups/familyplan-auto-daily-20200101-120000.zip')));
        $this->assertSame(0, $manager->pruneAutomaticBackups());
    }

    public function test_equal_file_times_use_the_creation_date_in_the_name(): void
    {
        $files = [];
        for ($day = 1; $day <= 6; $day++) {
            $mode = $day === 1 ? 'weekly' : 'daily';
            $files[] = $this->archive('familyplan-auto-'.$mode.'-2026100'.$day.'-120000.zip', 1000);
        }
        $this->assertSame(1, (new BackupManager)->pruneAutomaticBackups());
        $this->assertFileDoesNotExist($files[0]);
        foreach (array_slice($files, 1) as $path) {
            $this->assertFileExists($path);
        }
    }

    public function test_successful_creation_prunes_only_after_the_new_zip_is_complete(): void
    {
        $this->withSyntheticDump(function () {
            for ($day = 1; $day <= 6; $day++) {
                $this->archive('familyplan-auto-daily-2026100'.$day.'-120000.zip', 1000 + $day);
            }
            $name = (new BackupManager)->createBackup('auto-daily');
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open(storage_path('app/backups/'.$name)));
            $this->assertSame('-- synthetic database dump', $zip->getFromName('database.sql'));
            $this->assertNotFalse($zip->getFromName('metadata.json'));
            $zip->close();
            $this->assertCount(5, File::files(storage_path('app/backups')));
            $this->assertFileDoesNotExist(storage_path('app/backups/familyplan-auto-daily-20261001-120000.zip'));
            $this->assertFileDoesNotExist(storage_path('app/backups/familyplan-auto-daily-20261002-120000.zip'));
        });
    }

    public function test_failed_publication_preserves_existing_archives_and_removes_pending_zip(): void
    {
        $this->withSyntheticDump(function () {
            for ($day = 1; $day <= 6; $day++) {
                $this->archive('familyplan-auto-daily-2026100'.$day.'-120000.zip', 1000 + $day);
            }
            File::partialMock()->shouldReceive('move')->once()->andReturn(false);
            try {
                (new BackupManager)->createBackup('auto-daily');
                $this->fail('A failed publication must be reported.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('salvare il backup', $exception->getMessage());
            }
            $this->assertCount(6, File::files(storage_path('app/backups')));
            $this->assertCount(0, File::glob(storage_path('app/backups/*.pending-*')));
        });
    }

    public function test_scheduled_check_cleans_up_existing_backups_even_when_none_is_due(): void
    {
        SystemSetting::setValue('backup_frequency', 'daily');
        SystemSetting::setValue('backup_last_auto_at', now()->toIso8601String());
        for ($day = 1; $day <= 7; $day++) {
            $this->archive('familyplan-auto-daily-2026100'.$day.'-120000.zip', 1000 + $day);
        }
        $this->assertFalse((new BackupManager)->runScheduledBackup());
        $this->assertCount(5, File::files(storage_path('app/backups')));
    }

    public function test_failed_scheduled_dump_does_not_remove_previous_archives(): void
    {
        SystemSetting::setValue('backup_frequency', 'daily');
        $this->withSyntheticDump(function () {
            File::put($this->temporaryStorage.'/bin/mysqldump', "#!/bin/sh\nexit 1\n");
            for ($day = 1; $day <= 7; $day++) {
                $this->archive('familyplan-auto-daily-2026100'.$day.'-120000.zip', 1000 + $day);
            }
            try {
                (new BackupManager)->runScheduledBackup();
                $this->fail('The failed database dump must be reported.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('Backup database fallito', $exception->getMessage());
            }
            $this->assertCount(7, File::files(storage_path('app/backups')));
        });
    }

    private function withSyntheticDump(callable $test): void
    {
        $bin = $this->temporaryStorage.'/bin';
        File::ensureDirectoryExists($bin);
        File::put($bin.'/mysqldump', <<<'SH'
#!/bin/sh
for argument in "$@"; do
    case "$argument" in
        --result-file=*) printf '%s' '-- synthetic database dump' > "${argument#*=}" ;;
    esac
done
SH);
        chmod($bin.'/mysqldump', 0700);
        config(['database.connections.mysql' => ['host' => 'fixture', 'port' => 3306, 'username' => 'fixture', 'password' => '', 'database' => 'fixture']]);
        $originalPath = getenv('PATH');
        putenv('PATH='.$bin.PATH_SEPARATOR.$originalPath);
        try {
            $test();
        } finally {
            putenv('PATH='.$originalPath);
        }
    }

    public function test_missing_backup_directory_requires_no_cleanup(): void
    {
        File::deleteDirectory(storage_path('app/backups'));
        $this->assertSame(0, (new BackupManager)->pruneAutomaticBackups());
        $this->assertDirectoryDoesNotExist(storage_path('app/backups'));
    }
}
