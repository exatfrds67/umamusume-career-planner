/**
 * Tool Execution Monitor Component
 * Monitors MCP tool execution status and statistics
 */

// Register Alpine component
document.addEventListener("alpine:init", () => {
    // eslint-disable-next-line no-undef
    Alpine.data("toolExecutionMonitor", (config) => ({
        toolData: null,
        loading: false,
        lastUpdated: "Never",
        activeTab: "active",
        refreshInterval: config.refreshInterval,
        intervalId: null,

        init() {
            this.fetchToolData();
            this.startAutoRefresh();
        },

        async fetchToolData() {
            this.loading = true;

            try {
                const response = await fetch("/api/ai/chat/tool-usage", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.toolData = data.data;
                    this.lastUpdated = new Date().toLocaleTimeString();
                }
            } catch (error) {
                console.error("Failed to fetch tool execution data:", error);
            } finally {
                this.loading = false;
            }
        },

        async refresh() {
            await this.fetchToolData();
        },

        startAutoRefresh() {
            this.intervalId = setInterval(() => {
                this.fetchToolData();
            }, this.refreshInterval);
        },

        formatTime(timestamp) {
            if (!timestamp) return "N/A";
            const date = new Date(timestamp);
            return date.toLocaleTimeString();
        },

        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        },
    }));
});
