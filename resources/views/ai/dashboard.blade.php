<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('AI Management Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="aiDashboard()">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Requests Card -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requests (24h)</p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                    x-text="summary.total_requests_24h">0</p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Success Rate: </span>
                            <span class="font-semibold text-green-600 dark:text-green-400"
                                x-text="summary.success_rate + '%'">0%</span>
                        </div>
                    </div>
                </div>

                <!-- Average Response Time Card -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Response Time</p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                    x-text="summary.avg_response_time + 's'">0s</p>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
                                <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Cost Card -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Cost (24h)</p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white"
                                    x-text="'$' + summary.total_cost_24h">$0</p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Server Health Card -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Server Health</p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                    <span x-text="summary.healthy_servers">0</span>/<span
                                        x-text="summary.total_servers">0</span>
                                </p>
                            </div>
                            <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MCP Server Status -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">MCP Server Status</h3>
                    <div class="space-y-4">
                        <template x-for="(server, name) in servers" :key="name">
                            <div
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex items-center space-x-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-green-100 dark:bg-green-900': server.status === 'healthy',
                                            'bg-red-100 dark:bg-red-900': server.status === 'unhealthy',
                                            'bg-gray-100 dark:bg-gray-700': server.status === 'disabled'
                                        }">
                                        <svg class="h-6 w-6"
                                            :class="{
                                                'text-green-600 dark:text-green-300': server.status === 'healthy',
                                                'text-red-600 dark:text-red-300': server.status === 'unhealthy',
                                                'text-gray-600 dark:text-gray-400': server.status === 'disabled'
                                            }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white" x-text="server.name"></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span x-text="server.status"></span>
                                            <span x-show="server.consecutive_failures > 0"
                                                class="ml-2 text-red-600 dark:text-red-400">
                                                (<span x-text="server.consecutive_failures"></span> failures)
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': server
                                                .is_connected,
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': !server
                                                .is_connected
                                        }">
                                        <span x-text="server.is_connected ? 'Connected' : 'Disconnected'"></span>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Performance Comparison -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">AI Provider Performance
                        Comparison</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Provider</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Requests</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Success Rate</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Avg Response</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <template x-for="(provider, name) in performance.providers" :key="name">
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                                            x-text="provider.name"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                            x-text="provider.requests_24h"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                            x-text="provider.success_rate + '%'"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                            x-text="provider.avg_response_time + 's'"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                            x-text="'$' + provider.total_cost"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cost Summary -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Cost Summary</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Daily Cost</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"
                                x-text="'$' + costs.daily_cost">$0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Weekly Cost</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"
                                x-text="'$' + costs.weekly_cost">$0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Monthly Cost</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"
                                x-text="'$' + costs.monthly_cost">$0</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Budget Utilization</span>
                            <span class="text-sm font-semibold"
                                :class="{
                                    'text-green-600 dark:text-green-400': costs.budget_status?.budget_utilization < 75,
                                    'text-yellow-600 dark:text-yellow-400': costs.budget_status?.budget_utilization >=
                                        75 && costs.budget_status?.budget_utilization < 90,
                                    'text-red-600 dark:text-red-400': costs.budget_status?.budget_utilization >= 90
                                }"
                                x-text="costs.budget_status?.budget_utilization + '%'">0%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-full transition-all duration-300"
                                :class="{
                                    'bg-green-600': costs.budget_status?.budget_utilization < 75,
                                    'bg-yellow-600': costs.budget_status?.budget_utilization >= 75 && costs
                                        .budget_status?.budget_utilization < 90,
                                    'bg-red-600': costs.budget_status?.budget_utilization >= 90
                                }"
                                :style="'width: ' + (costs.budget_status?.budget_utilization || 0) + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function aiDashboard() {
                return {
                    summary: {
                        total_requests_24h: 0,
                        success_rate: 0,
                        avg_response_time: 0,
                        total_cost_24h: 0,
                        active_agents: 0,
                        healthy_servers: 0,
                        total_servers: 0
                    },
                    servers: {},
                    performance: {
                        providers: {}
                    },
                    costs: {
                        daily_cost: 0,
                        weekly_cost: 0,
                        monthly_cost: 0,
                        budget_status: null
                    },
                    loading: false,

                    init() {
                        this.loadDashboard();
                        // Refresh every 5 seconds
                        setInterval(() => this.loadDashboard(), 5000);
                    },

                    async loadDashboard() {
                        if (this.loading) return;
                        this.loading = true;

                        try {
                            const response = await fetch('/api/ai/dashboard/overview');
                            const data = await response.json();

                            if (data.success) {
                                this.summary = data.data.summary;
                                this.servers = data.data.servers;
                                this.performance = data.data.performance;
                                this.costs = data.data.costs;
                            }
                        } catch (error) {
                            console.error('Failed to load dashboard:', error);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
