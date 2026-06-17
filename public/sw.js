const CACHE_NAME = 'amicci-cache-v2';

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

self.addEventListener('install', event => {
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    // Eliminar caches viejos al activar nueva versión del SW
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

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
    } catch {
        return;
    }

    if (!isCacheable(request)) {
        // Para requests no cacheables, ir siempre a la red sin interferir
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
