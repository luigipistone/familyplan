<!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Accesso FamilyPlan</title><link rel="icon" href="/images/app/familyplan-mark.svg" type="image/svg+xml"><link rel="apple-touch-icon" href="/images/app/familyplan-mark-192.png"><link rel="stylesheet" href="/css/app.css"></head>
<body class="auth">
<form class="auth-card" method="post" action="{{ route('login') }}">
    @csrf
    <div class="auth-brand"><img src="/images/app/familyplan-mark.svg" alt=""><h1>FamilyPlan</h1></div>
    <p>Accesso riservato alla famiglia.</p>
    @if(session('status'))<div class="toast">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    <label>Nome<input name="name" value="{{ old('name') }}" autocomplete="username" required></label>
    <label>Password
        <span class="input-with-icon password-field">
            <input type="password" name="password" autocomplete="current-password" data-password-input required>
            <span class="password-toggle" role="button" tabindex="0" data-password-toggle aria-label="Mostra password" title="Mostra password">
                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
            </span>
        </span>
    </label>
    <label class="check"><input type="checkbox" name="remember" checked> Ricordami</label>
    <button>Entra</button>
    <a href="{{ route('password.request') }}">Password dimenticata</a>
</form>
<script src="/js/app.js?v={{ filemtime(public_path('js/app.js')) }}"></script></body></html>
