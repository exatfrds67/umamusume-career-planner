{{-- Agent Performance Tab --}}
<div class="space-y-6">
    {{-- Performance Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-body text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">Total SP Saved</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400"
                    x-text="agentPerformance?.total_sp_saved || 0">
                </p>
            </div>
        </div>
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-body text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">Recommendations</p>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400"
                    x-text="agentPerformance?.total_recommendations || 0">
                </p>
            </div>
        </div>
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-body text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">Success Rate</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"
                    x-text="`${agentPerformance?.success_rate || 0}%`">
                </p>
            </div>
        </div>
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-body text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">Avg Response Time</p>
                <p class="text-3xl font-bold text-neutral-900 dark:text-white"
                    x-text="`${agentPerformance?.avg_response_time || 0}s`">
                </p>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card bg-white dark:bg-neutral-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Recent Agent Activity</h3>
        </div>
        <div class="card-body">
            <div x-show="!agentPerformance?.activities || agentPerformance.activities.length === 0"
                class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                No recent activity
            </div>
            <div x-show="agentPerformance?.activities && agentPerformance.activities.length > 0" class="space-y-3">
                <template x-for="activity in agentPerformance.activities" :key="activity.id">
                    <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                            :class="{
                                'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400': activity
                                    .type === 'success',
                                'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400': activity
                                    .type === 'info',
                                'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400': activity
                                    .type === 'warning'
                            }">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white" x-text="activity.message"></p>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400" x-text="activity.timestamp"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Agent Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Skill Analysis Agent --}}
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Skill Analysis Agent</h3>
            </div>
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Analyses</span>
                    <span class="font-semibold text-neutral-900 dark:text-white"
                        x-text="agentPerformance?.agents?.skill_analysis?.total || 0">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Accuracy</span>
                    <span class="font-semibold text-green-600 dark:text-green-400"
                        x-text="`${agentPerformance?.agents?.skill_analysis?.accuracy || 0}%`">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">SP Optimized</span>
                    <span class="font-semibold text-primary-600 dark:text-primary-400"
                        x-text="agentPerformance?.agents?.skill_analysis?.sp_optimized || 0">
                    </span>
                </div>
            </div>
        </div>

        {{-- Hint Optimization Agent --}}
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Hint Optimization Agent</h3>
            </div>
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Optimizations</span>
                    <span class="font-semibold text-neutral-900 dark:text-white"
                        x-text="agentPerformance?.agents?.hint_optimization?.total || 0">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Avg Discount</span>
                    <span class="font-semibold text-green-600 dark:text-green-400"
                        x-text="`${agentPerformance?.agents?.hint_optimization?.avg_discount || 0}%`">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">SP Saved</span>
                    <span class="font-semibold text-primary-600 dark:text-primary-400"
                        x-text="agentPerformance?.agents?.hint_optimization?.sp_saved || 0">
                    </span>
                </div>
            </div>
        </div>

        {{-- Evolution Planning Agent --}}
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Evolution Planning Agent</h3>
            </div>
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Plans</span>
                    <span class="font-semibold text-neutral-900 dark:text-white"
                        x-text="agentPerformance?.agents?.evolution_planning?.total || 0">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Success Rate</span>
                    <span class="font-semibold text-green-600 dark:text-green-400"
                        x-text="`${agentPerformance?.agents?.evolution_planning?.success_rate || 0}%`">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Efficiency Gain</span>
                    <span class="font-semibold text-primary-600 dark:text-primary-400"
                        x-text="`${agentPerformance?.agents?.evolution_planning?.efficiency_gain || 0}%`">
                    </span>
                </div>
            </div>
        </div>

        {{-- Build Planning Agent --}}
        <div class="card bg-white dark:bg-neutral-800">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Build Planning Agent</h3>
            </div>
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Total Builds</span>
                    <span class="font-semibold text-neutral-900 dark:text-white"
                        x-text="agentPerformance?.agents?.build_planning?.total || 0">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Avg Synergy</span>
                    <span class="font-semibold text-green-600 dark:text-green-400"
                        x-text="`${agentPerformance?.agents?.build_planning?.avg_synergy || 0}/10`">
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Meta Alignment</span>
                    <span class="font-semibold text-primary-600 dark:text-primary-400"
                        x-text="`${agentPerformance?.agents?.build_planning?.meta_alignment || 0}%`">
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Recommendation Impact --}}
    <div class="card bg-white dark:bg-neutral-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Recommendation Impact</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <p class="text-sm text-green-700 dark:text-green-300 mb-1">Followed</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400"
                        x-text="agentPerformance?.recommendations?.followed || 0">
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        Saved: <span x-text="agentPerformance?.recommendations?.sp_saved || 0"></span> SP
                    </p>
                </div>
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-1">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"
                        x-text="agentPerformance?.recommendations?.pending || 0">
                    </p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                        Potential: <span x-text="agentPerformance?.recommendations?.potential_savings || 0"></span> SP
                    </p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 mb-1">Ignored</p>
                    <p class="text-2xl font-bold text-neutral-600 dark:text-neutral-400"
                        x-text="agentPerformance?.recommendations?.ignored || 0">
                    </p>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                        Missed: <span x-text="agentPerformance?.recommendations?.missed_savings || 0"></span> SP
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
