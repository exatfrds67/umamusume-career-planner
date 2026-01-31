
<div class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="shrink-0">
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ready to Evolve</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"
                            x-text="evolutionOpportunities.filter(e => e.can_evolve).length"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="shrink-0">
                        <div
                            class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending Prerequisites</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"
                            x-text="evolutionOpportunities.filter(e => !e.can_evolve).length"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="shrink-0">
                        <div
                            class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Potential SP Savings</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="totalEvolutionSavings"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div x-show="evolutionOpportunities.filter(e => e.can_evolve).length > 0" class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ready to Evolve</h3>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    All Prerequisites Met
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <template x-for="evolution in evolutionOpportunities.filter(e => e.can_evolve)" :key="evolution.id">
                    <div
                        class="border-2 border-green-200 dark:border-green-800 rounded-lg p-4 bg-green-50 dark:bg-green-900/10">
                        
                        <div class="flex items-center gap-4 mb-4">
                            
                            <div
                                class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Current</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Normal
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-1"
                                    x-text="evolution.normal_skill.name"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"
                                    x-text="evolution.normal_skill.description"></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">SP Cost</span>
                                    <span class="font-bold text-gray-900 dark:text-white"
                                        x-text="evolution.normal_skill.base_sp_cost"></span>
                                </div>
                            </div>

                            
                            <div class="shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>

                            
                            <div
                                class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-blue-500 dark:border-blue-400">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Evolved</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        Rare
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-1"
                                    x-text="evolution.rare_skill.name"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"
                                    x-text="evolution.rare_skill.description"></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">SP Cost</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400"
                                        x-text="evolution.rare_skill.base_sp_cost"></span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Evolution Cost</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="`${evolution.evolution_cost} SP`"></p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Hints Available</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400"
                                    x-text="evolution.hints_available"></p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">SP Savings</p>
                                <p class="text-lg font-bold text-purple-600 dark:text-purple-400"
                                    x-text="`${evolution.sp_savings} SP`"></p>
                            </div>
                        </div>

                        
                        <div x-show="evolution.ai_recommendation"
                            class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-purple-900 dark:text-purple-300">AI
                                        Recommendation</p>
                                    <p class="text-sm text-purple-700 dark:text-purple-400 mt-1"
                                        x-text="evolution.ai_recommendation"></p>
                                </div>
                            </div>
                        </div>

                        
                        <div class="flex gap-2">
                            <button @click="evolveSkill(evolution)"
                                :disabled="character.available_sp < evolution.evolution_cost"
                                class="flex-1 btn btn-primary"
                                :class="{ 'opacity-50 cursor-not-allowed': character.available_sp < evolution.evolution_cost }">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Evolve Now
                            </button>
                            <button @click="viewEvolutionDetails(evolution)" class="btn btn-secondary">
                                View Details
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div x-show="evolutionOpportunities.filter(e => !e.can_evolve).length > 0" class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Prerequisites</h3>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Requirements Not Met
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <template x-for="evolution in evolutionOpportunities.filter(e => !e.can_evolve)"
                    :key="evolution.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        
                        <div class="flex items-center gap-4 mb-4">
                            
                            <div
                                class="flex-1 bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Current</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Normal
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-1"
                                    x-text="evolution.normal_skill.name"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400"
                                    x-text="evolution.normal_skill.description"></p>
                            </div>

                            
                            <div class="shrink-0">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>

                            
                            <div
                                class="flex-1 bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 opacity-60">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Locked</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        Rare
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-1"
                                    x-text="evolution.rare_skill.name"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400"
                                    x-text="evolution.rare_skill.description"></p>
                            </div>
                        </div>

                        
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-2 mb-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 shrink-0 mt-0.5"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-yellow-900 dark:text-yellow-300">Missing
                                        Prerequisites</p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1"
                                        x-text="evolution.block_reason"></p>
                                </div>
                            </div>

                            
                            <div x-show="evolution.missing_prerequisites && evolution.missing_prerequisites.length > 0"
                                class="space-y-2">
                                <template x-for="prereq in evolution.missing_prerequisites" :key="prereq">
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300" x-text="prereq"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        
                        <div x-show="evolution.roadmap" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Roadmap to
                                        Evolution</p>
                                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1"
                                        x-text="evolution.roadmap"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Evolution Guide</h3>
        </div>
        <div class="card-body">
            <div class="prose dark:prose-invert max-w-none">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3">How Skill Evolution Works</h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Normal → Rare:</strong> Normal skills can evolve into more powerful Rare versions
                            with enhanced effects</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Automatic Replacement:</strong> When evolved, the Normal skill is automatically
                            replaced by the Rare version</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Hint Discounts Apply:</strong> Hints collected for the Rare skill reduce evolution
                            cost progressively: 10%/20%/30%/35%/40% (max at 5 hints)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Prerequisites Required:</strong> Some evolutions require specific stat thresholds
                            or prerequisite skills</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>SP Efficiency:</strong> Evolution path is often more SP-efficient than direct Rare
                            acquisition</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/skills/partials/evolution.blade.php ENDPATH**/ ?>