/**
 * Data Management Hub
 *
 * Unified interface for importing, exporting, migrating, and backing up career data.
 * Provides real-time operation tracking and comprehensive history.
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("dataManagementHub", () => ({
        activeTab: "overview",
        loading: false,
        quickStats: {
            total_characters: 0,
            total_careers: 0,
            total_backups: 0,
            last_backup: null,
        },
        statistics: {
            total_operations: 0,
            successful_operations: 0,
            failed_operations: 0,
            success_rate: 0,
            by_type: {},
            records_processed: 0,
        },
        recentOperations: [],
        ongoingOperations: [],
        history: [],
        historyPagination: null,
        historyFilters: {
            operation_type: "",
            status: "",
            date_from: "",
            date_to: "",
        },

        init() {
            this.loadDashboard();
            // Poll for ongoing operations every 5 seconds
            setInterval(() => this.checkOngoingOperations(), 5000);
        },

        async loadDashboard() {
            this.loading = true;
            try {
                const response = await fetch("/api/data-management/dashboard", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    this.quickStats = result.data.quick_stats;
                    this.statistics = result.data.statistics;
                    this.recentOperations = result.data.recent_operations;
                    this.ongoingOperations = result.data.ongoing_operations;
                }
            } catch (error) {
                console.error("Failed to load dashboard:", error);
                this.showToast("Failed to load dashboard data", "error");
            } finally {
                this.loading = false;
            }
        },

        async checkOngoingOperations() {
            try {
                const response = await fetch("/api/data-management/status", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    this.ongoingOperations = result.data.ongoing_operations;
                }
            } catch (error) {
                console.error("Failed to check ongoing operations:", error);
            }
        },

        async loadHistory(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: page,
                    ...Object.fromEntries(
                        Object.entries(this.historyFilters).filter(
                            ([_, v]) => v,
                        ),
                    ),
                });

                const response = await fetch(
                    `/api/data-management/history?${params}`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                    },
                );

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    this.history = result.data;
                    this.historyPagination = result.pagination;
                }
            } catch (error) {
                console.error("Failed to load history:", error);
                this.showToast("Failed to load operation history", "error");
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateString) {
            if (!dateString) return "N/A";
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        showToast(message, type = "info") {
            // Use global toast event system
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: {
                        type: type,
                        message: message,
                    },
                }),
            );
        },
    }));
});
