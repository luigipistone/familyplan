<x-layouts.app title="Collezione monete">
    <section class="page-head coin-page-head">
        <div><h1>Collezione monete</h1><p>Catalogo personale, valori e identificazioni.</p></div>
        <div class="page-actions"><button class="fab-inline" type="button" data-open-modal="#coin-create-modal">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi moneta</span>
        </button></div>
    </section>

    <section class="coin-stats" aria-label="Riepilogo collezione">
        <article class="card"><span>Monete</span><strong>{{ $stats['count'] }}</strong></article>
        <article class="card"><span>Valore facciale</span><strong>€ {{ number_format($stats['face'], 2, ',', '.') }}</strong></article>
        <article class="card"><span>Stima collezione</span><strong>€ {{ number_format($stats['min'], 2, ',', '.') }} – € {{ number_format($stats['max'], 2, ',', '.') }}</strong></article>
        <article @class(['card', 'has-warning' => $stats['verify'] > 0])><span>Da verificare</span><strong>{{ $stats['verify'] }}</strong></article>
    </section>

    <section class="coin-toolbar panel">
        <label class="coin-filter-field"><span>Cerca</span><span class="search-field">
            <input type="search" placeholder="Titolo, paese, anno o codice" data-coin-search>
            <button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
        </span></label>
        <label class="coin-filter-field"><span>Paese</span><select data-coin-country aria-label="Filtra per paese">
            <option value="">Tutti i paesi</option>
            @foreach($countries as $country)<option value="{{ mb_strtolower($country) }}">{{ $country }}</option>@endforeach
        </select></label>
        <label class="coin-filter-field"><span>Ordina per</span><select data-coin-sort aria-label="Ordina monete">
            <option value="default">Catalogo</option>
            <option value="name-asc">Nome A–Z</option>
            <option value="name-desc">Nome Z–A</option>
            <option value="country-asc">Paese A–Z</option>
            <option value="year-desc">Anno più recente</option>
            <option value="year-asc">Anno meno recente</option>
            <option value="estimate-desc">Stima più alta</option>
            <option value="estimate-asc">Stima più bassa</option>
        </select></label>
    </section>

    <div class="coin-grid" data-coin-grid>
        @foreach($coins as $coin)
            @php($payload = [
                'id' => $coin->id, 'code' => $coin->code, 'country' => $coin->country, 'year' => $coin->year,
                'title' => $coin->title, 'denomination_eur' => $coin->denomination_eur, 'type' => $coin->type,
                'condition' => $coin->condition, 'rarity' => $coin->rarity, 'estimated_min_eur' => $coin->estimated_min_eur,
                'estimated_max_eur' => $coin->estimated_max_eur, 'face_value_eur' => $coin->face_value_eur,
                'probable_duplicate' => $coin->probable_duplicate, 'identification_confidence' => $coin->identification_confidence,
                'manual_verification' => $coin->manual_verification, 'notes' => $coin->notes, 'image_url' => $coin->imageUrl(),
                'update_url' => route('coins.update', $coin), 'delete_url' => route('coins.destroy', $coin),
            ])
            <article class="card clickable-card coin-card" tabindex="0" role="button" data-coin-card data-coin-id="{{ $coin->id }}" data-coin-index="{{ $loop->index }}" data-coin-name="{{ mb_strtolower($coin->title) }}" data-coin-year="{{ $coin->year ?: 0 }}" data-coin-estimate="{{ $coin->estimated_max_eur ?: 0 }}" data-coin-b64="{{ base64_encode(json_encode($payload)) }}" data-coin-searchable="{{ mb_strtolower($coin->code.' '.$coin->country.' '.$coin->year.' '.$coin->title.' '.$coin->rarity) }}" data-coin-country-value="{{ mb_strtolower($coin->country) }}">
                <div class="coin-image-wrap">
                    @if($coin->imageUrl())<img src="{{ $coin->imageUrl() }}" alt="{{ $coin->title }}" loading="lazy" decoding="async">@else<span>{{ mb_substr($coin->country, 0, 2) }}</span>@endif
                    @if($coin->manual_verification)<span class="coin-warning" title="Identificazione da verificare">!</span>@endif
                    <button class="icon-btn coin-edit-trigger" type="button" data-edit-coin aria-label="Modifica {{ $coin->title }}" title="Modifica">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                    </button>
                </div>
                <div class="coin-card-body">
                    <span>{{ $coin->country }} · {{ $coin->year ?: 'Anno n.d.' }}</span>
                    <h2>{{ $coin->title }}</h2>
                    <div><small>{{ $coin->code }}</small><strong>€ {{ number_format((float) $coin->estimated_min_eur, 2, ',', '.') }}–{{ number_format((float) $coin->estimated_max_eur, 2, ',', '.') }}</strong></div>
                </div>
            </article>
        @endforeach
    </div>
    <p class="coin-empty" data-coin-empty hidden>Nessuna moneta corrisponde ai filtri.</p>

    <dialog class="modal user-modal" id="coin-create-modal" aria-labelledby="coin-create-title">
        <form class="modal-card form-stack" method="post" action="{{ route('coins.store') }}" enctype="multipart/form-data">@csrf
            <div class="modal-head"><div><p>Collezione</p><h2 id="coin-create-title">Aggiungi moneta</h2></div><button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div>
            @include('coins.partials.fields', ['coin' => null])
            <button>Salva moneta</button>
        </form>
    </dialog>

    <dialog class="modal user-modal coin-detail-modal" id="coin-detail-modal" aria-labelledby="coin-detail-title">
        <div class="modal-card">
            <div class="modal-head"><div><p data-coin-detail-code></p><h2 id="coin-detail-title" data-coin-detail-title></h2></div><div class="modal-actions"><button class="icon-btn" type="button" data-detail-edit-coin aria-label="Modifica" title="Modifica"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg></button><button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div></div>
            <img class="coin-detail-image" data-coin-detail-image alt="">
            <div class="coin-detail-facts" data-coin-detail-facts></div>
            <p class="coin-detail-notes" data-coin-detail-notes></p>
        </div>
    </dialog>

    <dialog class="modal user-modal" id="coin-edit-modal" aria-labelledby="coin-edit-title">
        <div class="modal-card">
            <div class="modal-head"><div><p>Collezione</p><h2 id="coin-edit-title">Modifica moneta</h2></div><div class="modal-actions"><form method="post" data-coin-delete-form data-confirm="Vuoi davvero eliminare questa moneta?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina" title="Elimina"><svg viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button></form><button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div></div>
            <form class="form-stack" method="post" data-coin-edit-form enctype="multipart/form-data">@csrf @method('patch')
                @include('coins.partials.fields', ['coin' => null])
                <button>Salva modifiche</button>
            </form>
        </div>
    </dialog>
</x-layouts.app>
