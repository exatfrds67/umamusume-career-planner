@props(['agent'])

<div
    class="rounded-lg border border-neutral-200 bg-white p-4 shadow-xs transition-all hover:shadow-md dark:border-neutral-700 dark:bg-neutral-800">
    <div class="flex items-start justify-between">
        <!-- Agent Info -->
        <div class="flex items-start space-x-3">
            <!-- Agent Icon -->
            <div class="flex h-10 w-10 items-center justify-center rounded-full"
                :class="{
                    'bg-blue-100 dark:bg-blue-900': agent.status === 'active',
                    'bg-green-100 dark:bg-green-900': agent.status === 'completed',
                    'bg-yellow-100 dark:bg-yellow-900': agent.status === 'waiting',
                    'bg-red-100 dark:bg-red-900': agent.status === 'failed',
                    'bg-neutral-100 dark:bg-neutral-700': agent.status === 'idle'
                }">
                <svg class="h-5 w-5"
                    :class="{
                        'text-blue-600 dark:text-blue-300': agent.status === 'active',
                        'text-green-600 dark:text-green-300': agent.status === 'completed',
                        'text-yellow-600 dark:text-yellow-300': agent.status === 'waiting',
                        'text-red-600 dark:text-red-300': agent.status === 'failed',
                        'text-neutral-600 dark:text-neutral-400': agent.status === 'idle'
                    }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
            </div>

            <!-- Agent Details -->
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h4 class="font-semibold text-neutral-900 dark:text-white" x-text="agent.name"></h4>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                        :class="{
                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': agent
                                .status === 'active',
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': agent
                                .status === 'completed',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': agent
                                .status === 'waiting',
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': agent.status === 'failed',
                            'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200': agent.status === 'idle'
                        }"
                        x-text="agent.status"></span>
                </div>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400" x-text="agent.type"></p>

                <!-- Current Task -->
                <div x-show="agent.current_task" class="mt-2">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Current Task:</p>
                    <p class="mt-0.5 text-sm text-neutral-700 dark:text-neutral-300" x-text="agent.current_task"></p>
                </div>
            </div>
        </div>

        <!-- Agent Actions -->
        <div class="flex space-x-2">
            <button x-show="agent.status === 'active'" @click="$dispatch('pause-agent', { agentId: agent.id })"
                class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                title="Pause Agent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
            <button @click="$dispatch('view-agent-details', { agentId: agent.id })"
                class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                title="View Details">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Progress Bar (for active agents) -->
    <div x-show="agent.status === 'active' && agent.progress !== undefined" class="mt-4">
        <div class="flex items-center justify-between text-xs text-neutral-600 dark:text-neutral-400">
            <span>Progress</span>
            <span x-text="agent.progress + '%'"></span>
        </div>
        <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
            <div class="h-full bg-blue-600 transition-all duration-300 dark:bg-blue-500"
                :style="'width: ' + (agent.progress || 0) + '%'"></div>
        </div>
    </div>

    <!-- Agent Metrics -->
    <div class="mt-4 grid grid-cols-4 gap-3 border-t border-neutral-200 pt-3 dark:border-neutral-700">
        <!-- Execution Time -->
        <div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Time</p>
            <p class="mt-0.5 text-sm font-semibold text-neutral-900 dark:text-white" x-text="agent.execution_time || '0s'">
            </p>
        </div>

        <!-- Tools Used -->
        <div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Tools</p>
            <p class="mt-0.5 text-sm font-semibold text-neutral-900 dark:text-white" x-text="agent.tools_used || 0"></p>
        </div>

        <!-- Cost -->
        <div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Cost</p>
            <p class="mt-0.5 text-sm font-semibold text-neutral-900 dark:text-white"
                x-text="'$' + (agent.cost || 0).toFixed(4)"></p>
        </div>

        <!-- Confidence -->
        <div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Confidence</p>
            <p class="mt-0.5 text-sm font-semibold"
                :class="{
                    'text-green-600 dark:text-green-400': agent.confidence >= 0.8,
                    'text-yellow-600 dark:text-yellow-400': agent.confidence >= 0.6 && agent.confidence < 0.8,
                    'text-red-600 dark:text-red-400': agent.confidence < 0.6
                }"
                x-text="agent.confidence ? (agent.confidence * 100).toFixed(0) + '%' : 'N/A'"></p>
        </div>
    </div>

    <!-- Workflow Steps (if available) -->
    <div x-show="agent.workflow_steps && agent.workflow_steps.length > 0" class="mt-3">
        <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Workflow Steps:</p>
        <div class="mt-2 space-y-1">
            <template x-for="(step, index) in agent.workflow_steps" :key="index">
                <div class="flex items-center space-x-2 text-xs">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-white"
                        :class="{
                            'bg-green-500': step.status === 'completed',
                            'bg-blue-500': step.status === 'active',
                            'bg-neutral-400': step.status === 'pending'
                        }">
                        <svg x-show="step.status === 'completed'" class="h-3 w-3" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span x-show="step.status !== 'completed'" x-text="index + 1"></span>
                    </span>
                    <span class="text-neutral-700 dark:text-neutral-300" x-text="step.name"></span>
                </div>
            </template>
        </div>
    </div>
</div>
