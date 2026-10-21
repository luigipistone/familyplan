<x-layouts.app title="Dashboard">
    @php($widgetLabels = ['calendar' => 'Calendario', 'shopping' => 'Spesa', 'family' => 'Famiglia', 'reminders' => 'Promemoria', 'notes' => 'Note', 'weather' => 'Meteo', 'football' => 'Calcio', 'tv' => 'TV stasera'])
    <section class="hero">
        <div><p>Ciao {{ auth()->user()->name }}</p><h1>Oggi in famiglia</h1></div>
    </section>
    <div
        data-vue-dashboard-summary
        data-events-count="{{ $events->count() }}"
        data-shopping-count="{{ $shoppingLists->count() }}"
        data-reminders-count="{{ $reminders->count() }}"
        data-notes-count="{{ $notes->count() }}"
    ></div>
    <dialog class="modal" id="widget-modal" aria-labelledby="widget-modal-title">
        <form class="modal-card" method="post" action="{{ route('dashboard.widgets') }}">@csrf
            <div class="modal-head">
                <div><p>Dashboard</p><h2 id="widget-modal-title">Widget visibili</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            <div class="widget-list" data-widget-sort-list>
                @php($widgetRows = collect($widgets)->concat(array_diff(array_keys($widgetLabels), $widgets))->unique()->values())
                @foreach($widgetRows as $key)
                    @php($label = $widgetLabels[$key] ?? $key)
                    <label class="switch-row widget-row" draggable="true" data-widget-sort-item data-widget-key="{{ $key }}">
                        <span class="widget-row-label">
                            <span class="widget-drag-handle" data-widget-drag-handle aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M9 6h6M9 12h6M9 18h6"/></svg>
                            </span>
                            <span>{{ $label }}</span>
                        </span>
                        <input type="checkbox" name="widgets[]" value="{{ $key }}" @checked(in_array($key,$widgets))><i></i>
                    </label>
                @endforeach
            </div>
            <div data-widget-order-inputs></div>
            <button>Salva</button>
        </form>
    </dialog>
    <div class="grid">
        @foreach($widgets as $widget)
            @switch($widget)
                @case('calendar')
                    @include('partials.card', ['title'=>'Calendario', 'items'=>$events->map(fn($e)=>['label'=>$e->starts_at->format('d/m H:i').' · '.$e->title.' · '.($e->creator->name ?? ''), 'url'=>route('calendar', ['mese'=>$e->starts_at->format('Y-m'), 'open_event'=>$e->id])])])
                    @break
                @case('shopping')
                    @include('partials.card', ['title'=>'Spesa', 'items'=>$shoppingLists->map(fn($l)=>['label'=>$l->name.' · '.$l->items->where('is_checked',false)->count().' da comprare', 'url'=>route('shopping', ['open'=>'shopping-view-'.$l->id])])])
                    @break
                @case('family')
                    @include('partials.card', ['title'=>'Figli oggi', 'items'=>$familyToday->map(fn($e)=>['label'=>$e->starts_at->format('H:i').' · '.($e->child->name ?? '').' · '.$e->title, 'url'=>route('family')])])
                    @break
                @case('reminders')
                    @include('partials.card', ['title'=>'Promemoria', 'items'=>$reminders->map(fn($r)=>['label'=>(optional($r->due_at)->format('d/m H:i') ?: 'Senza data').' · '.$r->title, 'url'=>route('reminders', ['open'=>'reminder-'.$r->id])])])
                    @break
                @case('notes')
                    @include('partials.card', ['title'=>'Note', 'items'=>$notes->map(fn($n)=>['label'=>$n->title, 'url'=>route('notes', ['open'=>'note-'.$n->id])])])
                    @break
                @case('weather')
                    <div
                        data-vue-dashboard-weather
                        data-endpoint="{{ route('dashboard.weather') }}"
                        data-default-location="milano"
                        data-locations-b64="{{ base64_encode(json_encode(collect($weatherLocations)->map(fn($location, $key) => ['key' => $key, 'label' => $location['label']])->values())) }}"
                    ></div>
                    @break
                @case('football')
                    <div
                        data-vue-dashboard-football
                        data-endpoint="{{ route('dashboard.football') }}"
                    ></div>
                    @break
                @case('tv')
                    <div
                        data-vue-dashboard-tv
                        data-endpoint="{{ route('dashboard.tv') }}"
                        data-channels-b64="{{ base64_encode(json_encode(collect($tvChannels)->map(fn($channel, $key) => ['key' => $key, 'label' => $channel['label']])->values())) }}"
                    ></div>
                    @break
            @endswitch
        @endforeach
    </div>
</x-layouts.app>
