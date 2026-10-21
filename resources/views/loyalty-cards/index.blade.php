<x-layouts.app title="Carte fedeltà">
    @php
        $loyaltyTextColor = function (?string $hex): string {
            $hex = ltrim((string) $hex, '#');
            if (strlen($hex) !== 6) return '#ffffff';
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
            return $luminance > 0.62 ? '#14201b' : '#ffffff';
        };
        $loyaltyMutedColor = function (?string $hex) use ($loyaltyTextColor): string {
            return $loyaltyTextColor($hex) === '#ffffff' ? 'rgba(255,255,255,.78)' : 'rgba(20,32,27,.7)';
        };
        $loyaltyBrandLogo = function (?string $brand): ?string {
            $brand = strtolower(\Illuminate\Support\Str::ascii((string) $brand));
            return match (true) {
                str_contains($brand, 'esselunga') => asset('images/loyalty-logos/esselunga.svg'),
                str_contains($brand, 'camaieu') => asset('images/loyalty-logos/camaieu.png'),
                str_contains($brand, 'carpisa') || str_contains($brand, 'yamamay') => asset('images/loyalty-logos/carpisa.svg'),
                str_contains($brand, 'carrefour') => asset('images/loyalty-logos/carrefour.png'),
                str_contains($brand, 'conad') => asset('images/loyalty-logos/conad.png'),
                str_contains($brand, 'conbipel') => asset('images/loyalty-logos/conbipel.png'),
                str_contains($brand, 'decathlon') => asset('images/loyalty-logos/decathlon.ico'),
                str_contains($brand, 'feltrinelli') => asset('images/loyalty-logos/feltrinelli.svg'),
                str_contains($brand, 'giudici') => asset('images/loyalty-logos/giudici.jpg'),
                str_contains($brand, 'guess') => asset('images/loyalty-logos/guess.png'),
                str_contains($brand, 'iper') => asset('images/loyalty-logos/iper.png'),
                str_contains($brand, 'libraccio') => asset('images/loyalty-logos/libraccio.png'),
                str_contains($brand, 'mediaworld') => asset('images/loyalty-logos/mediaworld.png'),
                str_contains($brand, 'motivi') => asset('images/loyalty-logos/motivi.png'),
                str_contains($brand, 'pimkie') => asset('images/loyalty-logos/pimkie.png'),
                str_contains($brand, 'pittarosso') => asset('images/loyalty-logos/pittarosso.png'),
                str_contains($brand, 'rinascente') => asset('images/loyalty-logos/rinascente.png'),
                str_contains($brand, 'scalo') => asset('images/loyalty-logos/scalo-milano.png'),
                str_contains($brand, 'scarpe') => asset('images/loyalty-logos/scarpe-scarpe.png'),
                str_contains($brand, 'sephora') => asset('images/loyalty-logos/sephora.png'),
                str_contains($brand, 'tigota') => asset('images/loyalty-logos/tigota.png'),
                str_contains($brand, 'tigros') => asset('images/loyalty-logos/tigros.png'),
                str_contains($brand, 'unieuro') => asset('images/loyalty-logos/unieuro.jpg'),
                str_contains($brand, 'universo') => asset('images/loyalty-logos/universo-bimbo.png'),
                str_contains($brand, 'coop') => asset('images/loyalty-logos/coop.png'),
                str_contains($brand, 'caddy') => asset('images/loyalty-logos/caddys.png'),
                default => null,
            };
        };
    @endphp
    <section class="page-head">
        <div><h1>Carte fedeltà</h1><p>Raccogli tessere, numeri e codici in un solo posto.</p></div>
        <div class="page-actions">
            <button class="ghost loyalty-import-btn" type="button" data-open-modal="#loyalty-card-import-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg><span>Importa Carta</span></button>
            <button class="fab-inline" type="button" data-open-modal="#loyalty-card-create-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi carta</span></button>
        </div>
    </section>

    <section class="panel loyalty-tools">
        <label class="contact-search">
            <span>Cerca</span>
            <span class="search-field"><input type="search" placeholder="Brand o utente associato" data-loyalty-search><button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></span>
        </label>
    </section>

    <section class="loyalty-favorites" data-loyalty-favorites @if($favoriteCards->isEmpty()) hidden @endif>
        <div class="loyalty-favorites-track" data-loyalty-favorites-track>
            @foreach($favoriteCards as $favoriteCard)
                @php($favoriteLogo = $loyaltyBrandLogo($favoriteCard->brand))
                @php($favoriteText = $loyaltyTextColor($favoriteCard->theme_color))
                <button class="loyalty-favorite-pill" type="button" data-open-modal="#loyalty-card-view-{{ $favoriteCard->id }}" data-favorite-pill="{{ $favoriteCard->id }}" style="--loyalty-color: {{ $favoriteCard->theme_color }}; --loyalty-text: {{ $favoriteText }};" aria-label="{{ $favoriteCard->brand }}">
                    @if($favoriteLogo)
                        <img src="{{ $favoriteLogo }}" alt="{{ $favoriteCard->brand }}" loading="lazy">
                    @else
                        <span>{{ \Illuminate\Support\Str::of($favoriteCard->brand)->substr(0, 1)->upper() }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </section>

    <dialog class="modal user-modal" id="loyalty-card-create-modal" aria-labelledby="loyalty-card-create-title">
        <form class="modal-card form-stack" method="post" action="{{ route('loyalty-cards.store') }}" enctype="multipart/form-data">@csrf
            <div class="modal-head">
                <div><p>Nuova</p><h2 id="loyalty-card-create-title">Aggiungi carta fedeltà</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            <label>Brand / negozio<input name="brand" required placeholder="Es. Esselunga"></label>
            <label>Codice carta / barcode<input name="barcode_value" inputmode="text"></label>
            <label>Codifica
                <select name="barcode_format">
                    <option value="auto">Automatica</option>
                    <option value="EAN">EAN</option>
                    <option value="CODE128">128</option>
                </select>
            </label>
            <label>Associata a
                <select name="associated_user_id">
                    <option value="">Nessun utente</option>
                    @foreach($eligibleUsers as $eligibleUser)
                        <option value="{{ $eligibleUser->id }}">{{ $eligibleUser->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Colore carta
                <span class="color-field">
                    <input name="theme_color" type="color" value="#6f9f92">
                    <span class="color-value">#6f9f92</span>
                </span>
            </label>
            <label>Note<textarea name="notes" rows="5" data-wysiwyg-input hidden></textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Informazioni utili, PIN, istruzioni, vantaggi."></div></div></label>
            <button>Salva carta</button>
        </form>
    </dialog>

    <dialog class="modal user-modal" id="loyalty-card-import-modal" aria-labelledby="loyalty-card-import-title">
        <form class="modal-card form-stack" method="post" action="{{ route('loyalty-cards.store') }}" enctype="multipart/form-data" data-loyalty-import-form>@csrf
            <div class="modal-head">
                <div><p>Importa</p><h2 id="loyalty-card-import-title">Importa da foto</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            <label>Foto carta<input name="card_photo" type="file" accept="image/*" data-loyalty-import-image required></label>
            <div class="loyalty-import-preview" data-loyalty-import-preview hidden>
                <img data-loyalty-import-preview-image alt="Anteprima carta">
            </div>
            <p class="muted" data-loyalty-import-status>Carica una foto nitida della tessera. Provero a leggere automaticamente il barcode.</p>
            <label>Brand / negozio<input name="brand" data-loyalty-import-brand required placeholder="Da confermare"></label>
            <label>Codice carta / barcode<input name="barcode_value" data-loyalty-import-code required></label>
            <label>Codifica
                <select name="barcode_format" data-loyalty-import-format>
                    <option value="auto">Automatica</option>
                    <option value="EAN">EAN</option>
                    <option value="CODE128">128</option>
                </select>
            </label>
            <label>Colore carta
                <span class="color-field">
                    <input name="theme_color" type="color" value="#6f9f92">
                    <span class="color-value">#6f9f92</span>
                </span>
            </label>
            <button>Importa carta</button>
        </form>
    </dialog>

    @if($myCards->isNotEmpty())
        <section class="stack loyalty-section">
            <div class="page-head loyalty-subhead"><div><h2>Associate a {{ auth()->user()->name }}</h2></div></div>
            <div class="grid loyalty-grid">
        @foreach($myCards as $card)
            @php($cardCode = $card->barcode_value ?: $card->card_number)
            @php($cardText = $loyaltyTextColor($card->theme_color))
            @php($cardMuted = $loyaltyMutedColor($card->theme_color))
            @php($cardLogo = $loyaltyBrandLogo($card->brand))
            <article class="card clickable-card loyalty-card" role="button" tabindex="0" data-open-modal="#loyalty-card-view-{{ $card->id }}" data-loyalty-card data-loyalty-searchable="{{ strtolower(trim($card->brand.' '.($card->associatedUser?->name ?? ''))) }}" style="--loyalty-color: {{ $card->theme_color }}; --loyalty-text: {{ $cardText }}; --loyalty-muted: {{ $cardMuted }};">
                <div class="loyalty-card-head">
                    @if($cardLogo)
                        <img class="loyalty-brand-logo" src="{{ $cardLogo }}" alt="{{ $card->brand }}" loading="lazy">
                    @endif
                    <div>
                        <h2>{{ $card->brand }}</h2>
                        <p>{{ $card->associatedUser?->name ?? 'Non associata' }}</p>
                    </div>
                    <div class="loyalty-card-actions">
                        <button class="icon-btn loyalty-edit-btn loyalty-star-btn @if(in_array((string) auth()->id(), $card->favorite_user_ids ?? [], true)) is-active @endif" type="button" data-loyalty-favorite-button data-url="{{ route('loyalty-cards.favorite', $card) }}" data-card-id="{{ $card->id }}" data-card-brand="{{ $card->brand }}" data-card-logo="{{ $cardLogo }}" data-card-color="{{ $card->theme_color }}" data-card-text="{{ $cardText }}" aria-label="Preferita" title="Preferita" aria-pressed="{{ in_array((string) auth()->id(), $card->favorite_user_ids ?? [], true) ? 'true' : 'false' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2 7.5 14 3 9.6l6.2-.9L12 3Z"/></svg>
                        </button>
                        @if($card->owner_id === auth()->id())
                            <button class="icon-btn loyalty-edit-btn" type="button" data-open-modal="#loyalty-card-edit-{{ $card->id }}" aria-label="Modifica carta" title="Modifica carta">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </article>

            <dialog class="modal user-modal" id="loyalty-card-view-{{ $card->id }}" aria-labelledby="loyalty-card-view-title-{{ $card->id }}">
                <div class="modal-card loyalty-view-card" style="--loyalty-color: {{ $card->theme_color }}; --loyalty-text: {{ $cardText }}; --loyalty-muted: {{ $cardMuted }};">
                    <div class="modal-head">
                        <div><p>Carta fedeltà</p><h2 id="loyalty-card-view-title-{{ $card->id }}">{{ $card->brand }}</h2></div>
                        <div class="modal-actions">
                            @if($card->owner_id === auth()->id())
                                <button class="icon-btn loyalty-view-icon" type="button" data-open-modal="#loyalty-card-edit-{{ $card->id }}" aria-label="Modifica carta" title="Modifica carta">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                                </button>
                            @endif
                            <button class="icon-btn loyalty-view-icon" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    @if($cardCode)
                        <section class="loyalty-pass">
                            @if($cardLogo)
                                <img class="loyalty-brand-logo loyalty-brand-logo-lg" src="{{ $cardLogo }}" alt="{{ $card->brand }}" loading="lazy">
                            @endif
                            <div class="loyalty-pass-barcode">
                                <svg data-loyalty-barcode="{{ $cardCode }}" data-loyalty-format="{{ $card->barcode_format ?? 'auto' }}" data-barcode-height="104" data-barcode-display-value="1" aria-label="Barcode {{ $cardCode }}"></svg>
                            </div>
                        </section>
                    @endif
                </div>
            </dialog>

            <dialog class="modal user-modal" id="loyalty-card-edit-{{ $card->id }}" aria-labelledby="loyalty-card-title-{{ $card->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>Carta fedeltà</p><h2 id="loyalty-card-title-{{ $card->id }}">Modifica carta</h2></div>
                        <div class="modal-actions">
                            <form method="post" action="{{ route('loyalty-cards.destroy', $card) }}" data-confirm="Vuoi eliminare questa carta fedeltà?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina carta" title="Elimina carta"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                            <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    <form class="form-stack" method="post" action="{{ route('loyalty-cards.update', $card) }}" enctype="multipart/form-data">@csrf @method('patch')
                        <label>Brand / negozio<input name="brand" value="{{ $card->brand }}" required></label>
                        <label>Associata a
                            <select name="associated_user_id">
                                <option value="">Nessun utente</option>
                                @foreach($eligibleUsers as $eligibleUser)
                                    <option value="{{ $eligibleUser->id }}" @selected($card->associated_user_id === $eligibleUser->id)>{{ $eligibleUser->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Codice carta / barcode
                            <span class="input-with-icon">
                                <input name="barcode_value" value="{{ $cardCode }}">
                                @if($cardCode)
                                    <button class="field-icon-btn" type="button" data-copy-text="{{ $cardCode }}" aria-label="Copia codice" title="Copia codice">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                @endif
                            </span>
                        </label>
                        <label>Codifica
                            <select name="barcode_format">
                                <option value="auto" @selected(($card->barcode_format ?? 'auto') === 'auto')>Automatica</option>
                                <option value="EAN" @selected(($card->barcode_format ?? 'auto') === 'EAN')>EAN</option>
                                <option value="CODE128" @selected(($card->barcode_format ?? 'auto') === 'CODE128')>128</option>
                            </select>
                        </label>
                        <label>Colore carta
                            <span class="color-field">
                                <input name="theme_color" type="color" value="{{ $card->theme_color }}">
                                <span class="color-value">{{ $card->theme_color }}</span>
                            </span>
                        </label>
                        <label>Note<textarea name="notes" rows="5" data-wysiwyg-input hidden>{{ $card->notes }}</textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor>{!! $card->notes !!}</div></div></label>
                        <button>Salva modifiche</button>
                    </form>
                </div>
            </dialog>
        @endforeach
            </div>
        </section>
    @endif

    <section class="stack loyalty-section">
        <div class="page-head loyalty-subhead"><div><h2>Altre carte</h2></div></div>
        <div class="grid loyalty-grid">
        @forelse($otherCards as $card)
            @php($cardCode = $card->barcode_value ?: $card->card_number)
            @php($cardText = $loyaltyTextColor($card->theme_color))
            @php($cardMuted = $loyaltyMutedColor($card->theme_color))
            @php($cardLogo = $loyaltyBrandLogo($card->brand))
            <article class="card clickable-card loyalty-card" role="button" tabindex="0" data-open-modal="#loyalty-card-view-{{ $card->id }}" data-loyalty-card data-loyalty-searchable="{{ strtolower(trim($card->brand.' '.($card->associatedUser?->name ?? ''))) }}" style="--loyalty-color: {{ $card->theme_color }}; --loyalty-text: {{ $cardText }}; --loyalty-muted: {{ $cardMuted }};">
                <div class="loyalty-card-head">
                    @if($cardLogo)
                        <img class="loyalty-brand-logo" src="{{ $cardLogo }}" alt="{{ $card->brand }}" loading="lazy">
                    @endif
                    <div>
                        <h2>{{ $card->brand }}</h2>
                        <p>{{ $card->associatedUser?->name ?? 'Non associata' }}</p>
                    </div>
                    <div class="loyalty-card-actions">
                        <button class="icon-btn loyalty-edit-btn loyalty-star-btn @if(in_array((string) auth()->id(), $card->favorite_user_ids ?? [], true)) is-active @endif" type="button" data-loyalty-favorite-button data-url="{{ route('loyalty-cards.favorite', $card) }}" data-card-id="{{ $card->id }}" data-card-brand="{{ $card->brand }}" data-card-logo="{{ $cardLogo }}" data-card-color="{{ $card->theme_color }}" data-card-text="{{ $cardText }}" aria-label="Preferita" title="Preferita" aria-pressed="{{ in_array((string) auth()->id(), $card->favorite_user_ids ?? [], true) ? 'true' : 'false' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2 7.5 14 3 9.6l6.2-.9L12 3Z"/></svg>
                        </button>
                        @if($card->owner_id === auth()->id())
                            <button class="icon-btn loyalty-edit-btn" type="button" data-open-modal="#loyalty-card-edit-{{ $card->id }}" aria-label="Modifica carta" title="Modifica carta">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </article>
            <dialog class="modal user-modal" id="loyalty-card-view-{{ $card->id }}" aria-labelledby="loyalty-card-view-title-{{ $card->id }}">
                <div class="modal-card loyalty-view-card" style="--loyalty-color: {{ $card->theme_color }}; --loyalty-text: {{ $cardText }}; --loyalty-muted: {{ $cardMuted }};">
                    <div class="modal-head">
                        <div><p>Carta fedeltà</p><h2 id="loyalty-card-view-title-{{ $card->id }}">{{ $card->brand }}</h2></div>
                        <div class="modal-actions">
                            @if($card->owner_id === auth()->id())
                                <button class="icon-btn loyalty-view-icon" type="button" data-open-modal="#loyalty-card-edit-{{ $card->id }}" aria-label="Modifica carta" title="Modifica carta">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                                </button>
                            @endif
                            <button class="icon-btn loyalty-view-icon" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    @if($cardCode)
                        <section class="loyalty-pass">
                            @if($cardLogo)
                                <img class="loyalty-brand-logo loyalty-brand-logo-lg" src="{{ $cardLogo }}" alt="{{ $card->brand }}" loading="lazy">
                            @endif
                            <div class="loyalty-pass-barcode">
                                <svg data-loyalty-barcode="{{ $cardCode }}" data-loyalty-format="{{ $card->barcode_format ?? 'auto' }}" data-barcode-height="104" data-barcode-display-value="1" aria-label="Barcode {{ $cardCode }}"></svg>
                            </div>
                        </section>
                    @endif
                </div>
            </dialog>
            @if($card->owner_id === auth()->id())
            <dialog class="modal user-modal" id="loyalty-card-edit-{{ $card->id }}" aria-labelledby="loyalty-card-title-{{ $card->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>Carta fedeltà</p><h2 id="loyalty-card-title-{{ $card->id }}">Modifica carta</h2></div>
                        <div class="modal-actions">
                            <form method="post" action="{{ route('loyalty-cards.destroy', $card) }}" data-confirm="Vuoi eliminare questa carta fedeltà?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina carta" title="Elimina carta"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                            <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    <form class="form-stack" method="post" action="{{ route('loyalty-cards.update', $card) }}" enctype="multipart/form-data">@csrf @method('patch')
                        <label>Brand / negozio<input name="brand" value="{{ $card->brand }}" required></label>
                        <label>Associata a
                            <select name="associated_user_id">
                                <option value="">Nessun utente</option>
                                @foreach($eligibleUsers as $eligibleUser)
                                    <option value="{{ $eligibleUser->id }}" @selected($card->associated_user_id === $eligibleUser->id)>{{ $eligibleUser->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Codice carta / barcode
                            <span class="input-with-icon">
                                <input name="barcode_value" value="{{ $cardCode }}">
                                @if($cardCode)
                                    <button class="field-icon-btn" type="button" data-copy-text="{{ $cardCode }}" aria-label="Copia codice" title="Copia codice">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                @endif
                            </span>
                        </label>
                        <label>Codifica
                            <select name="barcode_format">
                                <option value="auto" @selected(($card->barcode_format ?? 'auto') === 'auto')>Automatica</option>
                                <option value="EAN" @selected(($card->barcode_format ?? 'auto') === 'EAN')>EAN</option>
                                <option value="CODE128" @selected(($card->barcode_format ?? 'auto') === 'CODE128')>128</option>
                            </select>
                        </label>
                        <label>Colore carta
                            <span class="color-field">
                                <input name="theme_color" type="color" value="{{ $card->theme_color }}">
                                <span class="color-value">{{ $card->theme_color }}</span>
                            </span>
                        </label>
                        <label>Note<textarea name="notes" rows="5" data-wysiwyg-input hidden>{{ $card->notes }}</textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor>{!! $card->notes !!}</div></div></label>
                        <button>Salva modifiche</button>
                    </form>
                </div>
            </dialog>
            @endif
        @empty
            @if($myCards->isEmpty())
                <article class="card"><p class="muted">Nessuna carta fedeltà salvata.</p></article>
            @endif
        @endforelse
        </div>
    </section>
</x-layouts.app>
