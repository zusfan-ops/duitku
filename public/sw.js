const CACHE_NAME = 'duitku-v3';
const ASSETS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/images/logo.svg',
  '/images/icon.svg',
  '/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  // Skip caching for belanja assets — biar versioning ?v= yang handle
  // Skip caching for API/sync endpoints
  const url = event.request.url;
  if (url.includes('/belanja_assets/') || url.includes('/belanja/sync')) {
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request).then(m => m || Response.error())));
    return;
  }

  // Network-First Strategy for dynamic content
  event.respondWith(
    fetch(event.request)
      .then(fetchRes => {
        return caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request.url, fetchRes.clone());
          return fetchRes;
        });
      })
      .catch(() => {
        return caches.match(event.request).then(match => {
            return match || Response.error();
        });
      })
  );
});
