const CACHE_NAME = 'duitku-v4';
const ASSETS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/images/logo.png',
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

  const url = new URL(event.request.url);

  // Only handle http and https requests from the same origin
  if (!url.protocol.startsWith('http')) return;
  if (url.origin !== self.location.origin) return;

  // Skip dynamic API/sync endpoints, backups, and exports
  if (
    url.pathname.startsWith('/api/') ||
    url.pathname.startsWith('/backup/') ||
    url.pathname.startsWith('/export/') ||
    url.pathname.includes('/belanja_assets/') ||
    url.pathname.includes('/belanja/sync')
  ) {
    return;
  }

  // Network-First Strategy for app shell and assets
  event.respondWith(
    fetch(event.request)
      .then(fetchRes => {
        if (fetchRes && fetchRes.status === 200 && fetchRes.type === 'basic') {
          const resClone = fetchRes.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, resClone).catch(() => {});
          });
        }
        return fetchRes;
      })
      .catch(() => {
        return caches.match(event.request).then(match => {
          return match || Response.error();
        });
      })
  );
});
