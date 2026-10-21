<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SoundboardSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SoundboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_source_normalizes_only_valid_clips(): void
    {
        $clips = app(SoundboardSource::class)->clips();

        $this->assertCount(32, $clips);
        $this->assertSame('/media/soundboard/audio/ingola.mp3', $clips[0]['audio_url']);
        $this->assertSame('/soundboard-media/ingola.webp', $clips[0]['image_url']);
    }

    public function test_authenticated_user_can_open_soundboard(): void
    {
        $user = User::create([
            'name' => 'Soundboard Test',
            'phone' => '3888888888',
            'password' => 'password-di-test-123',
            'role' => 'familiare',
            'category' => 'altro',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('soundboard'))
            ->assertOk()
            ->assertSee('In Gola')
            ->assertSee('data-soundboard-clip', false);
    }
}
