@php
    $sharedIds = collect($entry->shared_with ?? [])->map(fn($id) => (string) $id);
    $sharedNames = $users->whereIn('id', $sharedIds->map(fn($id) => (int) $id))->pluck('name')->join(', ');
    $search = strtolower(trim($entry->title.' '.$entry->website.' '.$entry->username.' '.$entry->owner?->name.' '.$sharedNames));
@endphp

<article class="card clickable-card password-card" role="button" tabindex="0" data-open-modal="#password-view-{{ $entry->id }}" data-password-card data-password-searchable="{{ $search }}">
    <div class="password-card-head">
        <span class="password-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v3"/></svg></span>
        <div>
            <h2>{{ $entry->title }}</h2>
            <p>{{ $entry->username ?: ($entry->website ?: 'Accesso salvato') }}</p>
        </div>
        @if($owned)
            <button class="icon-btn password-edit-btn" type="button" data-open-modal="#password-edit-{{ $entry->id }}" aria-label="Modifica password" title="Modifica password">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
            </button>
        @endif
    </div>
</article>

<dialog class="modal user-modal" id="password-view-{{ $entry->id }}" aria-labelledby="password-view-title-{{ $entry->id }}">
    <div class="modal-card form-stack">
        <div class="modal-head">
            <div><p>{{ $owned ? 'Privata' : 'Condivisa da '.$entry->owner?->name }}</p><h2 id="password-view-title-{{ $entry->id }}">{{ $entry->title }}</h2></div>
            <div class="modal-actions">
                @if($owned)
                    <button class="icon-btn" type="button" data-open-modal="#password-edit-{{ $entry->id }}" aria-label="Modifica password" title="Modifica password"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.2-1 9.5-9.5a1.8 1.8 0 0 0 0-2.5l-.7-.7a1.8 1.8 0 0 0-2.5 0L5 15.8 4 20Z"/><path d="M13.5 7.5 16.5 10.5"/></svg></button>
                @endif
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
        </div>
        @if($entry->website)<a class="password-site" href="{{ str_starts_with($entry->website, 'http') ? $entry->website : 'https://'.$entry->website }}" target="_blank" rel="noopener">{{ $entry->website }}</a>@endif
        <div class="secret-row">
            <span>Username</span>
            <strong>{{ $entry->username ?: '-' }}</strong>
            @if($entry->username)<button class="icon-btn" type="button" data-copy-text="{{ $entry->username }}" data-copy-label="Username copiato" aria-label="Copia username" title="Copia username"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>@endif
        </div>
        <div class="secret-row">
            <span>Password</span>
            <strong class="password-masked" data-secret-value="{{ $entry->password }}">••••••••••••</strong>
            <button class="icon-btn" type="button" data-secret-toggle aria-label="Mostra password" title="Mostra password"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            <button class="icon-btn" type="button" data-copy-text="{{ $entry->password }}" data-copy-label="Password copiata" aria-label="Copia password" title="Copia password"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
        </div>
        @if($sharedNames)<p class="muted">Condivisa con: {{ $sharedNames }}</p>@endif
        @if($entry->notes)<div class="panel password-notes">{{ $entry->notes }}</div>@endif
    </div>
</dialog>

@if($owned)
    <dialog class="modal user-modal" id="password-edit-{{ $entry->id }}" aria-labelledby="password-edit-title-{{ $entry->id }}">
        <div class="modal-card">
            <div class="modal-head">
                <div><p>Password</p><h2 id="password-edit-title-{{ $entry->id }}">Modifica accesso</h2></div>
                <div class="modal-actions">
                    <form method="post" action="{{ route('passwords.destroy', $entry) }}" data-confirm="Vuoi eliminare questa password?">@csrf @method('delete')<button class="icon-btn danger-icon" type="submit" aria-label="Elimina password" title="Elimina password"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button></form>
                    <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
            </div>
            <form class="form-stack" method="post" action="{{ route('passwords.update', $entry) }}">@csrf @method('patch')
                <label>Nome servizio<input name="title" value="{{ $entry->title }}" required></label>
                <label>Sito web<input name="website" value="{{ $entry->website }}" inputmode="url"></label>
                <label>Username / email<input name="username" value="{{ $entry->username }}" autocomplete="off"></label>
                <label>Password
                    <span class="input-with-icon password-field">
                        <input name="password" type="password" value="{{ $entry->password }}" required data-password-input autocomplete="new-password">
                        <button class="field-icon-btn" type="button" data-password-toggle aria-label="Mostra password" title="Mostra password"><svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 10.6A3 3 0 0 0 14 14"/><path d="M9.9 5.4A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.1 4.1"/><path d="M6.5 6.5A18.4 18.4 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 4.2-.9"/></svg></button>
                    </span>
                    @include('passwords._generator')
                </label>
                <fieldset class="check-list">
                    <legend>Condividi</legend>
                    @foreach($users as $member)
                        <label class="check"><input type="checkbox" name="shared_with[]" value="{{ $member->id }}" @checked($sharedIds->contains((string) $member->id))> {{ $member->name }}</label>
                    @endforeach
                </fieldset>
                <label>Note<textarea name="notes" rows="4">{{ $entry->notes }}</textarea></label>
                <button>Salva modifiche</button>
            </form>
        </div>
    </dialog>
@endif
