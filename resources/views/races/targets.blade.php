{{--
View: races/targets
Purpose: Race targeting and planning interface

Features:
- Select target races for career plan
- View race requirements and rewards
- Plan race strategy and timing
- Track race completion status
- Full dark mode support

Accessibility: WCAG 2.2 AA compliant
--}}
@extends('layouts.app')

@section('title', 'Race Targets - ' . ($character?->name ?? 'Uma Musume Career Planner'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Races', 'href' => route('races.index')],
        ['label' => 'Target Planning'],
    ]" />

    <div class="space-y-8 py-8">
        {{-- Page Header --}}
        <header class="space-y-2">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                @if ($character)
                    {{ $character->name }}'s Race Plan
                @else
                    Race Targeting
                @endif
            </h1>
            <p class="text-neutral-600 dark:text-neutral-400">
                Plan and target specific races to maximize your character's potential
            </p>
        </header>

        {{-- Navigation Tabs --}}
        <div class="flex gap-4 border-b border-neutral-200 dark:border-neutral-700" role="tablist" aria-label="Race views">
            <a href="{{ route('races.calendar') }}"
                class="px-4 py-3 border-b-2 border-transparent hover:border-neutral-300 dark:hover:border-neutral-600 text-neutral-700 dark:text-neutral-300 transition-colors"
                role="tab" aria-selected="false">
                Calendar View
            </a>
            <a href="{{ route('races.targets') }}"
                class="px-4 py-3 border-b-2 border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400 font-semibold"
                role="tab" aria-selected="true" aria-current="page">
                Target Planning
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Target Race Selection --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                        Select Target Races
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Choose races you want to focus on for this career run
                    </p>

                    {{-- Grade Filter --}}
                    <div class="space-y-3">
                        <span id="grade-filter-label" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Filter by Grade
                        </span>
                        <div class="flex flex-wrap gap-2" role="group" aria-labelledby="grade-filter-label">
                            <button type="button"
                                class="px-4 py-2 rounded-lg transition-colors text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                :class="filterGrade === null ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                @click="setGradeFilter(null)"
                                :aria-pressed="filterGrade === null">
                                All Grades
                            </button>
                            <template x-for="grade in grades" :key="grade">
                                <button type="button" @click="setGradeFilter(grade)"
                                    :class="filterGrade === grade ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' :
                                        'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                    class="px-4 py-2 rounded-lg transition-colors text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                    :aria-pressed="filterGrade === grade"
                                    x-text="grade">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Phase Filter --}}
                    <div class="space-y-3">
                        <span id="phase-filter-label" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Filter by Phase
                        </span>
                        <div class="flex flex-wrap gap-2" role="group" aria-labelledby="phase-filter-label">
                            <button type="button"
                                class="px-4 py-2 rounded-lg transition-colors text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                :class="filterPhase === null ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' : 'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                @click="setPhaseFilter(null)"
                                :aria-pressed="filterPhase === null">
                                All Phases
                            </button>
                            <template x-for="phase in phases" :key="phase">
                                <button type="button" @click="setPhaseFilter(phase)"
                                    :class="filterPhase === phase ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' :
                                        'bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-900 dark:text-white'"
                                    class="px-4 py-2 rounded-lg transition-colors text-sm font-medium capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                    :aria-pressed="filterPhase === phase"
                                    x-text="phase.charAt(0).toUpperCase() + phase.slice(1)">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Race List --}}
                    <div class="space-y-2 max-h-96 overflow-y-auto"
                        role="region"
                        aria-label="Available races"
                        tabindex="0">
                        <template x-if="filteredRaces.length > 0">
                            <div class="space-y-2">
                                <template x-for="race in filteredRaces" :key="race.id">
                                    <label
                                        class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 cursor-pointer transition-colors">
                                        <input type="checkbox" @change="toggleTargetRace(race.id)"
                                            :checked="selectedRaces.includes(race.id)"
                                            class="mt-1 rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                                                <template x-if="race.isUraFinale">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300" aria-label="URA Finale">⭐ URA</span>
                                                </template>
                                                <template x-if="race.phase">
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 capitalize" x-text="race.phase"></span>
                                                </template>
                                            </div>
                                            <p class="font-medium text-neutral-900 dark:text-white" x-text="race.name">
                                            </p>
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                                                <span x-text="`${race.grade} · `"></span>
                                                <span x-text="`${race.distance}m · `"></span>
                                                <span x-text="race.type" class="capitalize"></span>
                                            </p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1 flex flex-wrap gap-3">
                                                <span>
                                                    <span aria-hidden="true">👥</span>
                                                    <span x-text="`${(race.fanCount ?? 0).toLocaleString()} fans on win`"></span>
                                                </span>
                                                <template x-if="(race.spReward ?? 0) > 0">
                                                    <span>
                                                        <span aria-hidden="true">✨</span>
                                                        <span x-text="`${race.spReward} SP`"></span>
                                                    </span>
                                                </template>
                                                <template x-if="(race.fanRequirement ?? 0) > 0">
                                                    <span class="text-amber-600 dark:text-amber-400">
                                                        <span aria-hidden="true">🔒</span>
                                                        <span x-text="`Need ${(race.fanRequirement).toLocaleString()} fans`"></span>
                                                    </span>
                                                </template>
                                            </p>
                                        </div>
                                        <span
                                            :class="{
                                                'text-red-500': race.grade === 'G1',
                                                'text-orange-500': race.grade === 'G2',
                                                'text-yellow-500': race.grade === 'G3',
                                                'text-green-600': race.grade === 'OP',
                                                'text-teal-500': race.grade === 'Pre-OP',
                                                'text-blue-500': race.grade === 'Debut'
                                            }"
                                            class="text-sm font-semibold shrink-0" x-text="race.grade">
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="filteredRaces.length === 0">
                            <div class="text-center py-8 text-neutral-600 dark:text-neutral-400">
                                <p>No races match the selected filters</p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Race Timeline/Strategy --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                        Race Timeline
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Plan when to tackle your target races during your career
                    </p>

                    <template x-if="selectedRaces.length > 0">
                        <div class="space-y-3">
                            <template x-for="(raceId, idx) in selectedRaces" :key="raceId">
                                <div class="flex items-center gap-4 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                    <div
                                        class="shrink-0 w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <span class="text-blue-700 dark:text-blue-300 font-semibold text-sm"
                                            x-text="idx + 1">
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-neutral-900 dark:text-white"
                                            x-text="selectedRaceDetails[raceId]?.name || 'Race ' + raceId">
                                        </p>
                                        <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                            <span x-text="selectedRaceDetails[raceId]?.grade || ''"></span>
                                            <span x-text="`${selectedRaceDetails[raceId]?.distance || 0}m`"></span>
                                        </p>
                                    </div>
                                    <input type="number" placeholder="Turn #" min="1"
                                        @change="updateRaceTurn(raceId, $event.target.value)"
                                        :value="raceTurns[raceId] || ''"
                                        :aria-label="`Turn number for ${selectedRaceDetails[raceId]?.name || 'race'}`"
                                        class="w-20 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button @click="removeTargetRace(raceId)"
                                        :aria-label="`Remove ${selectedRaceDetails[raceId]?.name || 'race'} from timeline`"
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedRaces.length === 0">
                        <div class="text-center py-8 text-neutral-600 dark:text-neutral-400">
                            <p>No target races selected yet</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Sidebar: Summary & Strategy --}}
            <div class="space-y-6">
                {{-- Summary Card --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Plan Summary</h3>

                    {{-- Selected Count --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Target Races</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="selectedRaces.length">
                            </span>
                        </div>
                    </div>

                    {{-- Grade Breakdown --}}
                    <div class="space-y-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Grade Distribution</p>
                        <div class="space-y-1">
                            <template x-for="grade in grades" :key="grade">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-neutral-600 dark:text-neutral-400" x-text="grade"></span>
                                    <span class="font-semibold text-neutral-900 dark:text-white" x-text="getGradeCount(grade)">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Total Fan Count --}}
                    <div class="space-y-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Projected Fans</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white"
                            x-text="`${totalProjectedFans.toLocaleString()}`">
                        </p>
                    </div>

                    {{-- Save Plan --}}
                    <button @click="savePlan()" :disabled="selectedRaces.length === 0"
                        :class="selectedRaces.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="w-full mt-4 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        Save Plan
                    </button>
                </div>

                {{-- Quick Tips --}}
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50 p-4 space-y-3">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100"><span aria-hidden="true">💡</span> Race Planning Tips</h4>
                    <ul class="space-y-2 text-xs text-blue-800 dark:text-blue-200">
                        <li>Focus on G1 races for maximum fan growth</li>
                        <li>Plan races aligned with your stat development</li>
                        <li>Balance high-difficulty and safe races</li>
                        <li>Track race completion to optimize strategy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.pageData = {!! json_encode(['races' => $races]) !!};
    </script>
    @vite(['resources/js/pages/races/targets.js'])
@endsection
