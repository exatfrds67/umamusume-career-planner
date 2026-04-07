@extends('layouts.app')

@section('title', 'AI Management Dashboard')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'AI & Tools']]" />

    <div class="py-12" x-data="aiDashboard()">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            {{-- Page Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">AI Management Dashboard</h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">Monitor AI provider performance, costs, and server health</p>
                </div>
                <div>
                    <button @click="loadDashboard(false)" 
                        type="button"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-neutral-900 shadow-sm ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50 dark:bg-neutral-800 dark:text-white dark:ring-neutral-700 dark:hover:bg-neutral-700">
                        <svg class="mr-2 h-4 w-4" :class="{'animate-spin text-blue-600': loading}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Initial Loading Skeleton -->
            <div x-show="initialLoad" class="space-y-6 animate-pulse">
                <!-- Skeletons for summary cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <template x-for="i in 4" :key="i">
                        <div class="h-32 rounded-lg bg-white dark:bg-neutral-800 shadow-xs"></div>
                    </template>
                </div>
                <!-- Skeletons for wide cards -->
                <div class="h-64 rounded-lg bg-white dark:bg-neutral-800 shadow-xs"></div>
                <div class="h-64 rounded-lg bg-white dark:bg-neutral-800 shadow-xs"></div>
            </div>

            <!-- Content Container -->
            <div x-show="!initialLoad" x-cloak class="space-y-6">

                <!-- Summary Cards -->
                <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4" aria-label="Summary Statistics">
                <!-- Total Requests Card -->
                <article class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests (24h)</h3>
                                <p class="mt-2 text-3xl font-semibold text-neutral-900 dark:text-white"
                                    x-text="summary.total_requests_24h">0</p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900" aria-hidden="true">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Success Rate: </span>
                            <span class="font-semibold text-green-600 dark:text-green-400"
                                x-text="summary.success_rate + '%'">0%</span>
                        </div>
                    </div>
                </article>

                <!-- Average Response Time Card -->
                <article class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg Response Time</h3>
                                <p class="mt-2 text-3xl font-semibold text-neutral-900 dark:text-white"
                                    x-text="summary.avg_response_time + 's'">0s</p>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900" aria-hidden="true">
                                <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Total Cost Card -->
                <article class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Cost (24h)</h3>
                                <p class="mt-2 text-3xl font-semibold text-neutral-900 dark:text-white"
                                    x-text="'$' + summary.total_cost_24h">$0</p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3 dark:bg-green-900" aria-hidden="true">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Server Health Card -->
                <article class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Server Health</h3>
                                <p class="mt-2 text-3xl font-semibold text-neutral-900 dark:text-white">
                                    <span x-text="summary.healthy_servers">0</span>/<span
                                        x-text="summary.total_servers">0</span>
                                </p>
                            </div>
                            <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900" aria-hidden="true">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <!-- MCP Server Status -->
            <section class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg" aria-labelledby="mcp-server-status-title">
                <div class="p-6">
                    <h2 id="mcp-server-status-title" class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white">MCP Server Status</h2>
                    <div class="space-y-4">
                        <template x-if="Object.keys(servers).length === 0">
                            <div class="rounded-lg border border-neutral-200 border-dashed p-8 text-center dark:border-neutral-700">
                                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white">No servers</h3>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">No MCP servers are currently registered or active.</p>
                            </div>
                        </template>

                        <template x-for="(server, name) in servers" :key="name">
                            <div
                                class="flex items-center justify-between rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                                <div class="flex items-center space-x-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-green-100 dark:bg-green-900': server.status === 'healthy',
                                            'bg-red-100 dark:bg-red-900': server.status === 'unhealthy',
                                            'bg-neutral-100 dark:bg-neutral-700': server.status === 'disabled'
                                        }">
                                        <svg class="h-6 w-6"
                                            :class="{
                                                'text-green-600 dark:text-green-300': server.status === 'healthy',
                                                'text-red-600 dark:text-red-300': server.status === 'unhealthy',
                                                'text-neutral-600 dark:text-neutral-400': server.status === 'disabled'
                                            }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-neutral-900 dark:text-white" x-text="server.name"></p>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
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
            </section>

            <!-- Performance Comparison -->
            <section class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg" aria-labelledby="performance-comparison-title">
                <div class="p-6">
                    <h2 id="performance-comparison-title" class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white">AI Provider Performance
                        Comparison</h2>
                    
                    <template x-if="Object.keys(performance.providers).length === 0">
                        <div class="mt-4 mb-4 rounded-lg border border-neutral-200 border-dashed p-8 text-center dark:border-neutral-700">
                            <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white">No performance data</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">No requests have been made to AI providers yet.</p>
                        </div>
                    </template>

                    <div class="overflow-x-auto" x-show="Object.keys(performance.providers).length > 0">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Provider</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Requests</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Success Rate</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Avg Response</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                                <template x-for="(provider, name) in performance.providers" :key="name">
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white"
                                            x-text="provider.name"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400"
                                            x-text="provider.requests_24h"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400"
                                            x-text="provider.success_rate + '%'"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400"
                                            x-text="provider.avg_response_time + 's'"></td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400"
                                            x-text="'$' + provider.total_cost"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cost Summary -->
            <section class="overflow-hidden bg-white shadow-xs dark:bg-neutral-800 sm:rounded-lg" aria-labelledby="cost-summary-title">
                <div class="p-6">
                    <h2 id="cost-summary-title" class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white">Cost Summary</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Daily Cost</h3>
                            <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white"
                                x-text="'$' + costs.daily_cost">$0</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Weekly Cost</h3>
                            <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white"
                                x-text="'$' + costs.weekly_cost">$0</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Monthly Cost</h3>
                            <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white"
                                x-text="'$' + costs.monthly_cost">$0</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Budget Utilization</span>
                            <span class="text-sm font-semibold"
                                :class="{
                                    'text-green-600 dark:text-green-400': costs.budget_status?.budget_utilization < 75,
                                    'text-yellow-600 dark:text-yellow-400': costs.budget_status?.budget_utilization >=
                                        75 && costs.budget_status?.budget_utilization < 90,
                                    'text-red-600 dark:text-red-400': costs.budget_status?.budget_utilization >= 90
                                }"
                                x-text="(costs.budget_status?.budget_utilization ?? 0) + '%'">0%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700" role="progressbar" :aria-valuenow="costs.budget_status?.budget_utilization || 0" aria-valuemin="0" aria-valuemax="100" aria-label="Budget status">
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
            </section>

            </div> <!-- End Content Container -->
        </div>
    </div>

    @vite('resources/js/pages/ai/dashboard.js')
@endsection
