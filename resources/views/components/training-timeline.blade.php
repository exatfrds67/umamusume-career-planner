{{--
Component: TrainingTimeline
Purpose: Turn-by-turn training progression visualization

Props:
  - character (object): Character with current training data
  - totalTurns (int): Total training turns available
  - currentTurn (int): Current active turn

Usage:
  <x-training-timeline :character="$character" :totalTurns="78" :currentTurn="15" />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'character' => null,
    'totalTurns' => 1,
    'currentTurn' => 1,
])

<div x-data="trainingTimeline()" 
     x-init="totalTurns = {{ $totalTurns }}; currentTurn = {{ $currentTurn }}; loadTurns()"
     class="space-y-4">
    
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Training Progress</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Turn <span x-text="currentTurn"></span> of <span x-text="totalTurns"></span>
            </p>
        </div>
        <div class="text-right">
            <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-900/20 px-3 py-1 rounded-lg">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="`${progressPercentage}% Complete`"></span>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div class="absolute h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-300"
             :style="`width: ${progressPercentage}%`">
        </div>
    </div>

    {{-- Navigation Controls --}}
    <div class="flex items-center justify-between gap-4">
        <button
            type="button"
            @click="prevTurn()"
            :disabled="!canGoBackward"
            class="
                px-4 py-2 rounded-lg font-medium
                bg-white dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                text-gray-700 dark:text-gray-300
                hover:bg-gray-50 dark:hover:bg-gray-700
                disabled:opacity-50 disabled:cursor-not-allowed
                transition-colors
                focus:outline-none focus:ring-2 focus:ring-blue-500
            "
            aria-label="Previous turn"
        >
            <svg class="w-5 h-5 inline-block -ml-1 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
        </button>

        {{-- Turn Indicator --}}
        <div class="flex-1 text-center">
            <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="currentTurn"></span>
            <span class="text-gray-500 dark:text-gray-400"> / </span>
            <span class="text-2xl font-bold text-gray-500 dark:text-gray-400" x-text="totalTurns"></span>
        </div>

        <button
            type="button"
            @click="nextTurn()"
            :disabled="!canGoForward"
            class="
                px-4 py-2 rounded-lg font-medium
                bg-blue-600 hover:bg-blue-700
                text-white
                disabled:opacity-50 disabled:cursor-not-allowed
                transition-colors
                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                dark:focus:ring-offset-gray-900
            "
            aria-label="Next turn"
        >
            Next
            <svg class="w-5 h-5 inline-block ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Swipe Hint --}}
    <div class="text-center text-xs text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m0 0l4 4m10 0v4m0 0l4-4m0 0l-4-4" />
            </svg>
            Swipe or use buttons to navigate turns
        </span>
    </div>

    {{-- Turn Details Card --}}
    <template x-if="currentTurnData">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            
            {{-- Condition Badge --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Condition</span>
                <span class="inline-flex items-center gap-2 
                    px-3 py-1 rounded-full text-sm font-semibold
                    capitalize"
                    :class="getConditionColor(currentTurnData.condition)"
                >
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <span x-text="currentTurnData.condition"></span>
                </span>
            </div>

            {{-- Stats Gains --}}
            <div class="space-y-3">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Stat Gains</h4>
                <div class="grid grid-cols-5 gap-2">
                    <template x-for="(stat, value) in currentTurnData.stats" :key="stat">
                        <div class="flex flex-col items-center p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 capitalize mb-1" x-text="stat"></span>
                            <span class="text-lg font-bold transition-all"
                                :class="getStatChangeColor(value)"
                                x-text="value >= 0 ? `+${value}` : value"
                            ></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Energy Status --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Energy</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="`${currentTurnData.energy}/100`"></span>
                </div>
                <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-500 to-emerald-500"
                         :style="`width: ${currentTurnData.energy}%`">
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</span>
                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold"
                    :class="getTurnStatusColor(currentTurnData)"
                    x-text="getTurnStatusText(currentTurnData)"
                ></span>
            </div>
        </div>
    </template>

    {{-- Upcoming Events --}}
    <template x-if="upcomingEventsCount > 0">
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h4 class="font-semibold text-amber-900 dark:text-amber-200">Upcoming Events</h4>
                    <p class="text-sm text-amber-800 dark:text-amber-300 mt-1">
                        <span x-text="`${upcomingEventsCount} turn${upcomingEventsCount !== 1 ? 's' : ''} with events ahead`"></span>
                    </p>
                </div>
            </div>
        </div>
    </template>
</div>
