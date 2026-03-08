@props(['performance' => []])

<div class="rounded-lg border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Performance Metrics</h3>
        <div class="flex items-center space-x-2">
            <select x-model="performanceTimeRange"
                @change="$dispatch('update-performance-range', { range: performanceTimeRange })"
                class="rounded-md border-neutral-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                <option value="1h">Last Hour</option>
                <option value="24h" selected>Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
            </select>
            <button @click="$dispatch('refresh-performance')"
                class="rounded-md p-2 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                title="Refresh Performance">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Provider Comparison Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-900">
                <tr>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Provider
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Requests
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Success Rate
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Avg Response
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        P95 Response
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Cost/Request
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                        Rating
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                <template x-for="(provider, name) in performance.providers" :key="name">
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                        <!-- Provider Name -->
                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="flex items-center">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full"
                                    :class="{
                                        'bg-blue-100 dark:bg-blue-900': name === 'ollama',
                                        'bg-purple-100 dark:bg-purple-900': name === 'bedrock',
                                        'bg-green-100 dark:bg-green-900': name === 'agents'
                                    }">
                                    <span class="text-xs font-semibold"
                                        :class="{
                                            'text-blue-600 dark:text-blue-300': name === 'ollama',
                                            'text-purple-600 dark:text-purple-300': name === 'bedrock',
                                            'text-green-600 dark:text-green-300': name === 'agents'
                                        }"
                                        x-text="name.substring(0, 2).toUpperCase()"></span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white" x-text="provider.name">
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400" x-text="provider.model"></p>
                                </div>
                            </div>
                        </td>

                        <!-- Requests -->
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-neutral-700 dark:text-neutral-300"
                            x-text="provider.requests"></td>

                        <!-- Success Rate -->
                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="flex items-center">
                                <span class="text-sm font-medium"
                                    :class="{
                                        'text-green-600 dark:text-green-400': provider.success_rate >= 95,
                                        'text-yellow-600 dark:text-yellow-400': provider.success_rate >= 85 && provider
                                            .success_rate < 95,
                                        'text-red-600 dark:text-red-400': provider.success_rate < 85
                                    }"
                                    x-text="provider.success_rate.toFixed(1) + '%'"></span>
                            </div>
                        </td>

                        <!-- Avg Response Time -->
                        <td class="whitespace-nowrap px-4 py-4">
                            <span class="text-sm"
                                :class="{
                                    'text-green-600 dark:text-green-400': provider.avg_response_time < 2,
                                    'text-yellow-600 dark:text-yellow-400': provider.avg_response_time >= 2 && provider
                                        .avg_response_time < 5,
                                    'text-red-600 dark:text-red-400': provider.avg_response_time >= 5
                                }"
                                x-text="provider.avg_response_time.toFixed(2) + 's'"></span>
                        </td>

                        <!-- P95 Response Time -->
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-neutral-700 dark:text-neutral-300"
                            x-text="provider.p95_response_time.toFixed(2) + 's'"></td>

                        <!-- Cost per Request -->
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-neutral-700 dark:text-neutral-300"
                            x-text="'$' + provider.cost_per_request.toFixed(6)"></td>

                        <!-- Rating -->
                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="flex items-center">
                                <template x-for="i in 5" :key="i">
                                    <svg class="h-4 w-4"
                                        :class="{
                                            'text-yellow-400': i <= provider.rating,
                                            'text-neutral-300 dark:text-neutral-600': i > provider.rating
                                        }"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Performance Insights -->
    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Fastest Provider -->
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <p class="text-sm font-medium text-green-900 dark:text-green-200">Fastest Provider</p>
            </div>
            <p class="mt-2 text-lg font-bold text-green-600 dark:text-green-400"
                x-text="performance.fastest?.name || 'N/A'"></p>
            <p class="mt-1 text-xs text-green-700 dark:text-green-300"
                x-text="performance.fastest?.avg_response_time ? performance.fastest.avg_response_time.toFixed(2) + 's avg' : ''">
            </p>
        </div>

        <!-- Most Reliable -->
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Most Reliable</p>
            </div>
            <p class="mt-2 text-lg font-bold text-blue-600 dark:text-blue-400"
                x-text="performance.most_reliable?.name || 'N/A'"></p>
            <p class="mt-1 text-xs text-blue-700 dark:text-blue-300"
                x-text="performance.most_reliable?.success_rate ? performance.most_reliable.success_rate.toFixed(1) + '% success' : ''">
            </p>
        </div>

        <!-- Most Cost-Effective -->
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <p class="text-sm font-medium text-purple-900 dark:text-purple-200">Most Cost-Effective</p>
            </div>
            <p class="mt-2 text-lg font-bold text-purple-600 dark:text-purple-400"
                x-text="performance.most_cost_effective?.name || 'N/A'"></p>
            <p class="mt-1 text-xs text-purple-700 dark:text-purple-300"
                x-text="performance.most_cost_effective?.cost_per_request ? '$' + performance.most_cost_effective.cost_per_request.toFixed(6) + '/req' : ''">
            </p>
        </div>
    </div>

    <!-- Performance Recommendations -->
    <div x-show="performance.recommendations && performance.recommendations.length > 0" class="mt-6">
        <h4 class="mb-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Performance Recommendations</h4>
        <div class="space-y-2">
            <template x-for="(rec, index) in performance.recommendations" :key="index">
                <div class="flex items-start space-x-2 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-yellow-900 dark:text-yellow-200" x-text="rec.message"></p>
                </div>
            </template>
        </div>
    </div>
</div>
