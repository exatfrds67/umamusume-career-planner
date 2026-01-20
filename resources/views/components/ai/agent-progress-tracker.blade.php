@props([
    'refreshInterval' => 5000, // 5 seconds for real-time updates
])

<div class="agent-progress-tracker bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6" x-data="agentProgressTracker({
    refreshInterval: {{ $refreshInterval }}
})">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Agent Workflow Progress
        </h3>
        <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 rounded-full font-medium"
                :class="{
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': progress?.status === 'idle',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': progress?.status === 'running',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': progress
                        ?.status === 'completed',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': progress?.status === 'failed'
                }"
                x-text="progress?.status || 'idle'">
            </span>
        </div>
    </div>

    {{-- No Active Workflow --}}
    <div x-show="!progress?.workflow_id" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>
        <p class="text-gray-500 dark:text-gray-400">No active workflow</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Agent workflows will appear here when active</p>
    </div>

    {{-- Active Workflow --}}
    <div x-show="progress?.workflow_id" class="space-y-6">
        {{-- Workflow Info --}}
        <div
            class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900 dark:to-primary-800 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-primary-900 dark:text-primary-100"
                    x-text="progress?.workflow_name || 'Unnamed Workflow'"></h4>
                <span class="text-xs text-primary-700 dark:text-primary-300"
                    x-text="'ID: ' + (progress?.workflow_id || 'N/A')"></span>
            </div>
            <div class="flex items-center gap-4 text-sm text-primary-800 dark:text-primary-200">
                <div>
                    <span class="font-medium" x-text="progress?.completed_steps || 0"></span>
                    <span>/</span>
                    <span x-text="progress?.total_steps || 0"></span>
                    <span class="ml-1">steps</span>
                </div>
                <div x-show="progress?.started_at">
                    <span>Started:</span>
                    <span class="font-medium ml-1" x-text="formatTime(progress?.started_at)"></span>
                </div>
                <div x-show="progress?.estimated_completion">
                    <span>ETA:</span>
                    <span class="font-medium ml-1" x-text="formatTime(progress?.estimated_completion)"></span>
                </div>
            </div>
        </div>

        {{-- Overall Progress Bar --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400"
                    x-text="(progress?.progress_percentage || 0).toFixed(1) + '%'"></span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500 ease-out"
                    :style="'width: ' + (progress?.progress_percentage || 0) + '%'">
                </div>
            </div>
        </div>

        {{-- Current Step --}}
        <div x-show="progress?.current_step"
            class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="animate-pulse w-2 h-2 bg-blue-500 rounded-full"></div>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Current Step</span>
            </div>
            <p class="text-sm text-blue-800 dark:text-blue-200" x-text="progress?.current_step"></p>
        </div>

        {{-- Agent List --}}
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Agents</h4>
            <div class="space-y-2">
                <template x-for="(agent, index) in progress?.agents || []" :key="agent.agent_id">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                {{-- Status Indicator --}}
                                <div class="relative">
                                    <div class="w-3 h-3 rounded-full"
                                        :class="{
                                            'bg-gray-400': agent.status === 'idle',
                                            'bg-blue-500 animate-pulse': agent.status === 'running',
                                            'bg-green-500': agent.status === 'completed',
                                            'bg-red-500': agent.status === 'failed'
                                        }">
                                    </div>
                                    <div x-show="agent.status === 'running'"
                                        class="absolute inset-0 w-3 h-3 bg-blue-500 rounded-full animate-ping opacity-75">
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white capitalize"
                                    x-text="agent.agent_type"></span>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full font-medium"
                                :class="{
                                    'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300': agent
                                        .status === 'idle',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': agent
                                        .status === 'running',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': agent
                                        .status === 'completed',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': agent
                                        .status === 'failed'
                                }"
                                x-text="agent.status">
                            </span>
                        </div>

                        {{-- Agent Progress Bar --}}
                        <div x-show="agent.status === 'running' || agent.status === 'completed'" class="mb-2">
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                                <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                                    :style="'width: ' + (agent.progress || 0) + '%'">
                                </div>
                            </div>
                        </div>

                        {{-- Agent Details --}}
                        <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                            <div x-show="agent.started_at">
                                <span>Started:</span>
                                <span class="ml-1" x-text="formatTime(agent.started_at)"></span>
                            </div>
                            <div x-show="agent.completed_at">
                                <span>Completed:</span>
                                <span class="ml-1" x-text="formatTime(agent.completed_at)"></span>
                            </div>
                            <div x-show="agent.execution_time">
                                <span>Duration:</span>
                                <span class="ml-1" x-text="agent.execution_time.toFixed(2) + 's'"></span>
                            </div>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="agent.error"
                            class="mt-2 p-2 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded text-xs text-red-800 dark:text-red-200">
                            <span class="font-medium">Error:</span>
                            <span x-text="agent.error"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function agentProgressTracker(config) {
        return {
            progress: null,
            loading: false,
            refreshInterval: config.refreshInterval,
            intervalId: null,

            init() {
                this.fetchProgress();
                this.startAutoRefresh();
            },

            async fetchProgress() {
                try {
                    const response = await fetch('/api/ai/chat/workflow-status', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.progress = data.data;
                    }
                } catch (error) {
                    console.error('Failed to fetch agent progress:', error);
                }
            },

            startAutoRefresh() {
                this.intervalId = setInterval(() => {
                    this.fetchProgress();
                }, this.refreshInterval);
            },

            formatTime(timestamp) {
                if (!timestamp) return 'N/A';
                const date = new Date(timestamp);
                return date.toLocaleTimeString();
            },

            destroy() {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
            }
        };
    }
</script>
