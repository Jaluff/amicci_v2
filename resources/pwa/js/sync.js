/**
 * Sync module — handles offline queue synchronization.
 */

import { apiPost } from './api.js';
import { getSyncQueue, removeFromSyncQueue } from './store.js';
import { showToast } from './ui.js';

let isSyncing = false;

/**
 * Process all pending items in the sync queue.
 * Called when the app comes back online.
 */
export async function processSyncQueue() {
    if (isSyncing) return;

    const queue = await getSyncQueue();
    if (queue.length === 0) return;

    isSyncing = true;
    updateSyncBadge(queue.length);

    let successCount = 0;
    let failCount = 0;

    for (const item of queue) {
        try {
            const { ok } = await apiPost(item.endpoint, item.body);

            if (ok) {
                await removeFromSyncQueue(item.id);
                successCount++;
            } else {
                failCount++;
            }
        } catch {
            // Network still failing, stop processing
            failCount++;
            break;
        }
    }

    isSyncing = false;

    if (successCount > 0) {
        showToast(`✅ ${successCount} operación(es) sincronizada(s)`, 'success');
    }

    if (failCount > 0) {
        showToast(`⚠️ ${failCount} operación(es) pendiente(s)`, 'error');
    }

    // Update badge with remaining items
    const remaining = await getSyncQueue();
    updateSyncBadge(remaining.length);

    return { successCount, failCount };
}

/**
 * Update the offline queue badge in the UI.
 */
export function updateSyncBadge(count) {
    const badge = document.getElementById('offline-queue-badge');
    if (!badge) return;

    if (count > 0) {
        badge.textContent = `⏳ ${count} pendiente(s)`;
        badge.classList.add('visible');
    } else {
        badge.classList.remove('visible');
    }
}

/**
 * Update the connection status badge.
 */
export function updateConnectionStatus() {
    const badge = document.getElementById('sync-status');
    if (!badge) return;

    if (navigator.onLine) {
        badge.className = 'sync-badge online';
        badge.innerHTML = '<span class="dot"></span> Online';
    } else {
        badge.className = 'sync-badge offline';
        badge.innerHTML = '<span class="dot"></span> Offline';
    }
}

/**
 * Initialize online/offline event listeners.
 */
export function initSyncListeners() {
    window.addEventListener('online', async () => {
        updateConnectionStatus();
        showToast('🟢 Conexión restaurada. Sincronizando...', 'info');
        await processSyncQueue();
    });

    window.addEventListener('offline', () => {
        updateConnectionStatus();
        showToast('🔴 Sin conexión. Los cambios se guardarán offline.', 'error');
    });

    // Initial status
    updateConnectionStatus();

    // Check queue on startup
    getSyncQueue().then((queue) => {
        updateSyncBadge(queue.length);
        if (queue.length > 0 && navigator.onLine) {
            processSyncQueue();
        }
    });
}
