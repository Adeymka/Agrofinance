/**
 * AgroFinance+ Service Worker (BASE = préfixe APP_URL, généré côté serveur)
 */
const CACHE_VERSION = 'v1';
const SHELL_CACHE   = `agro-shell-${CACHE_VERSION}`;
const IMAGE_CACHE   = `agro-images-${CACHE_VERSION}`;
const PAGE_CACHE    = `agro-pages-${CACHE_VERSION}`;

const BASE = {!! json_encode($base) !!};
const manifestPath = {!! json_encode($manifestPath) !!};
const offlinePath = {!! json_encode($offlinePath) !!};

const SHELL_ASSETS = [(BASE || '') + '/', offlinePath, manifestPath];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => {
            return cache.addAll(SHELL_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    const allowedCaches = [SHELL_CACHE, IMAGE_CACHE, PAGE_CACHE];
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => !allowedCaches.includes(key))
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;
    if (!url.protocol.startsWith('http')) return;

    const apiPrefix = (BASE || '') + '/api/';
    if (url.pathname.startsWith(apiPrefix) || url.pathname.startsWith('/api/')) {
        return;
    }

    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(cacheFirst(request, SHELL_CACHE));
        return;
    }

    const buildPrefix = (BASE || '') + '/build/';
    if (url.pathname.startsWith(buildPrefix) || url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, SHELL_CACHE));
        return;
    }

    if (/\.(png|jpg|jpeg|svg|gif|webp|ico)$/i.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE));
        return;
    }

    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithFallback(request));
        return;
    }
});

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }
    return response;
}

async function staleWhileRevalidate(request, cacheName) {
    const cache   = await caches.open(cacheName);
    const cached  = await cache.match(request);

    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}

async function networkFirstWithFallback(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(PAGE_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        return caches.match(offlinePath) ?? new Response(
            '<h1 style="font-family:sans-serif;text-align:center;margin-top:20vh;color:#16a34a">Hors ligne — AgroFinance+</h1>',
            { headers: { 'Content-Type': 'text/html' } }
        );
    }
}

self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title ?? 'AgroFinance+', {
            body: data.body ?? '',
            icon: (BASE || '') + '/icons/icon-192x192.png',
            badge: (BASE || '') + '/icons/icon-72x72.png',
        })
    );
});
