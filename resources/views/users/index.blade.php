<x-layouts.app title="Utenti">
    <section class="page-head">
        <div><h1>Utenti</h1><p>Gestione profili e accessi. L’accesso avviene con nome e password.</p></div>
        <button class="fab-inline" type="button" data-open-modal="#user-create-modal">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span>Aggiungi utente</span>
        </button>
    </section>

    <dialog class="modal user-modal" id="user-create-modal" aria-labelledby="user-create-title" data-reset-event="user-form:reset:create">
        <form class="modal-card" method="post" action="{{ route('users.store') }}" data-user-form enctype="multipart/form-data">@csrf
            <div class="modal-head">
                <div><p>Nuovo profilo</p><h2 id="user-create-title">Aggiungi utente</h2></div>
                <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
            @if(file_exists(public_path('build/manifest.json')))
                <div
                    data-vue-user-fields
                    data-form-key="create"
                    data-show-is-active="0"
                    data-mothers-b64="{{ base64_encode(json_encode($mothers->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                    data-fathers-b64="{{ base64_encode(json_encode($fathers->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                ></div>
            @else
                @include('users.partials.form-fields', ['user' => null, 'mothers' => $mothers, 'fathers' => $fathers])
            @endif
            <button>Crea utente</button>
        </form>
    </dialog>

    <dialog class="modal user-modal" id="user-edit-modal" aria-labelledby="user-edit-title">
        <div class="modal-card">
            <div class="modal-head">
                <div><p>Profilo esistente</p><h2 id="user-edit-title">Modifica utente</h2></div>
                <div class="modal-actions">
                    <form method="post" action="" data-delete-user-form data-confirm="Vuoi davvero eliminare questo utente?">@csrf @method('delete')
                        <button class="icon-btn danger-icon" type="submit" aria-label="Elimina utente" title="Elimina utente"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button>
                    </form>
                    <button class="icon-btn" type="button" data-close-modal aria-label="Chiudi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
            </div>
            <form method="post" action="" data-user-form data-edit-user-form enctype="multipart/form-data">@csrf @method('patch')
                @if(file_exists(public_path('build/manifest.json')))
                    <div
                        data-vue-user-fields
                        data-form-key="edit"
                        data-show-is-active="1"
                        data-mothers-b64="{{ base64_encode(json_encode($mothers->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                        data-fathers-b64="{{ base64_encode(json_encode($fathers->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->values())) }}"
                    ></div>
                @else
                    @include('users.partials.form-fields', ['user' => null, 'mothers' => $mothers, 'fathers' => $fathers, 'edit' => true])
                @endif
                <button>Salva modifiche</button>
            </form>
        </div>
    </dialog>

    <div class="stack">
        @foreach($users as $u)
            <button class="card user-card" type="button"
                data-edit-user
                data-user-action="{{ route('users.update', $u) }}"
                data-user-delete-action="{{ route('users.destroy', $u) }}"
                data-user-name="{{ $u->name }}"
                data-user-phone="{{ $u->phone }}"
                data-user-email="{{ $u->email }}"
                data-user-role="{{ $u->role }}"
                data-user-category="{{ $u->category }}"
                data-user-mother-id="{{ $u->mother_id }}"
                data-user-father-id="{{ $u->father_id }}"
                data-user-birth-date="{{ optional($u->birth_date)->format('Y-m-d') }}"
                data-user-personal-info="{{ $u->personal_info }}"
                data-user-profile-photo-url="{{ $u->profilePhotoUrl() }}"
                data-user-avatar-initials="{{ $u->avatarInitials() }}"
                data-user-is-active="{{ $u->is_active ? '1' : '0' }}">
                <span class="user-card-main">
                    @if($u->profilePhotoUrl())
                        <img class="avatar avatar-md" src="{{ $u->profilePhotoUrl() }}" alt="{{ $u->name }}">
                    @else
                        <span class="avatar avatar-md avatar-fallback">{{ $u->avatarInitials() }}</span>
                    @endif
                    <span>
                        <strong>{{ $u->name }}</strong>
                        <small>{{ $u->role }} · {{ $u->category }} @if($u->mother)· mamma {{ $u->mother->name }}@endif @if($u->father)· papà {{ $u->father->name }}@endif</small>
                    </span>
                </span>
                <span class="user-meta">{{ $u->phone ?: 'senza telefono' }} {{ $u->is_active ? '' : '· disattivato' }}</span>
            </button>
        @endforeach
    </div>
</x-layouts.app>
