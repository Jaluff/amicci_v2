/**
 * API Client — Fetch wrapper with Sanctum token auth.
 */

const API_BASE = window.APP_CONFIG?.apiBase || '/api';

/**
 * Get the stored auth token.
 */
function getToken() {
    return localStorage.getItem('auth_token');
}

/**
 * Make an authenticated API request.
 * Returns { ok, status, data } or throws on network error.
 */
export async function apiRequest(endpoint, options = {}) {
    const token = getToken();
    const url = `${API_BASE}${endpoint}`;

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, {
        ...options,
        headers,
    });

    let data = null;
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
        data = await response.json();
    }

    return {
        ok: response.ok,
        status: response.status,
        data,
    };
}

/**
 * GET request shorthand.
 */
export function apiGet(endpoint) {
    return apiRequest(endpoint, { method: 'GET' });
}

/**
 * POST request shorthand.
 */
export function apiPost(endpoint, body = {}) {
    return apiRequest(endpoint, {
        method: 'POST',
        body: JSON.stringify(body),
    });
}
