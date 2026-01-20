@props([
    'refreshInterval' => 10000, // 10 seconds
])

<div class="tool-execution-monitor bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6" x-data="toolExecutionMonitor({
    refreshInterval: {{ $refreshInterval }}
})">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            MCP Tool Execution Monitor
        </h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="lastUpdated"></span>
            <button @click="refresh()"
                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-4" aria-label="Tabs">
            <button @click="activeTab = 'active'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === 'active'
                    ?
                    'border-primary-500 text-primary-600 dark:text-primary-400' :
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Active Tools
                <span x-show="toolData?.active_tools?.length > 0"
                    class="ml-2 px-2 py-0.5 text-xs rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200"
                    x-text="toolData?.active_tools?.length || 0">
                </span>
            </button>
            <button @click="activeTab = 'recent'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === 'recent'
                    ?
                    'border-primary-500 text-primary-600 dark:text-primary-400' :
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Recent Executions
            </button>
            <button @click="activeTab = 'statistics'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === 'statistics'
                    ?
                    'border-primary-500 text-primary-600 dark:text-primary-400' :
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Statistics
            </button>
        </nav>
    </div>

    {{-- Active Tools Tab --}}
    <div x-show="activeTab === 'active'" class="space-y-3">
        <div x-show="!toolData?.active_tools || toolData.active_tools.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">No active tool executions</p>
        </div>

        <template x-for="tool in toolData?.active_tools || []" :key="tool.tool_name + tool.started_at">
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="animate-pulse w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100"
                            x-text="tool.tool_name"></span>
                    </div>
                    <span
                        class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200"
                        x-text="tool.status"></span>
                </div>
                <div class="flex items-center gap-4 text-xs text-blue-800 dark:text-blue-200">
                    <div>
                        <span>Server:</span>
                        <span class="font-medium ml-1" x-text="tool.server"></span>
                    </div>
                    <div>
                        <span>Started:</span>
                        <span class="font-medium ml-1" x-text="formatTime(tool.started_at)"></span>
                    </div>
                    <div x-show="tool.execution_time">
                        <span>Duration:</span>
                        <span class="font-medium ml-1" x-text="tool.execution_time.toFixed(2) + 's'"></span>
                    </div>
                </div>
                <div x-show="tool.input_summary" class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                    <span class="font-medium">Input:</span>
                    <span x-text="tool.input_summary"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Recent Executions Tab --}}
    <div x-show="activeTab === 'recent'" class="space-y-2">
        <div x-show="!toolData?.recent_executions || toolData.recent_executions.length === 0" class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">No recent executions</p>
        </div>

        <template x-for="execution in toolData?.recent_executions || []"
            :key="execution.tool_name + execution.completed_at">
            <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-2 h-2 rounded-full" :class="execution.success ? 'bg-green-500' : 'bg-red-500'">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white"
                                x-text="execution.tool_name"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400"
                                x-text="'(' + execution.server + ')'"></span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400"
                            x-text="formatTime(execution.completed_at)"></div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-600 dark:text-gray-400"
                        x-text="execution.execution_time.toFixed(2) + 's'"></span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                        :class="execution.success ?
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                        x-text="execution.status">
                    </span>
                </div>
            </div>
        </template>
    </div>

    {{-- Statistics Tab --}}
    <div x-show="activeTab === 'statistics'" class="space-y-4">
        <div x-show="!toolData?.tool_statistics || Object.keys(toolData.tool_statistics).length === 0"
            class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">No statistics available</p>
        </div>

        <template x-for="(stats, toolName) in toolData?.tool_statistics || {}" :key="toolName">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white" x-text="toolName"></h4>
                    <span class="text-xs px-2 py-1 rounded-full"
                        :class="{
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': stats.success_rate >=
                                90,
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': stats
                                .success_rate >= 70 && stats.success_rate < 90,
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': stats.success_rate < 70
                        }"
                        x-text="stats.success_rate.toFixed(1) + '%'">
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-xs mb-1">Total</div>
                        <div class="font-semibold text-gray-900 dark:text-white" x-text="stats.total_executions">
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-xs mb-1">Successful</div>
                        <div class="font-semibold text-green-600 dark:text-green-400"
                            x-text="stats.successful_executions"></div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-xs mb-1">Failed</div>
                        <div class="font-semibold text-red-600 dark:text-red-400" x-text="stats.failed_executions">
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 text-xs mb-1">Avg Time</div>
                        <div class="font-semibold text-gray-900 dark:text-white"
                            x-text="stats.average_execution_time.toFixed(2) + 's'"></div>
                    </div>
                </div>

                {{-- Success Rate Bar --}}
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-300"
                            :class="{
                                'bg-green-500': stats.success_rate >= 90,
                                'bg-yellow-500': stats.success_rate >= 70 && stats.success_rate < 90,
                                'bg-red-500': stats.success_rate < 70
                            }"
                            :style="'width: ' + stats.success_rate + '%'">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    function toolExecutionMonitor(config) {
        return {
            toolData: null,
            loading: false,
            lastUpdated: 'Never',
            activeTab: 'active',
            refreshInterval: config.refreshInterval,
            intervalId: null,

            init() {
                this.fetchToolData();
                this.startAutoRefresh();
            },

            async fetchToolData() {
                this.loading = true;

                try {
                    const response = await fetch('/api/ai/chat/tool-usage', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.toolData = data.data;
                        this.lastUpdated = new Date().toLocaleTimeString();
                    }
                } catch (error) {
                    console.error('Failed to fetch tool execution data:', error);
                } finally {
                    this.loading = false;
                }
            },

            async refresh() {
                await this.fetchToolData();
            },

            startAutoRefresh() {
                this.intervalId = setInterval(() => {
                    this.fetchToolData();
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
