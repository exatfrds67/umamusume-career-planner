@props(['characterId', 'currentGauge' => 0, 'maxGauge' => 4])

<div x-data="spiritBurstGauge({{ $characterId }}, {{ $currentGauge }}, {{ $maxGauge }})" class="glass-card rounded-xl p-6">
    <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white transition-colors duration-300">
                Spirit Burst Gauge
            </h3>
            <template x-if="gauge >= maxGauge">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 animate-pulse">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
                    </svg>
                    Ready to Trigger!
                </span>
            </template>
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
            Train with teammates 4 times to fill the gauge and trigger massive stat bonuses
        </p>
    </div>

    <!-- Gauge Visual -->
    <div class="mb-6">
        <div class="flex items-center justify-center gap-4 mb-3">
            <template x-for="i in maxGauge" :key="i">
                <div class="relative">
                    <!-- Flame Icon -->
                    <div :class="{
                        'text-6xl': true,
                        'animate-bounce': i <= gauge && gauge >= maxGauge,
                        'scale-110': i <= gauge
                    }"
                        class="transition-all duration-300">
                        <span x-show="i <= gauge" class="text-yellow-500 drop-shadow-lg">🔥</span>
                        <span x-show="i > gauge" class="text-gray-300 dark:text-gray-600">○</span>
                    </div>
                    <!-- Progress Number -->
                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400" x-text="i"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Progress Bar -->
        <div class="relative w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div :style="`width: ${(gauge / maxGauge) * 100}%`"
                class="absolute top-0 left-0 h-full bg-linear-to-r from-yellow-400 to-orange-500 transition-all duration-500 ease-out">
            </div>
        </div>

        <!-- Progress Text -->
        <div class="text-center mt-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                <span x-text="gauge"></span> / <span x-text="maxGauge"></span> Sessions
            </span>
            <template x-if="gauge < maxGauge">
                <span class="text-xs text-gray-600 dark:text-gray-400 ml-2">
                    (<span x-text="maxGauge - gauge"></span> more needed)
                </span>
            </template>
        </div>
    </div>

    <!-- Spirit Burst Bonuses Info -->
    <div class="mb-4 p-4 glass-card-inner rounded-lg border-2 border-yellow-200 dark:border-yellow-800">
        <h4 class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
            </svg>
            Spirit Burst Bonuses
        </h4>
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-gray-700 dark:text-gray-300">Speed/Stamina/Power</span>
                <span class="font-bold text-green-600 dark:text-green-400">+50</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700 dark:text-gray-300">Guts/Wit</span>
                <span class="font-bold text-green-600 dark:text-green-400">+30</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700 dark:text-gray-300">Skill Hint Chance</span>
                <span class="font-bold text-blue-600 dark:text-blue-400">80%</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700 dark:text-gray-300">Energy Recovery</span>
                <span class="font-bold text-purple-600 dark:text-purple-400">+20</span>
            </div>
        </div>
    </div>

    <!-- Trigger Button -->
    <template x-if="gauge >= maxGauge">
        <button @click="triggerSpiritBurst" :disabled="triggering"
            class="w-full px-6 py-3 bg-linear-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!triggering" class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
                </svg>
                Trigger Spirit Burst!
            </span>
            <span x-show="triggering" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Triggering...
            </span>
        </button>
    </template>

    <!-- Info Box -->
    <div class="mt-4 p-3 glass-card-inner rounded-lg border-2 border-purple-200 dark:border-purple-800">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs text-purple-700 dark:text-purple-300">
                <strong>How it works:</strong> Each training session with teammates fills one segment of the gauge.
                When all 4 segments are filled, you can trigger Spirit Burst for massive bonuses and an 80% chance for a
                random skill hint!
            </div>
        </div>
    </div>
</div>

<!-- Spirit Burst Trigger Modal -->
<div x-show="showModal" x-cloak @keydown.escape.window="showModal = false" class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            aria-hidden="true" @click="showModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom glass-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Trigger Spirit Burst?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Are you ready to unleash the Spirit Burst? This will grant massive stat bonuses and
                                reset the gauge.
                            </p>
                            <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-200 mb-2">You will
                                    receive:</p>
                                <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                                    <li>• +50 Speed, Stamina, Power</li>
                                    <li>• +30 Guts, Wit</li>
                                    <li>• 80% chance for random skill hint</li>
                                    <li>• +20 Energy recovery</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button @click="confirmTrigger" type="button"
                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-linear-to-r from-yellow-400 to-orange-500 text-base font-medium text-white hover:from-yellow-500 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Trigger
                </button>
                <button @click="showModal = false" type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/components/spirit-burst-gauge.js'])
@endonce
