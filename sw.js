const CACHE = 'familyplan-v4';
const ASSETS = ['/assets/styles.css', '/assets/app.js', '/manifest.webmanifest', '/assets/icon.svg'];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(ASSETS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(key => key !== CACHE).map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('message', event => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
  if (event.data === 'CLEAR_CACHES') event.waitUntil(caches.keys().then(keys => Promise.all(keys.map(key => caches.delete(key)))));
});

async function networkFirst(request) {
  const cache = await caches.open(CACHE);
  try {
    const response = await fetch(request, { cache: 'no-store' });
    if (response.ok) cache.put(request, response.clone());
    return response;
  } catch (error) {
    const cached = await cache.match(request);
    if (cached) return cached;
    if (request.mode === 'navigate') {
      return new Response('<!doctype html><title>FamilyPlan offline</title><p>Sei offline. Riprova quando la connessione torna disponibile.</p>', {
        headers: { 'Content-Type': 'text/html; charset=utf-8' },
        status: 503,
      });
    }
    throw error;
  }
}

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (event.request.method !== 'GET' || url.pathname.endsWith('/api.php')) return;
  if (event.request.mode === 'navigate' || ASSETS.includes(url.pathname)) {
    event.respondWith(networkFirst(event.request));
  }
});

self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : { title: 'FamilyPlan', body: 'Nuova notifica' };
  event.waitUntil(self.registration.showNotification(data.title, { body: data.body, icon: '/assets/icon.svg', badge: '/assets/icon.svg' }));
});
