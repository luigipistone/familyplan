<?php
require __DIR__ . '/src/bootstrap.php';
start_secure_session();
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#8fb9aa">
  <title>FamilyPlan</title>
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <div id="app" class="app-shell">
    <section id="auth" class="auth-card">
      <div class="brand-mark">FP</div>
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
          <span class="eyebrow">Benvenuto</span>
          <h1 id="hello">FamilyPlan</h1>
        </div>
        <div class="top-actions">
          <button id="notifyBtn" class="icon-btn" title="Notifiche">🔔<span id="notifBadge"></span></button>
          <button id="themeToggle" class="icon-btn" title="Tema">🌗</button>
          <button id="logoutBtn" class="icon-btn" title="Esci">↪</button>
        </div>
      </header>

      <section id="dashboard" class="dashboard"></section>

      <nav id="tabs" class="tabs" aria-label="Sezioni"></nav>

      <section class="panel active" data-panel="calendar">
        <div class="section-title"><h2>Calendario</h2><button data-open="eventForm">+ Evento</button></div>
        <div id="calendarGrid" class="calendar-grid"></div>
        <form id="eventForm" class="card form-card hidden">
          <input name="title" placeholder="Titolo evento" required>
          <input name="starts_at" type="datetime-local" required>
          <input name="ends_at" type="datetime-local">
          <select name="child_id" data-users="children"><option value="">Nessun figlio</option></select>
          <textarea name="description" placeholder="Dettagli"></textarea>
          <button>Salva evento</button>
        </form>
      </section>

      <section class="panel" data-panel="shopping">
        <div class="section-title"><h2>Lista spesa</h2><button data-open="shoppingForm">+ Lista</button></div>
        <form id="shoppingForm" class="card form-card hidden">
          <input name="title" placeholder="Nome lista" value="Spesa">
          <input name="list_date" type="date">
          <label class="check"><input name="shared" type="checkbox"> condivisa con la famiglia</label>
          <textarea name="items" placeholder="Un prodotto per riga"></textarea>
          <button>Salva lista</button>
        </form>
        <div id="shoppingLists" class="cards"></div>
      </section>

      <section class="panel" data-panel="family">
        <div class="section-title"><h2>Famiglia e figli</h2><div><button data-open="childForm">+ Figlio</button> <button data-open="familyForm">+ Impegno</button></div></div>
        <form id="childForm" class="card form-card hidden">
          <input name="name" placeholder="Nome figlio" required>
          <input name="birth_date" type="date" required>
          <input name="email" type="email" placeholder="Email opzionale">
          <textarea name="personal_info" placeholder="Informazioni gestibili dal genitore"></textarea>
          <input type="hidden" name="category" value="figlio">
          <button>Salva profilo figlio</button>
        </form>
        <form id="familyForm" class="card form-card hidden">
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

      <section class="panel" data-panel="reminders">
        <div class="section-title"><h2>Promemoria</h2><button data-open="reminderForm">+ Promemoria</button></div>
        <form id="reminderForm" class="card form-card hidden">
          <input name="title" placeholder="Promemoria" required>
          <input name="due_at" type="datetime-local">
          <select name="recurrence"><option value="none">Non ricorrente</option><option value="daily">Giornaliero</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select>
          <label class="check"><input name="shared" type="checkbox"> condiviso</label>
          <button>Salva promemoria</button>
        </form>
        <div id="remindersList" class="cards"></div>
      </section>

      <section class="panel" data-panel="notes">
        <div class="section-title"><h2>Note</h2><button data-open="noteForm">+ Nota</button></div>
        <form id="noteForm" class="card form-card hidden">
          <input name="title" placeholder="Titolo nota" required>
          <textarea name="body" placeholder="Scrivi qui"></textarea>
          <label class="check"><input name="archived" type="checkbox"> archivia subito</label>
          <button>Salva nota</button>
        </form>
        <div id="notesList" class="cards"></div>
      </section>

      <section class="panel" data-panel="profile">
        <div class="section-title"><h2>Profilo</h2></div>
        <form id="profileForm" class="card form-card">
          <input name="email" type="email" placeholder="Email">
          <input name="birth_date" type="date">
          <select name="theme"><option value="system">Tema sistema</option><option value="light">Chiaro</option><option value="dark">Scuro</option></select>
          <textarea name="personal_info" placeholder="Informazioni personali"></textarea>
          <button>Aggiorna profilo</button>
        </form>
      </section>

      <section class="panel admin-only" data-panel="settings">
        <div class="section-title"><h2>Impostazioni</h2></div>
        <form id="settingsForm" class="card form-card">
          <label>Nome famiglia<input name="family_name" placeholder="Casa Rossi"></label>
          <label>Archivio automatico spesa (giorni)<input name="shopping_archive_days" type="number" value="7"></label>
          <label>Notifiche silenziose dalle<input name="quiet_hours" placeholder="22:00-07:00"></label>
          <button>Salva impostazioni</button>
        </form>
      </section>

      <section class="panel admin-only" data-panel="users">
        <div class="section-title"><h2>Utenti</h2><button data-open="userForm">+ Utente</button></div>
        <form id="userForm" class="card form-card hidden">
          <input name="name" placeholder="Nome" required>
          <input name="phone" placeholder="Telefono (chiave utente)">
          <input name="email" type="email" placeholder="Email">
          <input name="birth_date" type="date">
          <input name="password" type="password" placeholder="Password iniziale">
          <select name="role"><option value="familiare">Familiare</option><option value="admin">Admin</option></select>
          <select name="category"><option>mamma</option><option>papà</option><option>figlio</option><option>nonno</option><option>zia</option><option>familiare</option></select>
          <select name="parent_id" data-users="parents"><option value="">Genitore associato</option></select>
          <textarea name="personal_info" placeholder="Info personali"></textarea>
          <label class="check"><input name="active" type="checkbox" checked> attivo</label>
          <button>Salva utente</button>
        </form>
        <div id="usersList" class="cards"></div>
      </section>
    </main>

    <div id="toast" class="toast" role="status"></div>
  </div>
  <script>window.FP_CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;</script>
  <script src="/assets/app.js" defer></script>
</body>
</html>
