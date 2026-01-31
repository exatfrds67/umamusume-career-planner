/**
 * Performance Dashboard (APM) Page Script
 * Handles real-time monitoring, polling, and health score updates
 */

// Access data from window.apmDashboardData (injected by Blade)
const { lastUpdated } = window.apmDashboardData || {};

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("apmDashboard", () => ({
        // State
        isPolling: false,
        pollInterval: 10000, // 10 seconds default
        pollTimer: null,
        isLoading: false,
        lastUpdated: lastUpdated || new Date().toLocaleTimeString(),
        timeWindow: "15m",

        // Initialization
        init() {
            // Update clock every second
            setInterval(() => {
                if (!this.isLoading) {
                    // Clock update logic if needed
                }
            }, 1000);

            // Restore polling state from localStorage
            const savedPolling = localStorage.getItem("apm_polling");
            const savedInterval = localStorage.getItem("apm_poll_interval");
            const savedWindow = localStorage.getItem("apm_time_window");

            if (savedPolling === "true") {
                this.isPolling = true;
            }
            if (savedInterval) {
                this.pollInterval = parseInt(savedInterval);
            }
            if (savedWindow) {
                this.timeWindow = savedWindow;
            }

            // Start polling if was enabled
            if (this.isPolling) {
                this.startPolling();
            }
        },

        // Polling controls
        togglePolling() {
            this.isPolling = !this.isPolling;
            localStorage.setItem("apm_polling", this.isPolling);

            if (this.isPolling) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        },

        startPolling() {
            this.stopPolling(); // Clear any existing timer
            this.pollTimer = setInterval(() => {
                this.refreshDashboard();
            }, this.pollInterval);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        restartPolling() {
            localStorage.setItem("apm_poll_interval", this.pollInterval);
            if (this.isPolling) {
                this.startPolling();
            }
        },

        // Dashboard refresh
        async refreshDashboard() {
            if (this.isLoading) return;

            this.isLoading = true;
            localStorage.setItem("apm_time_window", this.timeWindow);

            try {
                const response = await fetch(
                    `/api/performance/apm/dashboard?window=${this.timeWindow}`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                    },
                );

                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}: ${response.statusText}`,
                    );
                }

                const data = await response.json();

                // Update health score
                this.updateHealthScore(data.health_score);

                // Update last updated time
                this.lastUpdated = new Date().toLocaleTimeString();

                // Dispatch event for other components to react
                window.dispatchEvent(
                    new CustomEvent("apm-refresh", { detail: data }),
                );

                // Show success toast
                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "success",
                            message: "Dashboard refreshed successfully",
                        },
                    }),
                );
            } catch (error) {
                console.error("APM refresh error:", error);
                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "error",
                            message: "Failed to refresh dashboard data",
                        },
                    }),
                );
            } finally {
                this.isLoading = false;
            }
        },

        // UI updates
        updateHealthScore(healthScore) {
            if (!healthScore) return;

            const scoreEl = document.getElementById("health-score");
            const statusEl = document.getElementById("health-status");

            if (scoreEl) {
                scoreEl.textContent = healthScore.score;
                scoreEl.className = `text-5xl font-bold ${this.getStatusColor(healthScore.status)}`;
            }

            if (statusEl) {
                statusEl.textContent = healthScore.status;
                statusEl.className = `text-sm font-medium uppercase ${this.getStatusColor(healthScore.status)}`;
            }
        },

        getStatusColor(status) {
            switch (status) {
                case "excellent":
                    return "text-green-500 dark:text-green-400";
                case "good":
                    return "text-blue-500 dark:text-blue-400";
                case "fair":
                    return "text-yellow-500 dark:text-yellow-400";
                case "poor":
                    return "text-orange-500 dark:text-orange-400";
                default:
                    return "text-red-500 dark:text-red-400";
            }
        },

        // Cleanup
        destroy() {
            this.stopPolling();
        },
    }));
});

// Legacy support for non-Alpine refresh button
window.refreshDashboard = function () {
    const alpineComponent = document.querySelector('[x-data="apmDashboard()"]');
    if (alpineComponent && alpineComponent.__x) {
        alpineComponent.__x.$data.refreshDashboard();
    } else {
        window.location.reload();
    }
};
