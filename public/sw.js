const CACHE = 'familyplan-static-v4';
const ASSETS = [
  '/manifest.webmanifest',
  '/images/app/familyplan-mark.svg',
  '/images/app/familyplan-mark-192.png',
  '/images/app/familyplan-mark-512.png',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE)
      .then(cache => Promise.allSettled(ASSETS.map(asset => cache.add(asset))))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(Promise.all([
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE).map(key => caches.delete(key)))),
    self.registration.navigationPreload?.enable(),
    self.clients.claim(),
  ]));
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);

  if (event.request.mode === 'navigate') {
    event.respondWith((async () => {
      const preloaded = await event.preloadResponse;
      return preloaded || fetch(event.request);
    })());
    return;
  }

  if (url.origin !== self.location.origin) return;
  const isStaticAsset = url.pathname.startsWith('/build/assets/')
    || url.pathname.startsWith('/images/')
    || url.pathname === '/css/app.css'
    || url.pathname === '/js/app.js'
    || url.pathname === '/manifest.webmanifest';
  if (!isStaticAsset) return;

  event.respondWith((async () => {
    const cache = await caches.open(CACHE);
    const cached = await cache.match(event.request);
    if (cached) return cached;
    const response = await fetch(event.request);
    if (response.ok) await cache.put(event.request, response.clone());
    return response;
  })());
});

self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : { title: 'FamilyPlan', body: 'Nuovo aggiornamento familiare' };
  event.waitUntil(self.registration.showNotification(data.title, {
    body: data.body,
    icon: '/images/app/familyplan-mark-192.png',
    badge: '/images/app/familyplan-mark-192.png',
    data: { url: data.url || '/' }
  }));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data.url || '/'));
});
