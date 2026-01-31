export default (config) => ({
    progress: null,
    loading: false,
    refreshInterval: config.refreshInterval,
    intervalId: null,

    init() {
        this.fetchProgress();
        this.startAutoRefresh();
    },

    async fetchProgress() {
        try {
            const response = await fetch("/api/ai/chat/workflow-status", {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
            });

            const data = await response.json();

            if (data.success) {
                this.progress = data.data;
            }
        } catch (error) {
            console.error("Failed to fetch agent progress:", error);
        }
    },

    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            this.fetchProgress();
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
});
