const state = { user: null, csrf: window.FP_CSRF, users: [], widgets: [], data: {}, page: 'dashboard' };

const pageDefs = {
  dashboard: ['Dashboard', 'home'],
  calendar: ['Calendario', 'calendar'],
  shopping: ['Spesa', 'shopping'],
  family: ['Famiglia', 'family'],
  reminders: ['Promemoria', 'bell'],
  notes: ['Note', 'note'],
  settings: ['Impostazioni', 'settings'],
  users: ['Utenti', 'users'],
};

const widgetDefs = {
  calendar: ['Eventi di oggi', 'calendar'],
  shopping: ['Spesa di oggi', 'shopping'],
  family: ['Figli oggi', 'family'],
  reminders: ['Promemoria', 'bell'],
  notes: ['Ultime note', 'note'],
  settings: ['Impostazioni', 'settings'],
  users: ['Utenti', 'users'],
};

const icons = {
  home: '<svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>',
  calendar: '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="3"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>',
  shopping: '<svg viewBox="0 0 24 24"><path d="M6 8h15l-2 8H8L6 4H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>',
  family: '<svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3.5 20c.6-4 2.5-6 4.5-6s3.9 2 4.5 6"/><path d="M13.5 20c.4-3 1.8-4.8 3.5-4.8s3.1 1.8 3.5 4.8"/></svg>',
  bell: '<svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>',
  note: '<svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6z"/><path d="M14 3v4h4M9 11h6M9 15h6"/></svg>',
  user: '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 4-7 8-7s7 2 8 7"/></svg>',
  users: '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20c.8-4.5 3-6 6-6s5.2 1.5 6 6"/><path d="M14 15c2.8.2 4.7 1.8 5.5 5"/></svg>',
  settings: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19 12a7.2 7.2 0 0 0-.1-1l2-1.5-2-3.5-2.4 1a7 7 0 0 0-1.8-1L14.4 3h-4l-.4 3a7 7 0 0 0-1.8 1L5.8 6l-2 3.5 2 1.5a7.2 7.2 0 0 0 0 2l-2 1.5 2 3.5 2.4-1a7 7 0 0 0 1.8 1l.4 3h4l.4-3a7 7 0 0 0 1.8-1l2.4 1 2-3.5-2.1-1.5c.1-.3.1-.7.1-1z"/></svg>',
  moon: '<svg viewBox="0 0 24 24"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.7 8.7 0 1 0 20 15.5z"/></svg>',
  logout: '<svg viewBox="0 0 24 24"><path d="M10 5H5v14h5"/><path d="M14 8l4 4-4 4M18 12H9"/></svg>',
  plus: '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>',
  edit: '<svg viewBox="0 0 24 24"><path d="M4 20h4l11-11a2.5 2.5 0 0 0-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>',
  widgets: '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/></svg>',
  x: '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>',
};

const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
const today = () => new Date().toISOString().slice(0, 10);
const icon = name => `<span class="line-icon" aria-hidden="true">${icons[name] || icons.home}</span>`;

async function api(action, opts = {}) {
  const res = await fetch(`/api.php?action=${action}`, { headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': state.csrf }, ...opts });
  const data = await res.json();
  if (!data.ok) throw new Error(data.error || 'Errore');
  if (data.csrf) state.csrf = data.csrf;
  return data;
}

function toast(msg) {
  const el = $('#toast');
  el.textContent = msg;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2600);
}

function formData(form) { return Object.fromEntries(new FormData(form).entries()); }

async function boot() {
  injectStaticIcons(document);
  setupModalForms();
  try {
    const s = await api('session');
    state.user = s.user;
    state.csrf = s.csrf;
    if (state.user) showApp(); else showAuth();
  } catch {
    showAuth();
  }
}

function injectStaticIcons(root = document) {
  root.querySelectorAll('[data-icon]').forEach(el => { el.outerHTML = icon(el.dataset.icon); });
}

function setupModalForms() {
  $$('.modal-form').forEach(form => {
    if (!form.querySelector('.modal-title')) {
      form.insertAdjacentHTML('afterbegin', `<div class="modal-title"><h3>${esc(form.dataset.modalTitle || 'Modifica')}</h3><button class="icon-btn modal-close" type="button" aria-label="Chiudi">${icon('x')}</button></div>`);
    }
  });
}

function openModal(id) {
  closeModal(false);
  const modal = $('#' + id);
  if (!modal) return;
  $('#modalBackdrop').classList.add('active');
  modal.classList.remove('hidden');
  modal.querySelector('input, select, textarea, button:not(.modal-close)')?.focus();
}

function closeModal(reset = false) {
  $('#modalBackdrop').classList.remove('active');
  $$('.modal-form:not(.hidden)').forEach(form => {
    form.classList.add('hidden');
    if (reset && !['profileForm', 'widgetForm', 'settingsForm'].includes(form.id)) form.reset();
  });
}

function showAuth() {
  $('#auth').classList.remove('hidden');
  $('#main').classList.add('hidden');
}

async function showApp() {
  $('#auth').classList.add('hidden');
  $('#main').classList.remove('hidden');
  $('#hello').textContent = state.user.name;
  applyTheme(state.user.theme);
  renderNav();
  await loadAll();
  navigateTo(new URLSearchParams(location.search).get('page') || 'dashboard', false);
  if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(() => {});
}

function applyTheme(theme) {
  document.body.classList.toggle('dark', theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches));
}

function allowedPages() {
  return Object.keys(pageDefs).filter(p => !['settings', 'users'].includes(p) || state.user.role === 'admin');
}

function allowedWidgets() {
  return Object.keys(widgetDefs).filter(w => !['settings', 'users'].includes(w) || state.user.role === 'admin');
}

function renderNav() {
  $('#pageNav').innerHTML = allowedPages().map(page => `<a href="?page=${page}" data-page-link="${page}">${icon(pageDefs[page][1])}<span>${pageDefs[page][0]}</span></a>`).join('');
  $$('.admin-only').forEach(e => e.classList.toggle('hidden', state.user.role !== 'admin'));
}

function navigateTo(page, push = true) {
  if (!allowedPages().includes(page)) page = 'dashboard';
  state.page = page;
  $$('.page').forEach(p => p.classList.toggle('active', p.dataset.page === page));
  $$('[data-page-link]').forEach(a => a.classList.toggle('active', a.dataset.pageLink === page));
  $('#pageEyebrow').textContent = pageDefs[page][0];
  if (push) history.pushState({ page }, '', `?page=${page}`);
  scrollTo({ top: 0, behavior: 'smooth' });
}

async function loadAll() {
  const [users, dash, events, shopping, family, reminders, notes, notifs] = await Promise.all([
    api('users'), api('dashboard'), api('calendar'), api('shopping'), api('family'), api('reminders'), api('notes'), api('notifications')
  ]);
  state.users = users.users;
  state.widgets = dash.widgets.length ? dash.widgets : defaultWidgets();
  state.data = { events: events.events, shopping: shopping.lists, family: family.tasks, reminders: reminders.reminders, notes: notes.notes, notifications: notifs.notifications };
  fillUserSelects();
  renderDashboard();
  renderWidgetForm();
  renderCalendar();
  renderShopping();
  renderFamily();
  renderReminders();
  renderNotes();
  renderUsers();
  renderProfile();
  renderNotifications();
}

function defaultWidgets() {
  return allowedWidgets().map((widget_key, i) => ({ widget_key, enabled: 1, sort_order: i }));
}

function renderDashboard() {
  const active = state.widgets.filter(w => Number(w.enabled) && allowedWidgets().includes(w.widget_key));
  $('#dashboard').innerHTML = active.length ? active.map(w => widgetCard(w.widget_key)).join('') : '<article class="card"><h3>Nessun widget attivo</h3><p>Usa l’icona widget in alto per riattivarli.</p></article>';
}

function widgetCard(key) {
  const [title, iconName] = widgetDefs[key] || [key, 'home'];
  const action = `<a href="?page=${key}" data-page-link="${key}">Apri pagina</a>`;
  return `<article class="widget" data-widget="${key}">
    <div class="widget-head">${icon(iconName)}<h3>${title}</h3></div>
    <div class="widget-body">${widgetBody(key)}</div>
    <div class="widget-actions">${action}</div>
  </article>`;
}

function widgetBody(key) {
  const todayIso = today();
  if (key === 'calendar') {
    const events = state.data.events.filter(e => (e.starts_at || '').startsWith(todayIso));
    return events.length ? events.slice(0, 3).map(e => `<p><strong>${esc(e.title)}</strong><br><small>${esc(e.starts_at?.slice(11, 16))} · inserito da ${esc(e.created_by_name)}</small></p>`).join('') : '<p>Nessun evento oggi.</p>';
  }
  if (key === 'shopping') {
    const list = state.data.shopping.find(l => l.list_date === todayIso && !l.archived_at);
    return list ? `<p><strong>${esc(list.title)}</strong></p>${(list.items || []).slice(0, 4).map(i => `<p class="mini-item ${i.checked == 1 ? 'done' : ''}">${esc(i.label)}</p>`).join('')}` : '<p>Nessuna lista spesa per oggi.</p>';
  }
  if (key === 'family') {
    return state.data.family.length ? state.data.family.slice(0, 3).map(t => `<p><strong>${esc(t.child_name)}</strong> · ${esc(t.type)}<br><small>${esc(t.task_time || '')} ${esc(t.assignee_name || 'da assegnare')}</small></p>`).join('') : '<p>Nessun impegno figli oggi.</p>';
  }
  if (key === 'reminders') {
    const due = state.data.reminders.filter(r => !r.completed_at).slice(0, 3);
    return due.length ? due.map(r => `<p><strong>${esc(r.title)}</strong><br><small>${esc(r.due_at || 'senza data')}</small></p>`).join('') : '<p>Nessun promemoria aperto.</p>';
  }
  if (key === 'notes') {
    const notes = state.data.notes.filter(n => !n.archived_at).slice(0, 2);
    return notes.length ? notes.map(n => `<p><strong>${esc(n.title)}</strong><br><small>${esc((n.body || '').slice(0, 70))}</small></p>`).join('') : '<p>Nessuna nota attiva.</p>';
  }
  if (key === 'users') return `<p>${state.users.length} utenti collegati</p>`;
  if (key === 'settings') return '<p>Preferenze famiglia e notifiche.</p>';
  return '<p>Widget disponibile.</p>';
}

function renderWidgetForm() {
  const enabled = new Set(state.widgets.filter(w => Number(w.enabled)).map(w => w.widget_key));
  $('#widgetChoices').innerHTML = allowedWidgets().map(key => `<label class="widget-choice">${icon(widgetDefs[key][1])}<span>${widgetDefs[key][0]}</span><input type="checkbox" name="widgets" value="${key}" ${enabled.has(key) ? 'checked' : ''}></label>`).join('');
}

function fillUserSelects() {
  const children = state.users.filter(u => u.category === 'figlio');
  const parents = state.users.filter(u => ['mamma', 'papà', 'papa'].includes(u.category));
  const all = state.users;
  $$('[data-users="children"]').forEach(s => s.innerHTML = '<option value="">Figlio</option>' + children.map(u => `<option value="${u.id}">${esc(u.name)}</option>`).join(''));
  $$('[data-users="parents"]').forEach(s => s.innerHTML = '<option value="">Genitore associato</option>' + parents.map(u => `<option value="${u.id}">${esc(u.name)}</option>`).join(''));
  $$('[data-users="all"]').forEach(s => s.innerHTML = '<option value="">Da assegnare</option>' + all.map(u => `<option value="${u.id}">${esc(u.name)}</option>`).join(''));
}

function renderCalendar() {
  const now = new Date(), y = now.getFullYear(), m = now.getMonth(), first = new Date(y, m, 1), last = new Date(y, m + 1, 0), pad = (first.getDay() + 6) % 7;
  let html = '';
  for (let i = 0; i < pad; i++) html += '<div></div>';
  for (let d = 1; d <= last.getDate(); d++) {
    const iso = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const ev = state.data.events.filter(e => (e.starts_at || '').startsWith(iso));
    html += `<div class="day"><b>${d}</b>${ev.map(e => `<span class="event-pill" title="${esc(e.created_by_name)}">${esc(e.title)} · ${esc(e.created_by_name)}</span>`).join('')}</div>`;
  }
  $('#calendarGrid').innerHTML = html;
}

function renderShopping() {
  $('#shoppingLists').innerHTML = state.data.shopping.map(l => `<article class="card"><h3>${esc(l.title)}</h3><p>${esc(l.list_date)} · ${l.shared == 1 ? 'condivisa' : 'privata'} · ${l.archived_at ? 'archiviata' : 'attiva'}</p>${(l.items || []).map(i => `<div class="item-row ${i.checked == 1 ? 'done' : ''}"><input type="checkbox" ${i.checked == 1 ? 'checked' : ''} disabled>${esc(i.label)}</div>`).join('')}<button data-archive-list="${l.id}">Archivia</button></article>`).join('');
}

function renderFamily() {
  $('#familyTasks').innerHTML = state.data.family.map(t => `<article class="card"><h3>${esc(t.child_name)} · ${esc(t.type)}</h3><p>${esc(t.task_date)} ${esc(t.task_time || '')} · ${esc(t.assignee_name || 'da assegnare')}</p><p>${esc(t.notes)}</p></article>`).join('') || '<p>Nessun impegno figli oggi.</p>';
}

function renderReminders() {
  $('#remindersList').innerHTML = state.data.reminders.map(r => `<article class="card"><h3>${esc(r.title)}</h3><p>${esc(r.due_at || 'senza data')} · ${esc(r.recurrence)} · ${r.shared == 1 ? 'condiviso' : 'privato'}</p></article>`).join('');
}

function renderNotes() {
  $('#notesList').innerHTML = state.data.notes.map(n => `<article class="card"><h3>${esc(n.title)}</h3><p>${esc(n.body)}</p><small>${n.archived_at ? 'Archiviata' : 'Attiva'}</small></article>`).join('');
}

function renderUsers() {
  $('#usersList').innerHTML = state.users.map(u => `<article class="card"><h3>${esc(u.name)}</h3><p>${esc(u.category)} · ${esc(u.role)} · ${esc(u.phone || 'senza telefono')}</p><p>${esc(u.email || '')}</p></article>`).join('');
}

function renderProfile() {
  const f = $('#profileForm');
  ['email', 'birth_date', 'theme', 'personal_info'].forEach(k => f.elements[k] && (f.elements[k].value = state.user[k] || ''));
}

function renderNotifications() {
  const unread = state.data.notifications.filter(n => !n.read_at).length;
  $('#notifBadge').style.display = unread ? 'block' : 'none';
}

async function saveForm(form, action, transform = x => x) {
  const payload = transform(formData(form));
  await api(action, { method: 'POST', body: JSON.stringify(payload) });
  toast('Salvato');
  form.reset();
  closeModal(false);
  await loadAll();
}

document.addEventListener('click', async e => {
  const pageLink = e.target.closest('[data-page-link]');
  if (pageLink) {
    e.preventDefault();
    navigateTo(pageLink.dataset.pageLink);
    return;
  }
  const open = e.target.closest('[data-open]');
  if (open) openModal(open.dataset.open);
  const close = e.target.closest('.modal-close');
  if (close) closeModal(true);
  if (e.target.id === 'modalBackdrop') closeModal(true);
  const arch = e.target.closest('[data-archive-list]');
  if (arch) {
    await api('shopping', { method: 'POST', body: JSON.stringify({ id: arch.dataset.archiveList, archive: true }) });
    toast('Lista archiviata');
    await loadAll();
  }
  if (e.target.id === 'logoutBtn' || e.target.closest('#logoutBtn')) {
    await api('logout', { method: 'POST', body: '{}' });
    location.reload();
  }
  if (e.target.id === 'profileBtn' || e.target.closest('#profileBtn')) openModal('profileForm');
  if (e.target.id === 'widgetBtn' || e.target.closest('#widgetBtn')) openModal('widgetForm');
  if (e.target.id === 'themeToggle' || e.target.closest('#themeToggle')) {
    state.user.theme = document.body.classList.contains('dark') ? 'light' : 'dark';
    applyTheme(state.user.theme);
    await api('profile-save', { method: 'POST', body: JSON.stringify({ theme: state.user.theme, email: state.user.email, birth_date: state.user.birth_date, personal_info: state.user.personal_info }) });
  }
  if (e.target.id === 'notifyBtn' || e.target.closest('#notifyBtn')) {
    const body = state.data.notifications.slice(0, 6).map(n => `${n.title}: ${n.body}`).join('\n') || 'Nessuna notifica';
    alert(body);
    await api('notifications', { method: 'POST', body: '{}' });
    await loadAll();
  }
});

window.addEventListener('popstate', () => navigateTo(new URLSearchParams(location.search).get('page') || 'dashboard', false));
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(true); });

$('#loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  try {
    const r = await api('login', { method: 'POST', body: JSON.stringify(formData(e.target)) });
    state.user = r.user;
    state.csrf = r.csrf;
    await showApp();
  } catch (err) { toast(err.message); }
});

$('#resetRequestForm').addEventListener('submit', async e => { e.preventDefault(); await api('request-reset', { method: 'POST', body: JSON.stringify(formData(e.target)) }); toast('Controlla la tua email'); });
$('#eventForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'calendar'); });
$('#shoppingForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'shopping', d => ({ ...d, shared: !!d.shared, items: (d.items || '').split('\n').filter(Boolean).map(label => ({ label, checked: false })) })); });
$('#familyForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'family'); });
$('#childForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'user-save', d => ({ ...d, phone: '', role: 'familiare', category: 'figlio', active: true })); });
$('#reminderForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'reminders', d => ({ ...d, shared: !!d.shared })); });
$('#noteForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'notes', d => ({ ...d, archived: !!d.archived })); });
$('#profileForm').addEventListener('submit', async e => { e.preventDefault(); const r = await api('profile-save', { method: 'POST', body: JSON.stringify(formData(e.target)) }); state.user = r.user; applyTheme(state.user.theme); closeModal(false); await loadAll(); toast('Profilo aggiornato'); });
$('#settingsForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'settings', d => ({ settings: d })); });
$('#userForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'user-save', d => ({ ...d, active: !!d.active })); });
$('#widgetForm').addEventListener('submit', async e => {
  e.preventDefault();
  const chosen = new Set($$('#widgetChoices input[name="widgets"]:checked').map(i => i.value));
  const widgets = allowedWidgets().map(key => ({ key, enabled: chosen.has(key) }));
  await api('dashboard', { method: 'POST', body: JSON.stringify({ widgets }) });
  closeModal(false);
  toast('Widget aggiornati');
  await loadAll();
});

boot();
