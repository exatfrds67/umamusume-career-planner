/**
 * Connectivity Monitor - Alpine.js Component
 *
 * Monitors network connectivity and displays offline mode indicators.
 * Automatically checks connectivity status and updates UI accordingly.
 *
 * Features:
 * - Real-time connectivity monitoring
 * - Offline mode banner
 * - Toast notifications for status changes
 * - Automatic retry on reconnection
 * - Cache status display
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.1
 */

export default function connectivityMonitor() {
    return {
        // State
        isOnline: true,
        isChecking: false,
        offlineSince: null,
        lastCheck: null,
        consecutiveFailures: 0,
        cacheStats: null,
        showBanner: false,
        showToast: false,
        toastMessage: "",
        toastType: "info", // 'info', 'warning', 'error', 'success'
        checkInterval: null,

        // Configuration
        checkIntervalMs: 30000, // 30 seconds
        toastDuration: 5000, // 5 seconds

        /**
         * Initialize the connectivity monitor
         */
        init() {
            console.log("[ConnectivityMonitor] Initializing...");

            // Check initial status
            this.checkConnectivity();

            // Set up periodic checks
            this.startPeriodicChecks();

            // Listen for online/offline events
            window.addEventListener("online", () => this.handleOnlineEvent());
            window.addEventListener("offline", () => this.handleOfflineEvent());

            // Listen for visibility change to check when tab becomes visible
            document.addEventListener("visibilitychange", () => {
                if (!document.hidden) {
                    this.checkConnectivity();
                }
            });

            console.log("[ConnectivityMonitor] Initialized");
        },

        /**
         * Check connectivity status
         */
        async checkConnectivity() {
            if (this.isChecking) {
                return;
            }

            this.isChecking = true;

            try {
                const response = await fetch("/api/connectivity/status", {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    cache: "no-cache",
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.updateStatus(data);
            } catch (error) {
                console.error("[ConnectivityMonitor] Check failed:", error);

                // Assume offline if check fails
                this.updateStatus({
                    is_online: false,
                    last_check: new Date().toISOString(),
                    offline_since:
                        this.offlineSince || new Date().toISOString(),
                    consecutive_failures: this.consecutiveFailures + 1,
                });
            } finally {
                this.isChecking = false;
            }
        },

        /**
         * Update connectivity status
         */
        updateStatus(data) {
            const wasOnline = this.isOnline;
            const wasOffline = !this.isOnline;

            this.isOnline = data.is_online;
            this.offlineSince = data.offline_since;
            this.lastCheck = data.last_check;
            this.consecutiveFailures = data.consecutive_failures || 0;
            this.cacheStats = data.cache_statistics || null;

            // Show/hide banner based on status
            this.showBanner = !this.isOnline;

            // Show toast notification on status change
            if (wasOnline && !this.isOnline) {
                this.showToastNotification(
                    "You are now offline. Using cached data.",
                    "warning",
                );
            } else if (wasOffline && this.isOnline) {
                this.showToastNotification(
                    "Connection restored! You are back online.",
                    "success",
                );
            }

            // Dispatch custom event for other components
            window.dispatchEvent(
                new CustomEvent("connectivity-change", {
                    detail: {
                        isOnline: this.isOnline,
                        offlineSince: this.offlineSince,
                        consecutiveFailures: this.consecutiveFailures,
                    },
                }),
            );

            console.log("[ConnectivityMonitor] Status updated:", {
                isOnline: this.isOnline,
                offlineSince: this.offlineSince,
                consecutiveFailures: this.consecutiveFailures,
            });
        },

        /**
         * Handle browser online event
         */
        handleOnlineEvent() {
            console.log("[ConnectivityMonitor] Browser online event detected");
            this.checkConnectivity();
        },

        /**
         * Handle browser offline event
         */
        handleOfflineEvent() {
            console.log("[ConnectivityMonitor] Browser offline event detected");

            this.updateStatus({
                is_online: false,
                last_check: new Date().toISOString(),
                offline_since: this.offlineSince || new Date().toISOString(),
                consecutive_failures: this.consecutiveFailures + 1,
            });
        },

        /**
         * Start periodic connectivity checks
         */
        startPeriodicChecks() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }

            this.checkInterval = setInterval(() => {
                this.checkConnectivity();
            }, this.checkIntervalMs);

            console.log("[ConnectivityMonitor] Periodic checks started");
        },

        /**
         * Stop periodic connectivity checks
         */
        stopPeriodicChecks() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
                this.checkInterval = null;
            }

            console.log("[ConnectivityMonitor] Periodic checks stopped");
        },

        /**
         * Force connectivity check
         */
        async forceCheck() {
            console.log("[ConnectivityMonitor] Force check requested");

            try {
                const response = await fetch("/api/connectivity/check", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    cache: "no-cache",
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.updateStatus(data);

                this.showToastNotification(
                    "Connectivity check completed",
                    "info",
                );
            } catch (error) {
                console.error(
                    "[ConnectivityMonitor] Force check failed:",
                    error,
                );

                this.showToastNotification(
                    "Failed to check connectivity",
                    "error",
                );
            }
        },

        /**
         * Show toast notification
         */
        showToastNotification(message, type = "info") {
            this.toastMessage = message;
            this.toastType = type;
            this.showToast = true;

            // Auto-hide after duration
            setTimeout(() => {
                this.showToast = false;
            }, this.toastDuration);
        },

        /**
         * Close toast notification
         */
        closeToast() {
            this.showToast = false;
        },

        /**
         * Close offline banner
         */
        closeBanner() {
            this.showBanner = false;
        },

        /**
         * Get offline duration in human-readable format
         */
        getOfflineDuration() {
            if (!this.offlineSince) {
                return null;
            }

            const offlineDate = new Date(this.offlineSince);
            const now = new Date();
            const diffMs = now - offlineDate;
            const diffMinutes = Math.floor(diffMs / 60000);

            if (diffMinutes < 1) {
                return "just now";
            } else if (diffMinutes < 60) {
                return `${diffMinutes} minute${diffMinutes > 1 ? "s" : ""} ago`;
            } else {
                const diffHours = Math.floor(diffMinutes / 60);
                return `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
            }
        },

        /**
         * Get cache hit rate percentage
         */
        getCacheHitRate() {
            if (!this.cacheStats) {
                return 0;
            }

            return this.cacheStats.hit_rate || 0;
        },

        /**
         * Check if cache hit rate is low
         */
        isLowCacheHitRate() {
            return this.getCacheHitRate() < 50;
        },

        /**
         * Cleanup on destroy
         */
        destroy() {
            this.stopPeriodicChecks();

            window.removeEventListener("online", this.handleOnlineEvent);
            window.removeEventListener("offline", this.handleOfflineEvent);

            console.log("[ConnectivityMonitor] Destroyed");
        },
    };
}
