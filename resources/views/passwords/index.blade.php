<x-layouts.app title="Password">
    <section class="page-head">
        <div><h1>Password</h1><p>Cassaforte familiare per accessi privati o condivisi.</p></div>
        <div class="page-actions">
            <button class="fab-inline" type="button" data-open-modal="#password-create-modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Aggiungi password</span>
            </button>
        </div>
    </section>

    <section class="panel loyalty-tools">
        <label class="contact-search">
            <span>Cerca</span>
            <span class="search-field"><input type="search" placeholder="Servizio, sito o username" data-password-search><button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></span>
        </label>
    </section>

    <dialog class="modal user-modal" id="password-create-modal" aria-labelledby="password-create-title">
        <form class="modal-card form-stack" method="post" action="{{ route('passwords.store') }}">@csrf
            <div class="modal-head">
                <div><p>Nuova</p><h2 id="password-create-title">Aggiungi password</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            <label>Nome servizio<input name="title" required placeholder="Es. Netflix, Gmail, Banca"></label>
            <label>Sito web<input name="website" inputmode="url" placeholder="https://"></label>
            <label>Username / email<input name="username" autocomplete="off"></label>
            <label>Password
                <span class="input-with-icon password-field">
                    <input name="password" type="password" required data-password-input autocomplete="new-password">
                    <button class="field-icon-btn" type="button" data-password-toggle aria-label="Mostra password" title="Mostra password">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 10.6A3 3 0 0 0 14 14"/><path d="M9.9 5.4A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.1 4.1"/><path d="M6.5 6.5A18.4 18.4 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 4.2-.9"/></svg>
                    </button>
                </span>
                @include('passwords._generator')
            </label>
            <fieldset class="check-list">
                <legend>Condividi</legend>
                @foreach($users as $member)
                    <label class="check"><input type="checkbox" name="shared_with[]" value="{{ $member->id }}"> {{ $member->name }}</label>
                @endforeach
            </fieldset>
            <label>Note<textarea name="notes" rows="4" placeholder="Domande di sicurezza, istruzioni, dettagli utili"></textarea></label>
            <button>Salva password</button>
        </form>
    </dialog>

    @if($myEntries->isNotEmpty())
        <section class="stack password-section">
            <div class="page-head loyalty-subhead"><div><h2>Le mie password</h2></div></div>
            <div class="grid password-grid">
                @foreach($myEntries as $entry)
                    @include('passwords._entry', ['entry' => $entry, 'owned' => true])
                @endforeach
            </div>
        </section>
    @endif

    <section class="stack password-section">
        <div class="page-head loyalty-subhead"><div><h2>Condivise con me</h2></div></div>
        <div class="grid password-grid">
            @forelse($sharedEntries as $entry)
                @include('passwords._entry', ['entry' => $entry, 'owned' => false])
            @empty
                @if($myEntries->isEmpty())
                    <article class="card"><p class="muted">Nessuna password salvata.</p></article>
                @else
                    <article class="card"><p class="muted">Nessuna password condivisa con te.</p></article>
                @endif
            @endforelse
        </div>
    </section>
</x-layouts.app>
