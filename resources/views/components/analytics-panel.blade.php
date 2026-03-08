{{-- Phase 6: Analytics Dashboard Component --}}
<div
    x-data="analyticsPanel()"
    @analytics-exported="$dispatch('notification-show', {message: 'Analytics exported successfully', type: 'success'})"
    class="space-y-6"
>
    <!-- Header with Controls -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Analytics</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Plan statistics and insights</p>
        </div>
        <button
            @click="exportAnalytics()"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition"
        >
            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Export
        </button>
    </div>

    <!-- Date Range Filters -->
    <div class="flex gap-2">
        <button
            @click="setDateRange('all')"
            :class="dateRange === 'all' ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
        >
            All Time
        </button>
        <button
            @click="setDateRange('week')"
            :class="dateRange === 'week' ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
        >
            This Week
        </button>
        <button
            @click="setDateRange('month')"
            :class="dateRange === 'month' ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
        >
            This Month
        </button>
        <button
            @click="setDateRange('year')"
            :class="dateRange === 'year' ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
        >
            This Year
        </button>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Plans -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Total Plans</p>
            <p class="text-3xl font-bold text-neutral-900 dark:text-white" x-text="totalPlans"></p>
        </div>

        <!-- Total Wins -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Total Wins</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400" x-text="totalWins"></p>
        </div>

        <!-- Win Rate -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Win Rate</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                <span x-text="winRate"></span>%
            </p>
        </div>

        <!-- Favorites -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Favorites</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400" x-text="totalFavorites"></p>
        </div>
    </div>

    <!-- Character Statistics -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Character Statistics</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="text-left py-2 px-3 text-neutral-600 dark:text-neutral-400">Character</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Plans</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Wins</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Win Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    <template x-for="stat in characterStats.slice(0, 10)" :key="stat.name">
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                            <td class="py-2 px-3 text-neutral-900 dark:text-white" x-text="stat.name"></td>
                            <td class="py-2 px-3 text-center text-neutral-700 dark:text-neutral-300" x-text="stat.count"></td>
                            <td class="py-2 px-3 text-center text-green-600 dark:text-green-400 font-medium" x-text="stat.wins"></td>
                            <td class="py-2 px-3 text-center text-neutral-700 dark:text-neutral-300">
                                <span x-text="stat.winRate"></span>%
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <template x-if="characterStats.length === 0">
            <p class="text-center text-neutral-500 dark:text-neutral-400 py-4">No data available</p>
        </template>
    </div>

    <!-- Scenario Statistics -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-neutral-200 dark:border-neutral-700">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Scenario Statistics</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="text-left py-2 px-3 text-neutral-600 dark:text-neutral-400">Scenario</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Plans</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Wins</th>
                        <th class="text-center py-2 px-3 text-neutral-600 dark:text-neutral-400">Win Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    <template x-for="stat in scenarioStats" :key="stat.name">
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                            <td class="py-2 px-3 text-neutral-900 dark:text-white" x-text="stat.name"></td>
                            <td class="py-2 px-3 text-center text-neutral-700 dark:text-neutral-300" x-text="stat.count"></td>
                            <td class="py-2 px-3 text-center text-green-600 dark:text-green-400 font-medium" x-text="stat.wins"></td>
                            <td class="py-2 px-3 text-center text-neutral-700 dark:text-neutral-300">
                                <span x-text="stat.winRate"></span>%
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <template x-if="scenarioStats.length === 0">
            <p class="text-center text-neutral-500 dark:text-neutral-400 py-4">No data available</p>
        </template>
    </div>
</div>
