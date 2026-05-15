const CACHE='familyplan-v1';
const ASSETS=['/','/assets/styles.css','/assets/app.js','/manifest.webmanifest','/assets/icon.svg'];
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(c=>c.addAll(ASSETS)).then(()=>self.skipWaiting())));
self.addEventListener('activate',e=>e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',e=>{if(e.request.method!=='GET'||e.request.url.includes('/api.php'))return;e.respondWith(caches.match(e.request).then(c=>c||fetch(e.request).then(r=>{const copy=r.clone();caches.open(CACHE).then(cache=>cache.put(e.request,copy));return r}).catch(()=>caches.match('/'))))});
self.addEventListener('push',e=>{const d=e.data?e.data.json():{title:'FamilyPlan',body:'Nuova notifica'};e.waitUntil(self.registration.showNotification(d.title,{body:d.body,icon:'/assets/icon.svg',badge:'/assets/icon.svg'}))});
