/**
 * AI Dashboard
 * Handles loading and auto-refreshing AI dashboard overview data.
 *
 * No server-side data injection needed — fetches data via API.
 */

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
document.addEventListener("alpine:init", () => {
    Alpine.data("aiDashboard", () => ({
        summary: {
            total_requests_24h: 0,
            success_rate: 0,
            avg_response_time: 0,
            total_cost_24h: 0,
            active_agents: 0,
            healthy_servers: 0,
            total_servers: 0,
        },
        servers: {},
        performance: {
            providers: {},
        },
        costs: {
            daily_cost: 0,
            weekly_cost: 0,
            monthly_cost: 0,
            budget_status: null,
        },
        loading: false,
        initialLoad: true,

        init() {
            this.loadDashboard();
            setInterval(() => this.loadDashboard(true), 5000);
        },

        async loadDashboard(isAutoRefresh = false) {
            if (this.loading && !isAutoRefresh) {
                return;
            }
            if (!isAutoRefresh) {
                this.loading = true;
            }

            try {
                const response = await fetch(
                    "/api/ai/dashboard/overview",
                );
                const data = await response.json();

                if (data.success) {
                    this.summary = { ...this.summary, ...(data.data.summary || {}) };
                    this.servers = data.data.servers || {};
                    this.performance = { ...this.performance, ...(data.data.performance || {}) };
                    this.costs = { ...this.costs, ...(data.data.costs || {}) };
                }
            } catch (error) {
                console.error("Failed to load dashboard:", error);
            } finally {
                this.loading = false;
                this.initialLoad = false;
            }
        },
    }));
});
