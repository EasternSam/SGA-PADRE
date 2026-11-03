// SGA Academic+ — Service Worker
// Estrategia: Network-Only para rutas dinámicas/Inertia y builds de Vite; fallback offline seguro

const CACHE_NAME = 'sga-v5-20261103';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    '/offline.html',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

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

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    // Nunca interceptar peticiones Inertia, panel administrativo, kiosko, APIs ni assets compilados de Vite
    if (
        request.headers.get('X-Inertia') ||
        request.url.includes('/build/') ||
        request.url.includes('/admin/') ||
        request.url.includes('/kiosk/') ||
        request.url.includes('/livewire/') ||
        request.url.includes('/api/') ||
        request.url.includes('cardnet.com.do') ||
        request.url.includes('PWCheckout') ||
        request.url.includes('/cardnet/')
    ) {
        return;
    }

    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});
