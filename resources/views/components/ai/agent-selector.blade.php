@props(['selectedAgent' => null])

<div class="agent-selector space-y-4" x-data="agentSelector()" x-init="initialize()">

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Select Specialized Agent
        </label>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Choose a specialized agent for domain-specific advice
        </p>
    </div>

    {{-- Agent Options --}}
    <div class="space-y-2">
        {{-- General Agent --}}
        <button @click="selectAgent(null)" class="w-full flex items-start gap-3 p-3 rounded-lg border-2 transition-all"
            :class="selectedAgent === null ?
                'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-gray-900 dark:text-white">General Assistant</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    General career planning advice and strategy
                </div>
            </div>
            <template x-if="selectedAgent === null">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </template>
        </button>

        {{-- Training Agent --}}
        <button @click="selectAgent('training')"
            class="w-full flex items-start gap-3 p-3 rounded-lg border-2 transition-all"
            :class="selectedAgent === 'training'
                ?
                'border-blue-500 bg-blue-50 dark:bg-blue-900/20' :
                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-gray-900 dark:text-white">Training Specialist</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Optimize training sessions, stat gains, and Spirit Burst mechanics
                </div>
            </div>
            <template x-if="selectedAgent === 'training'">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </template>
        </button>

        {{-- Career Agent --}}
        <button @click="selectAgent('career')"
            class="w-full flex items-start gap-3 p-3 rounded-lg border-2 transition-all"
            :class="selectedAgent === 'career'
                ?
                'border-green-500 bg-green-50 dark:bg-green-900/20' :
                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-gray-900 dark:text-white">Career Strategist</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Long-term career planning, goal setting, and progression analysis
                </div>
            </div>
            <template x-if="selectedAgent === 'career'">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </template>
        </button>

        {{-- Race Agent --}}
        <button @click="selectAgent('race')"
            class="w-full flex items-start gap-3 p-3 rounded-lg border-2 transition-all"
            :class="selectedAgent === 'race'
                ?
                'border-purple-500 bg-purple-50 dark:bg-purple-900/20' :
                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-gray-900 dark:text-white">Race Strategist</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Race preparation, strategy selection, and performance optimization
                </div>
            </div>
            <template x-if="selectedAgent === 'race'">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </template>
        </button>

        {{-- Skill Agent --}}
        <button @click="selectAgent('skill')"
            class="w-full flex items-start gap-3 p-3 rounded-lg border-2 transition-all"
            :class="selectedAgent === 'skill'
                ?
                'border-orange-500 bg-orange-50 dark:bg-orange-900/20' :
                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-gray-900 dark:text-white">Skill Optimizer</div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Skill builds, SP optimization, and hint collection strategies
                </div>
            </div>
            <template x-if="selectedAgent === 'skill'">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </template>
        </button>
    </div>

    {{-- Agent Status --}}
    <div x-show="selectedAgent"
        class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2 text-sm">
            <div class="flex items-center gap-1">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-gray-700 dark:text-gray-300">Agent Active</span>
            </div>
        </div>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
            This agent will provide specialized advice based on your selected domain.
        </p>
    </div>
</div>

{{-- Alpine component logic moved to resources/js/components/ai/agent-selector.js --}}
@once
    @vite(['resources/js/components/ai/agent-selector.js'])
@endonce
