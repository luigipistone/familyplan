<?php

namespace Tests\Unit;

use App\Services\ThreeMfImporter;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class ThreeMfImporterTest extends TestCase
{
    public function test_it_reads_bambu_slice_weight_and_time(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'three-mf-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('Metadata/slice_info.config', <<<'XML'
            <config><plate>
              <metadata key="prediction" value="6120"/>
              <metadata key="title" value="Gancio doccia"/>
              <filament type="PLA" used_g="18.4"/>
              <filament type="PLA" used_g="3.6"/>
            </plate></config>
            XML);
        $zip->close();

        $result = (new ThreeMfImporter)->import($path);
        unlink($path);

        $this->assertSame(22.0, $result['estimatedModelGrams']);
        $this->assertSame(102.0, $result['estimatedPrintTimeMinutes']);
        $this->assertSame('Gancio doccia', $result['title']);
        $this->assertSame('PLA', $result['material']);
    }
}
