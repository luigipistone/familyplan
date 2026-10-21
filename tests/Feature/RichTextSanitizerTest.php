<?php

namespace Tests\Feature;

use App\Support\RichTextSanitizer;
use Tests\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_it_removes_scripts_and_unsafe_attributes(): void
    {
        $clean = app(RichTextSanitizer::class)->clean('<p style="position:fixed" onclick="alert(1)">Test<script>alert(2)</script></p>');

        $this->assertSame('<p>Testalert(2)</p>', $clean);
    }

    public function test_it_keeps_only_safe_links(): void
    {
        $clean = app(RichTextSanitizer::class)->clean('<a href="javascript:alert(1)" target="_blank">No</a><a href="https://example.com">Si</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('https://example.com', $clean);
        $this->assertStringContainsString('noopener noreferrer', $clean);
    }
}
