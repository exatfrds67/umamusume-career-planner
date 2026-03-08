@props(['characterId', 'facilityLevels' => []])

<div x-data="facilityManagement({{ $characterId }}, {{ json_encode($facilityLevels) }})" class="glass-card rounded-xl p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2 transition-colors duration-300">
            Distance Team Facilities
        </h3>
        <p class="text-sm text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
            Upgrade facilities by improving team stat ranks through races (every 6 months)
        </p>
    </div>

    <!-- Facility Teams Grid -->
    <div class="space-y-4">
        <template x-for="(team, teamName) in teams" :key="teamName">
            <div class="glass-card-inner rounded-lg p-4">
                <!-- Team Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl"
                            :class="getTeamColorClass(teamName)">
                            <span x-text="team.icon"></span>
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-neutral-900 dark:text-white capitalize"
                                x-text="team.name"></h4>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400" x-text="team.distanceRange"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-neutral-600 dark:text-neutral-400 mb-1">Stat Rank</div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold"
                            :class="getRankColorClass(team.statRank)">
                            <span x-text="team.statRank"></span>
                        </div>
                    </div>
                </div>

                <!-- Facility Level Progress -->
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Facility Level <span x-text="team.facilityLevel"></span>/5
                        </span>
                        <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
                            +<span x-text="team.bonusMultiplier"></span>% Bonus
                        </span>
                    </div>

                    <!-- Level Indicators -->
                    <div class="flex items-center gap-2">
                        <template x-for="level in 5" :key="level">
                            <div class="flex-1 h-3 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-linear-to-r from-primary-400 to-primary-600': level <= team.facilityLevel,
                                    'bg-neutral-200 dark:bg-neutral-700': level > team.facilityLevel
                                }">
                            </div>
                        </template>
                    </div>

                    <!-- Level Labels -->
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-neutral-600 dark:text-neutral-400">Lv1 (0%)</span>
                        <span class="text-xs text-neutral-600 dark:text-neutral-400">Lv5 (100%)</span>
                    </div>
                </div>

                <!-- Stat Rank to Level Mapping -->
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <template x-for="rank in ['D', 'C', 'B', 'A', 'S']" :key="rank">
                        <div class="p-2 rounded transition-colors duration-200"
                            :class="{
                                'bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 font-bold': team
                                    .statRank === rank,
                                'bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400': team.statRank !== rank
                            }">
                            <div x-text="rank"></div>
                            <div class="text-xs opacity-75">Lv<span x-text="rankToLevel(rank)"></span></div>
                        </div>
                    </template>
                </div>

                <!-- Team Members (if any) -->
                <template x-if="team.members && team.members.length > 0">
                    <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="text-xs text-neutral-600 dark:text-neutral-400 mb-2">Team Members:</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="member in team.members" :key="member.id">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300">
                                    <span x-text="member.icon || '👤'"></span>
                                    <span class="ml-1" x-text="member.name"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <!-- Race Schedule Info -->
    <div class="mt-4 p-4 glass-card-inner rounded-lg border-2 border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">Team Race Schedule</h4>
                <p class="text-xs text-blue-600 dark:text-blue-400 mb-2">
                    Distance teams compete every 6 months. Improve your team's performance to increase stat ranks and
                    unlock higher facility levels.
                </p>
                <div class="text-xs text-blue-700 dark:text-blue-300">
                    <strong>Next Team Race:</strong> <span x-text="nextRaceDate"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Facility Bonus Explanation -->
    <div class="mt-4 p-3 glass-card-inner rounded-lg border-2 border-purple-200 dark:border-purple-800">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs text-purple-700 dark:text-purple-300">
                <strong>How Facilities Work:</strong> Each distance team has a stat rank (D-S) that determines facility
                level (1-5).
                Higher facility levels provide training bonuses: Level 1 = 0%, Level 2 = 25%, Level 3 = 50%, Level 4 =
                75%, Level 5 = 100%.
            </div>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/components/facility-management.js'])
@endonce
