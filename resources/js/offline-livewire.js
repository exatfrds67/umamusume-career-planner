/**
 * Offline-Aware Livewire Helper
 *
 * Provides graceful degradation for Livewire components when offline.
 * Disables server-dependent actions, shows cached data, and queues
 * operations for later sync.
 *
 * Requirements: NFR-PWA-01, NFR-PWA-06, FR-10.4
 */

import { queueOperation, getCachedData, cacheData } from './offline-storage.js';

/**
 * Alpine.js data component for offline-aware Livewire interactions
 *
 * Usage in Blade:
 *   <div x-data="offlineLivewire()">
 *     <button @click="safeAction('save', { data: 'value' })" :disabled="!canPerformAction">
 *       Save
 *     </button>
 *     <span x-show="isOffline" class="text-yellow-600">Offline - changes will sync later</span>
 *   </div>
 */
export function offlineLivewire() {
    return {
        isOnline: navigator.onLine,
        pendingCount: 0,
        lastSyncAt: null,

        init() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.precacheCriticalRoutes();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            window.addEventListener('sync-completed', (event) => {
                this.lastSyncAt = new Date().toISOString();
                this.pendingCount = 0;
            });

            window.addEventListener('sync-error', (event) => {
                console.warn('[OfflineLivewire] Sync error:', event.detail);
            });
        },

        get canPerformAction() {
            return this.isOnline;
        },

        get canPerformReadAction() {
            return true;
        },

        /**
         * Attempt a Livewire action; queue it offline if unavailable
         */
        async safeAction(actionName, data = {}, entityType = 'career', entityId = null) {
            if (this.isOnline) {
                return true;
            }

            await queueOperation({
                type: actionName,
                entity_type: entityType,
                entity_id: entityId,
                data: data,
                timestamp: Date.now(),
            });

            this.pendingCount++;
            this.$dispatch('offline-action-queued', { action: actionName, count: this.pendingCount });
            return false;
        },

        /**
         * Get data with offline cache fallback
         */
        async getCachedOrFetch(cacheKey, entityType, fetchFn) {
            if (this.isOnline) {
                try {
                    const data = await fetchFn();
                    await cacheData(cacheKey, data, entityType, 3600);
                    return data;
                } catch {
                    const cached = await getCachedData(cacheKey);
                    return cached ? cached.data : null;
                }
            }

            const cached = await getCachedData(cacheKey);
            return cached ? cached.data : null;
        },

        /**
         * Precache critical routes after coming back online
         */
        precacheCriticalRoutes() {
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({
                    type: 'PRECACHE_ROUTES',
                    payload: {
                        routes: ['/dashboard', '/characters', '/about'],
                    },
                });
            }
        },
    };
}

/**
 * Register the Alpine component globally
 */
export function registerOfflineLivewire(Alpine) {
    Alpine.data('offlineLivewire', offlineLivewire);
}
