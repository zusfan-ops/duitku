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

// Push notification handler (for future server-push support)
self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : {};
    event.waitUntil(
        self.registration.showNotification(data.title || 'DuitKu', {
            body:  data.body  || '',
            icon:  data.icon  || '/images/logo.png',
            badge: data.badge || '/images/logo.png',
            data:  data.url   || '/',
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(list => {
            for (const c of list) {
                if (c.url.includes(self.registration.scope) && 'focus' in c) return c.focus();
            }
            return clients.openWindow(event.notification.data || '/');
        })
    );
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
