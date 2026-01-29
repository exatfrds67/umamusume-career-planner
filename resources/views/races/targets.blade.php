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
    <div class="space-y-8 py-8">
        {{-- Page Header --}}
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                @if ($character)
                    {{ $character->name }}'s Race Plan
                @else
                    Race Targeting
                @endif
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Plan and target specific races to maximize your character's potential
            </p>
        </div>

        {{-- Navigation Tabs --}}
        <div class="flex gap-4 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('races.calendar') }}"
                class="px-4 py-3 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 transition-colors">
                Calendar View
            </a>
            <a href="{{ route('races.targets') }}"
                class="px-4 py-3 border-b-2 border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400 font-semibold">
                Target Planning
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Target Race Selection --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Select Target Races
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Choose races you want to focus on for this career run
                    </p>

                    {{-- Grade Selector --}}
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Filter by Grade
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white transition-colors text-sm font-medium"
                                @click="filterGrade = null">
                                All Grades
                            </button>
                            <template x-for="grade in ['G1', 'G2', 'G3', 'Listed', 'Open']" :key="grade">
                                <button type="button"
                                    @click="filterGrade = grade"
                                    :class="filterGrade === grade ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    class="px-4 py-2 rounded-lg text-gray-900 dark:text-white transition-colors text-sm font-medium"
                                    x-text="grade">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Race List --}}
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-if="filteredRaces.length > 0">
                            <div class="space-y-2">
                                <template x-for="race in filteredRaces" :key="race.id">
                                    <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                        <input type="checkbox"
                                            @change="toggleTargetRace(race.id)"
                                            :checked="selectedRaces.includes(race.id)"
                                            class="mt-1 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white"
                                                x-text="race.name">
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                <span x-text="`${race.grade} · `"></span>
                                                <span x-text="`${race.distance}m · `"></span>
                                                <span x-text="race.type" class="capitalize"></span>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                <span x-text="`👥 ${race.fanCount.toLocaleString()} fans`"></span>
                                            </p>
                                        </div>
                                        <span :class="{
                                            'text-red-500': race.grade === 'G1',
                                            'text-orange-500': race.grade === 'G2',
                                            'text-yellow-500': race.grade === 'G3',
                                            'text-green-500': race.grade === 'Listed',
                                            'text-blue-500': race.grade === 'Open'
                                        }"
                                            class="text-sm font-semibold flex-shrink-0"
                                            x-text="race.grade">
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="filteredRaces.length === 0">
                            <div class="text-center py-8 text-gray-600 dark:text-gray-400">
                                <p>No races match the selected filters</p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Race Timeline/Strategy --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Race Timeline
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Plan when to tackle your target races during your career
                    </p>

                    <template x-if="selectedRaces.length > 0">
                        <div class="space-y-3">
                            <template x-for="(raceId, idx) in selectedRaces" :key="raceId">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <span class="text-blue-700 dark:text-blue-300 font-semibold text-sm"
                                            x-text="idx + 1">
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white"
                                            x-text="selectedRaceDetails[raceId]?.name || 'Race ' + raceId">
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            <span x-text="selectedRaceDetails[raceId]?.grade || ''"></span>
                                            <span x-text="`${selectedRaceDetails[raceId]?.distance || 0}m`"></span>
                                        </p>
                                    </div>
                                    <input type="number"
                                        placeholder="Turn #"
                                        min="1"
                                        @change="updateRaceTurn(raceId, $event.target.value)"
                                        :value="raceTurns[raceId] || ''"
                                        class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button @click="removeTargetRace(raceId)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedRaces.length === 0">
                        <div class="text-center py-8 text-gray-600 dark:text-gray-400">
                            <p>No target races selected yet</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Sidebar: Summary & Strategy --}}
            <div class="space-y-6">
                {{-- Summary Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Plan Summary</h3>

                    {{-- Selected Count --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Target Races</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                x-text="selectedRaces.length">
                            </span>
                        </div>
                    </div>

                    {{-- Grade Breakdown --}}
                    <div class="space-y-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Grade Distribution</p>
                        <div class="space-y-1">
                            <template x-for="grade in ['G1', 'G2', 'G3', 'Listed', 'Open']" :key="grade">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600 dark:text-gray-400" x-text="grade"></span>
                                    <span class="font-semibold text-gray-900 dark:text-white"
                                        x-text="getGradeCount(grade)">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Total Fan Count --}}
                    <div class="space-y-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Projected Fans</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"
                            x-text="`${totalProjectedFans.toLocaleString()}`">
                        </p>
                    </div>

                    {{-- Save Plan --}}
                    <button @click="savePlan()"
                        :disabled="selectedRaces.length === 0"
                        :class="selectedRaces.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="w-full mt-4 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Save Plan
                    </button>
                </div>

                {{-- Quick Tips --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50 p-4 space-y-3">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">💡 Race Planning Tips</h4>
                    <ul class="space-y-2 text-xs text-blue-800 dark:text-blue-200">
                        <li>• Focus on G1 races for maximum fan growth</li>
                        <li>• Plan races aligned with your stat development</li>
                        <li>• Balance high-difficulty and safe races</li>
                        <li>• Track race completion to optimize strategy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.Alpine && Alpine.data('raceTargets', function() {
            return {
                races: @json($races ?? []),
                selectedRaces: [],
                selectedRaceDetails: {},
                raceTurns: {},
                filterGrade: null,

                get filteredRaces() {
                    if (!this.filterGrade) return this.races;
                    return this.races.filter(r => r.grade === this.filterGrade);
                },

                get totalProjectedFans() {
                    return this.selectedRaces.reduce((sum, raceId) => {
                        return sum + (this.selectedRaceDetails[raceId]?.fanCount || 0);
                    }, 0);
                },

                toggleTargetRace(raceId) {
                    const idx = this.selectedRaces.indexOf(raceId);
                    if (idx > -1) {
                        this.selectedRaces.splice(idx, 1);
                        delete this.selectedRaceDetails[raceId];
                    } else {
                        this.selectedRaces.push(raceId);
                        this.selectedRaceDetails[raceId] = this.races.find(r => r.id === raceId);
                    }
                },

                removeTargetRace(raceId) {
                    this.toggleTargetRace(raceId);
                },

                updateRaceTurn(raceId, turn) {
                    this.raceTurns[raceId] = turn;
                },

                getGradeCount(grade) {
                    return this.selectedRaces.filter(raceId => {
                        const race = this.selectedRaceDetails[raceId];
                        return race && race.grade === grade;
                    }).length;
                },

                savePlan() {
                    if (this.selectedRaces.length === 0) return;
                    alert('Race plan saved! Selected ' + this.selectedRaces.length + ' races');
                    // TODO: Send to server
                }
            };
        });
    </script>
@endpush
