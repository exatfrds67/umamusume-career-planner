@props(['characterId', 'facilityLevels' => []])

<div x-data="facilityManagement({{ $characterId }}, {{ json_encode($facilityLevels) }})" class="glass-card rounded-xl p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 transition-colors duration-300">
            Distance Team Facilities
        </h3>
        <p class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
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
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white capitalize"
                                x-text="team.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400" x-text="team.distanceRange"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Stat Rank</div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold"
                            :class="getRankColorClass(team.statRank)">
                            <span x-text="team.statRank"></span>
                        </div>
                    </div>
                </div>

                <!-- Facility Level Progress -->
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
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
                                    'bg-gray-200 dark:bg-gray-700': level > team.facilityLevel
                                }">
                            </div>
                        </template>
                    </div>

                    <!-- Level Labels -->
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Lv1 (0%)</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Lv5 (100%)</span>
                    </div>
                </div>

                <!-- Stat Rank to Level Mapping -->
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <template x-for="rank in ['D', 'C', 'B', 'A', 'S']" :key="rank">
                        <div class="p-2 rounded transition-colors duration-200"
                            :class="{
                                'bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 font-bold': team
                                    .statRank === rank,
                                'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': team.statRank !== rank
                            }">
                            <div x-text="rank"></div>
                            <div class="text-xs opacity-75">Lv<span x-text="rankToLevel(rank)"></span></div>
                        </div>
                    </template>
                </div>

                <!-- Team Members (if any) -->
                <template x-if="team.members && team.members.length > 0">
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">Team Members:</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="member in team.members" :key="member.id">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
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

@push('scripts')
    <script>
        function facilityManagement(characterId, initialFacilityLevels) {
            return {
                characterId: characterId,
                teams: {
                    sprint: {
                        name: 'Sprint Team',
                        icon: '⚡',
                        distanceRange: '1000-1400m',
                        statRank: 'C',
                        facilityLevel: 2,
                        bonusMultiplier: 25,
                        members: []
                    },
                    mile: {
                        name: 'Mile Team',
                        icon: '🏃',
                        distanceRange: '1401-1800m',
                        statRank: 'B',
                        facilityLevel: 3,
                        bonusMultiplier: 50,
                        members: []
                    },
                    medium: {
                        name: 'Medium Team',
                        icon: '🏇',
                        distanceRange: '1801-2400m',
                        statRank: 'A',
                        facilityLevel: 4,
                        bonusMultiplier: 75,
                        members: []
                    },
                    long: {
                        name: 'Long Team',
                        icon: '🎯',
                        distanceRange: '2401m+',
                        statRank: 'C',
                        facilityLevel: 2,
                        bonusMultiplier: 25,
                        members: []
                    },
                    dirt: {
                        name: 'Dirt Team',
                        icon: '🏜️',
                        distanceRange: 'All distances',
                        statRank: 'D',
                        facilityLevel: 1,
                        bonusMultiplier: 0,
                        members: []
                    }
                },
                nextRaceDate: 'Turn 24 (6 months)',

                init() {
                    // Initialize facility levels from character data
                    if (initialFacilityLevels && Object.keys(initialFacilityLevels).length > 0) {
                        this.updateFacilityLevels(initialFacilityLevels);
                    }

                    // Listen for facility level updates
                    window.addEventListener('facility-levels-updated', (event) => {
                        this.updateFacilityLevels(event.detail.facilityLevels);
                    });

                    // Dispatch initial state
                    this.$dispatch('facility-management-initialized', {
                        teams: this.teams
                    });
                },

                updateFacilityLevels(facilityLevels) {
                    Object.keys(facilityLevels).forEach(teamName => {
                        if (this.teams[teamName]) {
                            const level = facilityLevels[teamName];
                            this.teams[teamName].facilityLevel = level;
                            this.teams[teamName].bonusMultiplier = (level - 1) * 25;
                            this.teams[teamName].statRank = this.levelToRank(level);
                        }
                    });
                },

                rankToLevel(rank) {
                    const mapping = {
                        'S': 5,
                        'A': 4,
                        'B': 3,
                        'C': 2,
                        'D': 1
                    };
                    return mapping[rank] || 1;
                },

                levelToRank(level) {
                    const mapping = {
                        5: 'S',
                        4: 'A',
                        3: 'B',
                        2: 'C',
                        1: 'D'
                    };
                    return mapping[level] || 'D';
                },

                getTeamColorClass(teamName) {
                    const colors = {
                        sprint: 'bg-yellow-100 dark:bg-yellow-900/30',
                        mile: 'bg-blue-100 dark:bg-blue-900/30',
                        medium: 'bg-green-100 dark:bg-green-900/30',
                        long: 'bg-purple-100 dark:bg-purple-900/30',
                        dirt: 'bg-orange-100 dark:bg-orange-900/30'
                    };
                    return colors[teamName] || 'bg-gray-100 dark:bg-gray-900/30';
                },

                getRankColorClass(rank) {
                    const colors = {
                        'S': 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white',
                        'A': 'bg-gradient-to-r from-purple-400 to-pink-500 text-white',
                        'B': 'bg-gradient-to-r from-blue-400 to-cyan-500 text-white',
                        'C': 'bg-gradient-to-r from-green-400 to-emerald-500 text-white',
                        'D': 'bg-gray-400 dark:bg-gray-600 text-white'
                    };
                    return colors[rank] || 'bg-gray-400 text-white';
                }
            };
        }
    </script>
@endpush
