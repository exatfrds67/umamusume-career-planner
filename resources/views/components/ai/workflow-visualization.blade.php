@props(['workflow' => null])

<div class="workflow-visualization" x-data="workflowVisualization()" x-init="initialize()">

    {{-- Current Workflow --}}
    <template x-if="currentWorkflow">
        <div class="space-y-4">
            {{-- Workflow Header --}}
            <div
                class="flex items-center justify-between p-3 bg-linear-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white" x-text="currentWorkflow.name"></span>
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium"
                    :class="{
                        'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': currentWorkflow
                            .status === 'running',
                        'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300': currentWorkflow
                            .status === 'completed',
                        'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300': currentWorkflow
                            .status === 'failed',
                        'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': currentWorkflow
                            .status === 'pending'
                    }"
                    x-text="currentWorkflow.status"></span>
            </div>

            {{-- Workflow Steps --}}
            <div class="space-y-2">
                <template x-for="(step, index) in currentWorkflow.steps" :key="step.id">
                    <div class="relative">
                        {{-- Connection Line --}}
                        <template x-if="index < currentWorkflow.steps.length - 1">
                            <div class="absolute left-4 top-10 w-0.5 h-full bg-gray-200 dark:bg-gray-700"
                                :class="{ 'bg-blue-500': step.status === 'completed' }"></div>
                        </template>

                        {{-- Step Card --}}
                        <div class="flex items-start gap-3 p-3 bg-white dark:bg-gray-700 rounded-lg border"
                            :class="{
                                'border-blue-500 shadow-xs': step.status === 'running',
                                'border-green-500': step.status === 'completed',
                                'border-red-500': step.status === 'failed',
                                'border-gray-200 dark:border-gray-600': step.status === 'pending'
                            }">

                            {{-- Step Icon --}}
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                :class="{
                                    'bg-blue-100 dark:bg-blue-900': step.status === 'running',
                                    'bg-green-100 dark:bg-green-900': step.status === 'completed',
                                    'bg-red-100 dark:bg-red-900': step.status === 'failed',
                                    'bg-gray-100 dark:bg-gray-700': step.status === 'pending'
                                }">
                                <template x-if="step.status === 'running'">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="step.status === 'completed'">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template x-if="step.status === 'failed'">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template x-if="step.status === 'pending'">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        x-text="index + 1"></span>
                                </template>
                            </div>

                            {{-- Step Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-sm text-gray-900 dark:text-white"
                                        x-text="step.name"></span>
                                    <template x-if="step.duration">
                                        <span class="text-xs text-gray-500 dark:text-gray-400"
                                            x-text="`${step.duration}s`"></span>
                                    </template>
                                </div>

                                <template x-if="step.agent">
                                    <div class="flex items-center gap-1 mb-1">
                                        <span
                                            class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full"
                                            x-text="step.agent"></span>
                                    </div>
                                </template>

                                <template x-if="step.description">
                                    <p class="text-xs text-gray-600 dark:text-gray-400" x-text="step.description"></p>
                                </template>

                                {{-- Step Progress --}}
                                <template x-if="step.status === 'running' && step.progress">
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                            <span>Progress</span>
                                            <span x-text="`${step.progress}%`"></span>
                                        </div>
                                        <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 transition-all duration-300"
                                                :style="`width: ${step.progress}%`"></div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Error Message --}}
                                <template x-if="step.status === 'failed' && step.error">
                                    <div
                                        class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded text-xs text-red-700 dark:text-red-300">
                                        <span x-text="step.error"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Workflow Summary --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-3 gap-4 text-center text-xs">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Total Steps</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white"
                            x-text="currentWorkflow.steps.length"></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Completed</div>
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400" x-text="completedSteps">
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Duration</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" x-text="totalDuration + 's'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- No Active Workflow --}}
    <template x-if="!currentWorkflow">
        <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p>No active workflow</p>
            <p class="text-xs mt-1">Agent workflows will appear here when processing complex requests</p>
        </div>
    </template>
</div>

{{-- JS extracted to resources/js/components/ai/workflow-visualization.js --}}
@pushOnce('scripts')
    @vite('resources/js/components/ai/workflow-visualization.js')
@endPushOnce
