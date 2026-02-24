/**
 * Offline Storage Module (IndexedDB)
 *
 * Provides persistent offline storage for career runs, pending operations,
 * and cached data using IndexedDB.
 *
 * Stores:
 * - pending_operations: Queued changes made while offline (FIFO)
 * - cached_data: Cached API responses and model data
 * - local_runs: Career runs stored locally (UUID-based)
 *
 * Requirements: NFR-PWA-03, FR-10.4
 */

const DB_NAME = 'umamusume-career-planner';
const DB_VERSION = 1;

/**
 * @type {IDBDatabase|null}
 */
let db = null;

/**
 * Open or create the IndexedDB database.
 *
 * @returns {Promise<IDBDatabase>}
 */
export function openDatabase() {
    if (db) {
        return Promise.resolve(db);
    }

    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const database = event.target.result;

            if (!database.objectStoreNames.contains('pending_operations')) {
                const opStore = database.createObjectStore('pending_operations', {
                    keyPath: 'id',
                    autoIncrement: true,
                });
                opStore.createIndex('timestamp', 'timestamp', { unique: false });
                opStore.createIndex('type', 'type', { unique: false });
                opStore.createIndex('status', 'status', { unique: false });
            }

            if (!database.objectStoreNames.contains('cached_data')) {
                const cacheStore = database.createObjectStore('cached_data', {
                    keyPath: 'key',
                });
                cacheStore.createIndex('expires_at', 'expires_at', { unique: false });
                cacheStore.createIndex('entity_type', 'entity_type', { unique: false });
            }

            if (!database.objectStoreNames.contains('local_runs')) {
                const runStore = database.createObjectStore('local_runs', {
                    keyPath: 'uuid',
                });
                runStore.createIndex('updated_at', 'updated_at', { unique: false });
                runStore.createIndex('character_name', 'character_name', { unique: false });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = (event) => {
            console.error('[OfflineStorage] Database open error:', event.target.error);
            reject(event.target.error);
        };
    });
}

/**
 * Queue a pending operation for offline sync.
 *
 * @param {Object} operation
 * @param {string} operation.type - Operation type (create|update|delete)
 * @param {string} operation.endpoint - API endpoint
 * @param {string} operation.method - HTTP method (POST|PUT|PATCH|DELETE)
 * @param {Object} operation.data - Request payload
 * @param {string} [operation.entityId] - Optional entity ID
 * @returns {Promise<number>} The generated operation ID
 */
export async function queueOperation(operation) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('pending_operations', 'readwrite');
        const store = tx.objectStore('pending_operations');

        const record = {
            type: operation.type,
            endpoint: operation.endpoint,
            method: operation.method,
            data: operation.data || {},
            entityId: operation.entityId || null,
            status: 'pending',
            timestamp: Date.now(),
            retryCount: 0,
            lastError: null,
        };

        const request = store.add(record);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

/**
 * Get all pending operations in FIFO order.
 *
 * @returns {Promise<Array>}
 */
export async function getPendingOperations() {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('pending_operations', 'readonly');
        const store = tx.objectStore('pending_operations');
        const index = store.index('timestamp');
        const request = index.getAll();

        request.onsuccess = () => {
            const operations = request.result.filter(op => op.status === 'pending');
            resolve(operations);
        };
        request.onerror = () => reject(request.error);
    });
}

/**
 * Update the status of a pending operation.
 *
 * @param {number} id - Operation ID
 * @param {string} status - New status (pending|syncing|completed|failed)
 * @param {string|null} [error] - Error message if failed
 * @returns {Promise<void>}
 */
export async function updateOperationStatus(id, status, error = null) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('pending_operations', 'readwrite');
        const store = tx.objectStore('pending_operations');
        const getRequest = store.get(id);

        getRequest.onsuccess = () => {
            const record = getRequest.result;
            if (!record) {
                resolve();
                return;
            }

            record.status = status;
            if (error) {
                record.lastError = error;
                record.retryCount = (record.retryCount || 0) + 1;
            }

            const putRequest = store.put(record);
            putRequest.onsuccess = () => resolve();
            putRequest.onerror = () => reject(putRequest.error);
        };

        getRequest.onerror = () => reject(getRequest.error);
    });
}

/**
 * Remove completed operations from the store.
 *
 * @returns {Promise<number>} Number of operations removed
 */
export async function clearCompletedOperations() {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('pending_operations', 'readwrite');
        const store = tx.objectStore('pending_operations');
        const request = store.getAll();

        request.onsuccess = () => {
            const completed = request.result.filter(op => op.status === 'completed');
            let removed = 0;

            completed.forEach(op => {
                store.delete(op.id);
                removed++;
            });

            tx.oncomplete = () => resolve(removed);
        };

        request.onerror = () => reject(request.error);
    });
}

/**
 * Cache data with an expiration time.
 *
 * @param {string} key - Cache key
 * @param {*} data - Data to cache
 * @param {string} entityType - Entity type for indexing (character|career|skill|race)
 * @param {number} [ttlSeconds=3600] - Time-to-live in seconds
 * @returns {Promise<void>}
 */
export async function cacheData(key, data, entityType, ttlSeconds = 3600) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('cached_data', 'readwrite');
        const store = tx.objectStore('cached_data');

        const record = {
            key,
            data,
            entity_type: entityType,
            cached_at: Date.now(),
            expires_at: Date.now() + (ttlSeconds * 1000),
        };

        const request = store.put(record);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Get cached data by key. Returns null if expired or missing.
 *
 * @param {string} key - Cache key
 * @returns {Promise<*|null>}
 */
export async function getCachedData(key) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('cached_data', 'readonly');
        const store = tx.objectStore('cached_data');
        const request = store.get(key);

        request.onsuccess = () => {
            const record = request.result;

            if (!record) {
                resolve(null);
                return;
            }

            if (record.expires_at < Date.now()) {
                resolve(null);
                return;
            }

            resolve(record.data);
        };

        request.onerror = () => reject(request.error);
    });
}

/**
 * Remove expired cache entries.
 *
 * @returns {Promise<number>} Number of entries removed
 */
export async function clearExpiredCache() {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('cached_data', 'readwrite');
        const store = tx.objectStore('cached_data');
        const index = store.index('expires_at');
        const bound = IDBKeyRange.upperBound(Date.now());
        const request = index.openCursor(bound);
        let removed = 0;

        request.onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                cursor.delete();
                removed++;
                cursor.continue();
            } else {
                resolve(removed);
            }
        };

        request.onerror = () => reject(request.error);
    });
}

/**
 * Save a local career run.
 *
 * @param {Object} run - Career run data with uuid
 * @returns {Promise<void>}
 */
export async function saveLocalRun(run) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('local_runs', 'readwrite');
        const store = tx.objectStore('local_runs');

        run.updated_at = Date.now();

        const request = store.put(run);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Get all local career runs.
 *
 * @returns {Promise<Array>}
 */
export async function getLocalRuns() {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('local_runs', 'readonly');
        const store = tx.objectStore('local_runs');
        const index = store.index('updated_at');
        const request = index.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

/**
 * Delete a local career run.
 *
 * @param {string} uuid - Run UUID
 * @returns {Promise<void>}
 */
export async function deleteLocalRun(uuid) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const tx = database.transaction('local_runs', 'readwrite');
        const store = tx.objectStore('local_runs');
        const request = store.delete(uuid);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Get the count of pending operations.
 *
 * @returns {Promise<number>}
 */
export async function getPendingOperationCount() {
    const operations = await getPendingOperations();
    return operations.length;
}

/**
 * Close the database connection.
 */
export function closeDatabase() {
    if (db) {
        db.close();
        db = null;
    }
}

export default {
    openDatabase,
    queueOperation,
    getPendingOperations,
    updateOperationStatus,
    clearCompletedOperations,
    cacheData,
    getCachedData,
    clearExpiredCache,
    saveLocalRun,
    getLocalRuns,
    deleteLocalRun,
    getPendingOperationCount,
    closeDatabase,
};
