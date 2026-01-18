@props(['costs' => []])

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cost Transparency</h3>
        <button @click="$dispatch('refresh-costs')"
            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            title="Refresh Costs">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
        </button>
    </div>

    <!-- Cost Summary Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Daily Cost -->
        <div
            class="rounded-lg bg-linear-to-br from-blue-50 to-blue-100 p-4 dark:from-blue-900/20 dark:to-blue-800/20">
            <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Daily Cost</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400"
                x-text="'$' + (costs.daily_cost || 0).toFixed(4)"></p>
            <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                <span x-text="costs.daily_requests || 0"></span> requests
            </p>
        </div>

        <!-- Weekly Cost -->
        <div
            class="rounded-lg bg-linear-to-br from-purple-50 to-purple-100 p-4 dark:from-purple-900/20 dark:to-purple-800/20">
            <p class="text-sm font-medium text-purple-900 dark:text-purple-200">Weekly Cost</p>
            <p class="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400"
                x-text="'$' + (costs.weekly_cost || 0).toFixed(4)"></p>
            <p class="mt-1 text-xs text-purple-700 dark:text-purple-300">
                <span x-text="costs.weekly_requests || 0"></span> requests
            </p>
        </div>

        <!-- Monthly Cost -->
        <div
            class="rounded-lg bg-linear-to-br from-green-50 to-green-100 p-4 dark:from-green-900/20 dark:to-green-800/20">
            <p class="text-sm font-medium text-green-900 dark:text-green-200">Monthly Cost</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400"
                x-text="'$' + (costs.monthly_cost || 0).toFixed(4)"></p>
            <p class="mt-1 text-xs text-green-700 dark:text-green-300">
                <span x-text="costs.monthly_requests || 0"></span> requests
            </p>
        </div>
    </div>

    <!-- Budget Utilization -->
    <div class="mt-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Budget Utilization</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="'$' + (costs.budget_status?.spent || 0).toFixed(2)"></span> of
                    <span x-text="'$' + (costs.budget_status?.limit || 0).toFixed(2)"></span>
                </p>
            </div>
            <span class="text-lg font-bold"
                :class="{
                    'text-green-600 dark:text-green-400': costs.budget_status?.utilization < 75,
                    'text-yellow-600 dark:text-yellow-400': costs.budget_status?.utilization >= 75 && costs
                        .budget_status?.utilization < 90,
                    'text-red-600 dark:text-red-400': costs.budget_status?.utilization >= 90
                }"
                x-text="(costs.budget_status?.utilization || 0).toFixed(1) + '%'"></span>
        </div>
        <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="h-full transition-all duration-500"
                :class="{
                    'bg-green-600': costs.budget_status?.utilization < 75,
                    'bg-yellow-600': costs.budget_status?.utilization >= 75 && costs.budget_status?.utilization < 90,
                    'bg-red-600': costs.budget_status?.utilization >= 90
                }"
                :style="'width: ' + (costs.budget_status?.utilization || 0) + '%'"></div>
        </div>

        <!-- Budget Alert -->
        <div x-show="costs.budget_status?.utilization >= 90" class="mt-3 rounded-md bg-red-50 p-3 dark:bg-red-900/20">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
                <p class="ml-3 text-sm text-red-800 dark:text-red-200">
                    Warning: You've used <span x-text="(costs.budget_status?.utilization || 0).toFixed(1) + '%'"></span>
                    of your monthly budget!
                </p>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown by Provider -->
    <div class="mt-6">
        <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Cost by Provider</h4>
        <div class="space-y-3">
            <template x-for="(provider, name) in costs.by_provider" :key="name">
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-900/50">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white dark:bg-gray-800">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                                x-text="name.substring(0, 2).toUpperCase()"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="provider.name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="provider.requests"></span> requests
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white"
                            x-text="'$' + (provider.cost || 0).toFixed(4)"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"
                            x-text="(provider.percentage || 0).toFixed(1) + '%'"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Cost Breakdown by Tool -->
    <div class="mt-6">
        <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Top MCP Tools by Cost</h4>
        <div class="space-y-2">
            <template x-for="(tool, index) in costs.top_tools" :key="index">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            x-text="index + 1"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="tool.name"></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="tool.calls + ' calls'"></span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white"
                            x-text="'$' + (tool.cost || 0).toFixed(4)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Cost Optimization Recommendations -->
    <div x-show="costs.recommendations && costs.recommendations.length > 0" class="mt-6">
        <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Cost Optimization Tips</h4>
        <div class="space-y-2">
            <template x-for="(rec, index) in costs.recommendations" :key="index">
                <div class="flex items-start space-x-2 rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-blue-900 dark:text-blue-200" x-text="rec.message"></p>
                </div>
            </template>
        </div>
    </div>
</div>
