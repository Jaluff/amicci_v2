/**
 * Simple hash-based router for the PWA SPA.
 */

const routes = {};
let currentRoute = null;

/**
 * Register a route handler.
 * @param {string} pattern - Route pattern (e.g., '/deliveries', '/deliveries/:id')
 * @param {function} handler - Function that receives (params) and returns HTML or renders to #app
 */
export function route(pattern, handler) {
    routes[pattern] = handler;
}

/**
 * Navigate to a route.
 */
export function navigate(path) {
    window.location.hash = '#' + path;
}

/**
 * Match a URL path against registered routes.
 */
function matchRoute(path) {
    // Try exact match first
    if (routes[path]) {
        return { handler: routes[path], params: {} };
    }

    // Try pattern matching (e.g., /deliveries/:id)
    for (const pattern in routes) {
        const patternParts = pattern.split('/');
        const pathParts = path.split('/');

        if (patternParts.length !== pathParts.length) continue;

        const params = {};
        let match = true;

        for (let i = 0; i < patternParts.length; i++) {
            if (patternParts[i].startsWith(':')) {
                params[patternParts[i].slice(1)] = pathParts[i];
            } else if (patternParts[i] !== pathParts[i]) {
                match = false;
                break;
            }
        }

        if (match) {
            return { handler: routes[pattern], params };
        }
    }

    return null;
}

/**
 * Handle the current hash route.
 */
async function handleRoute() {
    const hash = window.location.hash.slice(1) || '/login';
    const result = matchRoute(hash);

    if (result) {
        currentRoute = hash;
        await result.handler(result.params);
    } else {
        // Fallback to login
        navigate('/login');
    }
}

/**
 * Get the current route path.
 */
export function getCurrentRoute() {
    return currentRoute;
}

/**
 * Start listening for route changes.
 */
export function startRouter() {
    window.addEventListener('hashchange', handleRoute);
    handleRoute();
}
