@props([
    'refreshInterval' => 30000, // 30 seconds
])

<div class="performance-metrics bg-white dark:bg-neutral-800 rounded-lg shadow-xs p-6" x-data="performanceMetrics({
    refreshInterval: {{ $refreshInterval }}
})">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
            Performance Metrics
        </h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-neutral-500 dark:text-neutral-400" x-text="lastUpdated"></span>
            <button @click="refresh()"
                class="p-2 text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading && !metrics" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
    </div>

    {{-- Metrics Content --}}
    <div x-show="!loading || metrics" class="space-y-6">
        {{-- Provider Comparison --}}
        <div>
            <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">AI Provider Performance</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <template x-for="(provider, name) in metrics?.providers || {}" :key="name">
                    <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-neutral-900 dark:text-white capitalize"
                                x-text="name"></span>
                            <span class="text-xs px-2 py-1 rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': provider
                                        .success_rate >= 90,
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': provider
                                        .success_rate >= 70 && provider.success_rate < 90,
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': provider.success_rate <
                                        70
                                }"
                                x-text="provider.success_rate.toFixed(1) + '%'">
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">Requests:</span>
                                <span class="font-medium text-neutral-900 dark:text-white"
                                    x-text="provider.total_requests"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">Avg Time:</span>
                                <span class="font-medium text-neutral-900 dark:text-white"
                                    x-text="provider.average_response_time.toFixed(2) + 's'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">Avg Cost:</span>
                                <span class="font-medium text-neutral-900 dark:text-white"
                                    x-text="'$' + provider.average_cost.toFixed(4)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Best Performers --}}
        <div x-show="metrics?.comparison"
            class="bg-linear-to-r from-primary-50 to-primary-100 dark:from-primary-900 dark:to-primary-800 rounded-lg p-4">
            <h4 class="text-sm font-medium text-primary-900 dark:text-primary-100 mb-3">Top Performers</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-primary-700 dark:text-primary-300 mb-1">Fastest</div>
                    <div class="text-sm font-semibold text-primary-900 dark:text-primary-100 capitalize"
                        x-text="metrics?.comparison?.fastest_provider || 'N/A'"></div>
                </div>
                <div>
                    <div class="text-xs text-primary-700 dark:text-primary-300 mb-1">Most Reliable</div>
                    <div class="text-sm font-semibold text-primary-900 dark:text-primary-100 capitalize"
                        x-text="metrics?.comparison?.most_reliable_provider || 'N/A'"></div>
                </div>
                <div>
                    <div class="text-xs text-primary-700 dark:text-primary-300 mb-1">Most Cost-Effective</div>
                    <div class="text-sm font-semibold text-primary-900 dark:text-primary-100 capitalize"
                        x-text="metrics?.comparison?.most_cost_effective || 'N/A'"></div>
                </div>
                <div>
                    <div class="text-xs text-primary-700 dark:text-primary-300 mb-1">Best Agent</div>
                    <div class="text-sm font-semibold text-primary-900 dark:text-primary-100 capitalize"
                        x-text="metrics?.comparison?.best_performing_agent || 'N/A'"></div>
                </div>
            </div>
        </div>

        {{-- Agent Performance --}}
        <div x-show="Object.keys(metrics?.agents || {}).length > 0">
            <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Agent Performance</h4>
            <div class="space-y-2">
                <template x-for="(agent, type) in metrics?.agents || {}" :key="type">
                    <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full"
                                :class="{
                                    'bg-green-500': agent.health_status === 'healthy',
                                    'bg-yellow-500': agent.health_status === 'degraded',
                                    'bg-red-500': agent.health_status === 'unhealthy'
                                }">
                            </div>
                            <span class="text-sm font-medium text-neutral-900 dark:text-white capitalize"
                                x-text="type"></span>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <div>
                                <span class="text-neutral-600 dark:text-neutral-400">Executions:</span>
                                <span class="font-medium text-neutral-900 dark:text-white ml-1"
                                    x-text="agent.total_executions"></span>
                            </div>
                            <div>
                                <span class="text-neutral-600 dark:text-neutral-400">Success:</span>
                                <span class="font-medium text-neutral-900 dark:text-white ml-1"
                                    x-text="agent.success_rate.toFixed(1) + '%'"></span>
                            </div>
                            <div>
                                <span class="text-neutral-600 dark:text-neutral-400">Avg Time:</span>
                                <span class="font-medium text-neutral-900 dark:text-white ml-1"
                                    x-text="agent.average_execution_time.toFixed(2) + 's'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Alpine component logic moved to resources/js/components/ai/performance-metrics.js --}}
@once
    @vite(['resources/js/components/ai/performance-metrics.js'])
@endonce
