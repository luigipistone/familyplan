<x-layouts.app title="Contatti">
    <section class="page-head">
        <div><h1>Contatti</h1><p>Rubrica condivisa di famiglia con ricerca rapida e indice alfabetico.</p></div>
        <div class="page-actions">
            <button class="fab-inline" type="button" data-open-modal="#contact-create-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi contatto</span></button>
        </div>
    </section>

    <dialog class="modal user-modal" id="contact-create-modal" aria-labelledby="contact-create-title">
        <form class="modal-card form-stack" method="post" action="{{ route('contacts.store') }}">@csrf
            <div class="modal-head">
                <div><p>Nuovo</p><h2 id="contact-create-title">Aggiungi contatto</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            <div class="two">
                <label>Nome<input name="name" required></label>
                <label>Cognome<input name="surname" required></label>
            </div>
            <div class="two">
                <label>Azienda<input name="company"></label>
                <label>Telefono<input name="phone" inputmode="tel"></label>
            </div>
            <div class="two">
                <label>Email<input name="email" type="email"></label>
                <label>Indirizzo<input name="address"></label>
            </div>
            <label>Note<textarea name="notes" rows="5" data-wysiwyg-input hidden></textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Aggiungi dettagli utili."></div></div></label>
            <button>Salva contatto</button>
        </form>
    </dialog>

    <section class="panel contacts-tools">
        <label class="contact-search">
            <span>Cerca</span>
            <span class="search-field"><input type="search" placeholder="Nome, cognome, telefono, email o azienda" data-contact-search><button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></span>
        </label>
        <div class="alphabet-bar alphabet-bar-desktop" data-contact-alphabet>
            @foreach($letters as $letter)
                <a href="#contacts-letter-{{ $letter }}" data-letter-link>{{ $letter }}</a>
            @endforeach
        </div>
    </section>

    <div class="stack contacts-stack">
        @foreach($letters as $letter)
            @php($group = $contacts->filter(fn($contact) => strtoupper(mb_substr($contact->surname ?: $contact->name, 0, 1)) === $letter))
            <section class="letter-group" id="contacts-letter-{{ $letter }}" data-contact-group="{{ $letter }}" @if($group->isEmpty()) hidden @endif>
                <div class="letter-head"><span>{{ $letter }}</span></div>
                <div class="grid contacts-grid">
                    @foreach($group as $contact)
                        <article
                            class="card clickable-card contact-card"
                            role="button"
                            tabindex="0"
                            data-open-modal="#contact-{{ $contact->id }}"
                            data-contact-card
                            data-contact-searchable="{{ strtolower($contact->name.' '.$contact->surname.' '.$contact->company.' '.$contact->phone.' '.$contact->email) }}"
                        >
                            <div class="contact-card-main">
                                <div>
                                    <h2>{{ $contact->surname }} {{ $contact->name }}</h2>
                                    <p>{{ $contact->company ?: 'Contatto famiglia' }}</p>
                                </div>
                                <span class="contact-initial">{{ strtoupper(mb_substr($contact->surname ?: $contact->name, 0, 1)) }}</span>
                            </div>
                            <div class="contact-meta">
                                @if($contact->phone)
                                    <span class="contact-copy-line">
                                        <span>{{ $contact->phone }}</span>
                                        <button class="icon-btn copy-inline-btn" type="button" data-copy-text="{{ $contact->phone }}" aria-label="Copia numero" title="Copia numero">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        </button>
                                    </span>
                                @endif
                                @if($contact->email)<span>{{ $contact->email }}</span>@endif
                            </div>
                        </article>

                        <dialog class="modal user-modal" id="contact-{{ $contact->id }}" aria-labelledby="contact-title-{{ $contact->id }}">
                            <div class="modal-card">
                                <div class="modal-head">
                                    <div><p>Contatto</p><h2 id="contact-title-{{ $contact->id }}">Modifica contatto</h2></div>
                                    <div class="modal-actions">
                                        <form method="post" action="{{ route('contacts.destroy', $contact) }}" data-confirm="Vuoi eliminare questo contatto?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina contatto" title="Elimina contatto"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                                        <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                                    </div>
                                </div>
                                <form class="form-stack" method="post" action="{{ route('contacts.update', $contact) }}">@csrf @method('patch')
                                    <div class="two">
                                        <label>Nome<input name="name" value="{{ $contact->name }}" required></label>
                                        <label>Cognome<input name="surname" value="{{ $contact->surname }}" required></label>
                                    </div>
                                    <div class="two">
                                        <label>Azienda<input name="company" value="{{ $contact->company }}"></label>
                                        <label>Telefono
                                            <span class="input-with-icon">
                                                <input name="phone" inputmode="tel" value="{{ $contact->phone }}">
                                                @if($contact->phone)
                                                    <button class="field-icon-btn" type="button" data-copy-text="{{ $contact->phone }}" aria-label="Copia numero" title="Copia numero">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                                    </button>
                                                @endif
                                            </span>
                                        </label>
                                    </div>
                                    <div class="two">
                                        <label>Email<input name="email" type="email" value="{{ $contact->email }}"></label>
                                        <label>Indirizzo<input name="address" value="{{ $contact->address }}"></label>
                                    </div>
                                    <label>Note<textarea name="notes" rows="5" data-wysiwyg-input hidden>{{ $contact->notes }}</textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor>{!! $contact->notes !!}</div></div></label>
                                    <button>Salva modifiche</button>
                                </form>
                            </div>
                        </dialog>
                    @endforeach
                </div>
            </section>
        @endforeach
        @if($contacts->isEmpty())
            <article class="card"><p class="muted">Nessun contatto salvato.</p></article>
        @endif
    </div>

    <div class="alphabet-bar alphabet-bar-mobile" data-contact-alphabet-mobile>
        @foreach($letters as $letter)
            <a href="#contacts-letter-{{ $letter }}" data-letter-link>{{ $letter }}</a>
        @endforeach
    </div>
</x-layouts.app>
