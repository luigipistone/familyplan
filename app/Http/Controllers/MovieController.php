<?php

namespace App\Http\Controllers;

use App\Models\MovieEntry;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class MovieController extends Controller
{
    public function index(Request $request, TmdbService $tmdb)
    {
        $sections = ['nowPlaying' => [], 'suggestedMovies' => [], 'suggestedSeries' => []];
        $error = null;
        $genres = [];

        if ($tmdb->configured()) {
            try {
                $nowPlaying = $tmdb->discover('now_playing');
                $upcoming = $tmdb->discover('upcoming');
                $sections['nowPlaying'] = $tmdb->normalizeList($nowPlaying, 'movie');
                $genres = $tmdb->genres('movie');
                $entries = $request->user()->movieEntries()->orderByDesc('is_favorite')->latest()->get();
                $cinemaIds = collect($nowPlaying)->merge($upcoming)->pluck('id')->map(fn ($id) => (int) $id)->all();
                $sections['suggestedMovies'] = $tmdb->normalizeList($this->suggestTitles($entries, 'movie', $tmdb, $cinemaIds), 'movie');
                $sections['suggestedSeries'] = $tmdb->normalizeList($this->suggestTitles($entries, 'tv', $tmdb), 'tv');
            } catch (Throwable) {
                $error = 'I titoli non sono disponibili in questo momento. Riprova tra poco.';
            }
        }

        $mine = $request->user()->movieEntries()->latest()->get()->map(fn ($entry) => $this->entryPayload($entry));
        $shared = MovieEntry::with('user:id,name')->where('is_shared', true)
            ->where('user_id', '!=', $request->user()->id)->latest()->get()->map(fn ($entry) => $this->entryPayload($entry));

        return view('movies.index', compact('sections', 'error', 'mine', 'shared', 'genres') + ['configured' => $tmdb->configured()]);
    }

    public function search(Request $request, TmdbService $tmdb): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:100'],
            'type' => ['nullable', Rule::in(['multi', 'movie', 'tv'])],
        ]);
        try {
            $type = $data['type'] ?? 'multi';
            $forcedType = in_array($type, ['movie', 'tv'], true) ? $type : null;
            return response()->json(['movies' => $tmdb->normalizeList($tmdb->search($data['query'], $type), $forcedType)]);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function show(Request $request, int $id, TmdbService $tmdb): JsonResponse
    {
        $data = $request->validate(['type' => ['nullable', Rule::in(['movie', 'tv'])]]);
        $type = $data['type'] ?? 'movie';
        try {
            $movie = $tmdb->normalizeTitle($tmdb->title($id, $type), $type);
            $entry = $request->user()->movieEntries()->where(['tmdb_id' => $id, 'media_type' => $type])->first();
            return response()->json(['movie' => $movie, 'entry' => $entry ? $this->entryPayload($entry) : null]);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function genre(Request $request, TmdbService $tmdb): JsonResponse
    {
        $data = $request->validate(['genre' => ['required', 'integer', 'min:1']]);
        try {
            $saved = $request->user()->movieEntries()->where('media_type', 'movie')->pluck('tmdb_id');
            $excluded = collect($tmdb->discover('now_playing'))->merge($tmdb->discover('upcoming'))->pluck('id')->merge($saved)->map(fn ($id) => (int) $id);
            $movies = collect($tmdb->discoverGenre((int) $data['genre'], random_int(1, 3)))
                ->reject(fn ($movie) => $excluded->contains((int) ($movie['id'] ?? 0)))
                ->shuffle()->all();
            return response()->json(['movies' => $tmdb->normalizeList($movies, 'movie')]);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function save(Request $request, int $id, TmdbService $tmdb): JsonResponse
    {
        $data = $request->validate([
            'media_type' => ['required', Rule::in(['movie', 'tv'])],
            'status' => ['required', Rule::in(['watchlist', 'watched'])],
            'is_favorite' => ['required', 'boolean'],
            'is_shared' => ['required', 'boolean'],
            'rating' => ['nullable', 'integer', 'between:1,10'],
        ]);
        try {
            $metadata = $tmdb->normalizeTitle($tmdb->title($id, $data['media_type']), $data['media_type']);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
        $entry = $request->user()->movieEntries()->updateOrCreate(
            ['tmdb_id' => $id, 'media_type' => $data['media_type']],
            $data + ['metadata' => $metadata]
        );
        return response()->json(['ok' => true, 'message' => 'La tua lista è stata aggiornata.', 'entry' => $this->entryPayload($entry)]);
    }

    public function destroy(Request $request, MovieEntry $entry): JsonResponse
    {
        abort_unless($entry->user_id === $request->user()->id, 403);
        $entry->delete();
        return response()->json(['ok' => true, 'message' => 'Titolo rimosso dalla lista.']);
    }

    private function entryPayload(MovieEntry $entry): array
    {
        return array_merge($entry->metadata ?? [], [
            'entry_id' => $entry->id, 'status' => $entry->status,
            'is_favorite' => $entry->is_favorite, 'is_shared' => $entry->is_shared,
            'user_rating' => $entry->rating,
            'owner' => $entry->relationLoaded('user') ? $entry->user?->name : null,
        ]);
    }

    private function suggestTitles($entries, string $type, TmdbService $tmdb, array $excludedIds = []): array
    {
        $typed = $entries->where('media_type', $type);
        $savedIds = $typed->pluck('tmdb_id')->map(fn ($id) => (int) $id)->all();
        $excluded = array_flip(array_merge($excludedIds, $savedIds));
        $genreWeights = [];

        foreach ($typed as $entry) {
            $weight = $entry->rating === null ? 1.0 : ($entry->rating >= 8 ? 3.0 : ($entry->rating >= 6 ? 1.0 : -3.0));
            if ($entry->is_favorite) $weight += 2.0;
            foreach (($entry->metadata['genre_ids'] ?? []) as $genreId) {
                $genreWeights[$genreId] = ($genreWeights[$genreId] ?? 0) + $weight;
            }
        }

        $seeds = $typed->filter(fn ($entry) => $entry->is_favorite || $entry->rating === null || $entry->rating >= 6)
            ->sortByDesc(fn ($entry) => ($entry->is_favorite ? 20 : 0) + ($entry->rating ?? 6))->take(4);
        $candidates = collect();
        foreach ($seeds as $seed) {
            $seedWeight = ($seed->is_favorite ? 3 : 1) + (($seed->rating ?? 6) / 10);
            foreach ($tmdb->recommendations($seed->tmdb_id, $type) as $rank => $candidate) {
                $id = (int) ($candidate['id'] ?? 0);
                if (! $id || isset($excluded[$id])) continue;
                if ($type === 'movie' && filled($candidate['release_date'] ?? null) && $candidate['release_date'] > now()->toDateString()) continue;
                $score = (20 - min($rank, 19)) * $seedWeight;
                foreach (($candidate['genre_ids'] ?? []) as $genreId) $score += ($genreWeights[$genreId] ?? 0) * 2;
                $existing = $candidates->get($id);
                $candidate['_score'] = $score + ($existing['_score'] ?? 0) + random_int(0, 100) / 100;
                $candidates->put($id, $candidate);
            }
        }

        if ($candidates->isEmpty()) {
            $candidates = collect($tmdb->trending($type))->reject(function ($candidate) use ($excluded, $type) {
                if (isset($excluded[(int) ($candidate['id'] ?? 0)])) return true;
                return $type === 'movie' && filled($candidate['release_date'] ?? null) && $candidate['release_date'] > now()->toDateString();
            });
        }

        return $candidates->sortByDesc(fn ($candidate) => $candidate['_score'] ?? random_int(0, 1000))->take(20)->values()->all();
    }
}
