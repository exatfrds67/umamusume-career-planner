export default (config) => ({
    metrics: null,
    loading: false,
    lastUpdated: "Never",
    refreshInterval: config.refreshInterval,
    intervalId: null,

    init() {
        this.fetchMetrics();
        this.startAutoRefresh();
    },

    async fetchMetrics() {
        this.loading = true;

        try {
            const response = await fetch("/api/ai/chat/performance-metrics", {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
            });

            const data = await response.json();

            if (data.success) {
                this.metrics = data.data;
                this.lastUpdated = new Date().toLocaleTimeString();
            }
        } catch (error) {
            console.error("Failed to fetch performance metrics:", error);
        } finally {
            this.loading = false;
        }
    },

    async refresh() {
        await this.fetchMetrics();
    },

    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            this.fetchMetrics();
        }, this.refreshInterval);
    },

    destroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    },
});
