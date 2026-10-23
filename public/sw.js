const CACHE = 'familyplan-static-v5';
const ASSETS = [
  '/manifest.webmanifest',
  '/images/app/familyplan-mark.svg',
  '/images/app/familyplan-mark-192.png',
  '/images/app/familyplan-mark-512.png',
];

// Storage can be unavailable in installed/private contexts; it must not block the network.
async function openStaticCache() {
  try { return await caches.open(CACHE); } catch { return null; }
}

self.addEventListener('install', event => {
  event.waitUntil((async () => {
    const cache = await openStaticCache();
    if (cache) await Promise.allSettled(ASSETS.map(asset => cache.add(asset)));
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', event => {
  event.waitUntil((async () => {
    await Promise.allSettled([
      caches.keys().then(keys => Promise.all(keys.filter(key => key.startsWith('familyplan-static-') && key !== CACHE).map(key => caches.delete(key)))),
      // Avoid a second navigation request and inconsistent preload behavior on mobile.
      self.registration.navigationPreload?.disable(),
    ]);
    await self.clients.claim();
  })());
});

function unavailableNavigation() {
  // Never cache authenticated HTML. This page contains no account information.
  return new Response(`<!doctype html><html lang="it"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>FamilyPlan · Connessione</title><style>body{font:18px system-ui;background:#f7faf6;color:#244b42;margin:0;padding:32px;min-height:80vh;display:grid;place-content:center}main{max-width:440px}h1{font-size:28px}p{line-height:1.5}a{display:inline-block;padding:16px 24px;border-radius:16px;background:#335f49;color:white;text-decoration:none;font-weight:700}</style><main><h1>Ricolleghiamoci a FamilyPlan</h1><p>La connessione non è disponibile in questo momento. Controlla Wi-Fi o rete mobile e riprova.</p><a href="">Riprova</a></main></html>`, {
    status: 503,
    headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' },
  });
}

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) return;

  if (event.request.mode === 'navigate') {
    event.respondWith((async () => {
      // A rejected preload must not prevent the ordinary network request.
      try { const preloaded = await event.preloadResponse; if (preloaded) return preloaded; } catch {}
      try { return await fetch(event.request); } catch { return unavailableNavigation(); }
    })());
    return;
  }

  const isStaticAsset = url.pathname.startsWith('/build/assets/')
    || url.pathname.startsWith('/images/')
    || url.pathname === '/css/app.css'
    || url.pathname === '/js/app.js'
    || url.pathname === '/manifest.webmanifest';
  if (!isStaticAsset) return;

  event.respondWith((async () => {
    const cache = await openStaticCache();
    try { const cached = await cache?.match(event.request); if (cached) return cached; } catch {}
    try {
      const response = await fetch(event.request);
      if (response.ok && cache) {
        try { await cache.put(event.request, response.clone()); } catch {}
      }
      return response;
    } catch { return Response.error(); }
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
