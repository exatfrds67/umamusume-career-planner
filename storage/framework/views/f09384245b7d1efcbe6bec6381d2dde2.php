
<div class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Ready to Evolve</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400"
                    x-text="evolutionOpportunities.filter(e => e.can_evolve).length">
                </p>
            </div>
        </div>
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending Evolution</p>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"
                    x-text="evolutionOpportunities.filter(e => !e.can_evolve).length">
                </p>
            </div>
        </div>
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Savings</p>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400" x-text="totalEvolutionSavings">
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">SP</p>
            </div>
        </div>
    </div>

    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ready to Evolve</h3>
        </div>
        <div class="card-body">
            <div x-show="evolutionOpportunities.filter(e => e.can_evolve).length > 0" class="space-y-4">
                <template x-for="opp in evolutionOpportunities.filter(e => e.can_evolve)" :key="opp.base_skill_id">
                    <div
                        class="p-4 border-2 border-green-200 dark:border-green-800 rounded-lg bg-green-50 dark:bg-green-900/20">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white"
                                        x-text="opp.base_skill_name"></h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Base Skill</p>
                                </div>
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-green-600 dark:text-green-400"
                                        x-text="opp.evolved_skill_name"></h4>
                                    <p class="text-xs text-green-700 dark:text-green-300">Evolved Skill</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                    Save <span x-text="opp.sp_savings"></span> SP
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Requirements: <span x-text="opp.requirements"></span>
                            </span>
                            <button @click="viewSkillDetails(opp.evolved_skill)" class="btn btn-sm btn-primary">
                                View Details
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="evolutionOpportunities.filter(e => e.can_evolve).length === 0"
                class="text-center py-8 text-gray-500 dark:text-gray-400">
                No skills ready to evolve
            </div>
        </div>
    </div>

    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Evolution</h3>
        </div>
        <div class="card-body">
            <div x-show="evolutionOpportunities.filter(e => !e.can_evolve).length > 0" class="space-y-4">
                <template x-for="opp in evolutionOpportunities.filter(e => !e.can_evolve)" :key="opp.base_skill_id">
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white"
                                        x-text="opp.base_skill_name"></h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Base Skill</p>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-gray-600 dark:text-gray-400"
                                        x-text="opp.evolved_skill_name"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">Evolved Skill</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Potential: <span class="font-semibold" x-text="opp.sp_savings"></span> SP
                                </p>
                            </div>
                        </div>
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">
                            Missing: <span x-text="opp.missing_requirements"></span>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="evolutionOpportunities.filter(e => !e.can_evolve).length === 0"
                class="text-center py-8 text-gray-500 dark:text-gray-400">
                No pending evolutions
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/skills/partials/evolution.blade.php ENDPATH**/ ?>