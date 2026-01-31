{{-- Skill Acquisition Tab --}}
<div class="space-y-6">
    {{-- AI Recommendations --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Recommendations</h3>
            <button @click="getAIRecommendations()" :disabled="loading" class="btn btn-sm btn-primary">
                <span x-show="!loading">Get Recommendations</span>
                <span x-show="loading">Loading...</span>
            </button>
        </div>
        <div class="card-body">
            <div x-show="recommendations.length > 0" class="space-y-4">
                <template x-for="rec in recommendations" :key="rec.skill_id">
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="rec.skill_name"></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="rec.reason"></p>
                            </div>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                <span x-text="rec.final_cost"></span> SP
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>Priority: <span x-text="rec.priority"></span></span>
                            <span x-show="rec.sp_savings > 0" class="text-green-600 dark:text-green-400">
                                Save <span x-text="rec.sp_savings"></span> SP
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="recommendations.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                Click "Get Recommendations" to receive AI-powered skill suggestions
            </div>
        </div>
    </div>

    {{-- SP Planning --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SP Planning</h3>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Available SP</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="character?.available_sp || 0">
                    </p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Planned Spending</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="plannedSpending"></p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Remaining</p>
                    <p class="text-2xl font-bold"
                        :class="(character?.available_sp || 0) - plannedSpending >= 0 ?
                            'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                        x-text="(character?.available_sp || 0) - plannedSpending">
                    </p>
                </div>
            </div>

            {{-- SP Budget Bar --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">SP Budget</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="`${Math.round((plannedSpending / (character?.available_sp || 1)) * 100)}%`">
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all"
                        :class="(plannedSpending / (character?.available_sp || 1)) > 1 ? 'bg-red-500' : 'bg-primary-500'"
                        :style="`width: ${Math.min((plannedSpending / (character?.available_sp || 1)) * 100, 100)}%`">
                    </div>
                </div>
            </div>

            {{-- Hint Optimization --}}
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-200">Hint Optimization</h4>
                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        <span x-text="skillsWithHints"></span> skills
                    </span>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Potential savings: <span class="font-semibold" x-text="potentialSavings"></span> SP
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    Total available with hints: <span x-text="(character?.available_sp || 0) + potentialSavings"></span>
                    SP
                </p>
            </div>
        </div>
    </div>

    {{-- Recent Acquisitions --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Acquisitions</h3>
        </div>
        <div class="card-body">
            <div x-show="recentAcquisitions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No recent acquisitions
            </div>
            <div x-show="recentAcquisitions.length > 0" class="space-y-3">
                <template x-for="skill in recentAcquisitions" :key="skill.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white" x-text="skill.name"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Turn <span x-text="skill.turn_acquired"></span>
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            <span x-text="skill.final_sp_cost"></span> SP
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
