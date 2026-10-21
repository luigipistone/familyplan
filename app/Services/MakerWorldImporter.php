<?php

namespace App\Services;

use App\Contracts\ModelImporter;
use DOMDocument;
use Illuminate\Support\Facades\Http;

class MakerWorldImporter implements ModelImporter
{
    public function import(string $url): array
    {
        $normalized = $this->validatedUrl($url);
        if (! $normalized) {
            return $this->result('invalid_url', $url);
        }
        preg_match('~/models/(?:[^/?#]*-)?(\d+)~i', $normalized, $match);
        $externalId = $match[1] ?? null;

        try {
            $response = Http::timeout(10)->connectTimeout(5)->withHeaders([
                'User-Agent' => 'FamilyPlan/1.0 (+https://familyplan.lu3g.com)',
                'Accept' => 'text/html,application/xhtml+xml',
            ])->get($normalized);
        } catch (\Throwable) {
            return $this->result('temporary_error', $normalized, $externalId);
        }

        if (in_array($response->status(), [401, 403, 429], true)) {
            return $this->result('access_denied', $normalized, $externalId);
        }
        if ($response->status() === 404) {
            return $this->result('not_found', $normalized, $externalId);
        }
        if (! $response->successful()) {
            return $this->result('temporary_error', $normalized, $externalId);
        }

        $metadata = $this->publicMetadata($response->body());
        $status = array_filter($metadata) ? 'partial_success' : 'unavailable';

        return array_merge($this->result($status, $normalized, $externalId), $metadata, ['rawMetadata' => []]);
    }

    private function validatedUrl(string $url): ?string
    {
        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || ! in_array($host, ['makerworld.com', 'www.makerworld.com'], true)) {
            return null;
        }

        return $url;
    }

    private function publicMetadata(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $meta = [];
        foreach ($document->getElementsByTagName('meta') as $node) {
            $key = strtolower($node->getAttribute('property') ?: $node->getAttribute('name'));
            if ($key) {
                $meta[$key] = trim($node->getAttribute('content'));
            }
        }

        return [
            'title' => $meta['og:title'] ?? $meta['twitter:title'] ?? null,
            'author' => $meta['author'] ?? null,
            'thumbnailUrl' => $this->safeImageUrl($meta['og:image'] ?? $meta['twitter:image'] ?? null),
            'licenseName' => null, 'commercialUseStatus' => null, 'profiles' => [],
            'estimatedPrintTimeMinutes' => null, 'estimatedModelGrams' => null,
            'estimatedSupportGrams' => null, 'estimatedPurgeGrams' => null,
            'material' => null, 'colors' => null, 'printerProfile' => null,
            'layerHeight' => null, 'plates' => null,
        ];
    }

    private function safeImageUrl(?string $url): ?string
    {
        return $url && str_starts_with($url, 'https://') ? $url : null;
    }

    private function result(string $status, string $url, ?string $externalId = null): array
    {
        return ['status' => $status, 'source' => 'makerworld', 'sourceUrl' => $url, 'externalId' => $externalId];
    }
}
