<x-layouts.app title="Note">
    <section class="page-head">
        <div><h1>{{ $archived ? 'Archivio note' : 'Note' }}</h1><p>{{ $archived ? 'Note archiviate.' : 'Note nominate, condivisibili e archiviabili.' }}</p></div>
        <div class="page-actions">
            @if($archived)
                <a class="pill" href="{{ route('notes') }}">Note attive</a>
            @else
                <button class="fab-inline" type="button" data-open-modal="#note-create-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi nota</span></button>
                <a class="pill" href="{{ route('notes.archive.index') }}">Archivio</a>
            @endif
        </div>
    </section>

    <dialog class="modal user-modal" id="note-create-modal" aria-labelledby="note-create-title" data-reset-event="note-form:reset:create">
        <form class="modal-card" method="post" action="{{ route('notes.store') }}">@csrf
            <div class="modal-head"><div><p>Nuova</p><h2 id="note-create-title">Aggiungi nota</h2></div><button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div>
            @if(file_exists(public_path('build/manifest.json')))
                <div
                    data-vue-note-fields
                    data-form-key="create"
                    data-title=""
                    data-body-b64="{{ base64_encode(json_encode('')) }}"
                    data-shared-with-b64="{{ base64_encode(json_encode([])) }}"
                    data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                ></div>
            @else
                <label>Titolo<input name="title" required></label>
                <label>Nota<textarea name="body" rows="5" data-wysiwyg-input hidden></textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Scrivi la nota."></div></div></label>
                <fieldset class="check-list"><legend>Condividi</legend>@foreach($users as $u)<label class="check"><input type="checkbox" name="shared_with[]" value="{{ $u->id }}"> {{ $u->name }}</label>@endforeach</fieldset>
            @endif
            <button>Salva nota</button>
        </form>
    </dialog>

    <div class="stack">
        @forelse($notes as $note)
            <article class="card clickable-card" role="button" tabindex="0" data-open-modal="#note-{{ $note->id }}"><h2>{{ $note->title }}</h2><p>{{ \Illuminate\Support\Str::limit(strip_tags($note->body), 120) }}</p></article>
            <dialog class="modal user-modal" id="note-{{ $note->id }}" aria-labelledby="note-title-{{ $note->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>Nota</p><h2 id="note-title-{{ $note->id }}">Modifica nota</h2></div>
                        <div class="modal-actions">
                            @if($archived)
                                <form method="post" action="{{ route('notes.unarchive', $note) }}" data-confirm="Vuoi riattivare questa nota?">@csrf @method('patch')<button class="icon-btn" type="submit" aria-label="Riattiva nota" title="Riattiva nota"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></button></form>
                            @else
                                <form method="post" action="{{ route('notes.archive', $note) }}" data-confirm="Vuoi archiviare questa nota?">@csrf @method('patch')<button class="icon-btn" type="submit" aria-label="Archivia nota" title="Archivia nota"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M6 7v13h12V7"/><path d="M9 11h6"/></svg></button></form>
                            @endif
                            <form method="post" action="{{ route('notes.destroy', $note) }}" data-confirm="Vuoi eliminare questa nota?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina nota" title="Elimina nota"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                            <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    <form method="post" action="{{ route('notes.update', $note) }}">@csrf @method('patch')
                        @if(file_exists(public_path('build/manifest.json')))
                            <div
                                data-vue-note-fields
                                data-form-key="edit-{{ $note->id }}"
                                data-title="{{ $note->title }}"
                                data-body-b64="{{ base64_encode(json_encode($note->body)) }}"
                                data-shared-with-b64="{{ base64_encode(json_encode($note->shared_with ?? [])) }}"
                                data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                            ></div>
                        @else
                            <label>Titolo<input name="title" value="{{ $note->title }}" required></label>
                            <label>Nota<textarea name="body" rows="5" data-wysiwyg-input hidden>{{ $note->body }}</textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor>{!! $note->body !!}</div></div></label>
                            <fieldset class="check-list"><legend>Condividi</legend>@foreach($users as $u)<label class="check"><input type="checkbox" name="shared_with[]" value="{{ $u->id }}" @checked(in_array($u->id, $note->shared_with ?? []))> {{ $u->name }}</label>@endforeach</fieldset>
                        @endif
                        <button>Salva modifiche</button>
                    </form>
                </div>
            </dialog>
        @empty
            <article class="card"><p class="muted">{{ $archived ? 'Nessuna nota archiviata.' : 'Nessuna nota attiva.' }}</p></article>
        @endforelse
    </div>
</x-layouts.app>
