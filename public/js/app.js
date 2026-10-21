let serviceWorkerRegistrationPromise = null;

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    serviceWorkerRegistrationPromise = navigator.serviceWorker.register('/sw.js?v=20260826-1').catch(() => null);
  });
}

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const root = document.documentElement;
let reminderNotificationPollBusy = false;
let pushSubscriptionSyncBusy = false;
let draggedWidgetRow = null;
let widgetPointerDragActive = false;
let widgetPointerDragPointerId = null;
let mobileAlphabetHideTimer = null;
let pageNavigationController = null;
let topMenuCloseTimer = null;
let soundboardAudio = null;
let soundboardButton = null;
let modalScrollY = null;
const prefetchedPages = new Set();

function prefetchPage(link) {
  if (!link || link.origin !== window.location.origin || link.pathname === window.location.pathname || prefetchedPages.has(link.href)) return;
  prefetchedPages.add(link.href);
  const hint = document.createElement('link');
  hint.rel = 'prefetch';
  hint.href = link.href;
  hint.as = 'document';
  document.head.append(hint);
}

document.addEventListener('pointerdown', event => {
  const anchor = event.target.closest('.bottom-nav a, .top-menu-link, .top-actions a');
  if (anchor) prefetchPage(anchor);
}, { capture: true, passive: true });

document.addEventListener('click', event => {
  const anchor = event.target.closest('.bottom-nav a, .brand, .top-menu-link, .top-actions a');
  if (!anchor || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  if (anchor.target && anchor.target !== '_self') return;
  const url = new URL(anchor.href, window.location.href);
  if (url.origin !== window.location.origin || (url.pathname === window.location.pathname && url.search === window.location.search)) return;
  event.preventDefault();
  navigatePage(url.href);
}, true);

window.addEventListener('popstate', () => navigatePage(window.location.href, { push: false }));

function pushNotificationsEnabled() {
  return document.body?.dataset.pushNotifications !== '0';
}

function pushPublicKey() {
  return document.body?.dataset.pushPublicKey || '';
}

function pushSyncUrl() {
  return document.body?.dataset.pushSyncUrl || '';
}

function pushCanUseServerDelivery() {
  return 'serviceWorker' in navigator && 'PushManager' in window && pushPublicKey() !== '';
}

function securePick(chars) {
  const values = new Uint32Array(1);
  crypto.getRandomValues(values);
  return chars[values[0] % chars.length];
}

function shuffleSecure(chars) {
  return chars
    .map(char => ({ char, sort: crypto.getRandomValues(new Uint32Array(1))[0] }))
    .sort((a, b) => a.sort - b.sort)
    .map(item => item.char)
    .join('');
}

function generatePasswordFromOptions(box) {
  const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
  const numbers = '23456789';
  const symbols = '!@#$%&*?_-+=';
  const pools = [];
  if (box.querySelector('[data-password-letters]')?.checked) pools.push(letters);
  if (box.querySelector('[data-password-numbers]')?.checked) pools.push(numbers);
  if (box.querySelector('[data-password-symbols]')?.checked) pools.push(symbols);
  if (pools.length === 0) pools.push(letters, numbers);

  const lengthInput = box.querySelector('[data-password-length]');
  const length = Math.max(8, Math.min(64, parseInt(lengthInput?.value || '18', 10)));
  const chars = pools.map(pool => securePick(pool));
  const all = pools.join('');
  while (chars.length < length) chars.push(securePick(all));

  return shuffleSecure(chars);
}

function syncSearchClear(input) {
  input.closest('.search-field')?.classList.toggle('has-value', input.value.trim() !== '');
}

function coinData(card) {
  try {
    return JSON.parse(window.atob(card?.dataset.coinB64 || ''));
  } catch {
    return null;
  }
}

function coinEuro(value) {
  if (value === null || value === undefined || value === '') return '—';
  return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(Number(value));
}

function openCoinDetails(card) {
  const data = coinData(card);
  const modal = document.querySelector('#coin-detail-modal');
  if (!data || !modal) return;
  modal.dataset.coinId = data.id;
  modal.querySelector('[data-coin-detail-code]').textContent = `${data.code} · ${data.country} · ${data.year || 'Anno n.d.'}`;
  modal.querySelector('[data-coin-detail-title]').textContent = data.title;
  const image = modal.querySelector('[data-coin-detail-image]');
  image.hidden = !data.image_url;
  if (data.image_url) {
    image.src = data.image_url;
    image.alt = data.title;
  }
  const facts = modal.querySelector('[data-coin-detail-facts]');
  facts.replaceChildren();
  [
    ['Valore', coinEuro(data.denomination_eur)],
    ['Tipologia', data.type || '—'],
    ['Conservazione', data.condition || '—'],
    ['Rarità', data.rarity || '—'],
    ['Stima', `${coinEuro(data.estimated_min_eur)} – ${coinEuro(data.estimated_max_eur)}`],
    ['Confidenza', data.identification_confidence ? `${Math.round(Number(data.identification_confidence) * 100)}%` : '—'],
  ].forEach(([label, value]) => {
    const item = document.createElement('div');
    const term = document.createElement('span');
    const content = document.createElement('strong');
    term.textContent = label;
    content.textContent = value;
    item.append(term, content);
    facts.append(item);
  });
  const notes = modal.querySelector('[data-coin-detail-notes]');
  notes.textContent = data.notes || '';
  notes.hidden = !data.notes;
  showModalClean(modal, card);
}

function openCoinEditor(card) {
  const data = coinData(card);
  const modal = document.querySelector('#coin-edit-modal');
  const form = modal?.querySelector('[data-coin-edit-form]');
  if (!data || !modal || !form) return;
  form.action = data.update_url;
  modal.querySelector('[data-coin-delete-form]').action = data.delete_url;
  Object.entries(data).forEach(([name, value]) => {
    if (['probable_duplicate', 'manual_verification'].includes(name)) return;
    const input = form.elements.namedItem(name);
    if (!input || name === 'image_url') return;
    if (input instanceof RadioNodeList) return;
    if (input.type === 'checkbox') input.checked = Boolean(value);
    else if (input.type !== 'file') input.value = value ?? '';
  });
  form.querySelector('input[type="checkbox"][name="probable_duplicate"]').checked = Boolean(data.probable_duplicate);
  form.querySelector('input[type="checkbox"][name="manual_verification"]').checked = Boolean(data.manual_verification);
  showModalClean(modal, card);
}

function filterCoins() {
  const query = document.querySelector('[data-coin-search]')?.value.trim().toLowerCase() || '';
  const country = document.querySelector('[data-coin-country]')?.value || '';
  const sort = document.querySelector('[data-coin-sort]')?.value || 'default';
  const grid = document.querySelector('[data-coin-grid]');
  const cards = [...document.querySelectorAll('[data-coin-card]')];
  let visible = 0;
  cards.forEach(card => {
    const show = (!query || card.dataset.coinSearchable.includes(query)) && (!country || card.dataset.coinCountryValue === country);
    card.hidden = !show;
    if (show) visible += 1;
  });
  const textCompare = (left, right) => left.localeCompare(right, 'it', { sensitivity: 'base' });
  const yearCompare = (left, right, direction) => {
    if (!left && !right) return 0;
    if (!left) return 1;
    if (!right) return -1;
    return (left - right) * direction;
  };
  cards.sort((left, right) => {
    if (sort === 'name-asc') return textCompare(left.dataset.coinName, right.dataset.coinName);
    if (sort === 'name-desc') return textCompare(right.dataset.coinName, left.dataset.coinName);
    if (sort === 'country-asc') return textCompare(left.dataset.coinCountryValue, right.dataset.coinCountryValue) || textCompare(left.dataset.coinName, right.dataset.coinName);
    if (sort === 'year-desc') return yearCompare(Number(left.dataset.coinYear), Number(right.dataset.coinYear), -1);
    if (sort === 'year-asc') return yearCompare(Number(left.dataset.coinYear), Number(right.dataset.coinYear), 1);
    if (sort === 'estimate-desc') return Number(right.dataset.coinEstimate) - Number(left.dataset.coinEstimate);
    if (sort === 'estimate-asc') return Number(left.dataset.coinEstimate) - Number(right.dataset.coinEstimate);
    return Number(left.dataset.coinIndex) - Number(right.dataset.coinIndex);
  });
  cards.forEach(card => grid?.append(card));
  const empty = document.querySelector('[data-coin-empty]');
  if (empty) empty.hidden = visible !== 0;
}

function announceToastNotifications(scope = document) {
  if (!('Notification' in window) || Notification.permission !== 'granted' || !pushNotificationsEnabled()) return;

  scope.querySelectorAll('.toast').forEach(toast => {
    if (toast.dataset.notificationShown === '1') return;
    toast.dataset.notificationShown = '1';
    new Notification('FamilyPlan', {
      body: toast.textContent.trim(),
      icon: '/images/app/familyplan-mark-192.png',
    });
  });
}

function reminderNotificationKey(reminder) {
  return `familyplan:reminder-notified:${reminder.id}:${reminder.due_at || ''}`;
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replaceAll('-', '+').replaceAll('_', '/');
  const rawData = window.atob(base64);

  return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}

async function syncPushSubscriptionToServer(active, subscription) {
  const url = pushSyncUrl();
  if (!url) return;

  await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({
      active,
      subscription: subscription ? {
        ...subscription,
        contentEncoding: subscription.contentEncoding || 'aes128gcm',
      } : null,
    }),
  }).catch(() => {});
}

async function ensurePushSubscription() {
  if (pushSubscriptionSyncBusy || !pushCanUseServerDelivery()) return;

  pushSubscriptionSyncBusy = true;

  try {
    const registration = await (serviceWorkerRegistrationPromise || navigator.serviceWorker.ready.catch(() => null));
    if (!registration) return;

    let subscription = await registration.pushManager.getSubscription();

    if (!pushNotificationsEnabled()) {
      if (subscription) {
        const snapshot = subscription.toJSON();
        await subscription.unsubscribe().catch(() => {});
        await syncPushSubscriptionToServer(false, snapshot);
      }
      return;
    }

    if (Notification.permission === 'default') {
      const permission = await Notification.requestPermission().catch(() => 'default');
      if (permission !== 'granted') return;
    }

    if (Notification.permission !== 'granted') return;

    if (!subscription) {
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(pushPublicKey()),
      });
    }

    await syncPushSubscriptionToServer(true, subscription.toJSON());
  } catch {
    // ignore push sync failures
  } finally {
    pushSubscriptionSyncBusy = false;
  }
}

async function pollReminderNotifications() {
  if (pushCanUseServerDelivery()) return;
  if (reminderNotificationPollBusy || !('Notification' in window) || Notification.permission !== 'granted' || !pushNotificationsEnabled()) return;

  reminderNotificationPollBusy = true;

  try {
    const response = await fetch('/promemoria/notifiche', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) return;

    const payload = await response.json();
    const reminders = Array.isArray(payload.reminders) ? payload.reminders : [];

    reminders.forEach(reminder => {
      const key = reminderNotificationKey(reminder);
      if (window.localStorage.getItem(key) === '1') return;

      const dueAt = reminder.due_at
        ? new Date(reminder.due_at).toLocaleString('it-IT', { dateStyle: 'short', timeStyle: 'short' })
        : '';
      const bodyParts = [dueAt, reminder.body].filter(Boolean);

      const notification = new Notification(reminder.title || 'Promemoria FamilyPlan', {
        body: bodyParts.join(' - ') || 'Hai un promemoria da controllare.',
        icon: '/images/app/familyplan-mark-192.png',
      });

      notification.onclick = () => {
        window.focus();
        if (reminder.url) {
          window.location.href = reminder.url;
        }
      };

      window.localStorage.setItem(key, '1');
    });
  } catch {
    // ignore transient polling failures
  } finally {
    reminderNotificationPollBusy = false;
  }
}

function lockModalScroll() {
  if (modalScrollY !== null) return;
  modalScrollY = window.scrollY;
  document.documentElement.classList.add('modal-open');
  document.body.style.position = 'fixed';
  document.body.style.top = `-${modalScrollY}px`;
  document.body.style.left = '0';
  document.body.style.right = '0';
  document.body.style.width = '100%';
}

function unlockModalScroll() {
  if (modalScrollY === null || document.querySelector('dialog.modal[open]')) return;
  const scrollY = modalScrollY;
  modalScrollY = null;
  document.documentElement.classList.remove('modal-open');
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.left = '';
  document.body.style.right = '';
  document.body.style.width = '';
  window.scrollTo({ top: scrollY, left: 0, behavior: 'auto' });
}

function showModalClean(modal, trigger = null) {
  if (!modal || modal.open) return;
  lockModalScroll();
  modal.showModal();
  window.requestAnimationFrame(() => {
    trigger?.blur?.();
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
  });
}

function openTopMenu(menu, toggle, keyboardOpen = false) {
  prepareTopMenu();
  const panel = document.querySelector('[data-top-menu-panel]');
  const backdrop = document.querySelector('[data-top-menu-backdrop]');
  if (!menu || !panel || !backdrop) return;
  if (topMenuCloseTimer) window.clearTimeout(topMenuCloseTimer);
  panel.hidden = false;
  backdrop.hidden = false;
  panel.querySelectorAll('.top-menu-link').forEach((link, index) => link.style.setProperty('--menu-index', index));
  window.requestAnimationFrame(() => {
    window.requestAnimationFrame(() => {
      menu.classList.add('is-open');
      root.classList.add('tools-menu-open');
      if (keyboardOpen) panel.querySelector('[data-top-menu-close]')?.focus({ preventScroll: true });
      else toggle?.blur();
    });
  });
  toggle?.setAttribute('aria-expanded', 'true');
}

function closeTopMenu(menu, immediate = false) {
  const panel = document.querySelector('[data-top-menu-panel]');
  const backdrop = document.querySelector('[data-top-menu-backdrop]');
  const toggle = menu?.querySelector('[data-top-menu-toggle]');
  if (!menu || !panel || !backdrop) return;
  menu.classList.remove('is-open');
  root.classList.remove('tools-menu-open');
  toggle?.setAttribute('aria-expanded', 'false');
  if (topMenuCloseTimer) window.clearTimeout(topMenuCloseTimer);
  const finish = () => {
    panel.hidden = true;
    backdrop.hidden = true;
  };
  if (immediate) finish();
  else topMenuCloseTimer = window.setTimeout(finish, 280);
}

function prepareTopMenu() {
  const panel = document.querySelector('[data-top-menu-panel]');
  const backdrop = document.querySelector('[data-top-menu-backdrop]');
  if (panel?.parentElement !== document.body) document.body.append(panel);
  if (backdrop?.parentElement !== document.body) document.body.insertBefore(backdrop, panel);
}

function showCopyFeedback(message = 'Copiato') {
  const toast = document.createElement('div');
  toast.className = 'toast toast-floating';
  toast.textContent = message;
  (document.querySelector('dialog.modal[open]') || document.body).append(toast);
  window.setTimeout(() => toast.remove(), 1800);
}

function stopSoundboardAudio() {
  if (soundboardAudio) {
    soundboardAudio.pause();
    soundboardAudio.currentTime = 0;
  }
  soundboardButton?.classList.remove('is-playing');
  soundboardButton?.setAttribute('aria-label', `Riproduci ${soundboardButton.querySelector('.soundboard-clip-name')?.textContent || 'clip'}`);
  soundboardAudio = null;
  soundboardButton = null;
}

function filterSoundboard() {
  const search = document.querySelector('[data-soundboard-search]');
  const query = search?.value.trim().toLowerCase() || '';
  const category = document.querySelector('[data-soundboard-category][aria-pressed="true"]')?.dataset.soundboardCategory || '';
  let visible = 0;
  document.querySelectorAll('[data-soundboard-clip]').forEach(clip => {
    const matches = (!query || clip.dataset.soundboardName?.includes(query))
      && (!category || clip.dataset.soundboardCategoryName === category);
    clip.hidden = !matches;
    if (matches) visible += 1;
  });
  const empty = document.querySelector('[data-soundboard-empty]');
  if (empty) empty.hidden = visible > 0;
}

async function ensurePageAssets(nextDocument) {
  const moduleScript = nextDocument.querySelector('script[type="module"][src*="/build/assets/app-"]');
  if (!moduleScript || window.mountVueApps) return;
  await new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.type = 'module';
    script.src = moduleScript.src;
    script.onload = resolve;
    script.onerror = reject;
    document.head.append(script);
  });
}

function syncPersistentChrome(nextDocument) {
  const selectors = ['.bottom-nav a', '.top-menu-link', '.top-actions a', '[data-top-menu-toggle]'];
  selectors.forEach(selector => {
    const currentItems = [...document.querySelectorAll(selector)];
    const nextItems = [...nextDocument.querySelectorAll(selector)];
    currentItems.forEach((item, index) => {
      const next = nextItems[index];
      if (!next) return;
      item.className = next.className;
      if (next.hasAttribute('aria-current')) item.setAttribute('aria-current', next.getAttribute('aria-current'));
      else item.removeAttribute('aria-current');
    });
  });
}

async function replacePageFromResponse(response, navigationUrl = null) {
  const html = await response.text();
  if (!html.includes('<html')) return;
  const nextDocument = new DOMParser().parseFromString(html, 'text/html');
  await ensurePageAssets(nextDocument);
  document.title = nextDocument.title;
  document.documentElement.dataset.theme = nextDocument.documentElement.dataset.theme || document.documentElement.dataset.theme;
  document.documentElement.dataset.compact = nextDocument.documentElement.dataset.compact || document.documentElement.dataset.compact;
  document.body.dataset.pushNotifications = nextDocument.body.dataset.pushNotifications || document.body.dataset.pushNotifications || '0';
  document.body.dataset.pushPublicKey = nextDocument.body.dataset.pushPublicKey || document.body.dataset.pushPublicKey || '';
  document.body.dataset.pushSyncUrl = nextDocument.body.dataset.pushSyncUrl || document.body.dataset.pushSyncUrl || '';
  const currentMain = document.querySelector('main.shell');
  const nextMain = nextDocument.querySelector('main.shell');
  if (!currentMain || !nextMain) return;
  stopSoundboardAudio();
  window.unmountVueApps?.(currentMain);
  syncPersistentChrome(nextDocument);
  currentMain.replaceChildren(...Array.from(nextMain.childNodes));
  window.mountVueApps?.();
  hydrateDynamicState(navigationUrl ? new URL(navigationUrl, window.location.origin).search : window.location.search);
  announceToastNotifications(document);
  ensurePushSubscription();
  return true;
}

async function navigatePage(url, { push = true } = {}) {
  pageNavigationController?.abort();
  pageNavigationController = new AbortController();
  document.documentElement.setAttribute('aria-busy', 'true');
  try {
    const response = await fetch(url, {
      headers: { 'Accept': 'text/html,application/xhtml+xml', 'X-Requested-With': 'XMLHttpRequest' },
      signal: pageNavigationController.signal,
    });
    if (!response.ok || new URL(response.url).pathname === '/login') throw new Error('navigation');
    const replaced = await replacePageFromResponse(response, response.url);
    if (!replaced) throw new Error('navigation');
    if (push) history.pushState({ familyPlan: true }, '', response.url);
    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
  } catch (error) {
    if (error.name !== 'AbortError') window.location.assign(url);
  } finally {
    document.documentElement.removeAttribute('aria-busy');
  }
}

async function refreshCurrentPage() {
  const response = await fetch(window.location.href, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });
  await replacePageFromResponse(response);
}

document.addEventListener('submit', async event => {
  const form = event.target.closest('form');
  if (!form || form.dataset.nativeSubmit !== undefined || form.action.includes('/logout')) return;
  if (!document.querySelector('meta[name="csrf-token"]')) return;
  if (form.dataset.submitting === '1') {
    event.preventDefault();
    return;
  }
  if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
    event.preventDefault();
    return;
  }

  event.preventDefault();
  form.dataset.submitting = '1';
  syncWysiwygToInput(form);

  const submitter = event.submitter;
  if (submitter) submitter.disabled = true;

  const response = await fetch(form.action || window.location.href, {
    method: form.method?.toUpperCase() || 'POST',
    headers: {
      'Accept': 'text/html,application/xhtml+xml',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: new FormData(form),
  }).catch(() => null);

  if (response && form.matches('[data-inline-action]')) {
    if (form.classList.contains('checkline')) {
      const checkbox = form.querySelector('.checkbox');
      const label = form.querySelector('span');
      const checked = !label?.classList.contains('done');
      label?.classList.toggle('done', checked);
      if (checkbox) checkbox.textContent = checked ? '✓' : '';
    } else if (form.matches('[data-add-product-form]')) {
      const name = form.querySelector('[name="name"]');
      const quantity = form.querySelector('[name="quantity"]');
      const list = form.closest('.modal-card')?.querySelector('.shopping-products');
      if (list && name?.value.trim()) {
        const row = document.createElement('div');
        row.className = 'product-row';
        const text = document.createElement('span');
        text.textContent = `${name.value.trim()} ${quantity?.value.trim() || ''}`.trim();
        row.append(text);
        list.append(row);
      }
      form.reset();
    }
    if (submitter) submitter.disabled = false;
    form.dataset.submitting = '0';
  } else if (response) {
    await replacePageFromResponse(response);
  } else if (submitter) {
    submitter.disabled = false;
    form.dataset.submitting = '0';
  } else {
    form.dataset.submitting = '0';
  }
});

if (!['light', 'dark'].includes(root.dataset.theme)) {
  root.dataset.theme = 'light';
}

document.addEventListener('click', event => {
  const copyButton = event.target.closest('[data-copy-text]');
  if (copyButton) {
    event.preventDefault();
    event.stopPropagation();
    const text = copyButton.dataset.copyText || '';
    if (text.trim()) {
      navigator.clipboard?.writeText(text).then(() => {
        showCopyFeedback(copyButton.dataset.copyLabel || 'Copiato');
      }).catch(() => {
        showCopyFeedback('Copia non riuscita');
      });
    }
    return;
  }
}, true);

document.addEventListener('click', event => {

  const soundboardCategory = event.target.closest('[data-soundboard-category]');
  if (soundboardCategory) {
    document.querySelectorAll('[data-soundboard-category]').forEach(button => {
      const active = button === soundboardCategory;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    filterSoundboard();
    return;
  }

  const soundboardClip = event.target.closest('[data-soundboard-clip]');
  if (soundboardClip) {
    if (soundboardButton === soundboardClip && soundboardAudio && !soundboardAudio.paused) {
      stopSoundboardAudio();
      return;
    }
    stopSoundboardAudio();
    const audio = new Audio(soundboardClip.dataset.soundboardAudio);
    audio.preload = 'none';
    soundboardAudio = audio;
    soundboardButton = soundboardClip;
    soundboardClip.classList.add('is-playing');
    soundboardClip.setAttribute('aria-label', `Ferma ${soundboardClip.querySelector('.soundboard-clip-name')?.textContent || 'clip'}`);
    audio.addEventListener('ended', stopSoundboardAudio, { once: true });
    audio.addEventListener('error', () => {
      stopSoundboardAudio();
      showCopyFeedback('Audio non disponibile');
    }, { once: true });
    audio.play().catch(() => {
      stopSoundboardAudio();
      showCopyFeedback('Riproduzione non riuscita');
    });
    return;
  }

  const widgetButton = event.target.closest('[data-open-widget-modal]');
  if (widgetButton) {
    showModalClean(document.querySelector('#widget-modal'), widgetButton);
    return;
  }

  const dateInput = event.target.closest('input[type="date"], input[type="datetime-local"]');
  if (dateInput && typeof dateInput.showPicker === 'function') {
    dateInput.showPicker();
  }

  const shoppingButton = event.target.closest('[data-edit-shopping]');
  if (shoppingButton) {
    const editShoppingModal = document.querySelector('#shopping-edit-modal');
    const editShoppingForm = document.querySelector('[data-edit-shopping-form]');
    const deleteShoppingForm = document.querySelector('[data-delete-shopping-form]');
    const archiveShoppingForm = document.querySelector('[data-archive-shopping-form]');
    if (!editShoppingModal || !editShoppingForm) return;
    editShoppingForm.action = shoppingButton.dataset.shoppingAction;
    if (deleteShoppingForm) deleteShoppingForm.action = shoppingButton.dataset.shoppingDeleteAction;
    if (archiveShoppingForm) archiveShoppingForm.action = shoppingButton.dataset.shoppingArchiveAction;
    editShoppingForm.querySelectorAll('[data-shopping-field]').forEach(field => {
      const key = field.dataset.shoppingField;
      if (field.multiple) {
        const selected = (shoppingButton.dataset.shoppingSharedWith || '').split(',').filter(Boolean);
        Array.from(field.options).forEach(option => {
          option.selected = selected.includes(option.value);
        });
      } else {
        field.value = shoppingButton.dataset[`shopping${key.replace(/(^|_)([a-z])/g, (_, __, letter) => letter.toUpperCase())}`] ?? '';
      }
    });
    showModalClean(editShoppingModal, shoppingButton);
    return;
  }

  const widgetHandle = event.target.closest('[data-widget-drag-handle]');
  if (widgetHandle) {
    event.preventDefault();
    event.stopPropagation();
    return;
  }

  const topMenuToggle = event.target.closest('[data-top-menu-toggle]');
  if (topMenuToggle) {
    const menu = topMenuToggle.closest('[data-top-menu]');
    const panel = document.querySelector('[data-top-menu-panel]');
    if (panel && !panel.hidden) closeTopMenu(menu);
    else openTopMenu(menu, topMenuToggle, event.detail === 0);
    return;
  }

  const topMenuClose = event.target.closest('[data-top-menu-close], [data-top-menu-backdrop]');
  if (topMenuClose) {
    const menu = document.querySelector('[data-top-menu]');
    closeTopMenu(menu);
    if (event.detail === 0) menu?.querySelector('[data-top-menu-toggle]')?.focus();
    return;
  }

  const topMenuLink = event.target.closest('.top-menu-link');
  if (topMenuLink) {
    closeTopMenu(document.querySelector('[data-top-menu]'));
  }

  const themeButton = event.target.closest('[data-theme-toggle]');
  if (themeButton) {
    const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = nextTheme;

    fetch(themeButton.dataset.themeUrl, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ theme: nextTheme }),
    }).catch(() => {});
    return;
  }

  if (event.target.closest('.summary-actions form')) {
    return;
  }

  if (event.target.closest('[data-loyalty-favorite-button]')) {
    return;
  }

  const openButton = event.target.closest('[data-open-modal]');
  if (openButton) {
    const modal = document.querySelector(openButton.dataset.openModal);
    if (modal?.dataset.resetEvent) {
      window.dispatchEvent(new CustomEvent(modal.dataset.resetEvent));
      modal.querySelector('form')?.reset();
    }
    showModalClean(modal, openButton);
    return;
  }

  const closeButton = event.target.closest('[data-close-modal]');
  if (closeButton) {
    const modal = closeButton.closest('dialog');
    if (modal?.open) modal.close();
    return;
  }

  if (event.target.matches('dialog.modal') && event.target.open) {
    event.target.close();
    return;
  }

  const button = event.target.closest('[data-password-toggle]');
  if (button) {
    const field = button.closest('.password-field');
    const input = field?.querySelector('[data-password-input]');
    if (!field || !input) return;

    const showPassword = input.type === 'password';
    input.type = showPassword ? 'text' : 'password';
    field.classList.toggle('is-visible', showPassword);
    button.setAttribute('aria-label', showPassword ? 'Nascondi password' : 'Mostra password');
    button.setAttribute('title', showPassword ? 'Nascondi password' : 'Mostra password');
    return;
  }

  const secretButton = event.target.closest('[data-secret-toggle]');
  if (secretButton) {
    event.preventDefault();
    const value = secretButton.closest('.secret-row')?.querySelector('[data-secret-value]');
    if (!value) return;
    const visible = value.dataset.visible === '1';
    value.textContent = visible ? '••••••••••••' : value.dataset.secretValue;
    value.dataset.visible = visible ? '0' : '1';
    secretButton.setAttribute('aria-label', visible ? 'Mostra password' : 'Nascondi password');
    secretButton.setAttribute('title', visible ? 'Mostra password' : 'Nascondi password');
    return;
  }

  const generatorToggle = event.target.closest('[data-password-generator-toggle]');
  if (generatorToggle) {
    event.preventDefault();
    const box = generatorToggle.closest('label')?.querySelector('[data-password-generator]');
    if (box) box.hidden = !box.hidden;
    return;
  }

  const generateButton = event.target.closest('[data-password-generate]');
  if (generateButton) {
    event.preventDefault();
    const scope = generateButton.closest('label');
    const input = scope?.querySelector('[data-password-input]');
    const box = scope?.querySelector('[data-password-generator]');
    if (!input || !box) return;
    input.value = generatePasswordFromOptions(box);
    input.type = 'text';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.closest('.password-field')?.classList.add('is-visible');
    return;
  }

  const userButton = event.target.closest('[data-edit-user]');
  if (userButton) {
    openUserEditor(userButton);
    return;
  }

  const eventButton = event.target.closest('[data-edit-event]');
  if (eventButton) {
    openCalendarEventEditor(eventDataFromButton(eventButton), eventButton.dataset.eventDeleteAction);
    return;
  }

  const translatorSwap = event.target.closest('[data-translator-swap]');
  if (translatorSwap) {
    const form = translatorSwap.closest('[data-translator-form]');
    const source = form?.querySelector('[name="source"]');
    const target = form?.querySelector('[name="target"]');
    if (!source || !target) return;
    const previousSource = source.value;
    source.value = target.value;
    target.value = previousSource;
    return;
  }

  const translatorCopy = event.target.closest('[data-translator-copy]');
  if (translatorCopy) {
    const result = translatorCopy.closest('[data-translator-form]')?.querySelector('[data-translator-result]');
    if (result?.textContent?.trim()) {
      navigator.clipboard?.writeText(result.textContent.trim()).catch(() => {});
    }
    return;
  }

  const currencySwap = event.target.closest('[data-currency-swap]');
  if (currencySwap) {
    const form = currencySwap.closest('[data-currency-form]');
    const source = form?.querySelector('[name="source"]');
    const target = form?.querySelector('[name="target"]');
    if (!source || !target) return;
    const previousSource = source.value;
    source.value = target.value;
    target.value = previousSource;
    return;
  }

  const currencyCopy = event.target.closest('[data-currency-copy]');
  if (currencyCopy) {
    const result = currencyCopy.closest('[data-currency-form]')?.querySelector('[data-currency-result]');
    if (result?.textContent?.trim()) {
      navigator.clipboard?.writeText(result.textContent.trim()).then(() => showCopyFeedback('Risultato copiato')).catch(() => {});
    }
    return;
  }

  const searchClear = event.target.closest('[data-search-clear]');
  if (searchClear) {
    event.preventDefault();
    const input = searchClear.closest('.search-field')?.querySelector('input[type="search"]');
    if (!input) return;
    input.value = '';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.focus();
    return;
  }

  const coinEdit = event.target.closest('[data-edit-coin]');
  if (coinEdit) {
    event.preventDefault();
    event.stopPropagation();
    openCoinEditor(coinEdit.closest('[data-coin-card]'));
    return;
  }

  const coinCard = event.target.closest('[data-coin-card]');
  if (coinCard) {
    openCoinDetails(coinCard);
    return;
  }

  const detailEdit = event.target.closest('[data-detail-edit-coin]');
  if (detailEdit) {
    const detailModal = detailEdit.closest('#coin-detail-modal');
    const source = document.querySelector(`[data-coin-card][data-coin-id="${CSS.escape(detailModal?.dataset.coinId || '')}"]`);
    detailModal?.close();
    openCoinEditor(source);
    return;
  }

});

document.addEventListener('input', event => {
  const soundboardSearch = event.target.closest('[data-soundboard-search]');
  if (soundboardSearch) {
    syncSearchClear(soundboardSearch);
    filterSoundboard();
    return;
  }
  if (event.target.matches('[data-coin-search]')) filterCoins();
});

document.addEventListener('change', event => {
  if (event.target.matches('[data-coin-country], [data-coin-sort]')) filterCoins();
});

document.addEventListener('keydown', event => {
  const openToolsMenu = document.querySelector('[data-top-menu].is-open');
  if (openToolsMenu && event.key === 'Escape') {
    event.preventDefault();
    closeTopMenu(openToolsMenu);
    openToolsMenu.querySelector('[data-top-menu-toggle]')?.focus();
    return;
  }
  if (openToolsMenu && event.key === 'Tab') {
    const focusable = [...document.querySelectorAll('[data-top-menu-panel] button, [data-top-menu-panel] a')]
      .filter(element => !element.hidden && element.offsetParent !== null);
    const first = focusable[0];
    const last = focusable.at(-1);
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first?.focus();
    }
  }
  const openButton = event.target.closest('[data-open-modal]');
  if (openButton && ['Enter', ' '].includes(event.key)) {
    event.preventDefault();
    showModalClean(document.querySelector(openButton.dataset.openModal), openButton);
    return;
  }
  const card = event.target.closest('[data-coin-card]');
  if (!card || event.target.closest('button') || !['Enter', ' '].includes(event.key)) return;
  event.preventDefault();
  openCoinDetails(card);
});

document.addEventListener('click', async event => {
  const button = event.target.closest('[data-loyalty-favorite-button]');
  if (!button) return;
  event.preventDefault();
  event.stopPropagation();

  button.setAttribute('disabled', 'disabled');

  try {
    const response = await fetch(button.dataset.url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
      },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || payload.ok === false) throw new Error();

    const isFavorite = payload.is_favorite === true;
    button.classList.toggle('is-active', isFavorite);
    button.setAttribute('aria-pressed', isFavorite ? 'true' : 'false');
    syncLoyaltyFavoritePill(button, isFavorite);
  } catch {
    showCopyFeedback('Preferito non aggiornato');
  } finally {
    button.removeAttribute('disabled');
  }
}, true);

function syncLoyaltyFavoritePill(source, isFavorite) {
  const wrapper = document.querySelector('[data-loyalty-favorites]');
  const track = document.querySelector('[data-loyalty-favorites-track]');
  const cardId = source.dataset.cardId;
  if (!wrapper || !track || !cardId) return;

  const existing = track.querySelector(`[data-favorite-pill="${CSS.escape(cardId)}"]`);
  if (!isFavorite) {
    existing?.remove();
    wrapper.hidden = track.children.length === 0;
    return;
  }
  if (existing) return;

  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'loyalty-favorite-pill';
  button.dataset.openModal = `#loyalty-card-view-${cardId}`;
  button.dataset.favoritePill = cardId;
  button.style.setProperty('--loyalty-color', source.dataset.cardColor || '#6f9f92');
  button.style.setProperty('--loyalty-text', source.dataset.cardText || '#ffffff');
  button.setAttribute('aria-label', source.dataset.cardBrand || 'Carta preferita');

  if (source.dataset.cardLogo) {
    const image = document.createElement('img');
    image.src = source.dataset.cardLogo;
    image.alt = source.dataset.cardBrand || '';
    button.append(image);
  } else {
    const span = document.createElement('span');
    span.textContent = (source.dataset.cardBrand || '?').trim().slice(0, 1).toUpperCase();
    button.append(span);
  }

  track.append(button);
  wrapper.hidden = false;
}

function ageFromDate(value) {
  if (!value) return null;
  const birth = new Date(`${value}T00:00:00`);
  if (Number.isNaN(birth.getTime())) return null;
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const beforeBirthday = today.getMonth() < birth.getMonth() ||
    (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate());
  if (beforeBirthday) age -= 1;
  return age;
}

function updateUserFormVisibility(form) {
  const category = form.querySelector('[data-user-category]')?.value;
  const birthDate = form.querySelector('[data-user-birth-date]')?.value;
  const childParents = form.querySelector('[data-child-parents]');
  const phoneField = form.querySelector('[data-phone-field]');
  const emailField = form.querySelector('[data-email-field]');
  const passwordField = form.querySelector('[data-password-field]');
  const phoneInput = phoneField?.querySelector('input');
  const emailInput = emailField?.querySelector('input');
  const passwordInput = passwordField?.querySelector('input');
  const isChild = category === 'figlio';
  const age = ageFromDate(birthDate);
  const childCanHavePhone = isChild && age !== null && age >= 14;
  const childWithoutAccess = isChild && !childCanHavePhone;

  if (childParents) childParents.hidden = !isChild;
  if (phoneField) phoneField.hidden = childWithoutAccess;
  if (emailField) emailField.hidden = childWithoutAccess;
  if (passwordField) passwordField.hidden = childWithoutAccess;
  if (phoneInput && childWithoutAccess) phoneInput.value = '';
  if (emailInput && childWithoutAccess) emailInput.value = '';
  if (passwordInput && childWithoutAccess) passwordInput.value = '';
}

function syncWysiwygFromInput(scope = document) {
  scope.querySelectorAll('[data-wysiwyg]').forEach(editorShell => {
    const input = editorShell.closest('label')?.querySelector('[data-wysiwyg-input]');
    const editor = editorShell.querySelector('[data-wysiwyg-editor]');
    if (input && editor && editor.innerHTML !== input.value) {
      editor.innerHTML = input.value || '';
    }
  });
}

function syncWysiwygToInput(scope = document) {
  scope.querySelectorAll('[data-wysiwyg]').forEach(editorShell => {
    const input = editorShell.closest('label')?.querySelector('[data-wysiwyg-input]');
    const editor = editorShell.querySelector('[data-wysiwyg-editor]');
    if (input && editor) {
      input.value = editor.innerHTML.trim();
    }
  });
}

function syncWidgetOrderInputs() {
  const form = document.querySelector('#widget-modal form');
  const container = form?.querySelector('[data-widget-order-inputs]');
  const rows = form ? [...form.querySelectorAll('[data-widget-sort-item]')] : [];
  if (!form || !container) return;

  container.innerHTML = '';
  rows.forEach(row => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'widget_order[]';
    input.value = row.dataset.widgetKey || '';
    container.append(input);
  });
}

function hydrateDynamicState(search = window.location.search) {
  if (!document.querySelector('dialog.modal[open]')) unlockModalScroll();
  document.querySelectorAll('[data-user-form]').forEach(form => {
    syncWysiwygFromInput(form);
    updateUserFormVisibility(form);
  });
  const widgetButton = document.querySelector('[data-open-widget-modal]');
  if (widgetButton) widgetButton.hidden = !document.querySelector('#widget-modal');
  prepareTopMenu();
  document.querySelectorAll('[data-top-menu]').forEach(menu => closeTopMenu(menu, true));
  syncWidgetOrderInputs();
  updateMobileAlphabetVisibility(false);
  initMovieCoverflows();
  filterMovieLibrary('all');
  restoreMovieGenreSelection();
  const parameters = new URLSearchParams(search);
  const openTarget = parameters.get('open');
  if (openTarget) showModalClean(document.getElementById(openTarget));
  const openEventTarget = parameters.get('open_event');
  if (openEventTarget) {
    const eventButton = document.querySelector(`[data-edit-event][data-event-id="${CSS.escape(openEventTarget)}"]`);
    if (eventButton) openCalendarEventEditor(eventDataFromButton(eventButton), eventButton.dataset.eventDeleteAction);
  }
}

document.addEventListener('close', () => {
  window.requestAnimationFrame(unlockModalScroll);
}, true);

function movieCard(movie) {
  const button = document.createElement('button');
  button.className = 'movie-cover';
  button.type = 'button';
  button.dataset.movieId = movie.id;
  button.dataset.mediaType = movie.media_type || 'movie';
  button.setAttribute('aria-label', `Apri ${movie.title}`);

  const poster = movie.poster
    ? `<img src="${escapeHtml(movie.poster)}" alt="Locandina di ${escapeHtml(movie.title)}" loading="lazy" decoding="async">`
    : `<span class="movie-poster-empty">${escapeHtml(movie.title?.charAt(0) || '?')}</span>`;
  const rating = movie.rating ? ` · ${Number(movie.rating).toLocaleString('it-IT', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}` : '';
  button.innerHTML = `<span class="movie-poster">${poster}</span><strong>${escapeHtml(movie.title)}</strong><span>${escapeHtml(movie.year || 'Anno n.d.')}${rating}</span>`;
  return button;
}

function escapeHtml(value) {
  const node = document.createElement('div');
  node.textContent = String(value ?? '');
  return node.innerHTML;
}

function updateMovieCoverflow(track) {
  const trackRect = track.getBoundingClientRect();
  const center = trackRect.left + track.clientWidth / 2;
  track.querySelectorAll('.movie-cover').forEach(card => {
    const rect = card.getBoundingClientRect();
    const offset = (center - (rect.left + rect.width / 2)) / rect.width;
    const distance = Math.min(Math.abs(offset), 2.2);
    card.style.setProperty('--cover-distance', distance.toFixed(2));
    card.style.setProperty('--cover-offset', Math.max(-2.2, Math.min(2.2, offset)).toFixed(2));
  });
}

function initMovieCoverflows() {
  document.querySelectorAll('[data-movie-coverflow]').forEach(track => {
    if (track.dataset.coverflowReady) return;
    track.dataset.coverflowReady = '1';
    let frame = 0;
    const update = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => updateMovieCoverflow(track));
    };
    track.addEventListener('scroll', update, { passive: true });
    update();
  });
}

function filterMovieLibrary(filter) {
  const library = document.querySelector('[data-movie-library]');
  if (!library) return;
  let visible = 0;
  library.querySelectorAll('.movie-cover').forEach(card => {
    const show = filter === 'all' || card.dataset.entryStatus === filter || (filter === 'favorite' && card.dataset.entryFavorite === '1');
    card.hidden = !show;
    if (show) visible += 1;
  });
  const empty = library.querySelector('[data-library-empty]');
  if (empty) empty.hidden = visible > 0;
  const track = library.querySelector('[data-movie-coverflow]');
  if (track) updateMovieCoverflow(track);
}

function restoreMovieGenreSelection() {
  const select = document.querySelector('[data-movie-genre]');
  if (!select) return;
  const savedGenre = sessionStorage.getItem('familyplan.movieGenre') || '';
  if (!savedGenre || !select.querySelector(`option[value="${CSS.escape(savedGenre)}"]`)) return;
  select.value = savedGenre;
  select.dispatchEvent(new Event('change', { bubbles: true }));
}

async function openMovieDetail(button) {
  const modal = document.querySelector('[data-movie-modal]');
  const detail = modal?.querySelector('[data-movie-detail]');
  const track = button.closest('[data-movie-coverflow]');
  if (!modal || !detail || !track) return;

  detail.innerHTML = '<div class="movie-loading">Caricamento…</div>';
  showModalClean(modal);
  try {
    const detailUrl = new URL(`${track.dataset.detailUrl}/${button.dataset.movieId}`, window.location.origin);
    detailUrl.searchParams.set('type', button.dataset.mediaType || 'movie');
    const response = await fetch(detailUrl, { headers: { Accept: 'application/json' } });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || 'Dettagli non disponibili.');
    const movie = payload.movie;
    const entry = payload.entry;
    modal.dataset.movieId = movie.id;
    modal.dataset.mediaType = movie.media_type;
    modal.dataset.entryId = entry?.entry_id || '';
    modal.querySelector('[data-movie-title]').textContent = movie.title;
    modal.querySelector('[data-movie-kind]').textContent = movie.media_type === 'tv' ? 'Serie TV' : 'Film';
    const length = movie.media_type === 'tv' && movie.seasons ? `${movie.seasons} stagioni` : (movie.runtime ? `${movie.runtime} min` : '');
    const facts = [movie.year, length, movie.rating ? `★ ${movie.rating}` : ''].filter(Boolean).join(' · ');
    detail.innerHTML = `
      ${movie.backdrop ? `<img class="movie-detail-backdrop" src="${escapeHtml(movie.backdrop)}" alt="">` : ''}
      <div class="movie-detail-body">
        ${movie.tagline ? `<p class="movie-tagline">${escapeHtml(movie.tagline)}</p>` : ''}
        <p class="movie-facts">${escapeHtml(facts)}</p>
        ${movie.providers.length ? `<div class="movie-providers"><strong>Dove vederlo in streaming</strong><div>${movie.providers.map(provider => `<span>${provider.logo ? `<img src="${escapeHtml(provider.logo)}" alt="">` : ''}${escapeHtml(provider.name)}</span>`).join('')}</div></div>` : '<p class="movie-provider-empty">Non risulta disponibile in abbonamento streaming in Italia.</p>'}
        ${movie.genres.length ? `<p class="movie-genres">${movie.genres.map(genre => `<span>${escapeHtml(genre)}</span>`).join('')}</p>` : ''}
        <p>${escapeHtml(movie.overview)}</p>
        ${movie.cast.length ? `<p><strong>Cast</strong><br>${escapeHtml(movie.cast.join(', '))}</p>` : ''}
        ${movie.trailer_url ? `<a class="secondary-btn movie-trailer" href="${escapeHtml(movie.trailer_url)}" target="_blank" rel="noopener">Guarda trailer</a>` : ''}
        <form class="movie-list-form form-stack" data-movie-list-form data-native-submit>
          <div class="movie-list-grid">
            <label><span>Stato</span><select name="status"><option value="watchlist" ${entry?.status !== 'watched' ? 'selected' : ''}>Da vedere</option><option value="watched" ${entry?.status === 'watched' ? 'selected' : ''}>Visto</option></select></label>
            <label><span>Il mio voto</span><select name="rating"><option value="">Nessun voto</option>${Array.from({ length: 10 }, (_, index) => index + 1).map(vote => `<option value="${vote}" ${Number(entry?.user_rating) === vote ? 'selected' : ''}>${vote}/10</option>`).join('')}</select></label>
          </div>
          <div class="movie-checks">
            <label class="movie-toggle"><input class="checkbox" type="checkbox" name="is_favorite" value="1" ${entry?.is_favorite ? 'checked' : ''}><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9Z"/></svg><span>Preferito</span></label>
            <label class="movie-toggle"><input class="checkbox" type="checkbox" name="is_shared" value="1" ${entry?.is_shared ? 'checked' : ''}><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M13.5 20a4.5 4.5 0 0 1 7 0"/></svg><span>Condividi</span></label>
          </div>
          <div class="modal-actions">
            ${entry ? '<button class="ghost movie-remove-btn" type="button" data-movie-remove>Rimuovi</button>' : ''}
            <button type="submit">Salva</button>
          </div>
        </form>
      </div>`;
  } catch (error) {
    detail.innerHTML = `<div class="alert">${escapeHtml(error.message)}</div>`;
  }
}

document.addEventListener('click', event => {
  const movie = event.target.closest('[data-movie-id]');
  if (movie) openMovieDetail(movie);
  if (event.target.closest('[data-movie-close]')) event.target.closest('dialog')?.close();
  const remove = event.target.closest('[data-movie-remove]');
  if (remove) removeMovieEntry(remove);
  const libraryFilter = event.target.closest('[data-library-filter]');
  if (libraryFilter) {
    libraryFilter.parentElement.querySelectorAll('[data-library-filter]').forEach(button => {
      const active = button === libraryFilter;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    filterMovieLibrary(libraryFilter.dataset.libraryFilter);
  }
});

async function removeMovieEntry(button) {
  const modal = button.closest('[data-movie-modal]');
  if (!modal?.dataset.entryId || !window.confirm('Vuoi rimuovere questo titolo dalla tua lista?')) return;
  button.disabled = true;
  try {
    const url = modal.dataset.deleteTemplate.replace('__ID__', modal.dataset.entryId);
    const response = await fetch(url, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || 'Rimozione non riuscita.');
    modal.close();
    await refreshCurrentPage();
    showCopyFeedback(payload.message);
  } catch (error) {
    showCopyFeedback(error.message);
  } finally {
    button.disabled = false;
  }
}

document.addEventListener('submit', async event => {
  const listForm = event.target.closest('[data-movie-list-form]');
  if (listForm) {
    event.preventDefault();
    const modal = listForm.closest('[data-movie-modal]');
    const submit = listForm.querySelector('[type="submit"]');
    submit.disabled = true;
    try {
      const form = new FormData(listForm);
      const response = await fetch(modal.dataset.saveTemplate.replace('__ID__', modal.dataset.movieId), {
        method: 'PUT', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: JSON.stringify({ media_type: modal.dataset.mediaType, status: form.get('status'), rating: form.get('rating') || null, is_favorite: form.has('is_favorite'), is_shared: form.has('is_shared') }),
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || 'Salvataggio non riuscito.');
      modal.close();
      await refreshCurrentPage();
      showCopyFeedback(payload.message);
    } catch (error) {
      showCopyFeedback(error.message);
    } finally {
      submit.disabled = false;
    }
    return;
  }
  const form = event.target.closest('[data-movie-search]');
  if (!form) return;
  event.preventDefault();
  const input = form.querySelector('input[name="query"]');
  if (!input?.value.trim() || input.value.trim().length < 2) return;

  const section = document.querySelector('[data-movie-search-section]');
  const track = section?.querySelector('[data-movie-coverflow]');
  const empty = section?.querySelector('[data-movie-empty]');
  section.hidden = false;
  track.innerHTML = '<div class="movie-loading">Ricerca…</div>';
  empty.hidden = true;
  try {
    const url = new URL(form.dataset.searchUrl, window.location.origin);
    url.searchParams.set('query', input.value.trim());
    url.searchParams.set('type', form.querySelector('[name="type"]')?.value || 'multi');
    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || 'Ricerca non disponibile.');
    track.innerHTML = '';
    payload.movies.forEach(movie => track.append(movieCard(movie)));
    empty.hidden = payload.movies.length > 0;
    delete track.dataset.coverflowReady;
    initMovieCoverflows();
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (error) {
    track.innerHTML = `<div class="alert">${escapeHtml(error.message)}</div>`;
  }
});

document.addEventListener('change', async event => {
  const select = event.target.closest('[data-movie-genre]');
  if (!select) return;
  if (select.value) sessionStorage.setItem('familyplan.movieGenre', select.value);
  else sessionStorage.removeItem('familyplan.movieGenre');
  const section = select.closest('[data-movie-genre-section]');
  const track = section?.querySelector('[data-movie-coverflow]');
  if (!section || !track) return;
  if (!select.value) {
    track.innerHTML = '<p class="movies-empty">Seleziona un genere per ricevere nuovi suggerimenti.</p>';
    return;
  }
  track.innerHTML = '<div class="movie-loading">Cerco nuovi suggerimenti…</div>';
  try {
    const url = new URL(section.dataset.genreUrl, window.location.origin);
    url.searchParams.set('genre', select.value);
    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || 'Suggerimenti non disponibili.');
    track.replaceChildren(...payload.movies.map(movieCard));
    if (!payload.movies.length) track.innerHTML = '<p class="movies-empty">Nessun suggerimento disponibile per questo genere.</p>';
    delete track.dataset.coverflowReady;
    initMovieCoverflows();
  } catch (error) {
    track.innerHTML = `<div class="alert">${escapeHtml(error.message)}</div>`;
  }
});

function updateMobileAlphabetVisibility(show = true) {
  const rail = document.querySelector('[data-contact-alphabet-mobile]');
  if (!rail) return;
  rail.classList.toggle('is-visible', show);
  if (mobileAlphabetHideTimer) window.clearTimeout(mobileAlphabetHideTimer);
  if (show) {
    mobileAlphabetHideTimer = window.setTimeout(() => {
      rail.classList.remove('is-visible');
    }, 900);
  }
}

document.addEventListener('change', event => {
  if (event.target.matches('[data-user-category], [data-user-birth-date]')) {
    const form = event.target.closest('[data-user-form]');
    if (form) updateUserFormVisibility(form);
  }
});

document.addEventListener('click', event => {
  const commandButton = event.target.closest('[data-wysiwyg-command]');
  if (!commandButton) return;
  const form = commandButton.closest('form');
  const editor = form?.querySelector('[data-wysiwyg-editor]');
  editor?.focus();
  const command = commandButton.dataset.wysiwygCommand;
  let value = commandButton.dataset.wysiwygValue ?? null;

  if (command === 'createLink') {
    const selection = window.getSelection?.();
    if (!selection || selection.toString().trim() === '') return;
    value = window.prompt('Inserisci il link completo', 'https://') || '';
    if (!value.trim()) return;
  }

  document.execCommand(command, false, value);
  syncWysiwygToInput(form ?? document);
});

document.addEventListener('input', event => {
  if (!event.target.matches('[data-wysiwyg-editor]')) return;
  const input = event.target.closest('label')?.querySelector('[data-wysiwyg-input]');
  if (input) input.value = event.target.innerHTML.trim();
});

document.addEventListener('input', event => {
  const search = event.target.closest('[data-contact-search]');
  if (!search) return;
  syncSearchClear(search);
  const value = search.value.trim().toLowerCase();
  const cards = [...document.querySelectorAll('[data-contact-card]')];
  cards.forEach(card => {
    const searchable = card.dataset.contactSearchable || '';
    card.hidden = value !== '' && !searchable.includes(value);
  });

  document.querySelectorAll('[data-contact-group]').forEach(group => {
    group.hidden = !group.querySelector('[data-contact-card]:not([hidden])');
  });
});

document.addEventListener('input', event => {
  const search = event.target.closest('[data-password-search]');
  if (!search) return;
  syncSearchClear(search);
  const value = search.value.trim().toLowerCase();
  document.querySelectorAll('[data-password-card]').forEach(card => {
    const searchable = card.dataset.passwordSearchable || '';
    card.hidden = value !== '' && !searchable.includes(value);
  });
});

document.addEventListener('input', event => {
  const search = event.target.closest('[data-loyalty-search]');
  if (!search) return;
  syncSearchClear(search);
  const value = search.value.trim().toLowerCase();
  document.querySelectorAll('[data-loyalty-card]').forEach(card => {
    const searchable = card.dataset.loyaltySearchable || '';
    card.hidden = value !== '' && !searchable.includes(value);
  });
});

function renderSelectedGroceries(picker) {
  const selectedWrap = picker.querySelector('[data-selected-groceries]');
  const inputWrap = picker.querySelector('[data-selected-grocery-inputs]');
  if (!selectedWrap || !inputWrap) return;
  const selected = (picker.dataset.selectedGroceries || '').split('|').filter(Boolean);
  selectedWrap.innerHTML = '';
  inputWrap.innerHTML = '';
  selected.forEach(item => {
    const badge = document.createElement('span');
    badge.textContent = item;
    selectedWrap.append(badge);
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'selected_items[]';
    input.value = item;
    inputWrap.append(input);
  });
}

function renumberDraftProducts(container) {
  container.querySelectorAll('[data-draft-product]').forEach((row, index) => {
    row.querySelector('[data-draft-name]').name = `products[${index}][name]`;
    row.querySelector('[data-draft-quantity]').name = `products[${index}][quantity]`;
    row.querySelector('[data-draft-checked]').name = `products[${index}][checked]`;
  });
}

function addDraftProduct(scope, name, quantity = '', checked = false) {
  const container = scope.querySelector('[data-draft-products]');
  if (!container || !name.trim()) return;
  const row = document.createElement('div');
  row.className = 'product-row';
  row.dataset.draftProduct = '';
  row.innerHTML = `
    <input type="hidden" data-draft-name value="${name.trim().replaceAll('"', '&quot;')}">
    <input type="hidden" data-draft-quantity value="${quantity.trim().replaceAll('"', '&quot;')}">
    <input type="hidden" data-draft-checked value="${checked ? 1 : 0}">
    <span class="${checked ? 'done' : ''}">${name.trim()} ${quantity.trim()}</span>
    <button class="icon-btn danger-icon" type="button" data-draft-product-remove aria-label="Rimuovi prodotto" title="Rimuovi prodotto"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg></button>
  `;
  container.append(row);
  renumberDraftProducts(container);
}

document.addEventListener('click', async event => {
  const letterLink = event.target.closest('[data-letter-link]');
  if (letterLink) {
    event.preventDefault();
    const search = document.querySelector('[data-contact-search]');
    if (search && search.value !== '') {
      search.value = '';
      search.dispatchEvent(new Event('input', { bubbles: true }));
    }

    const targetId = letterLink.getAttribute('href');
    const target = targetId ? document.querySelector(targetId) : null;
    if (target) {
      const topbarHeight = document.querySelector('.topbar')?.offsetHeight ?? 64;
      const targetTop = target.getBoundingClientRect().top + window.scrollY - topbarHeight - 12;
      window.scrollTo({
        top: Math.max(targetTop, 0),
        behavior: 'smooth',
      });
    }
  }

  const removeDraft = event.target.closest('[data-draft-product-remove]');
  if (removeDraft) {
    const container = removeDraft.closest('[data-draft-products]');
    removeDraft.closest('[data-draft-product]')?.remove();
    if (container) renumberDraftProducts(container);
    return;
  }

  const addDraft = event.target.closest('[data-draft-product-add]');
  if (addDraft) {
    const scope = addDraft.closest('.modal-card');
    const name = scope?.querySelector('[data-draft-manual-name]');
    const quantity = scope?.querySelector('[data-draft-manual-quantity]');
    addDraftProduct(scope, name?.value ?? '', quantity?.value ?? '');
    if (name) name.value = '';
    if (quantity) quantity.value = '';
    return;
  }

  const groceryItem = event.target.closest('[data-grocery-item]');
  if (!groceryItem) return;
  const picker = groceryItem.closest('[data-grocery-picker]');
  const feedback = picker?.querySelector('[data-grocery-feedback]');
  if (!picker) return;

  if (picker.hasAttribute('data-grocery-create')) {
    const item = groceryItem.dataset.groceryItem;
    const selected = new Set((picker.dataset.selectedGroceries || '').split('|').filter(Boolean));
    if (selected.has(item)) {
      selected.delete(item);
      groceryItem.classList.remove('was-added');
      if (feedback) feedback.textContent = `${item} rimosso.`;
    } else {
      selected.add(item);
      groceryItem.classList.add('was-added');
      if (feedback) feedback.textContent = `${item} selezionato.`;
    }
    picker.dataset.selectedGroceries = [...selected].join('|');
    renderSelectedGroceries(picker);
    return;
  }

  if (picker.hasAttribute('data-grocery-draft')) {
    addDraftProduct(groceryItem.closest('.modal-card'), groceryItem.dataset.groceryItem);
    groceryItem.classList.add('was-added');
    if (feedback) feedback.textContent = `${groceryItem.dataset.groceryItem} aggiunto alla bozza.`;
    window.setTimeout(() => groceryItem.classList.remove('was-added'), 650);
    return;
  }

  groceryItem.disabled = true;
  const originalText = groceryItem.textContent;
  const response = await fetch(groceryItem.dataset.storeUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Accept': 'text/html,application/xhtml+xml',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: new URLSearchParams({ name: groceryItem.dataset.groceryItem, quantity: '' }),
  }).catch(() => null);

  if (response?.ok) {
    groceryItem.classList.add('was-added');
    groceryItem.textContent = 'Aggiunto';
    if (feedback) feedback.textContent = `${originalText} aggiunto alla lista.`;
    const list = groceryItem.closest('.modal-card')?.querySelector('.shopping-products');
    if (list) {
      const row = document.createElement('div');
      row.className = 'product-row';
      const text = document.createElement('span');
      text.textContent = originalText;
      row.append(text);
      list.append(row);
    }
    window.setTimeout(() => {
      groceryItem.disabled = false;
      groceryItem.textContent = originalText;
      groceryItem.classList.remove('was-added');
    }, 650);
  } else {
    groceryItem.disabled = false;
    if (feedback) feedback.textContent = 'Non sono riuscito ad aggiungere questo alimento.';
  }
});

window.addEventListener('scroll', () => {
  updateMobileAlphabetVisibility(true);
}, { passive: true });

document.addEventListener('submit', async event => {
  const currencyForm = event.target.closest('[data-currency-form]');
  if (currencyForm) {
    event.preventDefault();
    const submitter = event.submitter;
    if (submitter) submitter.disabled = true;
    const result = currencyForm.querySelector('[data-currency-result]');
    const meta = currencyForm.querySelector('[data-currency-meta]');
    if (result) result.textContent = 'Converto...';
    if (meta) meta.textContent = 'Sto leggendo il cambio giornaliero.';

    const response = await fetch(currencyForm.action, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: new FormData(currencyForm),
    }).catch(() => null);

    const payload = response ? await response.json().catch(() => ({})) : {};
    if (!response?.ok) {
      if (result) result.textContent = 'Cambio non disponibile.';
      if (meta) meta.textContent = payload.errors?.amount?.[0] || 'Servizio momentaneamente non disponibile.';
      if (submitter) submitter.disabled = false;
      return;
    }

    const converted = Number(payload.converted || 0).toLocaleString('it-IT', { maximumFractionDigits: 4 });
    const amount = Number(payload.amount || 0).toLocaleString('it-IT', { maximumFractionDigits: 4 });
    if (result) result.textContent = `${amount} ${payload.source} = ${converted} ${payload.target}`;
    if (meta) meta.textContent = `Cambio del ${payload.date || 'giorno'} via ${payload.provider || 'Frankfurter'}.`;
    if (submitter) submitter.disabled = false;
    return;
  }

  const form = event.target.closest('[data-translator-form]');
  if (!form) return;

  event.preventDefault();

  const submitter = event.submitter;
  if (submitter) submitter.disabled = true;

  const result = form.querySelector('[data-translator-result]');
  const meta = form.querySelector('[data-translator-meta]');
  if (result) result.textContent = 'Traduco...';
  if (meta) meta.textContent = 'Sto contattando il servizio di traduzione.';

  const response = await fetch(form.action, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: new FormData(form),
  }).catch(() => null);

  if (!response) {
    if (result) result.textContent = 'Non sono riuscito a contattare il servizio.';
    if (meta) meta.textContent = 'Riprova tra poco.';
    if (submitter) submitter.disabled = false;
    return;
  }

  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    if (result) result.textContent = 'Traduzione non disponibile.';
    if (meta) meta.textContent = payload.errors?.text?.[0] || 'Servizio momentaneamente non disponibile.';
    if (submitter) submitter.disabled = false;
    return;
  }

  if (result) result.textContent = payload.translated_text || '';
  if (meta) meta.textContent = `Traduzione via ${payload.provider || 'servizio esterno'}.`;
  if (submitter) submitter.disabled = false;
});

function userDataFromButton(button) {
  return {
    action: button.dataset.userAction,
    name: button.dataset.userName,
    phone: button.dataset.userPhone,
    email: button.dataset.userEmail,
    role: button.dataset.userRole,
    category: button.dataset.userCategory,
    mother_id: button.dataset.userMotherId,
    father_id: button.dataset.userFatherId,
    birth_date: button.dataset.userBirthDate,
    personal_info: button.dataset.userPersonalInfo,
    profile_photo_url: button.dataset.userProfilePhotoUrl,
    avatar_initials: button.dataset.userAvatarInitials,
    is_active: button.dataset.userIsActive === '1',
  };
}

function applyUserDataToForm(form, user) {
  const formKey = form.matches('[data-edit-user-form]') ? 'edit' : 'create';
  const hasVueFields = form.querySelector('[data-vue-user-fields]');
  if (hasVueFields) {
    window.dispatchEvent(new CustomEvent(`user-form:set-values:${formKey}`, {
      detail: user,
    }));
    return;
  }
  form.querySelectorAll('[data-user-field]').forEach(field => {
    const key = field.dataset.userField;
    if (field.type === 'checkbox') field.checked = Boolean(user[key]);
    else if (key === 'password') field.value = '';
    else field.value = user[key] ?? '';
  });
  syncWysiwygFromInput(form);
  updateUserFormVisibility(form);
}

function openUserEditor(button) {
  const editUserModal = document.querySelector('#user-edit-modal');
  const editUserForm = document.querySelector('[data-edit-user-form]');
  const deleteUserForm = document.querySelector('[data-delete-user-form]');
  if (!editUserModal || !editUserForm || editUserModal.open) return;
  const user = userDataFromButton(button);
  editUserForm.action = user.action;
  if (deleteUserForm) deleteUserForm.action = button.dataset.userDeleteAction;
  applyUserDataToForm(editUserForm, user);
  showModalClean(editUserModal, button);
}

function eventDataFromButton(button) {
  return {
    action: button.dataset.eventAction,
    duplicate_action: button.dataset.eventDuplicateAction,
    delete_action: button.dataset.eventDeleteAction,
    title: button.dataset.eventTitle,
    description: button.dataset.eventDescription,
    starts_at: button.dataset.eventStartsAt,
    ends_at: button.dataset.eventEndsAt,
    visibility: button.dataset.eventVisibility,
    assigned_user_id: button.dataset.eventAssignedUserId,
    child_id: button.dataset.eventChildId,
  };
}

function applyEventDataToForm(form, eventData) {
  const formKey = form.matches('[data-edit-event-form]') ? 'edit' : 'create';
  const hasVueFields = form.querySelector('[data-vue-calendar-fields]');
  if (hasVueFields) {
    window.dispatchEvent(new CustomEvent(`calendar-form:set-values:${formKey}`, {
      detail: eventData,
    }));
    return;
  }
  form.querySelectorAll('[data-event-field]').forEach(field => {
    field.value = eventData[field.dataset.eventField] ?? '';
  });
}

function openCalendarEventEditor(eventData, deleteAction = '') {
  const editEventModal = document.querySelector('#event-edit-modal');
  const editEventForm = document.querySelector('[data-edit-event-form]');
  const duplicateEventForm = document.querySelector('[data-duplicate-event-form]');
  const deleteEventForm = document.querySelector('[data-delete-event-form]');
  if (!editEventModal || !editEventForm || editEventModal.open) return;
  editEventForm.action = eventData.action;
  if (duplicateEventForm) duplicateEventForm.action = eventData.duplicate_action || '';
  if (deleteEventForm) deleteEventForm.action = deleteAction || eventData.delete_action || '';
  applyEventDataToForm(editEventForm, eventData);
  showModalClean(editEventModal);
}

function openCalendarCreateModal(day = '') {
  const createEventModal = document.querySelector('#event-create-modal');
  const createEventForm = document.querySelector('#event-create-modal [data-event-form]');
  if (!createEventModal || !createEventForm || createEventModal.open) return;
  const hasVueFields = createEventForm.querySelector('[data-vue-calendar-fields]');
  if (hasVueFields) {
    window.dispatchEvent(new CustomEvent('calendar-form:reset:create'));
  } else {
    createEventForm.reset();
  }
  if (day) {
    applyEventDataToForm(createEventForm, {
      title: '',
      starts_at: `${day}T09:00`,
      ends_at: `${day}T10:00`,
      visibility: 'family',
      assigned_user_id: '',
      child_id: '',
      description: '',
    });
  }
  showModalClean(createEventModal);
}

window.openCalendarEventEditor = openCalendarEventEditor;
window.openCalendarCreateModal = openCalendarCreateModal;

function localDateTimeValue(date) {
  const pad = value => String(value).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function moveDateTimeToDay(value, day) {
  const date = new Date(value);
  const [year, month, dateOfMonth] = day.split('-').map(Number);
  date.setFullYear(year, month - 1, dateOfMonth);
  return date;
}

let draggedEvent = null;
let calendarDragMoved = false;

document.addEventListener('click', event => {
  if (!calendarDragMoved || !event.target.closest('.calendar-event[draggable="true"]')) return;
  event.stopImmediatePropagation();
  event.preventDefault();
  calendarDragMoved = false;
}, true);

document.addEventListener('dragstart', event => {
  const button = event.target.closest('.calendar-event[draggable="true"]');
  if (!button) return;
  draggedEvent = button;
  calendarDragMoved = false;
  button.classList.add('is-dragging');
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', button.dataset.eventAction || '');
});

document.addEventListener('dragend', () => {
  draggedEvent?.classList.remove('is-dragging');
  draggedEvent = null;
  document.querySelectorAll('.calendar-day.is-drop-target').forEach(day => day.classList.remove('is-drop-target'));
});

document.addEventListener('dragover', event => {
  const day = event.target.closest('[data-calendar-day]');
  if (!day || !draggedEvent) return;
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
  day.classList.add('is-drop-target');
});

document.addEventListener('dragleave', event => {
  event.target.closest('[data-calendar-day]')?.classList.remove('is-drop-target');
});

document.addEventListener('drop', async event => {
  const day = event.target.closest('[data-calendar-day]');
  if (!day || !draggedEvent) return;
  event.preventDefault();
  day.classList.remove('is-drop-target');

  const eventData = eventDataFromButton(draggedEvent);
  const newStart = moveDateTimeToDay(eventData.starts_at, day.dataset.calendarDay);
  const oldStart = new Date(eventData.starts_at);
  const oldEnd = eventData.ends_at ? new Date(eventData.ends_at) : null;
  const newEnd = oldEnd ? new Date(newStart.getTime() + (oldEnd.getTime() - oldStart.getTime())) : null;
  const body = new URLSearchParams({
    _method: 'PATCH',
    title: eventData.title,
    description: eventData.description ?? '',
    starts_at: localDateTimeValue(newStart),
    ends_at: newEnd ? localDateTimeValue(newEnd) : '',
    visibility: eventData.visibility || 'family',
    assigned_user_id: eventData.assigned_user_id || '',
    child_id: eventData.child_id || '',
  });

  const response = await fetch(eventData.action, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Accept': 'text/html,application/xhtml+xml',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body,
  }).catch(() => null);

  if (response?.ok) {
    calendarDragMoved = true;
    draggedEvent = null;
    await replacePageFromResponse(response);
  } else {
    alert('Non sono riuscito a spostare questo evento.');
  }
});

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible' && Notification?.permission === 'default' && pushNotificationsEnabled()) {
    Notification.requestPermission().catch(() => {});
  }

  if (document.visibilityState === 'visible') {
    ensurePushSubscription();
    pollReminderNotifications();
  }
});

announceToastNotifications(document);
hydrateDynamicState();
const startBackgroundServices = () => {
  ensurePushSubscription();
  pollReminderNotifications();
};
if ('requestIdleCallback' in window) {
  window.requestIdleCallback(startBackgroundServices, { timeout: 2000 });
} else {
  window.setTimeout(startBackgroundServices, 800);
}
window.setInterval(pollReminderNotifications, 30000);

document.addEventListener('dragstart', event => {
  const widgetRow = event.target.closest('[data-widget-sort-item]');
  if (!widgetRow) return;
  draggedWidgetRow = widgetRow;
  widgetRow.classList.add('is-dragging');
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', widgetRow.dataset.widgetKey || '');
});

document.addEventListener('dragend', () => {
  draggedWidgetRow?.classList.remove('is-dragging');
  draggedWidgetRow = null;
  document.querySelectorAll('[data-widget-sort-item].is-drop-target').forEach(item => item.classList.remove('is-drop-target'));
});

document.addEventListener('dragover', event => {
  const widgetRow = event.target.closest('[data-widget-sort-item]');
  if (!widgetRow || !draggedWidgetRow || widgetRow === draggedWidgetRow) return;
  event.preventDefault();
  widgetRow.classList.add('is-drop-target');
  event.dataTransfer.dropEffect = 'move';
});

document.addEventListener('dragleave', event => {
  event.target.closest('[data-widget-sort-item]')?.classList.remove('is-drop-target');
});

document.addEventListener('drop', event => {
  const widgetRow = event.target.closest('[data-widget-sort-item]');
  const widgetList = event.target.closest('[data-widget-sort-list]');
  if (!widgetRow || !widgetList || !draggedWidgetRow || widgetRow === draggedWidgetRow) return;
  event.preventDefault();
  widgetRow.classList.remove('is-drop-target');

  const rowRect = widgetRow.getBoundingClientRect();
  const insertAfter = event.clientY > rowRect.top + rowRect.height / 2;
  if (insertAfter) {
    widgetRow.after(draggedWidgetRow);
  } else {
    widgetRow.before(draggedWidgetRow);
  }
  syncWidgetOrderInputs();
});

function clearWidgetDropTargets() {
  document.querySelectorAll('[data-widget-sort-item].is-drop-target').forEach(item => item.classList.remove('is-drop-target'));
}

function moveWidgetRowFromPoint(clientY) {
  if (!draggedWidgetRow) return;
  const rows = [...document.querySelectorAll('[data-widget-sort-item]')].filter(row => row !== draggedWidgetRow);
  const targetRow = rows.find(row => {
    const rect = row.getBoundingClientRect();
    return clientY >= rect.top && clientY <= rect.bottom;
  });

  clearWidgetDropTargets();
  if (!targetRow) return;

  targetRow.classList.add('is-drop-target');
  const rect = targetRow.getBoundingClientRect();
  const insertAfter = clientY > rect.top + rect.height / 2;
  if (insertAfter) targetRow.after(draggedWidgetRow);
  else targetRow.before(draggedWidgetRow);
}

document.addEventListener('pointerdown', event => {
  const handle = event.target.closest('[data-widget-drag-handle]');
  const widgetRow = handle?.closest('[data-widget-sort-item]');
  if (!handle || !widgetRow) return;

  widgetPointerDragActive = true;
  widgetPointerDragPointerId = event.pointerId;
  draggedWidgetRow = widgetRow;
  widgetRow.classList.add('is-dragging');
  handle.setPointerCapture?.(event.pointerId);
  event.preventDefault();
  event.stopPropagation();
});

document.addEventListener('pointermove', event => {
  if (!widgetPointerDragActive || !draggedWidgetRow || event.pointerId !== widgetPointerDragPointerId) return;
  moveWidgetRowFromPoint(event.clientY);
  event.preventDefault();
});

document.addEventListener('pointerup', () => {
  if (!widgetPointerDragActive) return;
  draggedWidgetRow?.classList.remove('is-dragging');
  clearWidgetDropTargets();
  syncWidgetOrderInputs();
  draggedWidgetRow = null;
  widgetPointerDragActive = false;
  widgetPointerDragPointerId = null;
});

document.addEventListener('pointercancel', () => {
  if (!widgetPointerDragActive) return;
  draggedWidgetRow?.classList.remove('is-dragging');
  clearWidgetDropTargets();
  draggedWidgetRow = null;
  widgetPointerDragActive = false;
  widgetPointerDragPointerId = null;
});
