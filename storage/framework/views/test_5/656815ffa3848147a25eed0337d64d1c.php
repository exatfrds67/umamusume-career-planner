
<div class="space-y-6">
    
    <div class="card bg-linear-to-br from-purple-500 to-purple-600 text-white">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="shrink-0">
                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold mb-2">AI-Powered Recommendations</h3>
                    <p class="text-purple-100 text-sm mb-4">
                        Our MCP agents analyze your character, support cards, and available hints to recommend the most
                        efficient skill acquisition strategy.
                    </p>
                    <button @click="getAIRecommendations()" :disabled="loading" class="btn btn-white">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Get AI Recommendations
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div x-show="recommendations.length > 0" class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Recommended Skills
                <span class="ml-2 text-sm font-normal text-gray-500">(Optimized for SP efficiency)</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <template x-for="(rec, index) in recommendations" :key="index">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start gap-4">
                            
                            <div class="shrink-0">
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-lg font-bold"
                                    :class="{
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': rec
                                            .priority === 'high',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': rec
                                            .priority === 'medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': rec
                                            .priority === 'low'
                                    }"
                                    x-text="index + 1"></span>
                            </div>

                            
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white text-lg"
                                            x-text="rec.skill.name"></h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                :class="{
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': rec
                                                        .skill.rarity === 'normal',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': rec
                                                        .skill.rarity === 'rare',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': rec
                                                        .skill.rarity === 'unique'
                                                }"
                                                x-text="rec.skill.rarity.charAt(0).toUpperCase() + rec.skill.rarity.slice(1)"></span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                                x-text="rec.skill.meta_tier"></span>
                                            <span x-show="rec.priority === 'high'"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                High Priority
                                            </span>
                                        </div>
                                    </div>

                                    
                                    <div class="text-right ml-4">
                                        <div class="flex items-baseline gap-2">
                                            <span x-show="rec.sp_saved > 0"
                                                class="text-sm text-gray-500 dark:text-gray-400 line-through"
                                                x-text="rec.base_cost"></span>
                                            <span class="text-2xl font-bold text-gray-900 dark:text-white"
                                                x-text="rec.final_cost"></span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">SP</span>
                                        </div>
                                        <div x-show="rec.sp_saved > 0"
                                            class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            Save <span x-text="rec.sp_saved"></span> SP
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 mb-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-medium mb-1">Why this skill?
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="rec.reasoning"></p>
                                </div>

                                
                                <div x-show="rec.hints_available > 0" class="grid grid-cols-3 gap-4 mb-3">
                                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                        <p class="text-xs text-green-600 dark:text-green-400 mb-1">Hints Available</p>
                                        <p class="text-lg font-bold text-green-900 dark:text-green-300"
                                            x-text="rec.hints_available"></p>
                                    </div>
                                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Discount</p>
                                        <p class="text-lg font-bold text-blue-900 dark:text-blue-300"
                                            x-text="`${rec.discount_percentage}%`"></p>
                                    </div>
                                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Efficiency Score
                                        </p>
                                        <p class="text-lg font-bold text-purple-900 dark:text-purple-300"
                                            x-text="rec.efficiency_score"></p>
                                    </div>
                                </div>

                                
                                <div x-show="rec.hint_sources && rec.hint_sources.length > 0" class="mb-3">
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Hint Sources:
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="source in rec.hint_sources" :key="source">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span x-text="source"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                
                                <div class="flex gap-2">
                                    <button @click="acquireSkill(rec.skill)"
                                        :disabled="character.available_sp < rec.final_cost" class="btn btn-primary"
                                        :class="{ 'opacity-50 cursor-not-allowed': character.available_sp < rec.final_cost }">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Acquire Skill
                                    </button>
                                    <button @click="viewSkillDetails(rec.skill)" class="btn btn-secondary">
                                        View Details
                                    </button>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SP Budget Planner</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Current Budget</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Available SP</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white"
                                x-text="character?.available_sp || 0"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Planned Spending</span>
                            <span class="text-lg font-bold text-orange-600 dark:text-orange-400"
                                x-text="plannedSpending"></span>
                        </div>
                        <div
                            class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-gray-900 dark:text-white font-medium">Remaining</span>
                            <span class="text-xl font-bold"
                                :class="(character?.available_sp || 0) - plannedSpending >= 0 ?
                                    'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                x-text="(character?.available_sp || 0) - plannedSpending"></span>
                        </div>
                    </div>

                    
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Budget Usage</span>
                            <span
                                x-text="`${Math.round((plannedSpending / (character?.available_sp || 1)) * 100)}%`"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="h-3 rounded-full transition-all"
                                :class="(plannedSpending / (character?.available_sp || 1)) > 1 ? 'bg-red-500' : 'bg-primary-500'"
                                :style="`width: ${Math.min((plannedSpending / (character?.available_sp || 1)) * 100, 100)}%`">
                            </div>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Potential Savings</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Skills with Hints</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400"
                                x-text="skillsWithHints"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Total Potential Savings</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400"
                                x-text="potentialSavings"></span>
                        </div>
                        <div
                            class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-gray-900 dark:text-white font-medium">Effective Budget</span>
                            <span class="text-xl font-bold text-primary-600 dark:text-primary-400"
                                x-text="(character?.available_sp || 0) + potentialSavings"></span>
                        </div>
                    </div>

                    
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Maximize Your Savings
                                </p>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                                    Collect hints before acquiring skills to save up to 40% SP per skill!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Acquisitions</h3>
        </div>
        <div class="card-body">
            <div x-show="recentAcquisitions.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No skills acquired yet</p>
            </div>

            <div x-show="recentAcquisitions.length > 0" class="space-y-3">
                <template x-for="acquisition in recentAcquisitions" :key="acquisition.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="acquisition.skill_name">
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Turn <span x-text="acquisition.turn_acquired"></span> •
                                    <span x-text="acquisition.career_phase"></span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900 dark:text-white"
                                x-text="`${acquisition.final_sp_cost} SP`"></p>
                            <p x-show="acquisition.sp_saved > 0" class="text-sm text-green-600 dark:text-green-400"
                                x-text="`Saved ${acquisition.sp_saved} SP`"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/skills/partials/acquisition.blade.php ENDPATH**/ ?>