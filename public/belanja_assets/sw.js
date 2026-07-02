const CACHE_NAME = 'belanja-v21';
const ASSETS = [
    '/belanja_assets/css/style.css',
    '/belanja_assets/js/app.js',
    '/belanja_assets/manifest.json',
    '/belanja_assets/logo.png',
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
    'https://unpkg.com/lucide@latest'
];

// Install Event
self.addEventListener('install', event => {
    console.log('SW Stage: Installing v21');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching assets');
                return cache.addAll(ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate Event
self.addEventListener('activate', event => {
    console.log('SW Stage: Activating v21');
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    // Kurs API: selalu ambil dari jaringan
    if (event.request.url.indexOf('open.er-api.com') > -1) {
        event.respondWith(fetch(event.request));
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(cacheRes => {
                return cacheRes || fetch(event.request).then(fetchRes => {
                    if (event.request.url.startsWith('http')) {
                        return caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request.url, fetchRes.clone());
                            return fetchRes;
                        });
                    }
                    return fetchRes;
                });
            }).catch(() => {
                // If offline and request is HTML/route, return the main belanja page from cache
                if (event.request.headers && event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
                    return caches.match('/belanja').then(match => {
                        return match || Response.error();
                    });
                }
                return Response.error();
            })
    );
});
