<?php

namespace App\Services;

use ZipArchive;

class ThreeMfImporter
{
    public function import(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \InvalidArgumentException('Il file 3MF non è valido o è danneggiato.');
        }
        $texts = [];
        $totalSize = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = (string) ($stat['name'] ?? '');
            $size = (int) ($stat['size'] ?? 0);
            $totalSize += $size;
            if ($totalSize > 250 * 1024 * 1024) {
                $zip->close();
                throw new \InvalidArgumentException('Il contenuto del file 3MF è troppo grande.');
            }
            if ($size <= 2 * 1024 * 1024 && preg_match('/(?:\.config|\.json|\.xml|\.model)$/i', $name)) {
                $content = $zip->getFromIndex($i);
                if (is_string($content)) $texts[] = $content;
            }
        }
        $zip->close();
        $all = implode("\n", $texts);
        $grams = $this->sumNumeric($all, ['used_g']);
        $seconds = $this->firstNumeric($all, ['prediction', 'print_time', 'estimated_time']);

        return [
            'status' => ($grams !== null || $seconds !== null) ? 'success' : 'partial',
            'title' => $this->firstValue($all, ['title', 'name']),
            'estimatedModelGrams' => $grams,
            'estimatedPrintTimeMinutes' => $seconds !== null ? round($seconds / 60, 2) : null,
            'material' => $this->firstValue($all, ['type', 'filament_type', 'material']),
        ];
    }

    private function values(string $text, array $keys): array
    {
        $keys = implode('|', array_map(fn ($key) => preg_quote($key, '/'), $keys));
        preg_match_all('/(?:key|name)=["\'](?:'.$keys.')["\'][^>]*value=["\']([^"\']+)|(?:'.$keys.')=["\']([^"\']+)/i', $text, $matches, PREG_SET_ORDER);
        return array_values(array_filter(array_map(fn ($match) => $match[1] ?: $match[2], $matches), fn ($value) => $value !== ''));
    }

    private function sumNumeric(string $text, array $keys): ?float
    {
        $values = array_values(array_filter(array_map(fn ($value) => is_numeric($value) ? (float) $value : null, $this->values($text, $keys)), fn ($value) => $value !== null));
        return $values ? round(array_sum($values), 2) : null;
    }

    private function firstNumeric(string $text, array $keys): ?float
    {
        $value = $this->firstValue($text, $keys);
        return is_numeric($value) ? (float) $value : null;
    }

    private function firstValue(string $text, array $keys): ?string
    {
        return $this->values($text, $keys)[0] ?? null;
    }
}
