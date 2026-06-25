/**
 * Amicci Repartos — PWA Entry Point
 */

import './pages/login.js';
import './pages/deliveries.js';
import './pages/delivery-detail.js';

import { route, startRouter, navigate } from './router.js';
import { isAuthenticated } from './auth.js';
import { initSyncListeners } from './sync.js';
import { renderLoginPage } from './pages/login.js';
import { renderDeliveriesPage } from './pages/deliveries.js';
import { renderDeliveryDetailPage } from './pages/delivery-detail.js';

// ── Auth Guard ───────────────────────────────────────────────
function requireAuth(handler) {
    return async (params) => {
        if (!isAuthenticated()) {
            navigate('/login');
            return;
        }
        await handler(params);
    };
}

// ── Register Routes ──────────────────────────────────────────
route('/login', () => {
    if (isAuthenticated()) {
        navigate('/deliveries');
        return;
    }
    renderLoginPage();
});

route('/deliveries', requireAuth(renderDeliveriesPage));
route('/deliveries/:id', requireAuth(renderDeliveryDetailPage));

// ── Initialize ───────────────────────────────────────────────
initSyncListeners();
startRouter();
