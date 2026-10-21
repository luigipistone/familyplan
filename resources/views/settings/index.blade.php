<x-layouts.app title="Impostazioni">
    <section class="page-head"><h1>Impostazioni</h1><p>Preferenze dell’applicazione.</p></section>
    <form class="panel" method="post" action="{{ route('settings.update') }}">@csrf @method('patch')
        @if(file_exists(public_path('build/manifest.json')))
            <div
                data-vue-settings-fields
                data-theme="{{ $user->theme }}"
                data-week-starts-on="{{ $user->week_starts_on ?? 'monday' }}"
                data-compact-dashboard="{{ $user->compact_dashboard ? '1' : '0' }}"
                data-email-notifications="{{ ($user->email_notifications ?? true) ? '1' : '0' }}"
                data-push-notifications="{{ ($user->push_notifications ?? true) ? '1' : '0' }}"
                data-calendar-reminder-minutes="{{ $user->calendar_reminder_minutes ?? 30 }}"
                data-shopping-archive-days="{{ $user->shopping_archive_days ?? 7 }}"
            ></div>
        @else
            <div class="settings-grid">
                <section class="setting-card">
                    <h2>Aspetto</h2>
                    <div class="two">
                        <label>Tema<select name="theme"><option value="light" @selected($user->theme==='light')>Chiaro</option><option value="dark" @selected($user->theme==='dark')>Scuro</option></select></label>
                        <label>Settimana<select name="week_starts_on"><option value="monday" @selected(($user->week_starts_on ?? 'monday') === 'monday')>Inizia di lunedì</option><option value="sunday" @selected(($user->week_starts_on ?? 'monday') === 'sunday')>Inizia di domenica</option></select></label>
                    </div>
                    <label class="check"><input type="checkbox" name="compact_dashboard" value="1" @checked($user->compact_dashboard)> Dashboard più compatta</label>
                </section>
                <section class="setting-card">
                    <h2>Notifiche</h2>
                    <p>Scegli quali canali usare per promemoria, eventi condivisi e aggiornamenti importanti.</p>
                    <div class="two">
                        <label class="check"><input type="checkbox" name="email_notifications" value="1" @checked($user->email_notifications ?? true)> Email attive</label>
                        <label class="check"><input type="checkbox" name="push_notifications" value="1" @checked($user->push_notifications ?? true)> Notifiche dispositivo</label>
                    </div>
                    <label>Preavviso calendario<select name="calendar_reminder_minutes">
                        <option value="0" @selected(($user->calendar_reminder_minutes ?? 30) === 0)>Nessun preavviso</option>
                        <option value="10" @selected(($user->calendar_reminder_minutes ?? 30) === 10)>10 minuti prima</option>
                        <option value="30" @selected(($user->calendar_reminder_minutes ?? 30) === 30)>30 minuti prima</option>
                        <option value="60" @selected(($user->calendar_reminder_minutes ?? 30) === 60)>1 ora prima</option>
                        <option value="1440" @selected(($user->calendar_reminder_minutes ?? 30) === 1440)>1 giorno prima</option>
                    </select></label>
                </section>
                <section class="setting-card">
                    <h2>Liste spesa</h2>
                    <p>Imposta dopo quanti giorni archiviare automaticamente una lista completata.</p>
                    <label>Archivia dopo<input type="number" name="shopping_archive_days" min="1" max="30" value="{{ $user->shopping_archive_days ?? 7 }}"></label>
                </section>
            </div>
        @endif
        <section class="setting-card form-stack">
            <h2>Backup</h2>
            <p>Gestisci la frequenza dei backup automatici e il ripristino dei backup disponibili.</p>
            <label>Frequenza backup
                <select name="backup_frequency">
                    <option value="manual" @selected($backupFrequency === 'manual')>Solo manuale</option>
                    <option value="daily" @selected($backupFrequency === 'daily')>Giornaliero</option>
                    <option value="weekly" @selected($backupFrequency === 'weekly')>Settimanale</option>
                    <option value="monthly" @selected($backupFrequency === 'monthly')>Mensile</option>
                </select>
            </label>
        </section>
        <button>Salva</button>
    </form>
    <section class="panel backup-panel">
        <div class="page-head compact-head">
            <div><h2>Backup disponibili</h2><p>Il ripristino sovrascrive database e file salvati.</p></div>
            <form method="post" action="{{ route('settings.backup.create') }}" data-confirm="Vuoi creare subito un nuovo backup manuale?">@csrf<button class="pill" type="submit">Crea backup ora</button></form>
        </div>
        <div class="stack">
            @forelse($backups as $backup)
                <article class="card backup-row">
                    <div>
                        <strong>{{ $backup['label'] }}</strong>
                        <p>{{ $backup['type'] }} · {{ $backup['size'] }}</p>
                    </div>
                    <form method="post" action="{{ route('settings.backup.restore') }}" data-confirm="Sei sicuro di voler ripristinare questo backup? L'operazione sovrascrive i dati attuali.">
                        @csrf
                        <input type="hidden" name="backup_filename" value="{{ $backup['filename'] }}">
                        <button class="pill" type="submit">Ripristina</button>
                    </form>
                </article>
            @empty
                <article class="card"><p class="muted">Nessun backup disponibile.</p></article>
            @endforelse
        </div>
    </section>
</x-layouts.app>
