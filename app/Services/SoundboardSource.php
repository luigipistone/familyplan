<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class SoundboardSource
{
    public function clips(): array
    {
        $manifest = config('soundboard.local_manifest');
        if (! is_string($manifest) || ! is_file($manifest)) {
            throw new RuntimeException('Local soundboard manifest unavailable.');
        }

        $cacheKey = 'soundboard.local.'.sha1($manifest.(string) filemtime($manifest));

        return Cache::remember($cacheKey, config('soundboard.cache_seconds', 86400), function () use ($manifest) {
            $payload = json_decode(file_get_contents($manifest), true);
            if (! is_array($payload)) {
                throw new RuntimeException('Invalid local soundboard manifest.');
            }

            return collect($payload)
                ->take(200)
                ->map(fn ($clip) => $this->normalize($clip))
                ->filter()
                ->values()
                ->all();
        });
    }

    private function normalize(mixed $clip): ?array
    {
        if (! is_array($clip)) {
            return null;
        }

        $id = trim((string) ($clip['id'] ?? ''));
        $name = trim((string) ($clip['name'] ?? ''));
        $category = trim((string) ($clip['category'] ?? 'Altro'));
        $audioUrl = $this->assetUrl($clip['file'] ?? null);
        $imageUrl = $this->assetUrl($clip['image'] ?? null);

        if ($id === '' || $name === '' || $audioUrl === null) {
            return null;
        }

        return [
            'id' => mb_substr($id, 0, 100),
            'name' => mb_substr($name, 0, 160),
            'category' => mb_substr($category !== '' ? $category : 'Altro', 0, 80),
            'audio_url' => $audioUrl,
            'image_url' => $imageUrl,
        ];
    }

    private function assetUrl(mixed $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '//')) {
            return null;
        }

        if (preg_match('#^/media/soundboard/(audio|images)/[a-z0-9-]+\.(mp3|wav|webp)$#i', $path)) {
            return $path;
        }

        if (preg_match('#^/soundboard-media/[a-z0-9-]+\.webp$#i', $path)) {
            return $path;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $host = parse_url($path, PHP_URL_HOST);
            return $host === parse_url(config('soundboard.base_url'), PHP_URL_HOST) ? $path : null;
        }

        return rtrim(config('soundboard.base_url'), '/').'/'.ltrim($path, '/');
    }
}
