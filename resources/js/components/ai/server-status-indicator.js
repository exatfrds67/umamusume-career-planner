export default () => ({
    servers: [],
    isRefreshing: false,
    overallHealth: "unknown",
    healthyCount: 0,

    initialize() {
        this.refreshStatus();
        setInterval(() => this.refreshStatus(), 5000);
    },

    async refreshStatus() {
        this.isRefreshing = true;

        try {
            const response = await fetch("/api/ai/chat/server-status");
            const responseData = await response.json();
            
            // Normalize payload: handle `{success: true, data: {...}}` envelope
            const data = responseData.success !== undefined ? responseData.data : responseData;

            if (data && data.servers) {
                this.servers = Object.entries(data.servers).map(
                    ([name, server]) => ({
                        name: name,
                        display_name: server.display_name || name,
                        status: server.status || "offline",
                        response_time: server.response_time,
                        uptime: server.uptime,
                        last_check: server.last_check,
                        error_message: server.error_message,
                        capabilities: server.capabilities || [],
                    }),
                );

                this.calculateOverallHealth();
            }
        } catch (error) {
            console.error("Failed to fetch server status:", error);
        } finally {
            this.isRefreshing = false;
        }
    },

    calculateOverallHealth() {
        if (this.servers.length === 0) {
            this.overallHealth = "unknown";
            this.healthyCount = 0;
            return;
        }

        this.healthyCount = this.servers.filter(
            (s) => s.status === "healthy",
        ).length;
        const healthyPercentage =
            (this.healthyCount / this.servers.length) * 100;

        if (healthyPercentage === 100) {
            this.overallHealth = "healthy";
        } else if (healthyPercentage >= 50) {
            this.overallHealth = "degraded";
        } else {
            this.overallHealth = "unhealthy";
        }
    },

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return `${diff}s ago`;
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return `${Math.floor(diff / 86400)}d ago`;
    },
});
