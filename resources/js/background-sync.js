/**
 * Background Sync Module
 *
 * Handles synchronization of offline operations when connectivity
 * is restored. Implements FIFO order processing with exponential
 * backoff retry (1s, 2s, 4s).
 *
 * Requirements: NFR-PWA-05, FR-10.8
 */

import {
    getPendingOperations,
    updateOperationStatus,
    clearCompletedOperations,
} from './offline-storage.js';

const MAX_RETRIES = 5;
const BASE_DELAY_MS = 1000;

/**
 * @type {boolean}
 */
let isSyncing = false;

/**
 * @type {AbortController|null}
 */
let syncAbortController = null;

/**
 * Start the background sync process.
 * Processes pending operations in FIFO order.
 *
 * @returns {Promise<Object>} Sync result summary
 */
export async function startSync() {
    if (isSyncing) {
        return { status: 'already_syncing' };
    }

    isSyncing = true;
    syncAbortController = new AbortController();

    const results = {
        processed: 0,
        succeeded: 0,
        failed: 0,
        remaining: 0,
        errors: [],
    };

    try {
        dispatchSyncEvent('sync-started', { timestamp: Date.now() });

        const operations = await getPendingOperations();
        const total = operations.length;

        for (let i = 0; i < operations.length; i++) {
            if (syncAbortController.signal.aborted) {
                break;
            }

            const operation = operations[i];

            dispatchSyncEvent('sync-progress', {
                current: i + 1,
                total,
                operation: operation.type,
                endpoint: operation.endpoint,
            });

            const success = await processOperation(operation);
            results.processed++;

            if (success) {
                results.succeeded++;
                await updateOperationStatus(operation.id, 'completed');
            } else {
                results.failed++;
                results.errors.push({
                    id: operation.id,
                    endpoint: operation.endpoint,
                    error: operation.lastError,
                });
            }
        }

        await clearCompletedOperations();

        const remaining = await getPendingOperations();
        results.remaining = remaining.length;

        dispatchSyncEvent('sync-completed', results);
    } catch (error) {
        console.error('[BackgroundSync] Sync error:', error);
        dispatchSyncEvent('sync-error', { error: error.message });
    } finally {
        isSyncing = false;
        syncAbortController = null;
    }

    return results;
}

/**
 * Process a single operation with retry logic.
 * Uses exponential backoff: 1s, 2s, 4s, 8s, 16s.
 *
 * @param {Object} operation
 * @returns {Promise<boolean>}
 */
async function processOperation(operation) {
    const retryCount = operation.retryCount || 0;

    if (retryCount >= MAX_RETRIES) {
        await updateOperationStatus(operation.id, 'failed', 'Max retries exceeded');
        return false;
    }

    try {
        await updateOperationStatus(operation.id, 'syncing');

        const response = await fetch(operation.endpoint, {
            method: operation.method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
            },
            body: operation.method !== 'DELETE' ? JSON.stringify(operation.data) : undefined,
            signal: syncAbortController?.signal,
        });

        if (response.ok) {
            return true;
        }

        if (response.status >= 400 && response.status < 500) {
            const errorText = await response.text();
            await updateOperationStatus(operation.id, 'failed', `Client error: ${response.status} - ${errorText}`);
            return false;
        }

        const delay = getBackoffDelay(retryCount);
        console.warn(`[BackgroundSync] Server error ${response.status}, retrying in ${delay}ms`);
        await updateOperationStatus(operation.id, 'pending', `Server error: ${response.status}`);
        await sleep(delay);

        return processOperation({ ...operation, retryCount: retryCount + 1 });
    } catch (error) {
        if (error.name === 'AbortError') {
            await updateOperationStatus(operation.id, 'pending', 'Sync aborted');
            return false;
        }

        const delay = getBackoffDelay(retryCount);
        console.warn(`[BackgroundSync] Network error, retrying in ${delay}ms:`, error.message);
        await updateOperationStatus(operation.id, 'pending', error.message);
        await sleep(delay);

        return processOperation({ ...operation, retryCount: retryCount + 1 });
    }
}

/**
 * Stop the current sync process.
 */
export function stopSync() {
    if (syncAbortController) {
        syncAbortController.abort();
    }
}

/**
 * Check if sync is currently in progress.
 *
 * @returns {boolean}
 */
export function isSyncInProgress() {
    return isSyncing;
}

/**
 * Calculate exponential backoff delay.
 *
 * @param {number} retryCount
 * @returns {number} Delay in milliseconds
 */
function getBackoffDelay(retryCount) {
    return BASE_DELAY_MS * Math.pow(2, retryCount);
}

/**
 * Sleep for a given number of milliseconds.
 *
 * @param {number} ms
 * @returns {Promise<void>}
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Get CSRF token from meta tag.
 *
 * @returns {string}
 */
function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Dispatch a custom sync event for UI updates.
 *
 * @param {string} eventName
 * @param {Object} detail
 */
function dispatchSyncEvent(eventName, detail) {
    window.dispatchEvent(new CustomEvent(eventName, { detail }));
}

/**
 * Register the online event listener for automatic sync.
 */
export function registerAutoSync() {
    window.addEventListener('online', () => {
        console.log('[BackgroundSync] Connection restored, starting sync...');
        startSync();
    });
}

/**
 * Request background sync through service worker.
 *
 * @returns {Promise<boolean>}
 */
export async function requestBackgroundSync() {
    if (!('serviceWorker' in navigator)) {
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.ready;

        if ('sync' in registration) {
            await registration.sync.register('sync-data');
            return true;
        }

        return false;
    } catch (error) {
        console.error('[BackgroundSync] Background sync registration failed:', error);
        return false;
    }
}

export default {
    startSync,
    stopSync,
    isSyncInProgress,
    registerAutoSync,
    requestBackgroundSync,
};
