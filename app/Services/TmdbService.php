<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TmdbService
{
    public function configured(): bool
    {
        return filled(config('movies.tmdb.api_key'));
    }

    public function discover(string $section = 'popular', string $type = 'movie'): array
    {
        $allowed = [
            'movie' => ['popular' => 'movie/popular', 'now_playing' => 'movie/now_playing', 'upcoming' => 'movie/upcoming'],
            'tv' => ['popular' => 'tv/popular', 'on_the_air' => 'tv/on_the_air'],
        ];
        $endpoint = $allowed[$type][$section] ?? $allowed['movie']['popular'];

        return Cache::remember("movies.tmdb.{$type}.{$section}.it", 3600, fn () => $this->request($endpoint)['results'] ?? []);
    }

    public function search(string $query, string $type = 'multi'): array
    {
        $endpoint = in_array($type, ['movie', 'tv'], true) ? "search/{$type}" : 'search/multi';
        return $this->request($endpoint, ['query' => $query, 'include_adult' => 'false'])['results'] ?? [];
    }

    public function title(int $id, string $type = 'movie'): array
    {
        $type = $type === 'tv' ? 'tv' : 'movie';
        return Cache::remember("movies.tmdb.detail.{$type}.{$id}.it", 86400, fn () => $this->request("{$type}/{$id}", [
            'append_to_response' => 'credits,videos,watch/providers',
        ]));
    }

    public function recommendations(int $id, string $type = 'movie'): array
    {
        $type = $type === 'tv' ? 'tv' : 'movie';
        return Cache::remember("movies.tmdb.recommendations.{$type}.{$id}.it", 3600,
            fn () => $this->request("{$type}/{$id}/recommendations")['results'] ?? []);
    }

    public function trending(string $type = 'movie'): array
    {
        $type = $type === 'tv' ? 'tv' : 'movie';
        return Cache::remember("movies.tmdb.trending.{$type}.week.it", 3600,
            fn () => $this->request("trending/{$type}/week")['results'] ?? []);
    }

    public function genres(string $type = 'movie'): array
    {
        $type = $type === 'tv' ? 'tv' : 'movie';
        return Cache::remember("movies.tmdb.genres.{$type}.it", 86400,
            fn () => $this->request("genre/{$type}/list")['genres'] ?? []);
    }

    public function discoverGenre(int $genreId, int $page = 1): array
    {
        return Cache::remember("movies.tmdb.genre.movie.{$genreId}.{$page}.it", 3600, fn () => $this->request('discover/movie', [
            'with_genres' => $genreId,
            'sort_by' => 'vote_average.desc',
            'vote_count.gte' => 250,
            'primary_release_date.lte' => now()->toDateString(),
            'page' => max(1, min($page, 10)),
        ])['results'] ?? []);
    }

    public function normalizeList(array $movies, ?string $forcedType = null): array
    {
        return collect($movies)->filter(fn (array $movie) => in_array($forcedType ?? ($movie['media_type'] ?? 'movie'), ['movie', 'tv'], true))->take(20)->map(fn (array $movie) => [
            'id' => (int) ($movie['id'] ?? 0),
            'media_type' => $forcedType ?? (($movie['media_type'] ?? 'movie') === 'tv' ? 'tv' : 'movie'),
            'title' => (string) ($movie['title'] ?? $movie['name'] ?? 'Titolo non disponibile'),
            'year' => substr((string) ($movie['release_date'] ?? $movie['first_air_date'] ?? ''), 0, 4),
            'rating' => round((float) ($movie['vote_average'] ?? 0), 1),
            'poster' => $this->image($movie['poster_path'] ?? null, 'w500'),
            'backdrop' => $this->image($movie['backdrop_path'] ?? null, 'w780'),
        ])->filter(fn (array $movie) => $movie['id'] > 0)->values()->all();
    }

    public function normalizeTitle(array $movie, string $type = 'movie'): array
    {
        $trailer = collect($movie['videos']['results'] ?? [])->first(fn ($video) =>
            ($video['site'] ?? '') === 'YouTube' && ($video['type'] ?? '') === 'Trailer'
        );

        $providers = $movie['watch/providers']['results']['IT']['flatrate'] ?? [];
        return [
            'id' => (int) $movie['id'],
            'media_type' => $type === 'tv' ? 'tv' : 'movie',
            'title' => (string) ($movie['title'] ?? $movie['name'] ?? ''),
            'tagline' => (string) ($movie['tagline'] ?? ''),
            'overview' => (string) ($movie['overview'] ?? 'Trama non disponibile.'),
            'year' => substr((string) ($movie['release_date'] ?? $movie['first_air_date'] ?? ''), 0, 4),
            'runtime' => (int) ($movie['runtime'] ?? 0),
            'seasons' => (int) ($movie['number_of_seasons'] ?? 0),
            'rating' => round((float) ($movie['vote_average'] ?? 0), 1),
            'genres' => collect($movie['genres'] ?? [])->pluck('name')->values()->all(),
            'genre_ids' => collect($movie['genres'] ?? [])->pluck('id')->filter()->values()->all(),
            'cast' => collect($movie['credits']['cast'] ?? [])->take(6)->pluck('name')->values()->all(),
            'poster' => $this->image($movie['poster_path'] ?? null, 'w500'),
            'backdrop' => $this->image($movie['backdrop_path'] ?? null, 'w1280'),
            'trailer_url' => $trailer ? 'https://www.youtube.com/watch?v='.rawurlencode($trailer['key']) : null,
            'providers' => collect($providers)->map(fn ($provider) => [
                'name' => (string) ($provider['provider_name'] ?? ''),
                'logo' => $this->image($provider['logo_path'] ?? null, 'w92'),
            ])->unique('name')->values()->all(),
        ];
    }

    private function request(string $endpoint, array $parameters = []): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Chiave TMDB non configurata.');
        }

        $response = Http::acceptJson()->timeout(10)->connectTimeout(4)->retry(2, 200)->get(
            rtrim(config('movies.tmdb.base_url'), '/').'/'.$endpoint,
            array_merge($parameters, [
                'api_key' => config('movies.tmdb.api_key'),
                'language' => 'it-IT',
                'region' => 'IT',
            ])
        );

        if (! $response->successful()) {
            throw new RuntimeException('TMDB non disponibile in questo momento.');
        }

        return $response->json();
    }

    private function image(?string $path, string $size): ?string
    {
        return $path ? rtrim(config('movies.tmdb.image_url'), '/').'/'.$size.$path : null;
    }
}
