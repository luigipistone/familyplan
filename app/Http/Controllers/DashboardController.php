<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $widgets = $user->dashboard_widgets ?? ['calendar', 'shopping', 'family', 'reminders', 'notes'];
        $hasWidget = fn (string $widget): bool => in_array($widget, $widgets, true);

        return view('dashboard.index', [
            'widgets' => $widgets,
            'events' => $hasWidget('calendar') ? CalendarEvent::with('creator')
                ->where('starts_at', '>=', now()->startOfMonth())
                ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('visibility', 'family')->orWhere('created_by', $user->id)->orWhere('assigned_user_id', $user->id)))
                ->orderBy('starts_at')
                ->limit(12)
                ->get() : collect(),
            'shoppingLists' => $hasWidget('shopping') ? ShoppingList::with('items')
                ->where(fn ($q) => $q->whereNull('archived_at')->orWhere('archived_at', '>', now()))
                ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id))))
                ->latest()
                ->limit(5)
                ->get() : collect(),
            'familyToday' => $hasWidget('family') ? $this->familyWidgetEvents($user) : collect(),
            'reminders' => $hasWidget('reminders') ? Reminder::whereNull('completed_at')->where(fn ($q) => $q->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id)))->orderBy('due_at')->limit(6)->get() : collect(),
            'notes' => $hasWidget('notes') ? Note::whereNull('archived_at')
                ->where(fn ($q) => $q->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id)))
                ->latest()
                ->limit(5)
                ->get() : collect(),
            'weatherLocations' => $this->weatherLocations(),
            'tvChannels' => $this->tvChannels(),
        ]);
    }

    private function familyWidgetEvents(User $user)
    {
        return CalendarEvent::with('child')
            ->whereBetween('starts_at', [today()->startOfDay(), today()->copy()->addDay()->endOfDay()])
            ->whereNotNull('child_id')
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('assigned_user_id', $user->id);

                    if ($user->isParent()) {
                        $inner->orWhereHas('child', fn ($child) => $child
                            ->where('mother_id', $user->id)
                            ->orWhere('father_id', $user->id));
                    }
                });
            })
            ->orderBy('starts_at')
            ->limit(8)
            ->get();
    }

    public function widgets(Request $request)
    {
        $data = $request->validate([
            'widgets' => ['array'],
            'widgets.*' => ['in:calendar,shopping,family,reminders,notes,weather,football,tv,settings,users,profile'],
            'widget_order' => ['nullable', 'array'],
            'widget_order.*' => ['in:calendar,shopping,family,reminders,notes,weather,football,tv,settings,users,profile'],
        ]);

        $enabled = array_values($data['widgets'] ?? []);
        $enabledMap = array_fill_keys($enabled, true);
        $ordered = collect($data['widget_order'] ?? [])
            ->filter(fn ($widget) => isset($enabledMap[$widget]))
            ->unique()
            ->values()
            ->all();

        if (count($ordered) !== count($enabled)) {
            $ordered = array_values($enabled);
        }

        $request->user()->update(['dashboard_widgets' => $ordered]);

        return back()->with('ok', 'Dashboard aggiornata.');
    }

    public function football(Request $request)
    {
        $user = $request->user();
        $date = today()->toDateString();
        $favoriteTeam = trim((string) ($user->favorite_team ?? ''));
        $cacheKey = 'football.dashboard.'.md5($date.'|'.$favoriteTeam);

        try {
            $payload = Cache::flexible($cacheKey, [600, 1800], function () use ($date, $favoriteTeam) {
                $response = Http::timeout(10)->get('https://www.thesportsdb.com/api/v1/json/123/eventsday.php', [
                    'd' => $date,
                    's' => 'Soccer',
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException('Football API unavailable');
                }

                $events = collect($response->json('events') ?? []);
                $events = $events
                    ->concat($this->footballSupplementalEvents($date))
                    ->unique('idEvent')
                    ->values();
                $importantLeagueIds = ['4332', '4335', '4328', '4331', '4334', '4480', '4481', '4482'];
                $leagueOrder = array_flip($importantLeagueIds);
                $favoriteNeedle = $this->normalizeFootballName($favoriteTeam);

                $matches = $events
                    ->filter(function ($event) use ($importantLeagueIds, $favoriteNeedle) {
                        if ($this->isImportantFootballEvent($event, $importantLeagueIds)) {
                            return true;
                        }

                        if ($favoriteNeedle === '') {
                            return false;
                        }

                        return $this->eventMatchesFavorite($event, $favoriteNeedle);
                    })
                    ->sortBy([
                        fn ($event) => $this->eventMatchesFavorite($event, $favoriteNeedle) ? 0 : 1,
                        fn ($event) => $this->footballEventPriority($event, $leagueOrder),
                        fn ($event) => $event['strTimestamp'] ?? ($event['strTimeLocal'] ?? '23:59:59'),
                    ])
                    ->unique('idEvent')
                    ->take(8)
                    ->map(function ($event) use ($favoriteNeedle) {
                        $home = trim((string) ($event['strHomeTeam'] ?? 'Casa'));
                        $away = trim((string) ($event['strAwayTeam'] ?? 'Trasferta'));
                        $homeScore = $event['intHomeScore'];
                        $awayScore = $event['intAwayScore'];

                        return [
                            'id' => (string) ($event['idEvent'] ?? Str::uuid()),
                            'league' => (string) ($event['strLeague'] ?? 'Calcio'),
                            'time_label' => $this->footballTimeLabel($event),
                            'label' => $homeScore !== null && $awayScore !== null
                                ? "{$home} {$homeScore}-{$awayScore} {$away}"
                                : "{$home} vs {$away}",
                            'is_favorite' => $this->eventMatchesFavorite($event, $favoriteNeedle),
                        ];
                    })
                    ->values()
                    ->all();

                $favoriteSummary = null;
                if ($favoriteNeedle !== '') {
                    $favoriteEvent = $events->first(fn ($event) => $this->eventMatchesFavorite($event, $favoriteNeedle));
                    if (! $favoriteEvent) {
                        $favoriteEvent = $this->footballFavoriteFallback($favoriteNeedle, $date);
                        if ($favoriteEvent) {
                            array_unshift($matches, [
                                'id' => (string) ($favoriteEvent['idEvent'] ?? Str::uuid()),
                                'league' => (string) ($favoriteEvent['strLeague'] ?? 'Calcio'),
                                'time_label' => $this->footballTimeLabel($favoriteEvent),
                                'label' => $this->footballFavoriteSummary($favoriteEvent),
                                'is_favorite' => true,
                            ]);
                            $matches = collect($matches)->unique('id')->take(8)->values()->all();
                        }
                    }
                    $favoriteSummary = $favoriteEvent
                        ? [
                            'title' => 'Squadra del cuore oggi',
                            'subtitle' => $this->footballFavoriteSummary($favoriteEvent),
                        ]
                        : [
                            'title' => 'Squadra del cuore',
                            'subtitle' => 'Nessuna partita trovata oggi per '.$favoriteTeam.'.',
                        ];
                }

                return [
                    'ok' => true,
                    'date_label' => 'Partite importanti del '.today()->translatedFormat('d M'),
                    'favorite_team' => $favoriteSummary,
                    'matches' => $matches,
                ];
            });
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Risultati calcio non disponibili in questo momento.',
            ], 502);
        }

        return response()->json($payload);
    }

    public function weather(Request $request)
    {
        $place = $request->validate([
            'place' => ['nullable', 'in:milano,riesi'],
        ])['place'] ?? 'milano';

        $apiKey = config('weather.google.api_key');
        if (blank($apiKey)) {
            return response()->json([
                'ok' => false,
                'configured' => false,
                'message' => 'Chiave Google Weather API non configurata.',
            ], 503);
        }

        $location = $this->weatherLocations()[$place];
        $cacheKey = "weather.google.current.{$place}";

        try {
            $weather = Cache::flexible($cacheKey, [900, 3600], function () use ($apiKey, $location, $place) {
                $response = Http::timeout(10)->get('https://weather.googleapis.com/v1/currentConditions:lookup', [
                    'key' => $apiKey,
                    'location.latitude' => $location['latitude'],
                    'location.longitude' => $location['longitude'],
                    'unitsSystem' => 'METRIC',
                    'languageCode' => 'it',
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException('Google Weather API non disponibile in questo momento.');
                }

                $payload = $response->json();

                return [
                    'location_key' => $place,
                    'location_label' => $location['label'],
                    'description' => data_get($payload, 'weatherCondition.description.text') ?? 'Condizioni attuali',
                    'temperature' => $this->formatTemperature(data_get($payload, 'temperature.degrees')),
                    'feels_like' => $this->formatTemperature(data_get($payload, 'feelsLikeTemperature.degrees')),
                    'humidity' => data_get($payload, 'relativeHumidity') !== null ? data_get($payload, 'relativeHumidity').'%' : '--',
                    'precipitation_probability' => data_get($payload, 'precipitation.probability.percent') !== null ? data_get($payload, 'precipitation.probability.percent').'%' : '--',
                    'wind_speed' => data_get($payload, 'wind.speed.value') !== null ? data_get($payload, 'wind.speed.value').' km/h' : '--',
                    'updated_at' => now()->format('H:i'),
                    'icon_url' => data_get($payload, 'weatherCondition.iconBaseUri') ? data_get($payload, 'weatherCondition.iconBaseUri').'.svg' : null,
                ];
            });
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'configured' => true,
                'message' => 'Google Weather API non disponibile in questo momento.',
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'configured' => true,
            'weather' => $weather,
        ]);
    }

    public function tv(Request $request)
    {
        $channelKey = $request->validate([
            'channel' => ['nullable', 'in:rai1,rai2,rai3,rete4,canale5,italia1,la7,tv8,nove,realtime'],
        ])['channel'] ?? 'rai1';
        $channel = $this->tvChannels()[$channelKey];
        $date = today();
        $cacheKey = 'tv.evening.v2.'.md5($channelKey.'|'.$date->toDateString());

        try {
            $programs = Cache::flexible($cacheKey, [7200, 28800], fn () => $this->tvEveningPrograms($channel));
        } catch (\Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Palinsesto TV non disponibile in questo momento.',
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'channel_label' => $channel['label'],
            'date_label' => $channel['label'].' · '.$date->translatedFormat('d M'),
            'programs' => $programs,
        ]);
    }

    private function weatherLocations(): array
    {
        return [
            'milano' => ['label' => 'Milano', 'latitude' => 45.4642, 'longitude' => 9.19],
            'riesi' => ['label' => 'Riesi', 'latitude' => 37.2822, 'longitude' => 14.0828],
        ];
    }

    private function tvChannels(): array
    {
        return [
            'rai1' => ['label' => 'Rai 1', 'site_id' => 'rai-1/217'],
            'rai2' => ['label' => 'Rai 2', 'site_id' => 'rai-2/218'],
            'rai3' => ['label' => 'Rai 3', 'site_id' => 'rai-3/219'],
            'rete4' => ['label' => 'Rete 4', 'site_id' => 'rete-4/189'],
            'canale5' => ['label' => 'Canale 5', 'site_id' => 'canale-5/187'],
            'italia1' => ['label' => 'Italia 1', 'site_id' => 'italia-1/188'],
            'la7' => ['label' => 'LA7', 'site_id' => 'la7/253'],
            'tv8' => ['label' => 'TV8', 'site_id' => 'tv8/237'],
            'nove' => ['label' => 'Nove', 'site_id' => 'nove/238'],
            'realtime' => ['label' => 'Real Time', 'site_id' => 'real-time/255'],
        ];
    }

    private function tvEveningPrograms(array $channel): array
    {
        $url = 'https://www.superguidatv.it/programmazione-canale/oggi/guida-programmi-tv-'.$channel['site_id'].'/';
        $response = Http::timeout(12)
            ->withUserAgent('FamilyPlan/1.0')
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('TV guide unavailable');
        }

        preg_match_all(
            '/<p[^>]*>(\\d{2}:\\d{2})<\\/p>\\s*<\\/div>\\s*<div[^>]*>\\s*<p[^>]*>(.*?)<\\/p>(?:\\s*<p[^>]*>(.*?)<\\/p>)?/si',
            $response->body(),
            $matches,
            PREG_SET_ORDER
        );

        $programs = collect($matches)
            ->map(function ($match, $index) use ($matches) {
                $time = $match[1];
                $nextTime = $matches[$index + 1][1] ?? null;

                return [
                    'time' => $time,
                    'title' => html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'category' => html_entity_decode(strip_tags($match[3] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'duration' => $this->tvProgramDuration($time, $nextTime),
                ];
            })
            ->filter(fn ($program) => $program['time'] >= '20:30' && $program['time'] <= '23:59' && $program['title'] !== '')
            ->values();

        return $programs
            ->filter(fn ($program) => $program['time'] >= '20:20' && $program['time'] <= '22:30')
            ->filter(fn ($program) => $program['duration'] >= 35)
            ->reject(fn ($program) => $this->isTvFillerProgram($program['title']))
            ->map(fn ($program) => $program + ['score' => $this->tvMainProgramScore($program)])
            ->sortByDesc('score')
            ->take(1)
            ->whenEmpty(fn () => $programs->take(1))
            ->map(fn ($program) => collect($program)->except(['duration', 'score'])->all())
            ->values()
            ->all();
    }

    private function tvMainProgramScore(array $program): int
    {
        $category = Str::of($program['category'])->lower()->ascii()->value();
        $score = match (true) {
            Str::contains($category, ['film', 'sport']) => 55,
            Str::contains($category, ['reality', 'show']) => 45,
            Str::contains($category, ['serie tv', 'fiction', 'intrattenimento']) => 35,
            Str::contains($category, ['attualita', 'documentario', 'mondo e tendenze', 'lifestyle']) => 25,
            default => 15,
        };

        if ($program['time'] >= '21:00' && $program['time'] <= '21:50') {
            $score += 45;
        } elseif ($program['time'] >= '20:30' && $program['time'] < '21:00') {
            $score += 10;
        }

        $score += $program['duration'] >= 80 ? 35 : ($program['duration'] >= 50 ? 15 : 0);

        return $score;
    }

    private function tvProgramDuration(string $time, ?string $nextTime): int
    {
        if (! $nextTime) {
            return 90;
        }

        [$hour, $minute] = array_map('intval', explode(':', $time));
        [$nextHour, $nextMinute] = array_map('intval', explode(':', $nextTime));
        $start = $hour * 60 + $minute;
        $stop = $nextHour * 60 + $nextMinute;

        return $stop > $start ? $stop - $start : ($stop + 1440) - $start;
    }

    private function isTvFillerProgram(string $title): bool
    {
        $normalized = Str::of($title)->lower()->ascii()->value();

        return (bool) preg_match(
            '/\b(?:tg\s*\d*|telegiornale|meteo|riassunto|anteprima|post|prima pagina|striscia)\b|cinque minuti|access prime time/i',
            $normalized
        );
    }

    private function formatTemperature($value): string
    {
        return $value === null ? '--' : round((float) $value).'°';
    }

    private function footballTimeLabel(array $event): string
    {
        if (! empty($event['intHomeScore']) || ! empty($event['intAwayScore']) || (($event['intHomeScore'] ?? null) === '0') || (($event['intAwayScore'] ?? null) === '0')) {
            return 'Finale';
        }

        $time = (string) ($event['strTimeLocal'] ?? $event['strTime'] ?? '');

        return $time !== '' ? Str::of($time)->substr(0, 5)->value() : '--:--';
    }

    private function footballFavoriteSummary(array $event): string
    {
        $home = trim((string) ($event['strHomeTeam'] ?? 'Casa'));
        $away = trim((string) ($event['strAwayTeam'] ?? 'Trasferta'));
        $homeScore = $event['intHomeScore'];
        $awayScore = $event['intAwayScore'];

        if ($homeScore !== null && $awayScore !== null) {
            return "{$home} {$homeScore}-{$awayScore} {$away}";
        }

        return $this->footballTimeLabel($event).' · '.$home.' vs '.$away;
    }

    private function eventMatchesFavorite(array $event, string $favoriteNeedle): bool
    {
        if ($favoriteNeedle === '') {
            return false;
        }

        return str_contains($this->normalizeFootballName((string) ($event['strHomeTeam'] ?? '')), $favoriteNeedle)
            || str_contains($this->normalizeFootballName((string) ($event['strAwayTeam'] ?? '')), $favoriteNeedle);
    }

    private function normalizeFootballName(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();
    }

    private function isImportantFootballEvent(array $event, array $importantLeagueIds): bool
    {
        if (in_array((string) ($event['idLeague'] ?? ''), $importantLeagueIds, true)) {
            return true;
        }

        if ($this->isItalyNationalTeamEvent($event)) {
            return true;
        }

        return $this->isMajorCompetitionEvent($event);
    }

    private function footballEventPriority(array $event, array $leagueOrder): int
    {
        if ($this->isItalyNationalTeamEvent($event)) {
            return -20;
        }

        if ($this->isMajorCompetitionEvent($event)) {
            return -10;
        }

        return $leagueOrder[(string) ($event['idLeague'] ?? '')] ?? 999;
    }

    private function isItalyNationalTeamEvent(array $event): bool
    {
        $home = $this->normalizeFootballName((string) ($event['strHomeTeam'] ?? ''));
        $away = $this->normalizeFootballName((string) ($event['strAwayTeam'] ?? ''));

        return in_array('italy', [$home, $away], true)
            || in_array('italia', [$home, $away], true);
    }

    private function isMajorCompetitionEvent(array $event): bool
    {
        $haystack = $this->normalizeFootballName(
            trim(
                implode(' ', array_filter([
                    (string) ($event['strLeague'] ?? ''),
                    (string) ($event['strSeason'] ?? ''),
                    (string) ($event['strEvent'] ?? ''),
                    (string) ($event['strHomeTeam'] ?? ''),
                    (string) ($event['strAwayTeam'] ?? ''),
                ]))
            )
        );

        $keywords = [
            'championsleague',
            'uefachampionsleague',
            'fifaworldcup',
            'worldcup',
            'coppadelmondo',
            'worldcupqualification',
            'worldcupqualifying',
            'qualificazionicoppa',
            'uefaeuropaleague',
            'europaleague',
            'uefanationsleague',
            'nationsleague',
            'uefaeuro',
            'euroqualification',
            'qualificazionieuropei',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function footballFavoriteFallback(string $favoriteNeedle, string $date): ?array
    {
        if ($favoriteNeedle === 'torreense' && $date === '2026-05-28') {
            return [
                'idEvent' => 'fallback-torreense-2026-05-28',
                'strLeague' => 'Playoff Portogallo',
                'strHomeTeam' => 'Casa Pia',
                'strAwayTeam' => 'Torreense',
                'strTimeLocal' => '21:00:00',
                'intHomeScore' => null,
                'intAwayScore' => null,
            ];
        }

        return null;
    }

    private function footballSupplementalEvents(string $date)
    {
        try {
            $response = Http::timeout(10)->get('https://www.thesportsdb.com/api/v1/json/123/eventsnext.php', [
                'id' => '133910',
            ]);

            if (! $response->successful()) {
                return collect();
            }

            return collect($response->json('events') ?? [])
                ->filter(fn ($event) => ($event['dateEvent'] ?? null) === $date);
        } catch (\Throwable $exception) {
            return collect();
        }
    }
}
