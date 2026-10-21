import { createApp, defineAsyncComponent } from 'vue';

const CalendarBoard = defineAsyncComponent(() => import('./components/CalendarBoard.vue'));
const CalendarEventFields = defineAsyncComponent(() => import('./components/CalendarEventFields.vue'));
const ChildrenCards = defineAsyncComponent(() => import('./components/ChildrenCards.vue'));
const DashboardSummary = defineAsyncComponent(() => import('./components/DashboardSummary.vue'));
const DashboardFootball = defineAsyncComponent(() => import('./components/DashboardFootball.vue'));
const DashboardTv = defineAsyncComponent(() => import('./components/DashboardTv.vue'));
const DashboardWeather = defineAsyncComponent(() => import('./components/DashboardWeather.vue'));
const FamilyTimeline = defineAsyncComponent(() => import('./components/FamilyTimeline.vue'));
const NoteFields = defineAsyncComponent(() => import('./components/NoteFields.vue'));
const ProfileFields = defineAsyncComponent(() => import('./components/ProfileFields.vue'));
const ReminderFields = defineAsyncComponent(() => import('./components/ReminderFields.vue'));
const SettingsFields = defineAsyncComponent(() => import('./components/SettingsFields.vue'));
const ShoppingBuilder = defineAsyncComponent(() => import('./components/ShoppingBuilder.vue'));
const UserFields = defineAsyncComponent(() => import('./components/UserFields.vue'));
const PrintCostManager = defineAsyncComponent(() => import('./components/PrintCostManager.vue'));

let jsBarcodePromise = null;
let loyaltyBarcodeReaderPromise = null;

function parseBase64Json(value, fallback) {
  if (!value) return fallback;
  try {
    return JSON.parse(window.atob(value));
  } catch {
    return fallback;
  }
}

function barcodeChecksum(value) {
  const digits = String(value).split('').map(Number);
  const sum = digits.reverse().reduce((carry, digit, index) => carry + digit * (index % 2 === 0 ? 3 : 1), 0);
  return String((10 - (sum % 10)) % 10);
}

function isValidEan(value) {
  const digits = String(value || '').replace(/\s+/g, '');
  if (!/^\d{8}$|^\d{13}$/.test(digits)) return false;
  const body = digits.slice(0, -1);
  return barcodeChecksum(body) === digits.slice(-1);
}

function loyaltyBarcodePayload(rawValue, rawFormat = 'auto') {
  const value = String(rawValue || '').replace(/\s+/g, '');
  const explicit = String(rawFormat || 'auto').toUpperCase();
  const autoFormat = () => {
    if (/^\d{13}$/.test(value) && isValidEan(value)) return { value, format: 'EAN13' };
    if (/^\d{12}$/.test(value) && /^0\d{11}$/.test(value)) {
      const asEan13 = `${value[0]}${value.slice(1)}`;
      if (isValidEan(asEan13)) return { value: asEan13, format: 'EAN13' };
    }
    if (/^\d{8}$/.test(value) && isValidEan(value)) return { value, format: 'EAN8' };
    if (/^\d+$/.test(value) && value.length > 13 && value.length % 2 === 0) return { value, format: 'CODE128C' };
    if (/^\d+$/.test(value) && value.length % 2 === 0) return { value, format: 'CODE128C' };
    return { value, format: 'CODE128' };
  };

  if (explicit === 'AUTO') return autoFormat();
  if (explicit === 'EAN') {
    if (/^\d{12}$/.test(value)) return { value: value + barcodeChecksum(value), format: 'EAN13' };
    if (/^\d{13}$/.test(value)) return { value, format: 'EAN13' };
    if (/^\d{7}$/.test(value)) return { value: value + barcodeChecksum(value), format: 'EAN8' };
    if (/^\d{8}$/.test(value)) return { value, format: 'EAN8' };
    return { value, format: 'CODE128' };
  }
  if (explicit === 'CODE128C') return { value, format: /^\d+$/.test(value) && value.length % 2 === 0 ? 'CODE128C' : 'CODE128' };
  if (explicit === 'CODE128') return { value, format: /^\d+$/.test(value) && value.length % 2 === 0 ? 'CODE128C' : 'CODE128' };

  return { value, format: explicit || 'CODE128' };
}

function loyaltyBarcodeWidth(format, height) {
  const large = height >= 80;
  if (format === 'EAN8') return large ? 2.6 : 2.05;
  if (format === 'EAN13') return large ? 2.15 : 1.7;
  if (format === 'CODE128C') return large ? 1.9 : 1.4;
  return large ? 1.55 : 1.18;
}

function loyaltyBarcodeTextMargin(format) {
  return format === 'EAN13' || format === 'EAN8' ? 8 : 6;
}

async function loadJsBarcode() {
  jsBarcodePromise ??= import('jsbarcode').then((module) => module.default);
  return jsBarcodePromise;
}

async function loadLoyaltyBarcodeReader() {
  loyaltyBarcodeReaderPromise ??= import('@zxing/browser')
    .then(({ BrowserMultiFormatReader }) => new BrowserMultiFormatReader());
  return loyaltyBarcodeReaderPromise;
}

async function renderLoyaltyBarcodes(scope = document) {
  const nodes = [...scope.querySelectorAll('[data-loyalty-barcode]')];
  if (!nodes.length) return;
  const JsBarcode = await loadJsBarcode();

  nodes.forEach((node) => {
    const rawValue = node.dataset.loyaltyBarcode?.trim();
    if (!rawValue) {
      node.innerHTML = '';
      return;
    }

    try {
      const height = Number.parseInt(node.dataset.barcodeHeight || '84', 10);
      const { value, format } = loyaltyBarcodePayload(rawValue, node.dataset.loyaltyFormat || 'auto');
      const displayValue = node.dataset.barcodeDisplayValue === '1';
      JsBarcode(node, value, {
        format,
        displayValue,
        margin: format.startsWith('EAN') ? 12 : 10,
        width: loyaltyBarcodeWidth(format, height),
        height,
        flat: false,
        fontSize: displayValue ? (height >= 100 ? 28 : 20) : 0,
        textMargin: displayValue ? loyaltyBarcodeTextMargin(format) : 0,
        textAlign: 'center',
        fontOptions: '',
        background: '#ffffff',
        lineColor: '#111111',
      });
    } catch {
      node.innerHTML = '';
    }
  });
}

async function decodeLoyaltyImage(file) {
  const loyaltyBarcodeReader = await loadLoyaltyBarcodeReader();
  const objectUrl = URL.createObjectURL(file);
  const image = new Image();
  image.src = objectUrl;
  await image.decode();
  try {
    const result = await loyaltyBarcodeReader.decodeFromImageElement(image);
    return result?.getText?.()?.trim() || '';
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
}

function mountVueComponent(element, component, props = {}) {
  const app = createApp(component, props);
  app.mount(element);
  element.__familyPlanVueApp = app;
  element.dataset.vueMounted = '1';
}

function mountVueApps() {
  document.querySelectorAll('[data-vue-dashboard-summary]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      eventsCount: Number.parseInt(element.dataset.eventsCount || '0', 10),
      shoppingCount: Number.parseInt(element.dataset.shoppingCount || '0', 10),
      remindersCount: Number.parseInt(element.dataset.remindersCount || '0', 10),
      notesCount: Number.parseInt(element.dataset.notesCount || '0', 10),
    };

    mountVueComponent(element, DashboardSummary, payload);
  });

  document.querySelectorAll('[data-vue-dashboard-weather]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      endpoint: element.dataset.endpoint || '',
      defaultLocation: element.dataset.defaultLocation || 'milano',
      locations: parseBase64Json(element.dataset.locationsB64, []),
    };

    mountVueComponent(element, DashboardWeather, payload);
  });

  document.querySelectorAll('[data-vue-dashboard-football]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    mountVueComponent(element, DashboardFootball, {
      endpoint: element.dataset.endpoint || '',
    });
  });

  document.querySelectorAll('[data-vue-dashboard-tv]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    mountVueComponent(element, DashboardTv, {
      endpoint: element.dataset.endpoint || '',
      channels: parseBase64Json(element.dataset.channelsB64, []),
    });
  });

  document.querySelectorAll('[data-vue-shopping-builder]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      formKey: element.dataset.formKey || '',
      mode: element.dataset.mode || 'create',
      categories: parseBase64Json(element.dataset.categoriesB64, {}),
      initialSelected: parseBase64Json(element.dataset.initialSelectedB64, []),
      initialProducts: parseBase64Json(element.dataset.initialProductsB64, []),
    };

    mountVueComponent(element, ShoppingBuilder, payload);
  });

  document.querySelectorAll('[data-vue-calendar-board]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      days: parseBase64Json(element.dataset.daysB64, []),
      events: parseBase64Json(element.dataset.eventsB64, []),
      todayEvents: parseBase64Json(element.dataset.todayEventsB64, []),
      monthLabel: element.dataset.monthLabel || '',
      todayLabel: element.dataset.todayLabel || '',
      previousMonthUrl: element.dataset.previousMonthUrl || '#',
      nextMonthUrl: element.dataset.nextMonthUrl || '#',
    };

    mountVueComponent(element, CalendarBoard, payload);
  });

  document.querySelectorAll('[data-vue-calendar-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      formKey: element.dataset.formKey || '',
      users: parseBase64Json(element.dataset.usersB64, []),
      children: parseBase64Json(element.dataset.childrenB64, []),
    };

    mountVueComponent(element, CalendarEventFields, payload);
  });

  document.querySelectorAll('[data-vue-reminder-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      formKey: element.dataset.formKey || '',
      title: element.dataset.title || '',
      dueAt: element.dataset.dueAt || '',
      recurrence: element.dataset.recurrence || 'none',
      body: parseBase64Json(element.dataset.bodyB64, ''),
      sharedWith: parseBase64Json(element.dataset.sharedWithB64, []),
      users: parseBase64Json(element.dataset.usersB64, []),
    };

    mountVueComponent(element, ReminderFields, payload);
  });

  document.querySelectorAll('[data-vue-note-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      formKey: element.dataset.formKey || '',
      title: element.dataset.title || '',
      body: parseBase64Json(element.dataset.bodyB64, ''),
      sharedWith: parseBase64Json(element.dataset.sharedWithB64, []),
      users: parseBase64Json(element.dataset.usersB64, []),
    };

    mountVueComponent(element, NoteFields, payload);
  });

  document.querySelectorAll('[data-vue-user-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      formKey: element.dataset.formKey || '',
      mothers: parseBase64Json(element.dataset.mothersB64, []),
      fathers: parseBase64Json(element.dataset.fathersB64, []),
      showIsActive: element.dataset.showIsActive === '1',
    };

    mountVueComponent(element, UserFields, payload);
  });

  document.querySelectorAll('[data-vue-profile-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      name: element.dataset.name || '',
      phone: element.dataset.phone || '',
      email: element.dataset.email || '',
      birthDate: element.dataset.birthDate || '',
      favoriteTeam: element.dataset.favoriteTeam || '',
      personalInfo: parseBase64Json(element.dataset.personalInfoB64, ''),
      profilePhotoUrl: element.dataset.profilePhotoUrl || '',
      avatarInitials: element.dataset.avatarInitials || '',
    };

    mountVueComponent(element, ProfileFields, payload);
  });

  document.querySelectorAll('[data-vue-settings-fields]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    const payload = {
      theme: element.dataset.theme || 'light',
      weekStartsOn: element.dataset.weekStartsOn || 'monday',
      compactDashboard: element.dataset.compactDashboard === '1',
      emailNotifications: element.dataset.emailNotifications !== '0',
      pushNotifications: element.dataset.pushNotifications !== '0',
      calendarReminderMinutes: Number.parseInt(element.dataset.calendarReminderMinutes || '30', 10),
      shoppingArchiveDays: Number.parseInt(element.dataset.shoppingArchiveDays || '7', 10),
    };

    mountVueComponent(element, SettingsFields, payload);
  });

  document.querySelectorAll('[data-vue-family-timeline]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    mountVueComponent(element, FamilyTimeline, {
      events: parseBase64Json(element.dataset.eventsB64, []),
    });
  });

  document.querySelectorAll('[data-vue-children-cards]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    mountVueComponent(element, ChildrenCards, {
      children: parseBase64Json(element.dataset.childrenB64, []),
    });
  });

  document.querySelectorAll('[data-vue-print-cost-manager]').forEach((element) => {
    if (element.dataset.vueMounted === '1') return;
    mountVueComponent(element, PrintCostManager);
  });

  renderLoyaltyBarcodes(document);
}

function unmountVueApps(scope = document) {
  scope.querySelectorAll('[data-vue-mounted="1"]').forEach((element) => {
    element.__familyPlanVueApp?.unmount();
    delete element.__familyPlanVueApp;
    delete element.dataset.vueMounted;
  });
}

window.mountVueApps = mountVueApps;
window.unmountVueApps = unmountVueApps;
mountVueApps();

document.addEventListener('input', (event) => {
  const loyaltySearch = event.target.closest('[data-loyalty-search]');
  if (!loyaltySearch) return;
  const value = loyaltySearch.value.trim().toLowerCase();
  document.querySelectorAll('[data-loyalty-card]').forEach((card) => {
    const searchable = card.dataset.loyaltySearchable || '';
    card.hidden = value !== '' && !searchable.includes(value);
  });
});

document.addEventListener('change', async (event) => {
  const imageInput = event.target.closest('[data-loyalty-import-image]');
  if (!imageInput) return;
  const form = imageInput.closest('[data-loyalty-import-form]');
  const file = imageInput.files?.[0];
  if (!form || !file) return;

  const preview = form.querySelector('[data-loyalty-import-preview]');
  const previewImage = form.querySelector('[data-loyalty-import-preview-image]');
  const status = form.querySelector('[data-loyalty-import-status]');
  const codeInput = form.querySelector('[data-loyalty-import-code]');
  if (!preview || !previewImage || !status || !codeInput) return;

  preview.hidden = false;
  previewImage.src = URL.createObjectURL(file);
  status.textContent = 'Analisi foto in corso...';

  try {
    const code = await decodeLoyaltyImage(file);
    if (code) {
      codeInput.value = code;
      status.textContent = 'Barcode rilevato. Controlla e completa i dati della carta.';
    } else {
      status.textContent = 'Barcode non trovato automaticamente. Inseriscilo a mano.';
    }
  } catch {
    status.textContent = 'Non sono riuscito a leggere il barcode da questa foto. Prova con un’immagine piu nitida.';
  }
});
