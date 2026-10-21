<!doctype html>
<html lang="it" data-theme="{{ auth()->user()->theme ?? 'light' }}" data-compact="{{ auth()->user()->compact_dashboard ? 'true' : 'false' }}">
<head>
    @php($needsVue = request()->routeIs('dashboard', 'calendar*', 'shopping*', 'family*', 'children*', 'reminders*', 'notes*', 'users*', 'profile*', 'settings*', 'loyalty-cards*', 'print-quotes*'))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#7c9a92">
    <title>{{ $title ?? 'FamilyPlan' }}</title>
    <link rel="icon" href="/images/app/familyplan-mark.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/app/familyplan-mark-192.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="stylesheet" href="/css/app.css?v={{ filemtime(public_path('css/app.css')) }}">
    @if($needsVue && file_exists(public_path('build/manifest.json')))
        @vite('resources/js/app.js')
    @endif
</head>
<body
    data-push-notifications="{{ auth()->user()->push_notifications ? '1' : '0' }}"
    data-push-public-key="{{ config('push.vapid.public_key') }}"
    data-push-sync-url="{{ route('push-subscriptions.sync') }}"
>
    @php($isDashboard = request()->routeIs('dashboard'))
    @php($isCalendar = request()->routeIs('calendar*'))
    @php($isShopping = request()->routeIs('shopping*'))
    @php($isFamily = request()->routeIs('family*', 'children*'))
    @php($isReminders = request()->routeIs('reminders*'))
    @php($isNotes = request()->routeIs('notes*'))
    @php($isUsers = request()->routeIs('users*'))
    @php($isSettings = request()->routeIs('settings*'))
    @php($isContacts = request()->routeIs('contacts*'))
    @php($isLoyaltyCards = request()->routeIs('loyalty-cards*'))
    @php($isCoins = request()->routeIs('coins*'))
    @php($isPrintQuotes = request()->routeIs('print-quotes*'))
    @php($isPasswords = request()->routeIs('passwords*'))
    @php($isCurrency = request()->routeIs('currency*'))
    @php($isTranslator = request()->routeIs('translator*'))
    @php($isSoundboard = request()->routeIs('soundboard*'))
    @php($isMovies = request()->routeIs('movies*'))
    @php($isProfile = request()->routeIs('profile*'))
    @php($isUtility = $isUsers || $isSettings || $isContacts || $isLoyaltyCards || $isCoins || $isPrintQuotes || $isPasswords || $isCurrency || $isTranslator || $isSoundboard || $isMovies)
    @php($isAdmin = auth()->user()->isAdmin())
    <header class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img class="brand-mark" src="/images/app/familyplan-mark.svg" alt="">
            <span>FamilyPlan</span>
        </a>
        <div class="top-actions">
            <button class="icon-btn" type="button" data-open-widget-modal title="Widget dashboard" aria-label="Widget dashboard">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1.5"/><rect x="14" y="4" width="6" height="6" rx="1.5"/><rect x="4" y="14" width="6" height="6" rx="1.5"/><rect x="14" y="14" width="6" height="6" rx="1.5"/></svg>
            </button>
            <button class="icon-btn" type="button" data-theme-toggle data-theme-url="{{ route('theme.update') }}" title="Cambia tema" aria-label="Cambia tema">
                <svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                <svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.2A7.6 7.6 0 0 1 9.8 4a8.3 8.3 0 1 0 10.2 10.2Z"/></svg>
            </button>
            <div class="top-menu" data-top-menu>
                <button @class(['icon-btn', 'is-active' => $isUtility]) type="button" data-top-menu-toggle aria-label="Apri menu strumenti" aria-expanded="false" aria-controls="tools-menu-panel" title="Strumenti">
                    <svg class="menu-hamburger" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M8 12h11M5 17h14"/></svg>
                </button>
                <button class="top-menu-backdrop" type="button" data-top-menu-backdrop aria-label="Chiudi menu strumenti" hidden></button>
                <aside class="top-menu-panel" id="tools-menu-panel" data-top-menu-panel role="dialog" aria-modal="true" aria-label="Strumenti" hidden>
                    <div class="top-menu-head">
                        <button class="icon-btn" type="button" data-top-menu-close aria-label="Chiudi menu strumenti" title="Chiudi">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                        </button>
                    </div>
                    <div class="top-menu-scroll">
                        <section class="top-menu-group">
                            <a class="top-menu-link" href="{{ route('dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1Z"/></svg><span>Home</span></a>
                            <a class="top-menu-link" href="{{ route('calendar') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg><span>Calendario</span></a>
                            <a class="top-menu-link" href="{{ route('shopping') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h15l-2 8H8L6 4H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>Spesa</span></a>
                            <a class="top-menu-link" href="{{ route('family') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M13.5 20a4.5 4.5 0 0 1 7 0"/></svg><span>Famiglia</span></a>
                            <a class="top-menu-link" href="{{ route('reminders') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg><span>Memo</span></a>
                            <a class="top-menu-link" href="{{ route('notes') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg><span>Note</span></a>
                        </section>
                        <section class="top-menu-group">
                            <a @class(['top-menu-link', 'is-active' => $isContacts]) href="{{ route('contacts') }}" @if($isContacts) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6z"/><path d="M8.5 9a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Z"/><path d="M7 18v-1a2.5 2.5 0 0 1 5 0v1"/><path d="M13 8h4M13 12h4M13 16h4"/></svg><span>Contatti</span></a>
                            <a @class(['top-menu-link', 'is-active' => $isLoyaltyCards]) href="{{ route('loyalty-cards') }}" @if($isLoyaltyCards) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h5M7 14h3M16 9v6"/></svg><span>Carte fedeltà</span></a>
                            <a @class(['top-menu-link', 'is-active' => $isPasswords]) href="{{ route('passwords') }}" @if($isPasswords) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v3"/></svg><span>Password</span></a>
                        </section>
                        <section class="top-menu-group">
                            <a @class(['top-menu-link', 'is-active' => $isPrintQuotes]) href="{{ route('print-quotes') }}" @if($isPrintQuotes) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8V4h12v4M6 17H4V9h16v8h-2"/><path d="M7 14h10v7H7z"/><path d="M9 11h1"/></svg><span>Costi 3D</span></a>
                            <a @class(['top-menu-link', 'is-active' => $isCurrency]) href="{{ route('currency') }}" @if($isCurrency) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h11a4 4 0 0 1 0 8H6"/><path d="M9 5 6 8l3 3"/><path d="M15 19l3-3-3-3"/></svg><span>Cambio valuta</span></a>
                            <a @class(['top-menu-link', 'is-active' => $isTranslator]) href="{{ route('translator') }}" @if($isTranslator) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h10"/><path d="M9 5a14 14 0 0 1-5 9"/><path d="M7 9a10 10 0 0 0 6 5"/><path d="M14 5h6"/><path d="M17 5v14"/><path d="m14 16 3-7 3 7"/></svg><span>Traduttore</span></a>
                            <a class="top-menu-link" href="{{ route('soundboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v6M9 6v12M13 8v8M17 4v16M21 10v4"/></svg><span>Soundboard</span></a>
                            <a @class(['top-menu-link', 'is-active' => $isMovies]) href="{{ route('movies') }}" @if($isMovies) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 9 4-4M9 9l4-4M15 9l4-4M3 9h18"/></svg><span>Film</span></a>
                        </section>
                        @if(auth()->user()->can_manage_coin_collection)
                            <section class="top-menu-group">
                                <a @class(['top-menu-link', 'is-active' => $isCoins]) href="{{ route('coins') }}" @if($isCoins) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M9 9.5c.8-1 2-1.5 3.4-1.5 2 0 3.6 1 3.6 2.5 0 3.5-7 1.5-7 5 0 1.5 1.6 2.5 3.6 2.5 1.4 0 2.6-.5 3.4-1.5"/></svg><span>Collezione monete</span></a>
                            </section>
                        @endif
                        @if($isAdmin)
                            <section class="top-menu-group">
                                <a class="top-menu-link" href="{{ route('users') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 11a2.5 2.5 0 1 0 0-5"/><path d="M17 20a4.5 4.5 0 0 0-2-3.7"/></svg><span>Utenti</span></a>
                                <a @class(['top-menu-link', 'is-active' => $isSettings]) href="{{ route('settings') }}" @if($isSettings) aria-current="page" @endif><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6l-.04.08a2 2 0 0 1-3.92 0L10 20a1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1l-.08-.04a2 2 0 0 1 0-3.92L4 10a1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6l.04-.08a2 2 0 0 1 3.92 0L14 4a1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.37.4.7.6 1l.08.04a2 2 0 0 1 0 3.92L20 14a1.7 1.7 0 0 0-.6 1Z"/></svg><span>Impostazioni</span></a>
                            </section>
                        @endif
                    </div>
                </aside>
            </div>
            <a @class(['icon-btn', 'avatar-btn', 'is-active' => $isProfile]) href="{{ route('profile') }}" title="Profilo" aria-label="Profilo" @if($isProfile) aria-current="page" @endif>
                @if(auth()->user()->profilePhotoUrl())
                    <img class="avatar avatar-sm" src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}">
                @else
                    <span class="avatar avatar-sm avatar-fallback">{{ auth()->user()->avatarInitials() }}</span>
                @endif
            </a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="icon-btn" title="Esci" aria-label="Esci"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6H6.8A1.8 1.8 0 0 0 5 7.8v8.4A1.8 1.8 0 0 0 6.8 18H10"/><path d="M14 8l4 4-4 4M18 12H9"/></svg></button></form>
        </div>
    </header>

    <main class="shell">
        @if(session('ok') || session('status'))<div class="toast">{{ session('ok') ?? session('status') }}</div>@endif
        @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
        {{ $slot }}
    </main>

    <script src="/js/app.js?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</body>
</html>
