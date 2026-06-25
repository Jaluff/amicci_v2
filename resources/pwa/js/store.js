/**
 * IndexedDB Store for offline data persistence.
 *
 * Uses two object stores:
 * - 'data': cached API responses (deliveries, shipments)
 * - 'syncQueue': pending offline operations
 */

const DB_NAME = 'amicci-repartos';
const DB_VERSION = 1;

let dbInstance = null;

function openDB() {
    if (dbInstance) return Promise.resolve(dbInstance);

    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('data')) {
                db.createObjectStore('data', { keyPath: 'key' });
            }

            if (!db.objectStoreNames.contains('syncQueue')) {
                const store = db.createObjectStore('syncQueue', {
                    keyPath: 'id',
                    autoIncrement: true,
                });
                store.createIndex('createdAt', 'createdAt');
            }
        };

        request.onsuccess = (event) => {
            dbInstance = event.target.result;
            resolve(dbInstance);
        };

        request.onerror = (event) => {
            console.error('[Store] Error opening IndexedDB:', event.target.error);
            reject(event.target.error);
        };
    });
}

/**
 * Save a value to the data store.
 */
export async function saveData(key, value) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('data', 'readwrite');
        tx.objectStore('data').put({ key, value, updatedAt: Date.now() });
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Get a value from the data store.
 */
export async function getData(key) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('data', 'readonly');
        const request = tx.objectStore('data').get(key);
        request.onsuccess = () => resolve(request.result?.value ?? null);
        request.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Add an operation to the sync queue (for offline writes).
 */
export async function addToSyncQueue(operation) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('syncQueue', 'readwrite');
        tx.objectStore('syncQueue').add({
            ...operation,
            createdAt: Date.now(),
        });
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Get all pending operations from the sync queue.
 */
export async function getSyncQueue() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('syncQueue', 'readonly');
        const request = tx.objectStore('syncQueue').getAll();
        request.onsuccess = () => resolve(request.result ?? []);
        request.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Remove a specific item from the sync queue.
 */
export async function removeFromSyncQueue(id) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('syncQueue', 'readwrite');
        tx.objectStore('syncQueue').delete(id);
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Clear the entire sync queue.
 */
export async function clearSyncQueue() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('syncQueue', 'readwrite');
        tx.objectStore('syncQueue').clear();
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Clear all cached data (used on logout).
 */
export async function clearAllData() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(['data', 'syncQueue'], 'readwrite');
        tx.objectStore('data').clear();
        tx.objectStore('syncQueue').clear();
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}
