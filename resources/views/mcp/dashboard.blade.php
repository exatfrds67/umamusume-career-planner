<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('MCP Management Dashboard') }}
            </h2>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Last updated: <span x-text="lastUpdated">--</span>
                </span>
                <button @click="refreshAll()"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Refresh All
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="mcpDashboard()">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        Overview
                    </button>
                    <button @click="activeTab = 'servers'"
                        :class="activeTab === 'servers' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        MCP Servers
                    </button>
                    <button @click="activeTab = 'agents'"
                        :class="activeTab === 'agents' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        Active Agents
                    </button>
                    <button @click="activeTab = 'costs'"
                        :class="activeTab === 'costs' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        Cost Transparency
                    </button>
                    <button @click="activeTab = 'performance'"
                        :class="activeTab === 'performance' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        Performance
                    </button>
                    <button @click="activeTab = 'settings'"
                        :class="activeTab === 'settings' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium">
                        Settings
                    </button>
                </nav>
            </div>

            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Servers Card -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">MCP Servers</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                        <span x-text="overview.healthy_servers">0</span>/<span
                                            x-text="overview.total_servers">0</span>
                                    </p>
                                </div>
                                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Status: </span>
                                <span class="font-semibold"
                                    :class="{
                                        'text-green-600 dark:text-green-400': overview.healthy_servers === overview
                                            .total_servers,
                                        'text-yellow-600 dark:text-yellow-400': overview.healthy_servers > 0 && overview
                                            .healthy_servers < overview.total_servers,
                                        'text-red-600 dark:text-red-400': overview.healthy_servers === 0
                                    }"
                                    x-text="overview.healthy_servers === overview.total_servers ? 'All Healthy' : (overview.healthy_servers > 0 ? 'Degraded' : 'Critical')"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Active Agents Card -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Agents</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                        x-text="overview.active_agents">0</p>
                                </div>
                                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Workflows: </span>
                                <span class="font-semibold text-gray-900 dark:text-white"
                                    x-text="overview.active_workflows">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cost (24h) Card -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cost (24h)</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                        x-text="'$' + (overview.cost_24h || 0).toFixed(4)">$0</p>
                                </div>
                                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Requests: </span>
                                <span class="font-semibold text-gray-900 dark:text-white"
                                    x-text="overview.requests_24h">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Avg Response Time Card -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Response</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                        x-text="(overview.avg_response_time || 0).toFixed(2) + 's'">0s</p>
                                </div>
                                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900">
                                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">P95: </span>
                                <span class="font-semibold text-gray-900 dark:text-white"
                                    x-text="(overview.p95_response_time || 0).toFixed(2) + 's'">0s</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Server Health Summary -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Server Health Summary
                            </h3>
                            <div class="space-y-3">
                                <template x-for="(server, name) in Object.values(servers).slice(0, 5)"
                                    :key="name">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="h-2 w-2 rounded-full"
                                                :class="{
                                                    'bg-green-500': server.status === 'healthy',
                                                    'bg-yellow-500': server.status === 'degraded',
                                                    'bg-red-500': server.status === 'unhealthy',
                                                    'bg-gray-400': server.status === 'disabled'
                                                }">
                                            </div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300"
                                                x-text="server.name"></span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400"
                                            x-text="server.uptime_percentage + '% uptime'"></span>
                                    </div>
                                </template>
                            </div>
                            <button @click="activeTab = 'servers'"
                                class="mt-4 w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                View All Servers
                            </button>
                        </div>
                    </div>

                    <!-- Recent Agent Activity -->
                    <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Recent Agent Activity
                            </h3>
                            <div class="space-y-3">
                                <template x-for="(agent, index) in Object.values(agents).slice(0, 5)"
                                    :key="index">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="h-2 w-2 rounded-full"
                                                :class="{
                                                    'bg-blue-500 animate-pulse': agent.status === 'active',
                                                    'bg-green-500': agent.status === 'completed',
                                                    'bg-yellow-500': agent.status === 'waiting',
                                                    'bg-red-500': agent.status === 'failed'
                                                }">
                                            </div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300"
                                                x-text="agent.name"></span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 capitalize"
                                            x-text="agent.status"></span>
                                    </div>
                                </template>
                            </div>
                            <button @click="activeTab = 'agents'"
                                class="mt-4 w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                View All Agents
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MCP Servers Tab -->
            <div x-show="activeTab === 'servers'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <template x-for="(server, name) in servers" :key="name">
                        <x-mcp.server-status-card :server="server" />
                    </template>
                </div>
            </div>

            <!-- Active Agents Tab -->
            <div x-show="activeTab === 'agents'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <template x-for="(agent, id) in agents" :key="id">
                        <x-mcp.agent-activity-card :agent="agent" />
                    </template>
                </div>
                <div x-show="Object.keys(agents).length === 0"
                    class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No active agents</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Agents will appear here when they are
                        processing tasks.</p>
                </div>
            </div>

            <!-- Cost Transparency Tab -->
            <div x-show="activeTab === 'costs'">
                <x-mcp.cost-transparency-panel :costs="costs" />
            </div>

            <!-- Performance Tab -->
            <div x-show="activeTab === 'performance'">
                <x-mcp.performance-metrics-dashboard :performance="performance" />
            </div>

            <!-- Settings Tab -->
            <div x-show="activeTab === 'settings'">
                <x-mcp.user-controls-panel :settings="settings" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function mcpDashboard() {
                return {
                    activeTab: 'overview',
                    lastUpdated: '--',
                    performanceTimeRange: '24h',
                    loading: false,

                    overview: {
                        total_servers: 0,
                        healthy_servers: 0,
                        active_agents: 0,
                        active_workflows: 0,
                        cost_24h: 0,
                        requests_24h: 0,
                        avg_response_time: 0,
                        p95_response_time: 0
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
                        recommendations: []
                    },

                    performance: {
                        providers: {},
                        fastest: null,
                        most_reliable: null,
                        most_cost_effective: null,
                        recommendations: []
                    },

                    settings: {
                        servers: {},
                        agents: {
                            training: 'auto',
                            career: 'auto',
                            race: 'auto',
                            skill: 'auto'
                        },
                        budget: {
                            daily: 1.00,
                            monthly: 30.00,
                            alert_threshold: 90
                        },
                        performance: {
                            auto_fallback: true,
                            parallel_processing: true,
                            cache_responses: true
                        }
                    },

                    init() {
                        this.loadDashboard();
                        // Refresh every 10 seconds
                        setInterval(() => this.loadDashboard(), 10000);

                        // Listen for custom events
                        this.$watch('activeTab', () => this.loadTabData());
                    },

                    async loadDashboard() {
                        if (this.loading) return;
                        this.loading = true;

                        try {
                            const response = await fetch('/api/mcp/dashboard/overview');
                            const data = await response.json();

                            if (data.success) {
                                this.overview = data.data.overview;
                                this.servers = data.data.servers;
                                this.agents = data.data.agents;
                                this.costs = data.data.costs;
                                this.performance = data.data.performance;
                                this.settings = data.data.settings;
                                this.lastUpdated = new Date().toLocaleTimeString();
                            }
                        } catch (error) {
                            console.error('Failed to load dashboard:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    async loadTabData() {
                        // Load specific tab data when switching tabs
                        switch (this.activeTab) {
                            case 'servers':
                                await this.loadServers();
                                break;
                            case 'agents':
                                await this.loadAgents();
                                break;
                            case 'costs':
                                await this.loadCosts();
                                break;
                            case 'performance':
                                await this.loadPerformance();
                                break;
                            case 'settings':
                                await this.loadSettings();
                                break;
                        }
                    },

                    async loadServers() {
                        try {
                            const response = await fetch('/api/mcp/servers');
                            const data = await response.json();
                            if (data.success) {
                                this.servers = data.data;
                            }
                        } catch (error) {
                            console.error('Failed to load servers:', error);
                        }
                    },

                    async loadAgents() {
                        try {
                            const response = await fetch('/api/mcp/agents');
                            const data = await response.json();
                            if (data.success) {
                                this.agents = data.data;
                            }
                        } catch (error) {
                            console.error('Failed to load agents:', error);
                        }
                    },

                    async loadCosts() {
                        try {
                            const response = await fetch('/api/mcp/costs');
                            const data = await response.json();
                            if (data.success) {
                                this.costs = data.data;
                            }
                        } catch (error) {
                            console.error('Failed to load costs:', error);
                        }
                    },

                    async loadPerformance() {
                        try {
                            const response = await fetch(`/api/mcp/performance?range=${this.performanceTimeRange}`);
                            const data = await response.json();
                            if (data.success) {
                                this.performance = data.data;
                            }
                        } catch (error) {
                            console.error('Failed to load performance:', error);
                        }
                    },

                    async loadSettings() {
                        try {
                            const response = await fetch('/api/mcp/settings');
                            const data = await response.json();
                            if (data.success) {
                                this.settings = data.data;
                            }
                        } catch (error) {
                            console.error('Failed to load settings:', error);
                        }
                    },

                    async refreshAll() {
                        await this.loadDashboard();
                        await this.loadTabData();
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
