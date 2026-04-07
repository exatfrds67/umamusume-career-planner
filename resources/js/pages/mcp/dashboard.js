/**
 * MCP Management Dashboard
 * Handles real-time monitoring of MCP servers, agents, costs, and performance
 *
 * Routes previously injected via inline <script> in mcp/dashboard.blade.php
 */

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
const ROUTES = {
    overview: "/api/mcp/dashboard/overview",
    servers: "/api/mcp/dashboard/servers",
    agents: "/api/mcp/dashboard/agents",
    costs: "/api/mcp/dashboard/costs",
    performance: "/api/mcp/dashboard/performance",
    settings: "/api/mcp/dashboard/settings",
};

// Register Alpine component — handles both pre-initialized and late-loaded Alpine
function registerMcpDashboard() {
    Alpine.data("mcpDashboard", () => ({
        activeTab: "overview",
        lastUpdated: "--",
        performanceTimeRange: "24h",
        loading: false,
        initialLoad: true,

        overview: {
            total_servers: 0,
            healthy_servers: 0,
            active_agents: 0,
            active_workflows: 0,
            cost_24h: 0,
            requests_24h: 0,
            avg_response_time: 0,
            p95_response_time: 0,
        },

        servers: {},
        agents: {},
        costs: {
            daily_cost: 0,
            weekly_cost: 0,
            monthly_cost: 0,
            budget_status: null,
            by_provider: {},
            top_tools: [],
            recommendations: [],
        },

        performance: {
            providers: {},
            fastest: null,
            most_reliable: null,
            most_cost_effective: null,
            recommendations: [],
        },

        settings: {
            servers: {},
            agents: {
                training: "auto",
                career: "auto",
                race: "auto",
                skill: "auto",
            },
            budget: {
                daily: 1.0,
                monthly: 30.0,
                alert_threshold: 90,
            },
            performance: {
                auto_fallback: true,
                parallel_processing: true,
                cache_responses: true,
            },
        },

        init() {
            this.loadDashboard();
            // Refresh every 10 seconds
            setInterval(() => this.loadDashboard(true), 10000);

            // Listen for custom events
            this.$watch("activeTab", () => this.loadTabData());
        },

        async loadDashboard(isAutoRefresh = false) {
            if (this.loading && !isAutoRefresh) {
                return;
            }

            if (!isAutoRefresh) {
                this.loading = true;
            }

            try {
                const response = await fetch(ROUTES.overview, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.overview = {
                        ...this.overview,
                        ...(data.data?.overview || {}),
                    };
                    this.servers = this.toObject(data.data?.servers);
                    this.agents = this.toObject(data.data?.agents);
                    this.costs = { ...this.costs, ...(data.data?.costs || {}) };
                    this.performance = {
                        ...this.performance,
                        ...(data.data?.performance || {}),
                    };
                    this.settings = {
                        ...this.settings,
                        ...(data.data?.settings || {}),
                    };
                    this.lastUpdated = new Date().toLocaleTimeString();
                }
            } catch (error) {
                console.error("Failed to load dashboard:", error);

                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "error",
                            message: "Failed to load dashboard data",
                        },
                    }),
                );
            } finally {
                this.loading = false;
                this.initialLoad = false;
            }
        },

        toObject(value) {
            return value && typeof value === "object" && !Array.isArray(value)
                ? value
                : {};
        },

        async loadTabData() {
            // Load specific tab data when switching tabs
            switch (this.activeTab) {
                case "servers":
                    await this.loadServers();
                    break;
                case "agents":
                    await this.loadAgents();
                    break;
                case "costs":
                    await this.loadCosts();
                    break;
                case "performance":
                    await this.loadPerformance();
                    break;
                case "settings":
                    await this.loadSettings();
                    break;
            }
        },

        async loadServers() {
            try {
                const response = await fetch(ROUTES.servers, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.servers = this.toObject(data.data);
                }
            } catch (error) {
                console.error("Failed to load servers:", error);
            }
        },

        async loadAgents() {
            try {
                const response = await fetch(ROUTES.agents, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.agents = this.toObject(data.data);
                }
            } catch (error) {
                console.error("Failed to load agents:", error);
            }
        },

        async loadCosts() {
            try {
                const response = await fetch(ROUTES.costs, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.costs = data.data;
                }
            } catch (error) {
                console.error("Failed to load costs:", error);
            }
        },

        async loadPerformance() {
            try {
                const response = await fetch(
                    `${ROUTES.performance}?range=${this.performanceTimeRange}`,
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

                const data = await response.json();
                if (data.success) {
                    this.performance = data.data;
                }
            } catch (error) {
                console.error("Failed to load performance:", error);
            }
        },

        async loadSettings() {
            try {
                const response = await fetch(ROUTES.settings, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.settings = data.data;
                }
            } catch (error) {
                console.error("Failed to load settings:", error);
            }
        },

        async refreshAll() {
            await this.loadDashboard();
            await this.loadTabData();
        },
    }));
}

if (window.Alpine) {
    registerMcpDashboard();
} else {
    document.addEventListener("alpine:init", registerMcpDashboard);
}
