@props(['characterId', 'scenarioType' => 'unity_cup', 'maxTeammates' => 3])

<div x-data="teamMemberSelector({{ $characterId }}, {{ $maxTeammates }})" class="glass-card rounded-xl p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2 transition-colors duration-300">
            Team Member Selection
        </h3>
        <p class="text-sm text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
            Select teammates for training (2 participants = +2 bonus, 3 participants = +3 bonus)
        </p>
    </div>

    <!-- Selected Teammates Display -->
    <div class="mb-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 transition-colors duration-300">
                Selected: <span x-text="selectedTeammates.length"></span>/{{ $maxTeammates }}
            </span>
            <template x-if="selectedTeammates.length >= 2">
                <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    Unity Bonus Active
                </span>
            </template>
        </div>

        <!-- Selected Teammates Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <template x-for="teammate in selectedTeammates" :key="teammate.id">
                <div class="glass-card-inner rounded-lg p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <span class="text-lg" x-text="teammate.icon || '👤'"></span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white" x-text="teammate.name"></div>
                            <div class="text-xs text-neutral-600 dark:text-neutral-400" x-text="teammate.specialty"></div>
                        </div>
                    </div>
                    <button @click="removeTeammate(teammate.id)"
                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>

            <!-- Empty Slots -->
            <template x-for="i in ({{ $maxTeammates }} - selectedTeammates.length)" :key="'empty-' + i">
                <div
                    class="glass-card-inner rounded-lg p-3 border-2 border-dashed border-neutral-300 dark:border-neutral-600 flex items-center justify-center">
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">Empty Slot</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Available Teammates -->
    <div>
        <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3 transition-colors duration-300">
            Available Teammates
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
            <template x-for="teammate in availableTeammates" :key="teammate.id">
                <button @click="addTeammate(teammate)" :disabled="selectedTeammates.length >= {{ $maxTeammates }}"
                    :class="{
                        'opacity-50 cursor-not-allowed': selectedTeammates.length >= {{ $maxTeammates }},
                        'hover:bg-primary-50 dark:hover:bg-primary-900/20': selectedTeammates.length <
                            {{ $maxTeammates }}
                    }"
                    class="glass-card-inner rounded-lg p-3 text-left transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                            <span class="text-xl" x-text="teammate.icon || '👤'"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-neutral-900 dark:text-white truncate"
                                x-text="teammate.name"></div>
                            <div class="text-xs text-neutral-600 dark:text-neutral-400 truncate" x-text="teammate.specialty">
                            </div>
                            <template x-if="teammate.bond_level >= 80">
                                <div class="flex items-center gap-1 mt-1">
                                    <svg class="w-3 h-3 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs text-pink-600 dark:text-pink-400">Friendship Training</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </button>
            </template>
        </div>
    </div>

    <!-- Unity Bonus Info -->
    <div class="mt-4 p-3 glass-card-inner rounded-lg border-2 border-purple-200 dark:border-purple-800">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs text-purple-700 dark:text-purple-300">
                <strong>Unity Training Bonus:</strong> Training with 2 teammates grants +2 bonus, 3 teammates grants +3
                bonus.
                Teammates with 80%+ bond level enable Friendship Training for additional benefits.
            </div>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/components/team-member-selector.js'])
@endonce
