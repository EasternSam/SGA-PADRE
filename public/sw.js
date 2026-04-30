// SGA Academic+ — Service Worker
// Estrategia: Network-First con cache fallback para assets estáticos

const CACHE_NAME = 'sga-v2';
const OFFLINE_URL = '/offline.html';

// Assets que siempre se cachean (app shell)
const PRECACHE_ASSETS = [
    '/centuu.png',
    '/offline.html',
];

// Instalar — Pre-cachear app shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activar — Limpiar caches viejos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch — Network first, cache fallback
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Solo cachear GET requests
    if (request.method !== 'GET') return;

    // No interceptar Livewire, API, ni Cardnet
    if (request.url.includes('/livewire/') || request.url.includes('/api/') ||
        request.url.includes('cardnet.com.do') || request.url.includes('PWCheckout') ||
        request.url.includes('/cardnet/')) return;

    // Assets estáticos: Cache first
    if (request.url.match(/\.(css|js|woff2?|ttf|png|jpg|jpeg|svg|ico|webp)$/)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Páginas HTML: Network first, offline fallback
    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cachear la página para offline
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => {
                    // Sin red → intentar cache, luego offline page
                    return caches.match(request).then((cached) => {
                        return cached || caches.match(OFFLINE_URL);
                    });
                })
        );
    }
});
