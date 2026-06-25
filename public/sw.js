const CACHE_NAME = 'amicci-cache-v3';

// ── PWA Shell — precache for offline ─────────────────────────
const PWA_CACHE = 'amicci-pwa-v1';
const PWA_PRECACHE_URLS = [
    '/pwa',
    '/manifest.json',
    '/pwa-icons/icon-192.png',
    '/pwa-icons/icon-512.png',
];

// Patrones de URL que NUNCA deben cachearse (datos dinámicos / AJAX)
const NEVER_CACHE = [
    '/datatable',
    '/available-shipments',
    '/available-routes',
    '/available-shipments',
    '/ajax-search',
    '/ajax-store',
    '/stats',
    '/datatable',
];

// Solo cachear estos tipos de assets estáticos
const CACHEABLE_EXTENSIONS = ['.js', '.css', '.woff', '.woff2', '.ttf', '.ico', '.png', '.jpg', '.svg', '.webp'];

function isStaticAsset(url) {
    return CACHEABLE_EXTENSIONS.some(ext => url.pathname.endsWith(ext));
}

function isCacheable(request) {
    const url = new URL(request.url);

    // Solo http y https — rechazar chrome-extension://, data:, etc.
    if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        return false;
    }

    // Nunca cachear endpoints de datos dinámicos
    if (NEVER_CACHE.some(pattern => url.pathname.includes(pattern))) {
        return false;
    }

    // Nunca cachear requests con query strings (AJAX DataTables, etc.)
    if (url.search !== '') {
        return false;
    }

    // Solo cachear assets estáticos
    return isStaticAsset(url);
}

// ── Install ──────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(PWA_CACHE).then(cache => {
            return cache.addAll(PWA_PRECACHE_URLS).catch(err => {
                // Non-fatal: PWA icons might not exist yet in dev
                console.warn('[SW] Precache parcial:', err);
            });
        })
    );
    self.skipWaiting();
});

// ── Activate ─────────────────────────────────────────────────
self.addEventListener('activate', event => {
    const VALID_CACHES = [CACHE_NAME, PWA_CACHE];
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => !VALID_CACHES.includes(key))
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch ────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const request = event.request;

    // Solo interceptar GET
    if (request.method !== 'GET') {
        return;
    }

    // Ignorar schemes no soportados (chrome-extension, data, blob, etc.)
    try {
        const url = new URL(request.url);
        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return;
        }

        // ── PWA API requests: Network First con cache fallback ──
        if (url.pathname.startsWith('/api/')) {
            event.respondWith(networkFirst(request, PWA_CACHE));
            return;
        }

        // ── PWA navigation: Stale While Revalidate ──────────────
        if (url.pathname.startsWith('/pwa')) {
            event.respondWith(staleWhileRevalidate(request, PWA_CACHE));
            return;
        }
    } catch {
        return;
    }

    // ── Web app: comportamiento original (solo assets estáticos) ──
    if (!isCacheable(request)) {
        return;
    }

    // Estrategia Cache First para assets estáticos (JS/CSS/fuentes)
    event.respondWith(
        caches.match(request).then(cached => {
            if (cached) {
                return cached;
            }
            return fetch(request).then(response => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                }
                return response;
            });
        })
    );
});

// ── Cache Strategies ─────────────────────────────────────────

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached || new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' },
        });
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}
