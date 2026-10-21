<x-layouts.app title="Calendario">
    @php
        $calendarEventStyle = function (?int $creatorId): string {
            $palette = ['#2f6f9d', '#7a5fc7', '#bf6c2f', '#2f8a61', '#b24f6b', '#8b7a2b'];
            $accent = $palette[abs((int) $creatorId) % count($palette)];
            [$red, $green, $blue] = sscanf(ltrim($accent, '#'), '%02x%02x%02x') ?: [47, 111, 157];
            return implode(';', [
                "--event-accent: {$accent}",
                "--event-bg: rgba({$red}, {$green}, {$blue}, .14)",
                "--event-bg-hover: rgba({$red}, {$green}, {$blue}, .22)",
                "--event-bg-span: rgba({$red}, {$green}, {$blue}, .20)",
                "--event-border: rgba({$red}, {$green}, {$blue}, .34)",
                "--event-border-span: rgba({$red}, {$green}, {$blue}, .50)",
                "--event-text: #18303c",
                "--event-muted: #56707d",
            ]);
        };
    @endphp
    <section class="page-head">
        <div><h1>Calendario</h1><p>Vista mensile con eventi familiari e personali.</p></div>
        <button class="fab-inline" type="button" data-open-modal="#event-create-modal">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span>Aggiungi evento</span>
        </button>
    </section>

    @if(file_exists(public_path('build/manifest.json')))
        <div
            data-vue-calendar-board
            data-days-b64="{{ base64_encode(json_encode($calendarDaysPayload)) }}"
            data-events-b64="{{ base64_encode(json_encode($calendarEventsPayload)) }}"
            data-today-events-b64="{{ base64_encode(json_encode($todayEventsPayload)) }}"
            data-month-label="{{ ucfirst($month->translatedFormat('F Y')) }}"
            data-today-label="{{ now()->translatedFormat('d F') }}"
            data-previous-month-url="{{ route('calendar', ['mese' => $previousMonth]) }}"
            data-next-month-url="{{ route('calendar', ['mese' => $nextMonth]) }}"
        ></div>
    @else
        <section class="mobile-today">
            <div class="row"><h2>Oggi</h2><time>{{ now()->translatedFormat('d F') }}</time></div>
            <div class="calendar-events">
                @forelse($todayEvents as $event)
                    <button class="calendar-event" type="button"
                        style="{{ $calendarEventStyle($event->created_by) }}"
                        data-edit-event
                        data-event-action="{{ route('calendar.update', $event) }}"
                        data-event-duplicate-action="{{ route('calendar.duplicate', $event) }}"
                        data-event-delete-action="{{ route('calendar.destroy', $event) }}"
                        data-event-title="{{ $event->title }}"
                        data-event-description="{{ $event->description }}"
                        data-event-starts-at="{{ $event->starts_at->format('Y-m-d\TH:i') }}"
                        data-event-ends-at="{{ optional($event->ends_at)->format('Y-m-d\TH:i') }}"
                        data-event-visibility="{{ $event->visibility }}"
                        data-event-assigned-user-id="{{ $event->assigned_user_id }}"
                        data-event-child-id="{{ $event->child_id }}">
                        <span>{{ $event->starts_at->format('H:i') }}</span>
                        <strong>{{ $event->title }}</strong>
                        <small>{{ $event->creator->name ?? '' }}</small>
                    </button>
                @empty
                    <p class="muted">Nessun evento per oggi.</p>
                @endforelse
            </div>
        </section>

        <section class="calendar-shell">
            <header class="calendar-toolbar">
                <a class="icon-btn" href="{{ route('calendar', ['mese' => $previousMonth]) }}" aria-label="Mese precedente" title="Mese precedente"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a>
                <h2>{{ ucfirst($month->translatedFormat('F Y')) }}</h2>
                <a class="icon-btn" href="{{ route('calendar', ['mese' => $nextMonth]) }}" aria-label="Mese successivo" title="Mese successivo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></a>
            </header>
            <div class="calendar-weekdays">
                @foreach(['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $weekday)
                    <span>{{ $weekday }}</span>
                @endforeach
            </div>
            <div class="calendar-month">
                @foreach($days as $day)
                    @php($dayEvents = $events->filter(fn($event) => $event->starts_at->lte($day->copy()->endOfDay()) && ($event->ends_at ?? $event->starts_at)->gte($day->copy()->startOfDay())))
                    <article @class(['calendar-day', 'is-muted' => !$day->isSameMonth($month), 'is-today' => $day->isToday()]) data-calendar-day="{{ $day->format('Y-m-d') }}">
                        <time datetime="{{ $day->format('Y-m-d') }}">{{ $day->day }}</time>
                        <div class="calendar-events">
                            @foreach($dayEvents as $event)
                                @php($eventEnd = $event->ends_at ?? $event->starts_at)
                                <button @class(['calendar-event', 'is-span' => !$event->starts_at->isSameDay($eventEnd), 'continues-before' => $event->starts_at->lt($day->copy()->startOfDay()), 'continues-after' => $eventEnd->gt($day->copy()->endOfDay())]) type="button" draggable="true" style="{{ $calendarEventStyle($event->created_by) }}"
                                    data-edit-event
                                    data-event-id="{{ $event->id }}"
                                    data-event-action="{{ route('calendar.update', $event) }}"
                                    data-event-duplicate-action="{{ route('calendar.duplicate', $event) }}"
                                    data-event-delete-action="{{ route('calendar.destroy', $event) }}"
                                    data-event-title="{{ $event->title }}"
                                    data-event-description="{{ $event->description }}"
                                    data-event-starts-at="{{ $event->starts_at->format('Y-m-d\TH:i') }}"
                                    data-event-ends-at="{{ optional($event->ends_at)->format('Y-m-d\TH:i') }}"
                                    data-event-visibility="{{ $event->visibility }}"
                                    data-event-assigned-user-id="{{ $event->assigned_user_id }}"
                                    data-event-child-id="{{ $event->child_id }}">
                                    <span>{{ $event->starts_at->isSameDay($day) ? $event->starts_at->format('H:i') : '↳' }}</span>
                                    <strong>{{ $event->title }}</strong>
                                    <small>{{ $event->creator->name ?? '' }}</small>
                                </button>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <dialog class="modal user-modal" id="event-create-modal" aria-labelledby="event-create-title" data-reset-event="calendar-form:reset:create">
        <form class="modal-card" method="post" action="{{ route('calendar.store') }}" data-event-form>@csrf
            <div class="modal-head">
                <div><p>Nuovo impegno</p><h2 id="event-create-title">Aggiungi evento</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            @if(file_exists(public_path('build/manifest.json')))
                <div
                    data-vue-calendar-fields
                    data-form-key="create"
                    data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                    data-children-b64="{{ base64_encode(json_encode($children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values())) }}"
                ></div>
            @else
                @include('calendar.partials.form-fields')
            @endif
            <button>Crea evento</button>
        </form>
    </dialog>

    <dialog class="modal user-modal" id="event-edit-modal" aria-labelledby="event-edit-title">
        <div class="modal-card">
            <div class="modal-head">
                <div><p>Impegno esistente</p><h2 id="event-edit-title">Modifica evento</h2></div>
                <div class="modal-actions">
                    <form method="post" action="" data-duplicate-event-form>@csrf
                        <button class="icon-btn" type="submit" aria-label="Duplica evento" title="Duplica evento"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                    </form>
                    <form method="post" action="" data-delete-event-form data-confirm="Vuoi davvero eliminare questo evento?">@csrf @method('delete')
                        <button class="icon-btn danger-icon" type="submit" aria-label="Elimina evento" title="Elimina evento"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button>
                    </form>
                    <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
            </div>
            <form method="post" action="" data-event-form data-edit-event-form>@csrf @method('patch')
                @if(file_exists(public_path('build/manifest.json')))
                    <div
                        data-vue-calendar-fields
                        data-form-key="edit"
                        data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                        data-children-b64="{{ base64_encode(json_encode($children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values())) }}"
                    ></div>
                @else
                    @include('calendar.partials.form-fields')
                @endif
                <button>Salva modifiche</button>
            </form>
        </div>
    </dialog>
</x-layouts.app>
