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
        <form id="eventForm" class="card form-card modal-form hidden" data-modal-title="Nuovo evento" data-modal-actions="event">
          <input type="hidden" name="id">
          <input name="title" placeholder="Titolo evento" required>
          <input type="hidden" name="starts_at">
          <input type="hidden" name="ends_at">
          <div class="soft-picker" data-picker="starts_at"><span>Inizio</span><input name="starts_date" type="date" required><select name="starts_hour"></select><select name="starts_minute"></select></div>
          <div class="soft-picker" data-picker="ends_at"><span>Fine</span><input name="ends_date" type="date"><select name="ends_hour"></select><select name="ends_minute"></select></div>
          <label class="check"><input name="shared" type="checkbox"> condividi con la famiglia</label>
          <textarea name="description" placeholder="Dettagli"></textarea>
          <button class="form-submit">Salva evento</button>
        </form>
      </section>

      <section class="page" data-page="shopping">
        <div class="section-title"><h2>Lista spesa</h2><button data-open="shoppingForm"><span data-icon="plus"></span> Lista</button></div>
        <form id="shoppingForm" class="card form-card modal-form hidden" data-modal-title="Nuova lista spesa" data-modal-actions="crud">
          <input type="hidden" name="id">
          <input name="title" placeholder="Nome lista" value="Spesa">
          <input name="list_date" type="date">
          <label class="check"><input name="shared" type="checkbox"> condivisa con la famiglia</label>
          <div id="productCatalog" class="product-catalog" aria-label="Categorie prodotti"></div>
          <textarea name="items" placeholder="Prodotti selezionati o scritti, uno per riga"></textarea>
          <button>Salva lista</button>
        </form>
        <div id="shoppingLists" class="cards"></div>
      </section>

      <section class="page" data-page="family">
        <div class="section-title"><h2>Famiglia e figli</h2><div class="actions-row"><button data-open="familyForm"><span data-icon="plus"></span> Impegno</button></div></div>
        <form id="familyForm" class="card form-card modal-form hidden" data-modal-title="Nuovo impegno figli" data-modal-actions="crud">
          <input type="hidden" name="id">
          <select name="child_id" data-users="children" required></select>
          <select name="assignee_id" data-users="all"><option value="">Da assegnare</option></select>
          <div class="soft-picker" data-picker="task"><span>Data e ora</span><input name="task_date" type="date" required><select name="task_hour"></select><select name="task_minute"></select></div>
          <input type="hidden" name="task_time">
          <input name="type" placeholder="Es. accompagna all'asilo">
          <select name="recurrence"><option value="none">Non ricorrente</option><option value="daily">Giornaliero</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select>
          <input name="recurrence_count" class="recurrence-count hidden" type="number" min="1" max="52" value="1" placeholder="Numero occorrenze">
          <textarea name="notes" placeholder="Note"></textarea>
          <button>Salva impegno</button>
        </form>
        <div id="familyTasks" class="timeline"></div>
      </section>

      <section class="page" data-page="reminders">
        <div class="section-title"><h2>Promemoria</h2><button data-open="reminderForm"><span data-icon="plus"></span> Promemoria</button></div>
        <form id="reminderForm" class="card form-card modal-form hidden" data-modal-title="Nuovo promemoria" data-modal-actions="crud">
          <input type="hidden" name="id">
          <input name="title" placeholder="Promemoria" required>
          <input type="hidden" name="due_at">
          <div class="soft-picker" data-picker="due_at"><span>Scadenza</span><input name="due_date" type="date"><select name="due_hour"></select><select name="due_minute"></select></div>
          <select name="recurrence"><option value="none">Non ricorrente</option><option value="daily">Giornaliero</option><option value="weekly">Settimanale</option><option value="monthly">Mensile</option></select>
          <label class="check"><input name="shared" type="checkbox"> condiviso</label>
          <button>Salva promemoria</button>
        </form>
        <div id="remindersList" class="cards"></div>
      </section>

      <section class="page" data-page="notes">
        <div class="section-title"><h2>Note</h2><button data-open="noteForm"><span data-icon="plus"></span> Nota</button></div>
        <form id="noteForm" class="card form-card modal-form hidden" data-modal-title="Nuova nota" data-modal-actions="crud">
          <input type="hidden" name="id">
          <input name="title" placeholder="Titolo nota" required>
          <div class="wysiwyg-wrap">
            <div class="wysiwyg-tools"><button type="button" data-wysiwyg="bold"><b>B</b></button><button type="button" data-wysiwyg="italic"><i>I</i></button><button type="button" data-wysiwyg="underline"><u>U</u></button></div>
            <div class="wysiwyg" data-wysiwyg-target="body" contenteditable="true" aria-label="Scrivi qui"></div>
          </div>
          <input type="hidden" name="body">
          <label class="check"><input name="archived" type="checkbox"> archivia subito</label>
          <button>Salva nota</button>
        </form>
        <div id="notesList" class="cards"></div>
      </section>
      <section class="page" data-page="notifications">
        <div class="section-title"><h2>Centro notifiche</h2><button id="markAllReadBtn" type="button">Segna tutte come lette</button></div>
        <div id="notificationsList" class="cards"></div>
      </section>

      <section class="page admin-only" data-page="settings">
        <div class="section-title"><h2>Impostazioni</h2></div>
        <form id="settingsForm" class="card form-card">
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
          <input name="phone" placeholder="Telefono (obbligatorio per adulti e figli dai 14 anni)">
          <input name="email" type="email" placeholder="Email">
          <input name="birth_date" type="date">
          <input name="password" type="password" placeholder="Password iniziale (opzionale per figli)">
          <select name="role"><option value="familiare">Familiare</option><option value="admin">Admin</option></select>
          <select name="category"><option value="mamma">Mamma</option><option value="papà">Papà</option><option value="figlio">Figlio</option><option value="nonno">Nonno</option><option value="nonna">Nonna</option><option value="zio">Zio</option><option value="zia">Zia</option><option value="familiare">Familiare</option></select>
          <div class="parent-fields hidden">
            <select name="parent_id" data-users="parents"><option value="">Mamma/Papà 1 se figlio minore di 14</option></select>
            <select name="second_parent_id" data-users="parents"><option value="">Mamma/Papà 2 se figlio minore di 14</option></select>
          </div>
          <div class="wysiwyg-wrap">
            <div class="wysiwyg-tools"><button type="button" data-wysiwyg="bold"><b>B</b></button><button type="button" data-wysiwyg="italic"><i>I</i></button><button type="button" data-wysiwyg="underline"><u>U</u></button></div>
            <div class="wysiwyg" data-wysiwyg-target="personal_info" contenteditable="true" aria-label="Info personali"></div>
          </div>
          <input type="hidden" name="personal_info">
          <label class="check"><input name="active" type="checkbox" checked> attivo</label>
          <button>Salva utente</button>
        </form>
        <div id="usersList" class="cards"></div>
      </section>

      <form id="profileForm" class="card form-card modal-form hidden" data-modal-title="Profilo personale">
        <input name="email" type="email" placeholder="Email">
        <input name="birth_date" type="date">
        <select name="category"><option value="mamma">Mamma</option><option value="papà">Papà</option><option value="figlio">Figlio</option><option value="nonno">Nonno</option><option value="nonna">Nonna</option><option value="zio">Zio</option><option value="zia">Zia</option><option value="familiare">Familiare</option></select>
        <select name="theme"><option value="system">Tema sistema</option><option value="light">Chiaro</option><option value="dark">Scuro</option></select>
        <textarea name="personal_info" placeholder="Informazioni personali"></textarea>
        <button>Aggiorna profilo</button>
      </form>

      <div id="shoppingDetail" class="card modal-form hidden" data-modal-title="Dettaglio lista spesa">
        <div id="shoppingDetailContent"></div>
      </div>

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
