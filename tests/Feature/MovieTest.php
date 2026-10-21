<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Film Test '.uniqid(),
            'phone' => '37'.random_int(10000000, 99999999),
            'password' => 'password-di-test-123',
            'role' => 'familiare',
            'category' => 'altro',
            'is_active' => true,
        ]);
    }

    public function test_movies_page_explains_when_api_is_not_configured(): void
    {
        config(['movies.tmdb.api_key' => null]);

        $this->actingAs($this->user())->get(route('movies'))
            ->assertOk()
            ->assertSee('chiave gratuita TMDB');
    }

    public function test_movies_page_renders_tmdb_sections(): void
    {
        config(['movies.tmdb.api_key' => 'test-key']);
        Http::fake(['api.themoviedb.org/*' => Http::response([
            'results' => [['id' => 12, 'title' => 'Film prova', 'release_date' => '2026-08-01', 'vote_average' => 7.4, 'poster_path' => '/poster.jpg']],
        ])]);

        $this->actingAs($this->user())->get(route('movies'))
            ->assertOk()
            ->assertSee('Film prova')
            ->assertSee('data-movie-coverflow', false);
    }

    public function test_movie_search_returns_normalized_results(): void
    {
        config(['movies.tmdb.api_key' => 'test-key']);
        Http::fake(['api.themoviedb.org/*' => Http::response([
            'results' => [['id' => 45, 'title' => 'Ricerca prova', 'release_date' => '2025-01-01']],
        ])]);

        $this->actingAs($this->user())->getJson(route('movies.search', ['query' => 'prova']))
            ->assertOk()
            ->assertJsonPath('movies.0.title', 'Ricerca prova');
    }

    public function test_tv_search_keeps_series_media_type(): void
    {
        config(['movies.tmdb.api_key' => 'test-key']);
        Http::fake(['api.themoviedb.org/*' => Http::response([
            'results' => [['id' => 46, 'name' => 'Serie prova', 'first_air_date' => '2026-01-01']],
        ])]);

        $this->actingAs($this->user())->getJson(route('movies.search', ['query' => 'serie', 'type' => 'tv']))
            ->assertOk()->assertJsonPath('movies.0.media_type', 'tv');
    }

    public function test_movie_detail_is_normalized(): void
    {
        config(['movies.tmdb.api_key' => 'test-key']);
        Http::fake(['api.themoviedb.org/*' => Http::response([
            'id' => 45, 'title' => 'Dettaglio prova', 'runtime' => 105, 'genres' => [['name' => 'Commedia']],
            'credits' => ['cast' => [['name' => 'Mario Rossi']]], 'videos' => ['results' => []],
        ])]);

        $this->actingAs($this->user())->getJson(route('movies.show', 45))
            ->assertOk()
            ->assertJsonPath('movie.runtime', 105)
            ->assertJsonPath('movie.genres.0', 'Commedia');
    }

    public function test_user_can_save_and_share_a_watched_title(): void
    {
        config(['movies.tmdb.api_key' => 'test-key']);
        Http::fake(['api.themoviedb.org/*' => Http::response(['id' => 80, 'title' => 'Titolo salvato'])]);
        $user = $this->user();

        $this->actingAs($user)->putJson(route('movies.save', 80), [
            'media_type' => 'movie', 'status' => 'watched', 'is_favorite' => true,
            'is_shared' => true, 'rating' => 9,
        ])->assertOk()->assertJsonPath('entry.user_rating', 9);

        $this->assertDatabaseHas('movie_entries', [
            'user_id' => $user->id, 'tmdb_id' => 80, 'status' => 'watched',
            'is_favorite' => true, 'is_shared' => true, 'rating' => 9,
        ]);
    }

    public function test_user_cannot_remove_another_users_entry(): void
    {
        $owner = $this->user();
        $entry = $owner->movieEntries()->create([
            'tmdb_id' => 90, 'media_type' => 'tv', 'status' => 'watchlist',
            'metadata' => ['id' => 90, 'title' => 'Serie privata'],
        ]);

        $this->actingAs($this->user())->deleteJson(route('movies.destroy', $entry))->assertForbidden();
    }
}
