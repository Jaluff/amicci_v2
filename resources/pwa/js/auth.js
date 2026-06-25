/**
 * Auth module — manages login/logout and token storage.
 */

import { apiPost } from './api.js';
import { clearAllData } from './store.js';

/**
 * Check if user is authenticated (has a stored token).
 */
export function isAuthenticated() {
    return !!localStorage.getItem('auth_token');
}

/**
 * Get stored user data.
 */
export function getUser() {
    const data = localStorage.getItem('auth_user');
    return data ? JSON.parse(data) : null;
}

/**
 * Attempt login with email/password.
 * @returns {{ success: boolean, error?: string }}
 */
export async function login(email, password) {
    try {
        const { ok, data } = await apiPost('/login', { email, password });

        if (!ok) {
            return {
                success: false,
                error: data?.message || 'Error de autenticación.',
            };
        }

        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));

        return { success: true };
    } catch (err) {
        return {
            success: false,
            error: 'Error de conexión. Verifica tu red.',
        };
    }
}

/**
 * Logout — revoke token and clear local data.
 */
export async function logout() {
    try {
        await apiPost('/logout');
    } catch {
        // Even if the API call fails, clear local data
    }

    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    await clearAllData();
}
