{{--
View Component: race-calendar
Purpose: Full-page race selection carousel with filtering and details

Features:
  - Horizontal race carousel with swipe gestures
  - Race type and month filtering
  - Detailed race information display
  - Fan count and progress tracking
  - Previous/Next navigation
  - Mobile-responsive design
  - Full dark mode support

Accessibility: WCAG 2.2 AA compliant with keyboard navigation
--}}
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4">
    <div class="max-w-6xl mx-auto space-y-8" x-data="raceCarouselView()" @alpine:initialized="initRaces()">

        {{-- Page Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Race Planning</h1>
            <p class="text-gray-600 dark:text-gray-400">Select races and plan your career trajectory</p>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-white">Filters</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Type Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Race Type
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button @click="filterByType(null)"
                            :class="!activeTypeFilter ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' :
                            'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors text-gray-900 dark:text-white"
                            :aria-pressed="!activeTypeFilter">
                            All
                        </button>
                        <template x-for="type in raceTypes" :key="type">
                            <button @click="filterByType(type)"
                                :class="activeTypeFilter === type ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' :
                                    'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors text-gray-900 dark:text-white capitalize"
                                x-text="type"
                                :aria-pressed="activeTypeFilter === type">
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Month Filter --}}
                <div>
                    <label for="month-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Month
                    </label>
                    <select id="month-select" @change="filterByMonth($event.target.value)"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Months</option>
                        <template x-for="month in months" :key="month.num">
                            <option :value="month.num" x-text="month.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <template x-if="activeTypeFilter || activeMonthFilter">
                <div class="flex flex-wrap gap-2 pt-2">
                    <template x-if="activeTypeFilter">
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm">
                            <span x-text="`Type: ${activeTypeFilter}`"></span>
                            <button @click="filterByType(null)" class="hover:opacity-70" aria-label="Clear race type filter">✕</button>
                        </span>
                    </template>
                    <template x-if="activeMonthFilter">
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm">
                            <span x-text="`Month: ${getMonthName(activeMonthFilter)}`"></span>
                            <button @click="filterByMonth('')" class="hover:opacity-70" aria-label="Clear month filter">✕</button>
                        </span>
                    </template>
                </div>
            </template>
        </div>

        {{-- Race Carousel --}}
        <div class="space-y-4">
            {{-- Progress Indicator --}}
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    <span x-text="currentRaceIndex + 1"></span> / <span x-text="filteredRaces.length"></span>
                </h2>
                <div class="h-2 flex-1 mx-4 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300"
                        :style="`width: ${raceProgress}%`">
                    </div>
                </div>
            </div>

            {{-- Main Race Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">

                {{-- Race Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-2xl font-bold" x-text="currentRace?.name || 'No races available'"></h3>
                            <p class="text-blue-100 text-sm mt-1"
                                x-text="currentRace ? getMonthName(currentRace.month) + ' - ' + getRaceTypeLabel(currentRace.type) : ''">
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-sm font-semibold"
                                x-text="currentRace?.grade || ''">
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Race Details --}}
                <div class="p-6 space-y-6">
                    <template x-if="currentRace">
                        {{-- Key Stats Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-4">
                                <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Distance</span>
                                <span class="text-base sm:text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="currentRace.distance + 'm'">
                                </span>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 sm:p-4">
                                <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Grade</span>
                                <span class="text-base sm:text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="currentRace.grade">
                                </span>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 sm:p-4 col-span-2 sm:col-span-1">
                                <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Fans</span>
                                <span class="text-base sm:text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="(currentRace.fanCount / 1000).toFixed(1) + 'K'">
                                </span>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50"
                            :class="{
                                'border-l-4 border-green-500': currentRace.status === 'completed',
                                'border-l-4 border-blue-500': currentRace.status === 'upcoming',
                                'border-l-4 border-yellow-500': currentRace.status === 'current'
                            }">
                            <span class="text-2xl" x-text="getRaceStatusIcon(currentRace.status)"></span>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white capitalize"
                                    x-text="currentRace.status">
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400"
                                    x-text="getRaceStatusMessage(currentRace.status)">
                                </p>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed"
                            x-text="getRaceDescription(currentRace)">
                        </p>

                        {{-- Upcoming Events --}}
                        <template x-if="currentRace.events && currentRace.events.length">
                            <div class="space-y-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Events</h4>
                                <div class="space-y-1">
                                    <template x-for="event in currentRace.events.slice(0, 3)" :key="event">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">• <span
                                                x-text="event"></span></p>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
                            <button @click="selectRace(currentRace.id)"
                                class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                Select Race
                            </button>
                            <button @click="saveRaceNote(currentRace.id)"
                                class="px-4 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors">
                                Save Note
                            </button>
                        </div>
                    </template>

                    {{-- Empty State --}}
                    <template x-if="!currentRace">
                        <div class="text-center py-12">
                            <div class="text-5xl mb-4 opacity-30">🏁</div>
                            <p class="text-gray-600 dark:text-gray-400">No races match your filters</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Navigation Controls --}}
            <div class="flex items-center justify-between gap-4">
                <button @click="prevRace()" :disabled="!canGoBackward"
                    :class="!canGoBackward ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-900 dark:text-white transition-colors"
                    aria-label="Previous race">
                    ← Prev
                </button>

                {{-- Upcoming Races Preview --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <template x-for="(race, idx) in upcomingRaces.slice(1, 4)" :key="race.id">
                        <button @click="goToRace(currentRaceIndex + idx + 1)"
                            class="p-3 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-left transition-colors group"
                            :aria-label="`View ${race.name}`">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white line-clamp-1"
                                x-text="race.name">
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1" x-text="race.grade">
                            </p>
                        </button>
                    </template>
                </div>

                <button @click="nextRace()" :disabled="!canGoForward"
                    :class="!canGoForward ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-900 dark:text-white transition-colors"
                    aria-label="Next race">
                    Next →
                </button>
            </div>
        </div>

        {{-- Swipe Hint (Mobile) --}}
        <div class="md:hidden text-center text-sm text-gray-600 dark:text-gray-400">
            💡 Swipe or use buttons to navigate races
        </div>
    </div>
</div>

{{-- Inject race data for JavaScript --}}
<script id="race-calendar-data" type="application/json">
    {!! json_encode([
        'races' => $races ?? []
    ]) !!}
</script>

@vite(['resources/js/pages/races/calendar.js'])
