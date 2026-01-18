{{-- Agent Performance Dashboard Tab --}}
<div class="space-y-6">
    {{-- Performance Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-green-100 text-sm font-medium">Total SP Saved</p>
                    <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold" x-text="agentPerformance?.total_sp_saved || 0"></p>
                <p class="text-green-100 text-xs mt-1">Through AI optimization</p>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-blue-100 text-sm font-medium">Recommendations</p>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold" x-text="agentPerformance?.total_recommendations || 0"></p>
                <p class="text-blue-100 text-xs mt-1">AI suggestions provided</p>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-purple-100 text-sm font-medium">Success Rate</p>
                    <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold" x-text="`${agentPerformance?.success_rate || 0}%`"></p>
                <p class="text-purple-100 text-xs mt-1">Recommendations followed</p>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-orange-100 text-sm font-medium">Avg Response Time</p>
                    <svg class="w-8 h-8 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold" x-text="`${agentPerformance?.avg_response_time || 0}s`"></p>
                <p class="text-orange-100 text-xs mt-1">Agent processing speed</p>
            </div>
        </div>
    </div>

    {{-- Agent Activity Timeline --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Agent Activity Timeline</h3>
        </div>
        <div class="card-body">
            <div x-show="!agentPerformance?.activities || agentPerformance.activities.length === 0"
                class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No agent activity recorded yet</p>
            </div>

            <div x-show="agentPerformance?.activities && agentPerformance.activities.length > 0" class="space-y-4">
                <template x-for="activity in agentPerformance.activities" :key="activity.id">
                    <div class="flex gap-4">
                        {{-- Timeline Indicator --}}
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                :class="{
                                    'bg-green-100 dark:bg-green-900': activity.type === 'success',
                                    'bg-blue-100 dark:bg-blue-900': activity.type === 'recommendation',
                                    'bg-yellow-100 dark:bg-yellow-900': activity.type === 'warning',
                                    'bg-purple-100 dark:bg-purple-900': activity.type === 'optimization'
                                }">
                                <svg class="w-5 h-5"
                                    :class="{
                                        'text-green-600 dark:text-green-400': activity.type === 'success',
                                        'text-blue-600 dark:text-blue-400': activity.type === 'recommendation',
                                        'text-yellow-600 dark:text-yellow-400': activity.type === 'warning',
                                        'text-purple-600 dark:text-purple-400': activity.type === 'optimization'
                                    }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-2"></div>
                        </div>

                        {{-- Activity Content --}}
                        <div class="flex-1 pb-8">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white" x-text="activity.title">
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                            x-text="activity.timestamp"></p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': activity
                                                .type === 'success',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': activity
                                                .type === 'recommendation',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': activity
                                                .type === 'warning',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': activity
                                                .type === 'optimization'
                                        }"
                                        x-text="activity.agent_name"></span>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"
                                    x-text="activity.description"></p>

                                {{-- Activity Metrics --}}
                                <div x-show="activity.metrics" class="grid grid-cols-3 gap-3">
                                    <div x-show="activity.metrics?.sp_saved">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">SP Saved</p>
                                        <p class="text-sm font-semibold text-green-600 dark:text-green-400"
                                            x-text="activity.metrics.sp_saved"></p>
                                    </div>
                                    <div x-show="activity.metrics?.efficiency">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Efficiency</p>
                                        <p class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                                            x-text="`${activity.metrics.efficiency}%`"></p>
                                    </div>
                                    <div x-show="activity.metrics?.processing_time">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Processing Time</p>
                                        <p class="text-sm font-semibold text-purple-600 dark:text-purple-400"
                                            x-text="`${activity.metrics.processing_time}s`"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Agent Performance by Type --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Skill Analysis Agent --}}
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-header border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Skill Analysis Agent</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Analyses</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"
                            x-text="agentPerformance?.agents?.skill_analysis?.total || 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Avg Accuracy</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400"
                            x-text="`${agentPerformance?.agents?.skill_analysis?.accuracy || 0}%`"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">SP Optimized</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400"
                            x-text="agentPerformance?.agents?.skill_analysis?.sp_optimized || 0"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hint Optimization Agent --}}
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-header border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hint Optimization Agent</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Hints Optimized</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"
                            x-text="agentPerformance?.agents?.hint_optimization?.total || 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Avg Discount</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400"
                            x-text="`${agentPerformance?.agents?.hint_optimization?.avg_discount || 0}%`"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total SP Saved</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400"
                            x-text="agentPerformance?.agents?.hint_optimization?.sp_saved || 0"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Evolution Planning Agent --}}
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-header border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Evolution Planning Agent</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Evolutions Planned</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"
                            x-text="agentPerformance?.agents?.evolution_planning?.total || 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Success Rate</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400"
                            x-text="`${agentPerformance?.agents?.evolution_planning?.success_rate || 0}%`"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Efficiency Gain</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400"
                            x-text="`${agentPerformance?.agents?.evolution_planning?.efficiency_gain || 0}%`"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Build Planning Agent --}}
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-header border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Build Planning Agent</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Builds Created</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"
                            x-text="agentPerformance?.agents?.build_planning?.total || 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Avg Synergy Score</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400"
                            x-text="`${agentPerformance?.agents?.build_planning?.avg_synergy || 0}/10`"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Meta Alignment</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400"
                            x-text="`${agentPerformance?.agents?.build_planning?.meta_alignment || 0}%`"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cost Savings Chart --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SP Savings Over Time</h3>
        </div>
        <div class="card-body">
            <div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
                <p>Chart visualization would go here</p>
            </div>
        </div>
    </div>

    {{-- Agent Recommendations Impact --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recommendation Impact Analysis</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Followed Recommendations</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Recommendations you acted on</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400"
                            x-text="agentPerformance?.recommendations?.followed || 0"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total SP Saved: <span
                                class="font-semibold"
                                x-text="agentPerformance?.recommendations?.sp_saved || 0"></span></p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Pending Recommendations</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Recommendations awaiting action</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"
                            x-text="agentPerformance?.recommendations?.pending || 0"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Potential Savings: <span
                                class="font-semibold"
                                x-text="agentPerformance?.recommendations?.potential_savings || 0"></span></p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Ignored Recommendations</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Recommendations not followed</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-gray-600 dark:text-gray-400"
                            x-text="agentPerformance?.recommendations?.ignored || 0"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Missed Savings: <span
                                class="font-semibold"
                                x-text="agentPerformance?.recommendations?.missed_savings || 0"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
