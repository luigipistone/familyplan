<x-layouts.app title="Promemoria">
    <section class="page-head">
        <div><h1>{{ $archived ? 'Archivio promemoria' : 'Promemoria' }}</h1><p>{{ $archived ? 'Promemoria archiviati.' : 'Singoli, ricorsivi, personali o condivisi.' }}</p></div>
        <div class="page-actions">
            @if($archived)
                <a class="pill" href="{{ route('reminders') }}">Promemoria attivi</a>
            @else
                <button class="fab-inline" type="button" data-open-modal="#reminder-create-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi promemoria</span></button>
                <a class="pill" href="{{ route('reminders.archive.index') }}">Archivio</a>
            @endif
        </div>
    </section>

    <dialog class="modal user-modal" id="reminder-create-modal" aria-labelledby="reminder-create-title" data-reset-event="reminder-form:reset:create">
        <form class="modal-card" method="post" action="{{ route('reminders.store') }}">@csrf
            <div class="modal-head"><div><p>Nuovo</p><h2 id="reminder-create-title">Aggiungi promemoria</h2></div><button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div>
            @if(file_exists(public_path('build/manifest.json')))
                <div
                    data-vue-reminder-fields
                    data-form-key="create"
                    data-title=""
                    data-due-at=""
                    data-recurrence="none"
                    data-body-b64="{{ base64_encode(json_encode('')) }}"
                    data-shared-with-b64="{{ base64_encode(json_encode([])) }}"
                    data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                ></div>
            @else
                <div class="two"><label>Titolo<input name="title" required></label><label>Data e ora<input type="datetime-local" name="due_at"></label></div>
                <label>Ricorrenza<select name="recurrence"><option value="none">Nessuna</option><option value="daily">Giornaliera</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select></label>
                <label>Testo<textarea name="body" data-wysiwyg-input hidden></textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Scrivi il promemoria."></div></div></label>
                <fieldset class="check-list"><legend>Condividi</legend>@foreach($users as $u)<label class="check"><input type="checkbox" name="shared_with[]" value="{{ $u->id }}"> {{ $u->name }}</label>@endforeach</fieldset>
            @endif
            <button>Salva</button>
        </form>
    </dialog>

    <div class="stack">
        @forelse($reminders as $r)
            <article class="card clickable-card" role="button" tabindex="0" data-open-modal="#reminder-{{ $r->id }}"><div><h2>{{ $r->title }}</h2><p>{{ optional($r->due_at)->format('d/m/Y H:i') ?: 'Senza data' }} · {{ $r->recurrence }}</p></div></article>
            <dialog class="modal user-modal" id="reminder-{{ $r->id }}" aria-labelledby="reminder-title-{{ $r->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><p>{{ optional($r->due_at)->format('d/m/Y H:i') ?: 'Senza data' }}</p><h2 id="reminder-title-{{ $r->id }}">Modifica promemoria</h2></div>
                        <div class="modal-actions">
                            @if($archived)
                                <form method="post" action="{{ route('reminders.unarchive', $r) }}" data-confirm="Vuoi riattivare questo promemoria?">@csrf @method('patch')<button class="icon-btn" type="submit" aria-label="Riattiva promemoria" title="Riattiva promemoria"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></button></form>
                            @else
                                <form method="post" action="{{ route('reminders.complete', $r) }}" data-confirm="Vuoi archiviare questo promemoria?">@csrf @method('patch')<button class="icon-btn" type="submit" aria-label="Archivia promemoria" title="Archivia promemoria"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M6 7v13h12V7"/><path d="M9 11h6"/></svg></button></form>
                            @endif
                            <form method="post" action="{{ route('reminders.destroy', $r) }}" data-confirm="Vuoi eliminare questo promemoria?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina promemoria" title="Elimina promemoria"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                            <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                        </div>
                    </div>
                    <form method="post" action="{{ route('reminders.update', $r) }}">@csrf @method('patch')
                        @if(file_exists(public_path('build/manifest.json')))
                            <div
                                data-vue-reminder-fields
                                data-form-key="edit-{{ $r->id }}"
                                data-title="{{ $r->title }}"
                                data-due-at="{{ optional($r->due_at)->format('Y-m-d\TH:i') }}"
                                data-recurrence="{{ $r->recurrence }}"
                                data-body-b64="{{ base64_encode(json_encode($r->body)) }}"
                                data-shared-with-b64="{{ base64_encode(json_encode($r->shared_with ?? [])) }}"
                                data-users-b64="{{ base64_encode(json_encode($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                            ></div>
                        @else
                            <div class="two"><label>Titolo<input name="title" value="{{ $r->title }}" required></label><label>Data e ora<input type="datetime-local" name="due_at" value="{{ optional($r->due_at)->format('Y-m-d\TH:i') }}"></label></div>
                            <label>Ricorrenza<select name="recurrence"><option value="none" @selected($r->recurrence === 'none')>Nessuna</option><option value="daily" @selected($r->recurrence === 'daily')>Giornaliera</option><option value="weekly" @selected($r->recurrence === 'weekly')>Settimanale</option><option value="monthly" @selected($r->recurrence === 'monthly')>Mensile</option></select></label>
                            <label>Testo<textarea name="body" data-wysiwyg-input hidden>{{ $r->body }}</textarea><div class="wysiwyg" data-wysiwyg><div class="wysiwyg-toolbar"><button type="button" data-wysiwyg-command="bold"><strong>B</strong></button><button type="button" data-wysiwyg-command="italic"><em>I</em></button><button type="button" data-wysiwyg-command="underline"><span style="text-decoration: underline;">U</span></button><button type="button" data-wysiwyg-command="strikeThrough"><span style="text-decoration: line-through;">S</span></button><button type="button" data-wysiwyg-command="insertUnorderedList">•</button><button type="button" data-wysiwyg-command="insertOrderedList">1.</button><button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote">"</button><button type="button" data-wysiwyg-command="createLink">Link</button><button type="button" data-wysiwyg-command="removeFormat">Tx</button></div><div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor>{!! $r->body !!}</div></div></label>
                            <fieldset class="check-list"><legend>Condividi</legend>@foreach($users as $u)<label class="check"><input type="checkbox" name="shared_with[]" value="{{ $u->id }}" @checked(in_array($u->id, $r->shared_with ?? []))> {{ $u->name }}</label>@endforeach</fieldset>
                        @endif
                        <button>Salva modifiche</button>
                    </form>
                </div>
            </dialog>
        @empty
            <article class="card"><p class="muted">{{ $archived ? 'Nessun promemoria archiviato.' : 'Nessun promemoria aperto.' }}</p></article>
        @endforelse
    </div>
</x-layouts.app>
