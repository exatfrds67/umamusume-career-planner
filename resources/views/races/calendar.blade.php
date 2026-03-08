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
@extends('layouts.app')
@section('title', 'Race Planning')
@section('content')
<div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8 px-4">
    <div class="max-w-6xl mx-auto space-y-8" x-data="raceCarouselView()" @alpine:initialized="initRaces()">

        {{-- Page Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Race Planning</h1>
            <p class="text-neutral-600 dark:text-neutral-400">Select races and plan your career trajectory</p>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6 space-y-5">
            <h2 class="font-semibold text-neutral-900 dark:text-white">Filters</h2>

            {{-- Phase Filter --}}
            <div>
                <span id="phase-filter-label" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                    Career Phase
                </span>
                <div class="flex flex-wrap gap-2" role="group" aria-labelledby="phase-filter-label">
                    <button @click="filterByPhase(null)"
                        :class="!activePhaseFilter ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' :
                            'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                        :aria-pressed="!activePhaseFilter">
                        All Phases
                    </button>
                    <template x-for="phase in phases" :key="phase">
                        <button @click="filterByPhase(phase)"
                            :class="activePhaseFilter === phase ?
                                'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' :
                                'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                            x-text="phase.charAt(0).toUpperCase() + phase.slice(1)"
                            :aria-pressed="activePhaseFilter === phase">
                        </button>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Surface Filter --}}
                <div>
                    <span id="surface-filter-label" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                        Surface
                    </span>
                    <div class="flex flex-wrap gap-2" role="group" aria-labelledby="surface-filter-label">
                        <button @click="filterBySurface(null)"
                            :class="!activeSurfaceFilter ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' :
                                'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            :aria-pressed="!activeSurfaceFilter">
                            All
                        </button>
                        <template x-for="surface in surfaces" :key="surface">
                            <button @click="filterBySurface(surface)"
                                :class="activeSurfaceFilter === surface ?
                                    'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' :
                                    'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                x-text="getRaceTypeLabel(surface)"
                                :aria-pressed="activeSurfaceFilter === surface">
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Distance Category Filter --}}
                <div>
                    <span id="distance-filter-label" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                        Distance Category
                    </span>
                    <div class="flex flex-wrap gap-2" role="group" aria-labelledby="distance-filter-label">
                        <button @click="filterByDistance(null)"
                            :class="!activeDistanceFilter ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' :
                                'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            :aria-pressed="!activeDistanceFilter">
                            All
                        </button>
                        <template x-for="dist in distanceCategories" :key="dist">
                            <button @click="filterByDistance(dist)"
                                :class="activeDistanceFilter === dist ?
                                    'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' :
                                    'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                x-text="getRaceTypeLabel(dist)"
                                :aria-pressed="activeDistanceFilter === dist">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Month Filter --}}
            <div>
                <label for="month-select" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                    In-Game Month
                </label>
                <select id="month-select" @change="filterByMonth($event.target.value)"
                    class="w-full md:w-64 px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Months</option>
                    <template x-for="month in months" :key="month">
                        <option :value="month" x-text="month" :selected="activeMonthFilter === month"></option>
                    </template>
                </select>
            </div>

            {{-- Active Filters Display --}}
            <template x-if="activeSurfaceFilter || activeDistanceFilter || activePhaseFilter || activeMonthFilter">
                <div class="flex flex-wrap gap-2 pt-2" aria-label="Active filters">
                    <template x-if="activePhaseFilter">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm">
                            <span x-text="`Phase: ${activePhaseFilter}`"></span>
                            <button @click="filterByPhase(null)" class="hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded" aria-label="Clear phase filter">✕</button>
                        </span>
                    </template>
                    <template x-if="activeSurfaceFilter">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm">
                            <span x-text="`Surface: ${activeSurfaceFilter}`"></span>
                            <button @click="filterBySurface(null)" class="hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded" aria-label="Clear surface filter">✕</button>
                        </span>
                    </template>
                    <template x-if="activeDistanceFilter">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm">
                            <span x-text="`Distance: ${activeDistanceFilter}`"></span>
                            <button @click="filterByDistance(null)" class="hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" aria-label="Clear distance filter">✕</button>
                        </span>
                    </template>
                    <template x-if="activeMonthFilter">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm">
                            <span x-text="`Month: ${activeMonthFilter}`"></span>
                            <button @click="filterByMonth('')" class="hover:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" aria-label="Clear month filter">✕</button>
                        </span>
                    </template>
                </div>
            </template>
        </div>

        {{-- Race Carousel --}}
        <div class="space-y-4">
            {{-- Progress Indicator --}}
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-neutral-900 dark:text-white" aria-live="polite" aria-atomic="true">
                    <span x-text="currentRaceIndex + 1"></span> / <span x-text="filteredRaces.length"></span>
                </h2>
                <div class="h-2 flex-1 mx-4 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                    <div class="h-full bg-linear-to-r from-blue-500 to-purple-500 transition-all duration-300"
                        :style="`width: ${raceProgress}%`"
                        role="progressbar"
                        :aria-valuenow="currentRaceIndex + 1"
                        aria-valuemin="1"
                        :aria-valuemax="filteredRaces.length"
                        :aria-label="`Race ${currentRaceIndex + 1} of ${filteredRaces.length}`">
                    </div>
                </div>
            </div>

            {{-- Main Race Card --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden"
                @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">

                {{-- Race Header --}}
                <div class="bg-linear-to-r from-blue-600 to-purple-600 p-6 text-white space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <template x-if="currentRace?.isUraFinale">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-400 text-yellow-900 text-xs font-bold uppercase tracking-wide" aria-label="URA Finale race">⭐ URA Finale</span>
                                </template>
                                <template x-if="currentRace?.phase">
                                    <span class="inline-block px-2 py-0.5 rounded-full bg-white/20 text-xs font-semibold capitalize" x-text="currentRace.phase"></span>
                                </template>
                            </div>
                            <h3 class="text-2xl font-bold" x-text="currentRace?.name || 'No races available'"></h3>
                            <p class="text-blue-100 text-sm mt-1"
                                x-text="currentRace ? getMonthName(currentRace.month) + ' · ' + getRaceTypeLabel(currentRace.surface) + ' · ' + getRaceTypeLabel(currentRace.distanceCategory) : ''"
                            ></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-sm font-semibold"
                                x-text="currentRace?.grade || ''"
                            ></span>
                        </div>
                    </div>
                </div>

                {{-- Race Details --}}
                <div class="p-6 space-y-6">
                    <template x-if="currentRace">
                        {{-- Key Stats Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
                            <dl class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-4">
                                <dt class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Distance</dt>
                                <dd class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white"
                                    x-text="currentRace.distance + 'm'">
                                </dd>
                            </dl>
                            <dl class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 sm:p-4">
                                <dt class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Grade</dt>
                                <dd class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white"
                                    x-text="currentRace.grade">
                                </dd>
                            </dl>
                            <dl class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 sm:p-4">
                                <dt class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Fans Reward</dt>
                                <dd class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white"
                                    x-text="(currentRace.fansReward ?? 0).toLocaleString()">
                                </dd>
                            </dl>
                            <dl class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 sm:p-4">
                                <dt class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">SP Reward</dt>
                                <dd class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white"
                                    x-text="(currentRace.spReward ?? 0) + ' SP'">
                                </dd>
                            </dl>
                            <dl class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 sm:p-4 col-span-2 sm:col-span-1">
                                <dt class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Fans Required</dt>
                                <dd class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white"
                                    x-text="currentRace.fanRequirement > 0 ? (currentRace.fanRequirement).toLocaleString() : 'None'">
                                </dd>
                            </dl>
                        </div>

                        {{-- Status Badge --}}
                        <div class="flex items-center gap-3 p-4 rounded-lg bg-neutral-50 dark:bg-neutral-700/50"
                            :class="{
                                'border-l-4 border-green-500': currentRace.status === 'completed',
                                'border-l-4 border-blue-500': currentRace.status === 'upcoming',
                                'border-l-4 border-yellow-500': currentRace.status === 'current'
                            }">
                            <span class="text-2xl" x-text="getRaceStatusIcon(currentRace.status)" aria-hidden="true"></span>
                            <span class="sr-only" x-text="getRaceStatusLabel(currentRace.status)"></span>
                            <div>
                                <p class="font-semibold text-neutral-900 dark:text-white capitalize"
                                    x-text="currentRace.status">
                                </p>
                                <p class="text-xs text-neutral-600 dark:text-neutral-400"
                                    x-text="getRaceStatusMessage(currentRace.status)">
                                </p>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed"
                            x-text="getRaceDescription(currentRace)">
                        </p>

                        {{-- Upcoming Events --}}
                        <template x-if="currentRace.events && currentRace.events.length">
                            <div class="space-y-2">
                                <h4 class="font-semibold text-neutral-900 dark:text-white text-sm">Events</h4>
                                <div class="space-y-1">
                                    <template x-for="event in currentRace.events.slice(0, 3)" :key="event">
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">• <span
                                                x-text="event"></span></p>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
                            <button @click="selectRace(currentRace.id)"
                                class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                Select Race
                            </button>
                            <button @click="saveRaceNote(currentRace.id)"
                                class="px-4 py-3 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white font-semibold rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                Save Note
                            </button>
                        </div>
                    </template>

                    {{-- Empty State --}}
                    <template x-if="!currentRace">
                        <div class="text-center py-12">
                            <div class="text-5xl mb-4 opacity-30"><span aria-hidden="true">🏁</span></div>
                            <p class="text-neutral-600 dark:text-neutral-400">No races match your filters</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Navigation Controls --}}
            <div class="flex items-center justify-between gap-4">
                <button @click="prevRace()" :disabled="!canGoBackward"
                    :class="!canGoBackward ? 'opacity-50 cursor-not-allowed' : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                    class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-900 dark:text-white transition-colors"
                    aria-label="Previous race">
                    ← Prev
                </button>

                {{-- Upcoming Races Preview --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <template x-for="(race, idx) in upcomingRaces.slice(1, 4)" :key="race.id">
                        <button @click="goToRace(currentRaceIndex + idx + 1)"
                            class="p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-left transition-colors group"
                            :aria-label="`View ${race.name}`">
                            <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-900 dark:group-hover:text-white line-clamp-1"
                                x-text="race.name">
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1" x-text="race.grade">
                            </p>
                        </button>
                    </template>
                </div>

                <button @click="nextRace()" :disabled="!canGoForward"
                    :class="!canGoForward ? 'opacity-50 cursor-not-allowed' : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                    class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-900 dark:text-white transition-colors"
                    aria-label="Next race">
                    Next →
                </button>
            </div>
        </div>

        {{-- Swipe Hint (Mobile) --}}
        <div class="md:hidden text-center text-sm text-neutral-600 dark:text-neutral-400">
            <span aria-hidden="true">💡</span> Swipe or use buttons to navigate races
        </div>
    </div>
</div>

{{-- Inject race data for JavaScript --}}
<script id="race-calendar-data" type="application/json">
    {!! json_encode($races ?? []) !!}
</script>

@vite(['resources/js/pages/races/calendar.js'])
@endsection
