<?php

namespace Tests\Feature;

use App\Services\MakerWorldImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MakerWorldImporterTest extends TestCase
{
    public function test_invalid_domain_is_rejected_without_request(): void
    {
        Http::fake();
        $result = app(MakerWorldImporter::class)->import('https://example.com/models/123');
        $this->assertSame('invalid_url', $result['status']);
        Http::assertNothingSent();
    }

    public function test_public_metadata_returns_partial_success_without_inventing_fields(): void
    {
        Http::fake(['makerworld.com/*' => Http::response('<html><head><meta property="og:title" content="Lampada"><meta property="og:image" content="https://makerworld.com/image.jpg"></head></html>')]);
        $result = app(MakerWorldImporter::class)->import('https://makerworld.com/it/models/12345-lampada');
        $this->assertSame('partial_success', $result['status']);
        $this->assertSame('12345', $result['externalId']);
        $this->assertSame('Lampada', $result['title']);
        $this->assertNull($result['licenseName']);
    }

    public function test_unavailable_page_has_manual_fallback_status(): void
    {
        Http::fake(['makerworld.com/*' => Http::response('<html><body></body></html>')]);
        $this->assertSame('unavailable', app(MakerWorldImporter::class)->import('https://makerworld.com/it/models/999')['status']);
    }
}
