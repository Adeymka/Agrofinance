/**
 * AgroFinance+ Service Worker
 *
 * Stratégie :
 *  - Shell de l'app (CSS, JS, polices) → Cache First (mis en cache à l'install)
 *  - Pages HTML           → Network First avec fallback sur /offline
 *  - API (/api/*)         → Network Only (pas de cache, données sensibles)
 *  - Images statiques     → Stale-While-Revalidate
 */

const CACHE_VERSION = 'v1';
const SHELL_CACHE   = `agro-shell-${CACHE_VERSION}`;
const IMAGE_CACHE   = `agro-images-${CACHE_VERSION}`;
const PAGE_CACHE    = `agro-pages-${CACHE_VERSION}`;

// Chemin de base de l'application (sous-dossier XAMPP)
const BASE = '/agrofinanceplus/public';

// Ressources pré-cachées lors de l'installation (shell de l'app)
const SHELL_ASSETS = [
    BASE + '/',
    BASE + '/offline',
    BASE + '/manifest.json',
];

// ── Install : pré-cache du shell ──────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => {
            return cache.addAll(SHELL_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ── Activate : suppression des anciens caches ─────────────────────────────────
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

// ── Fetch : stratégies par type de ressource ──────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorer les requêtes non-GET et les extensions non-HTTP (chrome-extension, etc.)
    if (request.method !== 'GET') return;
    if (!url.protocol.startsWith('http')) return;

    // API → Network Only, jamais de cache
    if (url.pathname.startsWith(BASE + '/api/') || url.pathname.startsWith('/api/')) {
        return; // laisse passer sans interception
    }

    // Polices Google → Cache First
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(cacheFirst(request, SHELL_CACHE));
        return;
    }

    // Assets compilés Vite (CSS, JS) → Cache First
    if (url.pathname.startsWith(BASE + '/build/') || url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, SHELL_CACHE));
        return;
    }

    // Images statiques → Stale-While-Revalidate
    if (/\.(png|jpg|jpeg|svg|gif|webp|ico)$/i.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE));
        return;
    }

    // Pages HTML → Network First + fallback offline
    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithFallback(request));
        return;
    }
});

// ── Helpers ───────────────────────────────────────────────────────────────────

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
    }).catch(() => cached); // réseau mort → renvoie le cache

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

        // Fallback ultime : page offline
        return caches.match(BASE + '/offline') ?? new Response(
            '<h1 style="font-family:sans-serif;text-align:center;margin-top:20vh;color:#16a34a">Hors ligne — AgroFinance+</h1>',
            { headers: { 'Content-Type': 'text/html' } }
        );
    }
}

// ── Push notifications (placeholder pour plus tard) ───────────────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title ?? 'AgroFinance+', {
            body: data.body ?? '',
            icon: '/icons/icon-192x192.png',
            badge: '/icons/icon-72x72.png',
        })
    );
});
