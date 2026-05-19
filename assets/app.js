const state = { user: null, csrf: window.FP_CSRF, users: [], widgets: [], data: {}, page: 'dashboard' };

const pageDefs = {
  dashboard: ['Dashboard', 'home'],
  calendar: ['Calendario', 'calendar'],
  shopping: ['Spesa', 'shopping'],
  family: ['Famiglia', 'family'],
  reminders: ['Promemoria', 'bell'],
  notes: ['Note', 'note'],
  notifications: ['Notifiche', 'bell'],
  shoppingArchive: ['Archivio spesa', 'shopping'],
  remindersArchive: ['Archivio promemoria', 'bell'],
  notesArchive: ['Archivio note', 'note'],
  settings: ['Impostazioni', 'settings'],
  users: ['Utenti', 'users'],
};

const widgetDefs = {
  calendar: ['Eventi di oggi', 'calendar'],
  shopping: ['Spesa di oggi', 'shopping'],
  family: ['Figli oggi', 'family'],
  reminders: ['Promemoria', 'bell'],
  notes: ['Ultime note', 'note'],
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
  trash: '<svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg>',
  save: '<svg viewBox="0 0 24 24"><path d="M5 4h12l2 2v14H5z"/><path d="M8 4v6h8V4"/><path d="M8 20v-6h8v6"/></svg>',
  widgets: '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/></svg>',
  x: '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>',
};

const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
const today = () => new Date().toISOString().slice(0, 10);
const icon = name => `<span class="line-icon" aria-hidden="true">${icons[name] || icons.home}</span>`;
const categoryLabels = { mamma: 'Mamma', 'papà': 'Papà', figlio: 'Figlio', nonno: 'Nonno', nonna: 'Nonna', zio: 'Zio', zia: 'Zia', familiare: 'Familiare' };
const productCategories = {
  'Frutta e Verdura': ['Mele', 'Pere', 'Banane', 'Arance', 'Limoni', 'Insalata', 'Pomodori', 'Carote', 'Zucchine', 'Patate', 'Cipolle', 'Spinaci'],
  'Pane e Pasta': ['Pane', 'Pancarrè', 'Pasta', 'Riso', 'Farina', 'Cracker', 'Grissini', 'Cous cous', 'Piadine'],
  'Latte e Freschi': ['Latte', 'Yogurt', 'Mozzarella', 'Uova', 'Burro', 'Formaggio', 'Ricotta', 'Prosciutto', 'Stracchino'],
  'Carne e Pesce': ['Pollo', 'Tacchino', 'Manzo', 'Salsicce', 'Salmone', 'Tonno', 'Merluzzo', 'Gamberi', 'Hamburger'],
  'Detersivi': ['Detersivo lavatrice', 'Ammorbidente', 'Sapone piatti', 'Sgrassatore', 'Carta casa', 'Candeggina', 'Panni microfibra', 'Spugne', 'Sacchi immondizia'],
  'Dispensa': ['Olio', 'Sale', 'Zucchero', 'Caffè', 'Biscotti', 'Cereali', 'Passata', 'Legumi', 'Tonno scatola', 'Marmellata', 'Miele', 'Tè'],
  'Igiene': ['Shampoo', 'Bagnoschiuma', 'Dentifricio', 'Carta igienica', 'Sapone mani', 'Deodorante', 'Salviette', 'Pannolini', 'Crema'],
};

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
function labelCategory(value) { return categoryLabels[value] || (value ? value.charAt(0).toUpperCase() + value.slice(1) : ''); }
function optionRange(max, step = 1) { return Array.from({ length: Math.ceil(max / step) }, (_, i) => String(i * step).padStart(2, '0')).map(v => `<option value="${v}">${v}</option>`).join(''); }
function combineDateTime(date, hour, minute) { return date ? `${date}T${hour || '00'}:${minute || '00'}` : ''; }
function formatDate(value) { if (!value) return ''; const [y, m, d] = String(value).slice(0, 10).split('-'); return y && m && d ? `${d}/${m}/${y}` : value; }
function toSqlDateTime(value) { return value ? value.replace('T', ' ') + ':00' : ''; }

async function boot() {
  injectStaticIcons(document);
  setupModalForms();
  setupSoftPickers();
  setupDateFieldOpeners();
  setupWysiwyg();
  renderProductCatalog();
  setupConditionalFields();
  try {
    const s = await api('session');
    state.user = s.user;
    state.csrf = s.csrf;
    if (state.user) showApp(); else showAuth();
  } catch {
    showAuth();
  }
}


function setupDateFieldOpeners() {
  $$('input[type="date"]').forEach(input => {
    input.classList.add('date-clickable');
    input.addEventListener('click', () => input.showPicker?.());
  });
}

function setupWysiwyg() {
  $$('.wysiwyg').forEach(el => {
    const name = el.dataset.wysiwygTarget;
    const hidden = el.closest('form')?.querySelector(`input[name="${name}"]`);
    if (hidden) el.innerHTML = hidden.value || '';
    el.addEventListener('input', () => { if (hidden) hidden.value = el.innerHTML; });
  });
}

function injectStaticIcons(root = document) {
  root.querySelectorAll('[data-icon]').forEach(el => { el.outerHTML = icon(el.dataset.icon); });
}


function setupSoftPickers() {
  $$('select[name$="_hour"]').forEach(select => { select.innerHTML = optionRange(24); });
  $$('select[name$="_minute"]').forEach(select => { select.innerHTML = optionRange(60, 5); });
  const todayIso = today();
  ['starts_date', 'task_date', 'list_date'].forEach(name => { const input = document.querySelector(`[name="${name}"]`); if (input) input.value = todayIso; });
}

function renderProductCatalog() {
  const wrap = $('#productCatalog');
  if (!wrap) return;
  wrap.innerHTML = Object.entries(productCategories).map(([category, products], index) => `
    <div class="product-category ${index === 0 ? 'active' : ''}" data-category-panel="${esc(category)}">
      <button type="button" data-product-category="${esc(category)}">${esc(category)}</button>
      <div class="product-list">${products.map(product => `<button type="button" data-product="${esc(product)}">${esc(product)}</button>`).join('')}</div>
    </div>`).join('');
}

function toggleProductCategory(category) {
  $$('.product-category').forEach(panel => panel.classList.toggle('active', panel.dataset.categoryPanel === category));
}

function addShoppingProduct(product) {
  const textarea = $('#shoppingForm textarea[name="items"]');
  const items = textarea.value.split('\n').map(v => v.trim()).filter(Boolean);
  if (!items.includes(product)) items.push(product);
  textarea.value = items.join('\n');
}


function setupConditionalFields() {
  const userForm = $('#userForm');
  const category = userForm?.elements.category;
  const parentFields = userForm?.querySelector('.parent-fields');
  const updateParents = () => parentFields?.classList.toggle('hidden', category?.value !== 'figlio');
  if (category && !category.dataset.conditionalReady) {
    category.addEventListener('change', updateParents);
    category.dataset.conditionalReady = '1';
  }
  updateParents();

  const familyForm = $('#familyForm');
  const recurrence = familyForm?.elements.recurrence;
  const count = familyForm?.elements.recurrence_count;
  const updateRecurrence = () => count?.classList.toggle('hidden', recurrence?.value === 'none');
  if (recurrence && !recurrence.dataset.conditionalReady) {
    recurrence.addEventListener('change', updateRecurrence);
    recurrence.dataset.conditionalReady = '1';
  }
  updateRecurrence();
}

function setupModalForms() {
  $$('.modal-form').forEach(form => {
    if (!form.querySelector('.modal-title')) {
      const actions = form.dataset.modalActions
        ? `<div class="modal-actions">${form.dataset.modalActions === 'crud-noedit' ? '' : `<button class="icon-btn event-edit hidden" type="button" title="Modifica" aria-label="Modifica">${icon('edit')}</button>`}<button class="icon-btn event-save" type="submit" title="Salva" aria-label="Salva">${icon('save')}</button><button class="icon-btn event-delete hidden" type="button" title="Elimina" aria-label="Elimina">${icon('trash')}</button><button class="icon-btn modal-close" type="button" aria-label="Chiudi">${icon('x')}</button></div>`
        : `<button class="icon-btn modal-close" type="button" aria-label="Chiudi">${icon('x')}</button>`;
      form.insertAdjacentHTML('afterbegin', `<div class="modal-title"><h3>${esc(form.dataset.modalTitle || 'Modifica')}</h3>${actions}</div>`);
    }
  });
}

function openModal(id) {
  closeModal(false);
  const modal = $('#' + id);
  if (!modal) return;
  $('#modalBackdrop').classList.add('active');
  modal.classList.remove('hidden');
  if (id === 'eventForm' && !modal.elements.id.value) {
    modal.querySelector('.modal-title h3').textContent = modal.dataset.modalTitle || 'Nuovo evento';
    modal.querySelector('.event-delete')?.classList.add('hidden');
    modal.querySelector('.event-save')?.classList.remove('hidden');
    modal.querySelector('.form-submit')?.classList.remove('hidden');
  }
  modal.querySelector('input, select, textarea, button:not(.modal-close)')?.focus();
}

function closeModal(reset = false) {
  $('#modalBackdrop').classList.remove('active');
  $$('.modal-form:not(.hidden)').forEach(form => {
    form.classList.add('hidden');
    if (reset && !['profileForm', 'widgetForm', 'settingsForm'].includes(form.id) && typeof form.reset === 'function') form.reset();
    if (form.id === 'eventForm') {
      form.querySelector('.modal-title h3').textContent = form.dataset.modalTitle || 'Nuovo evento';
      form.querySelector('.event-delete')?.classList.add('hidden');
    }
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
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' })
      .then(registration => {
        registration.update().catch(() => {});
        if (registration.waiting) registration.waiting.postMessage('SKIP_WAITING');
        registration.addEventListener('updatefound', () => {
          const worker = registration.installing;
          worker?.addEventListener('statechange', () => {
            if (worker.state === 'installed' && navigator.serviceWorker.controller) worker.postMessage('SKIP_WAITING');
          });
        });
      })
      .catch(() => {});
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      if (!sessionStorage.getItem('familyplan-sw-reloaded')) {
        sessionStorage.setItem('familyplan-sw-reloaded', '1');
        location.reload();
      }
    });
  }
}

function applyTheme(theme) {
  document.body.classList.toggle('dark', theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches));
}

function allowedPages() {
  return Object.keys(pageDefs).filter(p => !['settings', 'users'].includes(p) || state.user.role === 'admin');
}

function navPages() {
  return allowedPages().filter(page => !page.endsWith('Archive'));
}

function allowedWidgets() {
  return Object.keys(widgetDefs);
}

function renderNav() {
  $('#pageNav').innerHTML = navPages().map(page => `<a href="?page=${page}" data-page-link="${page}">${icon(pageDefs[page][1])}<span>${pageDefs[page][0]}</span></a>`).join('');
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
  const pageName = pageDefs[key]?.[0] || widgetDefs[key]?.[0] || key;
  const action = `<a href="?page=${key}" data-page-link="${key}">Vai a ${pageName}</a>`;
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
    return events.length ? events.slice(0, 3).map(e => `<p><strong>${esc(e.title)}</strong><br><small>${esc(formatDate(e.starts_at))} ${esc(e.starts_at?.slice(11, 16))} · inserito da ${esc(e.created_by_name)}</small></p>`).join('') : '<p>Nessun evento oggi.</p>';
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
    const visible = ev.slice(0, 2);
    const hidden = ev.slice(2);
    html += `<div class="day" data-day="${iso}"><b>${d}</b>${visible.map(eventPill).join('')}${hidden.length ? `<div class="more-events hidden">${hidden.map(eventPill).join('')}</div><button type="button" class="more-btn" data-expand-day="${iso}" data-more-count="${hidden.length}">Altri ${hidden.length}</button>` : ''}</div>`;
  }
  $('#calendarGrid').innerHTML = html;
}

function eventPill(e) {
  const sharedOther = Number(e.shared) === 1 && Number(e.created_by) !== Number(state.user.id);
  return `<button type="button" class="event-pill ${sharedOther ? 'shared-other' : ''}" data-event-id="${e.id}" title="${esc(e.created_by_name)}">${esc(e.title)} · ${esc(e.created_by_name)}</button>`;
}


function splitSqlDateTime(value) {
  if (!value) return { date: '', hour: '00', minute: '00' };
  return { date: value.slice(0, 10), hour: value.slice(11, 13) || '00', minute: value.slice(14, 16) || '00' };
}

function openEventDetail(id) {
  const event = state.data.events.find(e => Number(e.id) === Number(id));
  if (!event) return;
  const form = $('#eventForm');
  const start = splitSqlDateTime(event.starts_at);
  const end = splitSqlDateTime(event.ends_at);
  form.elements.id.value = event.id;
  form.elements.title.value = event.title || '';
  form.elements.starts_date.value = start.date;
  form.elements.starts_hour.value = start.hour;
  form.elements.starts_minute.value = start.minute;
  form.elements.ends_date.value = end.date;
  form.elements.ends_hour.value = end.hour;
  form.elements.ends_minute.value = end.minute;
  form.elements.shared.checked = Number(event.shared) === 1;
  form.elements.description.value = event.description || '';
  form.querySelector('.modal-title h3').textContent = 'Dettaglio evento';
  const readonly = Number(event.created_by) !== Number(state.user.id);
  form.querySelector('.event-delete')?.classList.toggle('hidden', readonly);
  form.querySelector('.event-save')?.classList.toggle('hidden', readonly);
  form.querySelector('.form-submit')?.classList.toggle('hidden', readonly);
  openModal('eventForm');
}

async function deleteCurrentEvent() {
  const form = $('#eventForm');
  const id = form.elements.id.value;
  if (!id || !confirm('Eliminare questo evento?')) return;
  await api('calendar', { method: 'POST', body: JSON.stringify({ id, delete: true }) });
  closeModal(true);
  toast('Evento eliminato');
  await loadAll();
}

function renderShopping() {
  const activeLists = state.data.shopping.filter(l => !l.archived_at);
  const archivedLists = state.data.shopping.filter(l => !!l.archived_at);
  $('#shoppingLists').innerHTML = activeLists.map(l => `<article class="card shopping-preview" data-shopping-id="${l.id}"><div><h3>${esc(l.title)}</h3><p>${esc(formatDate(l.list_date))} · ${l.shared == 1 ? 'condivisa' : 'privata'} · attiva</p></div></article>`).join('') || '<article class="card"><p>Nessuna lista attiva.</p></article>';
  $('#shoppingArchiveContent').innerHTML = archivedLists.map(l => `<article class="card shopping-preview" data-shopping-id="${l.id}"><div><h3>${esc(l.title)}</h3><p>Archiviata · ${esc(formatDate(l.archived_at || l.list_date))}</p></div></article>`).join('') || '<article class="card"><p>Archivio vuoto.</p></article>';
}

function openShoppingDetail(id) {
  const list = state.data.shopping.find(l => Number(l.id) === Number(id));
  if (!list) return;
  const form = $('#shoppingDetail');
  form.elements.id.value = list.id;
  form.querySelector('.modal-title h3').textContent = `${list.title} · ${formatDate(list.list_date)}`;
  form.querySelector('.event-edit')?.classList.remove('hidden');
  form.querySelector('.event-delete')?.classList.remove('hidden');
  form.querySelector('.event-save')?.classList.remove('hidden');
  form.querySelector('.event-save')?.setAttribute('title', 'Salva');
  form.querySelector('.event-save')?.setAttribute('aria-label', 'Salva');
  $('#shoppingDetailContent').innerHTML = `<div class="shopping-detail-list">${(list.items || []).map((item, index) => `<label class="shopping-line ${item.checked == 1 ? 'done' : ''}"><input type="checkbox" data-shopping-item="${index}" ${item.checked == 1 ? 'checked' : ''}><span>${esc(item.label)}</span></label>`).join('')}</div>`;
  form.dataset.listId = list.id;
  openModal('shoppingDetail');
}

async function toggleShoppingItem(input) {
  const list = state.data.shopping.find(l => Number(l.id) === Number($('#shoppingDetail').dataset.listId));
  if (!list) return;
  const item = list.items[Number(input.dataset.shoppingItem)];
  item.checked = input.checked ? 1 : 0;
  input.closest('.shopping-line')?.classList.toggle('done', input.checked);
  await api('shopping', { method: 'POST', body: JSON.stringify({ id: list.id, title: list.title, list_date: list.list_date, shared: Number(list.shared) === 1, items: list.items }) });
  await loadAll();
  openShoppingDetail(list.id);
}

function renderFamily() {
  $('#familyTasks').innerHTML = state.data.family.map(t => `<article class="card"><h3>${esc(t.child_name)} · ${esc(t.type)}</h3><p>${esc(formatDate(t.task_date))} ${esc(t.task_time || '')} · ${esc(t.assignee_name || 'da assegnare')}</p><p>${esc(t.notes)}</p><button type="button" class="link-button" data-edit-family="${t.id}">${icon('edit')} Modifica</button></article>`).join('') || '<p>Nessun impegno figli oggi.</p>';
}

function renderReminders() {
  const active = state.data.reminders.filter(r => !r.completed_at);
  const archived = state.data.reminders.filter(r => !!r.completed_at);
  $('#remindersList').innerHTML = active.map(r => `<article class="card" data-edit-reminder="${r.id}"><h3>${esc(r.title)}</h3><p>${esc(r.due_at ? `${formatDate(r.due_at)} ${r.due_at.slice(11, 16)}` : 'senza data')} · ${esc(r.recurrence)} · ${r.shared == 1 ? 'condiviso' : 'privato'}</p></article>`).join('') || '<article class="card"><p>Nessun promemoria attivo.</p></article>';
  $('#remindersArchiveList').innerHTML = archived.map(r => `<article class="card" data-edit-reminder="${r.id}"><h3>${esc(r.title)}</h3><p>${esc(r.due_at ? `${formatDate(r.due_at)} ${r.due_at.slice(11, 16)}` : 'senza data')} · archiviato</p></article>`).join('') || '<article class="card"><p>Archivio promemoria vuoto.</p></article>';
}

function renderNotes() {
  const active = state.data.notes.filter(n => !n.archived_at);
  const archived = state.data.notes.filter(n => !!n.archived_at);
  $('#notesList').innerHTML = active.map(n => `<article class="card" data-edit-note="${n.id}"><h3>${esc(n.title)}</h3><p>${n.body || ''}</p><small>Attiva</small></article>`).join('') || '<article class="card"><p>Nessuna nota attiva.</p></article>';
  $('#notesArchiveList').innerHTML = archived.map(n => `<article class="card" data-edit-note="${n.id}"><h3>${esc(n.title)}</h3><p>${n.body || ''}</p><small>Archiviata</small></article>`).join('') || '<article class="card"><p>Archivio note vuoto.</p></article>';
}

function renderUsers() {
  $('#usersList').innerHTML = state.users.map(u => `<article class="card"><h3>${esc(u.name)}</h3><p>${esc(labelCategory(u.category))} · ${esc(u.role)} · ${esc(u.phone || 'senza telefono')}</p><p>${esc(u.email || '')}</p></article>`).join('');
}

function renderProfile() {
  const f = $('#profileForm');
  ['email', 'birth_date', 'theme', 'category', 'personal_info'].forEach(k => f.elements[k] && (f.elements[k].value = state.user[k] || ''));
}

function renderNotifications() {
  const unread = state.data.notifications.filter(n => !n.read_at).length;
  const unreadItems = state.data.notifications.filter(n => !n.read_at);
  const archivedItems = state.data.notifications.filter(n => !!n.read_at);
  $('#notifBadge').style.display = unread ? 'block' : 'none';
  $('#notificationsList').innerHTML = unreadItems.map(n => `<article class="card"><h3>${esc(n.title)}</h3><p>${esc(n.body)}</p></article>`).join('') || '<article class="card"><p>Nessuna notifica nuova.</p></article>';
  $('#notificationsArchiveList').innerHTML = archivedItems.map(n => `<article class="card"><h3>${esc(n.title)}</h3><p>${esc(n.body)}</p><small>${esc(formatDate(n.read_at))} ${esc((n.read_at || '').slice(11, 16))}</small></article>`).join('') || '<article class="card"><p>Nessuna notifica archiviata.</p></article>';
}

async function deleteFromModal(formId, action) {
  const form = $('#' + formId);
  const id = Number(form?.elements?.id?.value || 0);
  if (!id) return;
  if (!confirm('Eliminare questo elemento?')) return;
  await api(action, { method: 'POST', body: JSON.stringify({ id, delete: true }) });
  closeModal(true);
  toast('Eliminato');
  await loadAll();
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
  if (open) {
    if (open.dataset.open === 'eventForm') $('#eventForm').reset();
    if (['shoppingForm', 'familyForm', 'reminderForm', 'noteForm'].includes(open.dataset.open)) {
      const f = $('#' + open.dataset.open);
      if (f) {
        f.reset();
        if (f.elements.id) f.elements.id.value = '';
        f.querySelector('.event-delete')?.classList.add('hidden');
        f.querySelector('.event-edit')?.classList.add('hidden');
      }
    }
    openModal(open.dataset.open);
    setupConditionalFields();
  }
  const eventBtn = e.target.closest('[data-event-id]');
  if (eventBtn) openEventDetail(eventBtn.dataset.eventId);
  const shoppingBtn = e.target.closest('[data-open-shopping], .shopping-preview');
  if (shoppingBtn && !e.target.closest('[data-archive-list]')) openShoppingDetail(shoppingBtn.dataset.openShopping || shoppingBtn.dataset.shoppingId);
  const shoppingItem = e.target.closest('[data-shopping-item]');
  if (shoppingItem) await toggleShoppingItem(shoppingItem);
  const close = e.target.closest('.modal-close');
  if (close) closeModal(true);
  if (e.target.id === 'modalBackdrop') closeModal(true);
  const expandDay = e.target.closest('[data-expand-day]');
  if (expandDay) {
    const day = document.querySelector(`[data-day="${expandDay.dataset.expandDay}"]`);
    day?.classList.toggle('expanded');
    expandDay.textContent = day?.classList.contains('expanded') ? 'Mostra meno' : `Altri ${expandDay.dataset.moreCount}`;
  }
  const product = e.target.closest('[data-product]');
  if (product) { addShoppingProduct(product.dataset.product); product.classList.add('shopping-product-picked'); setTimeout(() => product.classList.remove('shopping-product-picked'), 350); }
  const cat = e.target.closest('[data-product-category]');
  if (cat) toggleProductCategory(cat.dataset.productCategory);
  const arch = e.target.closest('[data-archive-list]');
  if (arch) {
    await api('shopping', { method: 'POST', body: JSON.stringify({ id: arch.dataset.archiveList, archive: true }) });
    toast('Lista archiviata');
    await loadAll();
  }
  const editShopping = e.target.closest('[data-edit-shopping]');
  if (editShopping) {
    const list = state.data.shopping.find(l => Number(l.id) === Number(editShopping.dataset.editShopping));
    if (list) {
      const f = $('#shoppingForm');
      f.elements.id.value = list.id;
      f.elements.title.value = list.title || '';
      f.elements.list_date.value = list.list_date || today();
      f.elements.shared.checked = Number(list.shared) === 1;
      f.elements.items.value = (list.items || []).map(i => i.label).join('\n');
      f.querySelector('.event-delete')?.classList.remove('hidden');
      f.querySelector('.event-edit')?.classList.remove('hidden');
      openModal('shoppingForm');
    }
  }
  const editReminder = e.target.closest('[data-edit-reminder]');
  if (editReminder) {
    const r = state.data.reminders.find(v => Number(v.id) === Number(editReminder.dataset.editReminder));
    if (r) {
      const f = $('#reminderForm');
      const dt = splitSqlDateTime(r.due_at);
      f.elements.id.value = r.id;
      f.elements.title.value = r.title || '';
      f.elements.due_date.value = dt.date;
      f.elements.due_hour.value = dt.hour;
      f.elements.due_minute.value = dt.minute;
      f.elements.recurrence.value = r.recurrence || 'none';
      f.elements.shared.checked = Number(r.shared) === 1;
      f.querySelector('.event-delete')?.classList.remove('hidden');
      f.querySelector('.event-edit')?.classList.add('hidden');
      f.querySelector('.event-save')?.setAttribute('title', 'Salva');
      f.querySelector('.event-save')?.setAttribute('aria-label', 'Salva');
      const reminderSave = f.querySelector('.event-save');
      if (reminderSave) reminderSave.dataset.mode = 'save';
      openModal('reminderForm');
    }
  }
  const editNote = e.target.closest('[data-edit-note]');
  if (editNote) {
    const n = state.data.notes.find(v => Number(v.id) === Number(editNote.dataset.editNote));
    if (n) {
      const f = $('#noteForm');
      f.elements.id.value = n.id;
      f.elements.title.value = n.title || '';
      f.elements.body.value = n.body || '';
      f.querySelector('[data-wysiwyg-target="body"]').innerHTML = n.body || '';
      f.elements.archived.checked = !!n.archived_at;
      f.querySelector('.event-delete')?.classList.remove('hidden');
      f.querySelector('.event-edit')?.classList.add('hidden');
      f.querySelector('.event-save')?.setAttribute('title', 'Salva');
      f.querySelector('.event-save')?.setAttribute('aria-label', 'Salva');
      const noteSave = f.querySelector('.event-save');
      if (noteSave) noteSave.dataset.mode = 'save';
      openModal('noteForm');
    }
  }
  const editFamily = e.target.closest('[data-edit-family]');
  if (editFamily) {
    const t = state.data.family.find(v => Number(v.id) === Number(editFamily.dataset.editFamily));
    if (t) {
      const f = $('#familyForm');
      f.elements.id.value = t.id;
      f.elements.child_id.value = t.child_id || '';
      f.elements.assignee_id.value = t.assignee_id || '';
      f.elements.task_date.value = t.task_date || today();
      const [h, m] = String(t.task_time || '00:00').split(':');
      f.elements.task_hour.value = h || '00';
      f.elements.task_minute.value = m || '00';
      f.elements.type.value = t.type || '';
      f.elements.recurrence.value = t.recurrence || 'none';
      f.elements.notes.value = t.notes || '';
      f.querySelector('.event-delete')?.classList.remove('hidden');
      f.querySelector('.event-edit')?.classList.remove('hidden');
      openModal('familyForm');
    }
  }
  if (e.target.id === 'logoutBtn' || e.target.closest('#logoutBtn')) {
    await api('logout', { method: 'POST', body: '{}' });
    location.reload();
  }
  const deleteEvent = e.target.closest('.event-delete');
  if (deleteEvent) {
    const form = deleteEvent.closest('form');
    if (form?.id === 'eventForm') await deleteCurrentEvent();
    if (form?.id === 'reminderForm') await deleteFromModal('reminderForm', 'reminders');
    if (form?.id === 'noteForm') await deleteFromModal('noteForm', 'notes');
    if (form?.id === 'shoppingForm') await deleteFromModal('shoppingForm', 'shopping');
    if (form?.id === 'shoppingDetail') await deleteFromModal('shoppingDetail', 'shopping');
    if (form?.id === 'familyForm') await deleteFromModal('familyForm', 'family');
  }
  if (e.target.id === 'profileBtn' || e.target.closest('#profileBtn')) openModal('profileForm');
  if (e.target.id === 'widgetBtn' || e.target.closest('#widgetBtn')) openModal('widgetForm');
  if (e.target.id === 'themeToggle' || e.target.closest('#themeToggle')) {
    state.user.theme = document.body.classList.contains('dark') ? 'light' : 'dark';
    applyTheme(state.user.theme);
    await api('profile-save', { method: 'POST', body: JSON.stringify({ theme: state.user.theme, email: state.user.email, birth_date: state.user.birth_date, personal_info: state.user.personal_info, category: state.user.category }) });
  }
  if (e.target.id === 'notifyBtn' || e.target.closest('#notifyBtn')) {
    navigateTo('notifications');
    await api('notifications', { method: 'POST', body: '{}' });
    await loadAll();
  }
  if (e.target.id === 'markAllReadBtn' || e.target.closest('#markAllReadBtn')) {
    await api('notifications', { method: 'POST', body: '{}' });
    await loadAll();
    toast('Notifiche segnate come lette');
  }
  const archiveToggleInDetail = e.target.closest('#shoppingDetail .event-save');
  if (archiveToggleInDetail) {
    const list = state.data.shopping.find(l => Number(l.id) === Number($('#shoppingDetail').elements.id.value));
    if (list) {
      await api('shopping', { method: 'POST', body: JSON.stringify({ id: list.id, title: list.title, list_date: list.list_date, shared: Number(list.shared) === 1, items: list.items }) });
      await loadAll();
      openShoppingDetail(list.id);
    }
  }
  const shoppingArchive = e.target.closest('#shoppingDetail .event-edit');
  if (shoppingArchive) {
    const list = state.data.shopping.find(l => Number(l.id) === Number($('#shoppingDetail').elements.id.value));
    if (list) {
      if (list.archived_at) {
        await api('shopping', { method: 'POST', body: JSON.stringify({ id: list.id, title: list.title, list_date: list.list_date, shared: Number(list.shared) === 1, items: list.items }) });
      } else {
        await api('shopping', { method: 'POST', body: JSON.stringify({ id: list.id, archive: true }) });
      }
      await loadAll();
      openShoppingDetail(list.id);
    }
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
$('#eventForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'calendar', d => ({ ...d, starts_at: toSqlDateTime(combineDateTime(d.starts_date, d.starts_hour, d.starts_minute)), ends_at: toSqlDateTime(combineDateTime(d.ends_date, d.ends_hour, d.ends_minute)), shared: !!d.shared })); });
$('#shoppingForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'shopping', d => ({ ...d, id: Number(d.id || 0), shared: !!d.shared, items: (d.items || '').split('\n').filter(Boolean).map(label => ({ label, checked: false })) })); });
$('#familyForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'family', d => ({ ...d, id: Number(d.id || 0), task_time: `${d.task_hour || '00'}:${d.task_minute || '00'}` })); });
$('#reminderForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'reminders', d => ({ ...d, id: Number(d.id || 0), due_at: toSqlDateTime(combineDateTime(d.due_date, d.due_hour, d.due_minute)), shared: !!d.shared })); });
$('#noteForm').addEventListener('submit', e => { e.preventDefault(); saveForm(e.target, 'notes', d => ({ ...d, id: Number(d.id || 0), archived: !!d.archived })); });
$('#profileForm').addEventListener('submit', async e => { e.preventDefault(); const r = await api('profile-save', { method: 'POST', body: JSON.stringify(formData(e.target)) }); state.user = r.user; applyTheme(state.user.theme); closeModal(false); await loadAll(); toast('Profilo aggiornato'); });
$('#settingsForm').addEventListener('submit', async e => { e.preventDefault(); await api('settings', { method: 'POST', body: JSON.stringify({ settings: formData(e.target) }) }); toast('Impostazioni aggiornate'); });
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
