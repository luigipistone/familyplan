<?php

namespace App\Support;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupManager
{
    private const AUTOMATIC_BACKUP_LIMIT = 5;

    public function listBackups(): array
    {
        $directory = $this->backupDirectory();
        if (! is_dir($directory)) {
            return [];
        }

        $files = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'zip')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'filename' => $file->getFilename(),
                'label' => Carbon::createFromTimestamp($file->getMTime())->format('d/m/Y H:i'),
                'size' => $this->humanSize($file->getSize()),
                'type' => str_contains($file->getFilename(), '-auto-') ? 'Automatico' : 'Manuale',
            ])
            ->values()
            ->all();

        return $files;
    }

    public function createBackup(string $mode = 'manual'): string
    {
        $this->ensureDirectory($this->backupDirectory());
        $tempDirectory = storage_path('app/backup-temp/'.uniqid('familyplan-', true));
        $this->ensureDirectory($tempDirectory);
        $pendingZipPath = null;

        try {
            $databaseDump = $tempDirectory.'/database.sql';
            $this->dumpDatabase($databaseDump);

            $metadata = [
                'created_at' => now()->toIso8601String(),
                'mode' => $mode,
                'app_url' => config('app.url'),
            ];
            File::put($tempDirectory.'/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $filename = sprintf('familyplan-%s-%s.zip', $mode, now()->format('Ymd-His'));
            $zipPath = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;
            // Only complete archives are visible to listing and retention.
            $pendingZipPath = $zipPath.'.pending-'.uniqid();
            $this->createZip($pendingZipPath, $tempDirectory);
            if (! File::move($pendingZipPath, $zipPath)) {
                throw new RuntimeException('Non sono riuscito a salvare il backup completato.');
            }
            $this->pruneAutomaticBackups();

            return $filename;
        } finally {
            if ($pendingZipPath !== null && is_file($pendingZipPath)) {
                File::delete($pendingZipPath);
            }
            File::deleteDirectory($tempDirectory);
        }
    }

    public function pruneAutomaticBackups(): int
    {
        $directory = $this->backupDirectory();
        if (! is_dir($directory)) {
            return 0;
        }

        $expired = collect(File::files($directory))
            ->filter(fn ($file) => ! $file->isLink()
                && preg_match('/^familyplan-auto-(daily|weekly|monthly)-[0-9]{8}-[0-9]{6}\.zip$/D', $file->getFilename()))
            ->sortByDesc(fn ($file) => [$file->getMTime(), substr($file->getFilename(), -19), $file->getFilename()])
            ->skip(self::AUTOMATIC_BACKUP_LIMIT);

        $deleted = 0;
        foreach ($expired as $file) {
            if (File::delete($file->getPathname())) {
                $deleted++;
            } elseif (is_file($file->getPathname())) {
                throw new RuntimeException('Non sono riuscito a eliminare un backup automatico precedente.');
            }
        }

        return $deleted;
    }

    public function restoreBackup(string $filename): void
    {
        $backupPath = $this->resolveBackupPath($filename);
        $tempDirectory = storage_path('app/backup-restore/'.uniqid('familyplan-', true));
        $this->ensureDirectory($tempDirectory);

        try {
            $zip = new ZipArchive;
            if ($zip->open($backupPath) !== true) {
                throw new RuntimeException('Non sono riuscito ad aprire il backup selezionato.');
            }

            $this->validateArchive($zip);
            $zip->extractTo($tempDirectory);
            $zip->close();

            $databaseDump = $tempDirectory.'/database.sql';
            if (! is_file($databaseDump)) {
                throw new RuntimeException('Il backup non contiene il dump del database.');
            }

            $this->restoreDatabase($databaseDump);

            $storagePublicSource = $tempDirectory.'/storage-public';
            $storagePublicTarget = storage_path('app/public');
            if (is_dir($storagePublicSource)) {
                $previousStorage = storage_path('app/backup-restore/storage-public-previous-'.uniqid());
                if (is_dir($storagePublicTarget) && ! File::moveDirectory($storagePublicTarget, $previousStorage)) {
                    throw new RuntimeException('Non sono riuscito a preparare lo storage per il ripristino.');
                }

                try {
                    if (! File::moveDirectory($storagePublicSource, $storagePublicTarget)) {
                        throw new RuntimeException('Ripristino dei file pubblici fallito.');
                    }
                    File::deleteDirectory($previousStorage);
                } catch (\Throwable $exception) {
                    File::deleteDirectory($storagePublicTarget);
                    if (is_dir($previousStorage)) {
                        File::moveDirectory($previousStorage, $storagePublicTarget);
                    }
                    throw $exception;
                }
            }
        } finally {
            File::deleteDirectory($tempDirectory);
        }
    }

    private function validateArchive(ZipArchive $zip): void
    {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            $normalized = str_replace('\\', '/', $name);
            if ($normalized === '' || str_starts_with($normalized, '/') || preg_match('#(^|/)\.\.(/|$)#', $normalized)) {
                throw new RuntimeException('Il backup contiene un percorso non valido.');
            }
            if ($normalized !== 'database.sql' && $normalized !== 'metadata.json' && ! str_starts_with($normalized, 'storage-public/')) {
                throw new RuntimeException('Il backup contiene file non previsti.');
            }
        }
    }

    public function runScheduledBackup(): bool
    {
        $frequency = SystemSetting::getValue('backup_frequency', 'manual');
        if (! in_array($frequency, ['daily', 'weekly', 'monthly'], true)) {
            $this->pruneAutomaticBackups();

            return false;
        }

        $lastRunAt = SystemSetting::getValue('backup_last_auto_at');
        $lastRun = $lastRunAt ? Carbon::parse($lastRunAt) : null;
        $now = now();

        $due = match ($frequency) {
            'daily' => ! $lastRun || ! $lastRun->isSameDay($now),
            'weekly' => ! $lastRun || $lastRun->startOfWeek()->notEqualTo($now->copy()->startOfWeek()),
            'monthly' => ! $lastRun || ! ($lastRun->year === $now->year && $lastRun->month === $now->month),
            default => false,
        };

        if (! $due) {
            $this->pruneAutomaticBackups();

            return false;
        }

        $this->createBackup('auto-'.$frequency);
        SystemSetting::setValue('backup_last_auto_at', $now->toIso8601String());

        return true;
    }

    private function dumpDatabase(string $targetPath): void
    {
        $config = config('database.connections.mysql');

        $process = new Process([
            'mysqldump',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--skip-lock-tables',
            '--result-file='.$targetPath,
            $config['database'],
        ], null, ['MYSQL_PWD' => (string) $config['password']]);

        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Backup database fallito: '.$process->getErrorOutput());
        }
    }

    private function restoreDatabase(string $sourcePath): void
    {
        $config = config('database.connections.mysql');

        $process = new Process([
            'mysql',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ], null, ['MYSQL_PWD' => (string) $config['password']]);

        $process->setTimeout(180);
        $process->setInput(File::get($sourcePath));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Ripristino database fallito: '.$process->getErrorOutput());
        }
    }

    private function createZip(string $zipPath, string $tempDirectory): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Non sono riuscito a creare l’archivio di backup.');
        }

        if (! $zip->addFile($tempDirectory.'/database.sql', 'database.sql')
            || ! $zip->addFile($tempDirectory.'/metadata.json', 'metadata.json')) {
            throw new RuntimeException('Non sono riuscito a inserire database e metadati nel backup.');
        }

        $storagePublic = storage_path('app/public');
        if (is_dir($storagePublic)) {
            $this->addDirectoryToZip($zip, $storagePublic, 'storage-public');
        }

        if (! $zip->close()) {
            throw new RuntimeException('Non sono riuscito a completare l’archivio di backup.');
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relative = $prefix.'/'.ltrim(str_replace($directory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $added = $file->isDir()
                ? $zip->addEmptyDir($relative)
                : $zip->addFile($file->getPathname(), $relative);
            if (! $added) {
                throw new RuntimeException('Non sono riuscito a inserire tutti i file nel backup.');
            }
        }
    }

    private function resolveBackupPath(string $filename): string
    {
        $basename = basename($filename);
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$basename;

        if (! is_file($path)) {
            throw new RuntimeException('Backup non trovato.');
        }

        return $path;
    }

    private function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            File::ensureDirectoryExists($directory);
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, $unit === 0 ? 0 : 1, ',', '.').' '.$units[$unit];
    }
}
