<x-layouts.app title="{{ $archived ? 'Archivio spesa' : 'Lista spesa' }}">
    <section class="page-head">
        <div>
            <h1>{{ $archived ? 'Archivio spesa' : 'Lista della spesa' }}</h1>
            <p>{{ $archived ? 'Liste archiviate manualmente o completate.' : 'Liste condivisibili con articoli da spuntare.' }}</p>
        </div>
        <div class="page-actions">
            @if($archived)
                <a class="pill" href="{{ route('shopping') }}">Liste attive</a>
            @else
                <button class="fab-inline" type="button" data-open-modal="#shopping-create-modal">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Aggiungi lista</span>
                </button>
                <a class="pill" href="{{ route('shopping.archive.index') }}">Archivio</a>
            @endif
        </div>
    </section>

    @unless($archived)
        <dialog class="modal user-modal" id="shopping-create-modal" aria-labelledby="shopping-create-title" data-reset-event="shopping-builder:reset:create">
            <form class="modal-card" method="post" action="{{ route('shopping.store') }}" data-shopping-form>@csrf
                <div class="modal-head">
                    <div><p>Nuova lista</p><h2 id="shopping-create-title">Aggiungi lista spesa</h2></div>
                    <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
                @include('shopping.partials.form-fields', ['list' => null])
                @if(file_exists(public_path('build/manifest.json')))
                    <div
                        data-vue-shopping-builder
                        data-form-key="create"
                        data-mode="create"
                        data-categories-b64="{{ base64_encode(json_encode($groceryCategories)) }}"
                        data-initial-selected-b64="{{ base64_encode(json_encode([])) }}"
                        data-initial-products-b64="{{ base64_encode(json_encode([])) }}"
                    ></div>
                @else
                    @include('shopping.partials.grocery-picker', ['target' => 'create', 'list' => null])
                @endif
                <button>Crea lista</button>
            </form>
        </dialog>
    @endunless

    <div class="stack">
        @forelse($lists as $list)
            <article class="card shopping-card clickable-card" role="button" tabindex="0" data-open-modal="#shopping-view-{{ $list->id }}">
                <div class="shopping-summary">
                    <div>
                        <h2>{{ $list->name }}</h2>
                        <p>{{ $list->list_date->format('d/m/Y') }}</p>
                        <small>
                            Condivisa con:
                            @php($sharedNames = $users->whereIn('id', $list->shared_with ?? [])->pluck('name')->join(', '))
                            {{ $sharedNames !== '' ? $sharedNames : 'nessuno' }}
                        </small>
                    </div>
                    <div class="summary-actions">
                        <form method="post" action="{{ route('shopping.duplicate', $list) }}">@csrf
                            <button class="icon-btn" type="submit" aria-label="Duplica lista" title="Duplica lista">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </form>
                        <button class="icon-btn" type="button" data-open-modal="#shopping-edit-{{ $list->id }}" aria-label="Modifica lista" title="Modifica lista">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L8 20H4v-4Z"/></svg>
                        </button>
                    </div>
                </div>
            </article>

            <dialog class="modal user-modal" id="shopping-view-{{ $list->id }}" aria-labelledby="shopping-view-title-{{ $list->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>{{ $list->list_date->format('d/m/Y') }}</p><h2 id="shopping-view-title-{{ $list->id }}">{{ $list->name }}</h2></div>
                        <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                    </div>
                    <div class="shopping-products">
                        @forelse($list->items as $item)
                            <form class="checkline" method="post" action="{{ route('shopping.items.toggle', $item) }}" data-inline-action>@csrf @method('patch')<button class="checkbox">@if($item->is_checked)✓@endif</button><span @class(['done' => $item->is_checked])>{{ $item->name }} {{ $item->quantity }}</span></form>
                        @empty
                            <p class="muted">Nessun prodotto inserito.</p>
                        @endforelse
                    </div>
                </div>
            </dialog>

            <dialog class="modal user-modal" id="shopping-edit-{{ $list->id }}" aria-labelledby="shopping-edit-title-{{ $list->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>Lista spesa</p><h2 id="shopping-edit-title-{{ $list->id }}">Modifica lista</h2></div>
                        <div class="modal-actions">
                            @if($archived)
                                <form method="post" action="{{ route('shopping.unarchive', $list) }}" data-confirm="Vuoi riattivare questa lista?">@csrf @method('patch')
                                    <button class="icon-btn" type="submit" aria-label="Riattiva lista" title="Riattiva lista"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7"/><path d="M18 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7"/><path d="M6 21v-5h5"/></svg></button>
                                </form>
                            @else
                                <form method="post" action="{{ route('shopping.archive', $list) }}" data-confirm="Vuoi archiviare questa lista?">@csrf @method('patch')
                                    <button class="icon-btn" type="submit" aria-label="Archivia lista" title="Archivia lista"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M6 7v13h12V7"/><path d="M9 11h6"/></svg></button>
                                </form>
                            @endif
                            <form method="post" action="{{ route('shopping.destroy', $list) }}" data-confirm="Vuoi davvero eliminare questa lista?">@csrf @method('delete')
                                <button class="icon-btn danger-icon" type="submit" aria-label="Elimina lista" title="Elimina lista"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button>
                            </form>
                            <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    <form method="post" action="{{ route('shopping.update', $list) }}" data-shopping-form>@csrf @method('patch')
                        @include('shopping.partials.form-fields', ['list' => $list])
                        @unless($archived)
                            @if(file_exists(public_path('build/manifest.json')))
                                <div
                                    data-vue-shopping-builder
                                    data-form-key="edit-{{ $list->id }}"
                                    data-mode="edit"
                                    data-categories-b64="{{ base64_encode(json_encode($groceryCategories)) }}"
                                    data-initial-selected-b64="{{ base64_encode(json_encode([])) }}"
                                    data-initial-products-b64="{{ base64_encode(json_encode($list->items->map(fn($item) => ['name' => $item->name, 'quantity' => $item->quantity, 'checked' => (bool) $item->is_checked])->values())) }}"
                                ></div>
                            @else
                                <section class="shopping-products" data-draft-products>
                                    <h2>Prodotti</h2>
                                    @foreach($list->items as $item)
                                        <div class="product-row" data-draft-product>
                                            <input type="hidden" name="products[{{ $loop->index }}][name]" value="{{ $item->name }}" data-draft-name>
                                            <input type="hidden" name="products[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" data-draft-quantity>
                                            <input type="hidden" name="products[{{ $loop->index }}][checked]" value="{{ $item->is_checked ? 1 : 0 }}" data-draft-checked>
                                            <span @class(['done' => $item->is_checked])>{{ $item->name }} {{ $item->quantity }}</span>
                                            <button class="icon-btn danger-icon" type="button" data-draft-product-remove aria-label="Rimuovi prodotto" title="Rimuovi prodotto"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button>
                                        </div>
                                    @endforeach
                                </section>
                                <div class="inline" data-draft-product-manual><input data-draft-manual-name placeholder="Articolo"><input data-draft-manual-quantity placeholder="Qtà"><button type="button" data-draft-product-add>+</button></div>
                                @include('shopping.partials.grocery-picker', ['target' => 'draft', 'list' => $list])
                            @endif
                        @endunless
                        <button>Salva modifiche</button>
                    </form>
                </div>
            </dialog>
        @empty
            <article class="card"><p class="muted">{{ $archived ? 'Nessuna lista archiviata.' : 'Nessuna lista spesa attiva.' }}</p></article>
        @endforelse
    </div>
</x-layouts.app>
