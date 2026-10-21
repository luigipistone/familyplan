<x-layouts.app title="Profilo">
    <section class="page-head"><h1>Profilo</h1><p>Dati personali e contatti.</p></section>
    <form class="panel" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">@csrf @method('patch')
        @if(file_exists(public_path('build/manifest.json')))
            <div
                data-vue-profile-fields
                data-name="{{ $user->name }}"
                data-phone="{{ $user->phone }}"
                data-email="{{ $user->email }}"
                data-birth-date="{{ optional($user->birth_date)->format('Y-m-d') }}"
                data-personal-info-b64="{{ base64_encode(json_encode($user->personal_info)) }}"
                data-profile-photo-url="{{ $user->profilePhotoUrl() }}"
                data-favorite-team="{{ $user->favorite_team }}"
                data-avatar-initials="{{ $user->avatarInitials() }}"
            ></div>
        @else
            <div class="avatar-upload-panel">
                @if($user->profilePhotoUrl())
                    <img class="avatar avatar-xl" src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}">
                @else
                    <span class="avatar avatar-xl avatar-fallback">{{ $user->avatarInitials() }}</span>
                @endif
                <label>Immagine profilo<input type="file" name="profile_photo" accept="image/*"></label>
            </div>
            <div class="two">
                <label>Nome<input name="name" value="{{ $user->name }}" required></label>
                <label>Telefono<input name="phone" value="{{ $user->phone }}"></label>
            </div>
            <div class="two">
                <label>Email<input type="email" name="email" value="{{ $user->email }}"></label>
                <label>Data di nascita<input type="date" name="birth_date" value="{{ optional($user->birth_date)->format('Y-m-d') }}"></label>
            </div>
            <label>Squadra del cuore<input name="favorite_team" value="{{ $user->favorite_team }}" placeholder="Es. Milan, Juventus, Inter, Arsenal"></label>
            <label>Informazioni personali<textarea name="personal_info" rows="5">{{ $user->personal_info }}</textarea></label>
        @endif
        <section class="setting-card form-stack">
            <h2>Password</h2>
            <p>Per cambiare password inserisci prima quella attuale.</p>
            <div class="two">
                <label>Password attuale
                    <span class="input-with-icon password-field">
                        <input type="password" name="current_password" autocomplete="current-password" data-password-input>
                        <span class="password-toggle" role="button" tabindex="0" data-password-toggle aria-label="Mostra password" title="Mostra password">
                            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
                        </span>
                    </span>
                </label>
                <label>Nuova password
                    <span class="input-with-icon password-field">
                        <input type="password" name="password" minlength="12" autocomplete="new-password" data-password-input>
                        <span class="password-toggle" role="button" tabindex="0" data-password-toggle aria-label="Mostra password" title="Mostra password">
                            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
                        </span>
                    </span>
                </label>
            </div>
            <label>Conferma nuova password
                <span class="input-with-icon password-field">
                    <input type="password" name="password_confirmation" minlength="12" autocomplete="new-password" data-password-input>
                    <span class="password-toggle" role="button" tabindex="0" data-password-toggle aria-label="Mostra password" title="Mostra password">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
                    </span>
                </span>
            </label>
        </section>
        <button>Salva profilo</button>
    </form>
</x-layouts.app>
