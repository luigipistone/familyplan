<?php
require __DIR__ . '/src/bootstrap.php';
start_secure_session();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
$assetVersion = substr(hash('sha256', (string) max(
    filemtime(__DIR__ . '/assets/styles.css'),
    filemtime(__DIR__ . '/assets/app.js'),
    filemtime(__DIR__ . '/manifest.webmanifest'),
    filemtime(__DIR__ . '/sw.js')
)), 0, 12);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#8fb9aa">
  <title>FamilyPlan</title>
  <link rel="manifest" href="/manifest.webmanifest?v=<?= $assetVersion ?>">
  <link rel="stylesheet" href="/assets/styles.css?v=<?= $assetVersion ?>">
</head>
<body>
  <div id="app" class="app-shell">
    <section id="auth" class="auth-card">
      <div class="brand-mark" aria-hidden="true">FP</div>
      <h1>FamilyPlan</h1>
      <p>Calendario, figli, spesa e promemoria per una famiglia organizzata.</p>
      <form id="loginForm" class="stack">
        <label>Nome<input name="name" autocomplete="username" required placeholder="Luigi"></label>
        <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
        <button type="submit">Accedi</button>
      </form>
      <details>
        <summary>Recupero password</summary>
        <form id="resetRequestForm" class="inline-form">
          <input name="email" type="email" placeholder="email@dominio.it" required>
          <button>Invia</button>
        </form>
      </details>
    </section>

    <main id="main" class="hidden">
      <header class="topbar">
        <div>
          <span class="eyebrow" id="pageEyebrow">Dashboard</span>
          <h1 id="hello">FamilyPlan</h1>
        </div>
        <div class="top-actions">
          <button id="widgetBtn" class="icon-btn" title="Widget dashboard" aria-label="Widget dashboard"><span data-icon="widgets"></span></button>
          <button id="profileBtn" class="icon-btn" title="Profilo" aria-label="Profilo"><span data-icon="user"></span></button>
          <button id="notifyBtn" class="icon-btn" title="Notifiche" aria-label="Notifiche"><span data-icon="bell"></span><span id="notifBadge"></span></button>
          <button id="themeToggle" class="icon-btn" title="Tema" aria-label="Tema"><span data-icon="moon"></span></button>
          <button id="logoutBtn" class="icon-btn" title="Esci" aria-label="Esci"><span data-icon="logout"></span></button>
        </div>
      </header>

      <nav id="pageNav" class="page-nav" aria-label="Pagine principali"></nav>

      <section class="page active" data-page="dashboard">
        <div class="section-title">
          <div>
            <span class="eyebrow">Riepilogo</span>
            <h2>Dashboard</h2>
          </div>
        </div>
        <section id="dashboard" class="dashboard" aria-label="Widget dashboard"></section>
      </section>

      <section class="page" data-page="calendar">
        <div class="section-title"><h2>Calendario</h2><button data-open="eventForm"><span data-icon="plus"></span> Evento</button></div>
        <div id="calendarGrid" class="calendar-grid"></div>
        <form id="eventForm" class="card form-card modal-form hidden" data-modal-title="Nuovo evento">
          <input name="title" placeholder="Titolo evento" required>
          <input name="starts_at" type="datetime-local" required>
          <input name="ends_at" type="datetime-local">
          <select name="child_id" data-users="children"><option value="">Nessun figlio</option></select>
          <textarea name="description" placeholder="Dettagli"></textarea>
          <button>Salva evento</button>
        </form>
      </section>

      <section class="page" data-page="shopping">
        <div class="section-title"><h2>Lista spesa</h2><button data-open="shoppingForm"><span data-icon="plus"></span> Lista</button></div>
        <form id="shoppingForm" class="card form-card modal-form hidden" data-modal-title="Nuova lista spesa">
          <input name="title" placeholder="Nome lista" value="Spesa">
          <input name="list_date" type="date">
          <label class="check"><input name="shared" type="checkbox"> condivisa con la famiglia</label>
          <textarea name="items" placeholder="Un prodotto per riga"></textarea>
          <button>Salva lista</button>
        </form>
        <div id="shoppingLists" class="cards"></div>
      </section>

      <section class="page" data-page="family">
        <div class="section-title"><h2>Famiglia e figli</h2><div class="actions-row"><button data-open="childForm"><span data-icon="plus"></span> Figlio</button><button data-open="familyForm"><span data-icon="plus"></span> Impegno</button></div></div>
        <form id="childForm" class="card form-card modal-form hidden" data-modal-title="Nuovo utente figlio">
          <input name="name" placeholder="Nome figlio" required>
          <input name="birth_date" type="date" required>
          <input name="email" type="email" placeholder="Email opzionale">
          <select name="parent_id" data-users="parents"><option value="">Genitore associato</option></select>
          <textarea name="personal_info" placeholder="Informazioni gestibili dal genitore"></textarea>
          <input type="hidden" name="role" value="familiare">
          <input type="hidden" name="category" value="figlio">
          <button>Salva utente figlio</button>
        </form>
        <form id="familyForm" class="card form-card modal-form hidden" data-modal-title="Nuovo impegno figli">
          <select name="child_id" data-users="children" required></select>
          <select name="assignee_id" data-users="all"><option value="">Da assegnare</option></select>
          <input name="task_date" type="date" required>
          <input name="task_time" type="time">
          <input name="type" placeholder="Es. accompagna all'asilo">
          <textarea name="notes" placeholder="Note"></textarea>
          <button>Salva impegno</button>
        </form>
        <div id="familyTasks" class="timeline"></div>
      </section>

      <section class="page" data-page="reminders">
        <div class="section-title"><h2>Promemoria</h2><button data-open="reminderForm"><span data-icon="plus"></span> Promemoria</button></div>
        <form id="reminderForm" class="card form-card modal-form hidden" data-modal-title="Nuovo promemoria">
          <input name="title" placeholder="Promemoria" required>
          <input name="due_at" type="datetime-local">
          <select name="recurrence"><option value="none">Non ricorrente</option><option value="daily">Giornaliero</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select>
          <label class="check"><input name="shared" type="checkbox"> condiviso</label>
          <button>Salva promemoria</button>
        </form>
        <div id="remindersList" class="cards"></div>
      </section>

      <section class="page" data-page="notes">
        <div class="section-title"><h2>Note</h2><button data-open="noteForm"><span data-icon="plus"></span> Nota</button></div>
        <form id="noteForm" class="card form-card modal-form hidden" data-modal-title="Nuova nota">
          <input name="title" placeholder="Titolo nota" required>
          <textarea name="body" placeholder="Scrivi qui"></textarea>
          <label class="check"><input name="archived" type="checkbox"> archivia subito</label>
          <button>Salva nota</button>
        </form>
        <div id="notesList" class="cards"></div>
      </section>

      <section class="page admin-only" data-page="settings">
        <div class="section-title"><h2>Impostazioni</h2><button data-open="settingsForm"><span data-icon="edit"></span> Modifica</button></div>
        <form id="settingsForm" class="card form-card modal-form hidden" data-modal-title="Impostazioni famiglia">
          <label>Nome famiglia<input name="family_name" placeholder="Casa Rossi"></label>
          <label>Archivio automatico spesa (giorni)<input name="shopping_archive_days" type="number" value="7"></label>
          <label>Notifiche silenziose dalle<input name="quiet_hours" placeholder="22:00-07:00"></label>
          <button>Salva impostazioni</button>
        </form>
      </section>

      <section class="page admin-only" data-page="users">
        <div class="section-title"><h2>Utenti</h2><button data-open="userForm"><span data-icon="plus"></span> Utente</button></div>
        <form id="userForm" class="card form-card modal-form hidden" data-modal-title="Nuovo utente">
          <input name="name" placeholder="Nome" required>
          <input name="phone" placeholder="Telefono (obbligatorio tranne figli)">
          <input name="email" type="email" placeholder="Email">
          <input name="birth_date" type="date">
          <input name="password" type="password" placeholder="Password iniziale (opzionale per figli)">
          <select name="role"><option value="familiare">Familiare</option><option value="admin">Admin</option></select>
          <select name="category"><option>mamma</option><option>papà</option><option>figlio</option><option>nonno</option><option>zia</option><option>familiare</option></select>
          <select name="parent_id" data-users="parents"><option value="">Genitore associato se figlio</option></select>
          <textarea name="personal_info" placeholder="Info personali"></textarea>
          <label class="check"><input name="active" type="checkbox" checked> attivo</label>
          <button>Salva utente</button>
        </form>
        <div id="usersList" class="cards"></div>
      </section>

      <form id="profileForm" class="card form-card modal-form hidden" data-modal-title="Profilo personale">
        <input name="email" type="email" placeholder="Email">
        <input name="birth_date" type="date">
        <select name="theme"><option value="system">Tema sistema</option><option value="light">Chiaro</option><option value="dark">Scuro</option></select>
        <textarea name="personal_info" placeholder="Informazioni personali"></textarea>
        <button>Aggiorna profilo</button>
      </form>

      <form id="widgetForm" class="card form-card modal-form hidden" data-modal-title="Widget dashboard">
        <div id="widgetChoices" class="widget-choices"></div>
        <button>Salva widget</button>
      </form>
    </main>

    <div id="modalBackdrop" class="modal-backdrop" aria-hidden="true"></div>
    <div id="toast" class="toast" role="status"></div>
  </div>
  <script>window.FP_CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;</script>
  <script src="/assets/app.js?v=<?= $assetVersion ?>" defer></script>
</body>
</html>
