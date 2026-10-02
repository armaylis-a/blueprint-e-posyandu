/* eslint-env serviceworker */

const CACHE_NAME = 'eposyandu-v1';
const OFFLINE_URL = './offline.html';
const PRECACHE_URLS = [
    OFFLINE_URL,
    './modules/gizi/index.html',
    './assets/css/style.css',
    './assets/js/app.js',
    './assets/images/icon-192.png',
    './assets/images/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return PRECACHE_URLS.some((path) => {
        const assetUrl = new URL(path, self.location.href);
        return url.pathname === assetUrl.pathname;
    });
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Jangan pernah menyimpan halaman PHP, API, atau data terautentikasi.
    if (url.pathname.endsWith('.php') || url.pathname.includes('/api/')) {
        return;
    }

    if (request.mode === 'navigate' && url.pathname.endsWith('/modules/gizi/index.html')) {
        event.respondWith(
            caches.match(request, { ignoreSearch: true }).then((cached) => {
                return cached || fetch(request);
            })
        );
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request, { ignoreSearch: true }).then((cached) => {
                return cached || fetch(request).then((response) => {
                    if (response && response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                });
            })
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => response)
                .catch(() => caches.match(request, { ignoreSearch: true })
                    .then((cached) => cached || caches.match(OFFLINE_URL)))
        );
    }
});