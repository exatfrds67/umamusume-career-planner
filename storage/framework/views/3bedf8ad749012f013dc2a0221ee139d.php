
<div class="space-y-6">
    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label for="search-skills" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Search Skills
                    </label>
                    <input type="text" id="search-skills" name="search_skills" x-model="filters.searchQuery"
                        @input="resetPagination()" placeholder="Search by name..."
                        class="form-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="filter-stat-affinity"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Stat Affinity
                    </label>
                    <select id="filter-stat-affinity" name="stat_affinity" x-model="filters.statAffinity"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Stats</option>
                        <option value="speed">🏃 SPD</option>
                        <option value="stamina">💪 STA</option>
                        <option value="power">⚡ POW</option>
                        <option value="guts">🔥 GUT</option>
                        <option value="wisdom">🧠 WIT</option>
                    </select>
                </div>
                <div>
                    <label for="filter-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Skill Type
                    </label>
                    <select id="filter-type" name="skill_type" x-model="filters.skillType" @change="resetPagination()"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Types</option>
                        <option value="speed">Speed</option>
                        <option value="passive">Passive</option>
                        <option value="recovery">Recovery</option>
                        <option value="debuff">Debuff</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
                <div>
                    <label for="filter-rarity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Rarity
                    </label>
                    <select id="filter-rarity" name="rarity" x-model="filters.rarity" @change="resetPagination()"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Rarities</option>
                        <option value="normal">Normal</option>
                        <option value="rare">Rare</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
                <div>
                    <label for="filter-hint-level"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Hint Level
                    </label>
                    <select id="filter-hint-level" name="hint_level" x-model="filters.hintLevel"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Levels</option>
                        <option value="0">No Hints (0)</option>
                        <option value="1">Level 1 (10% off)</option>
                        <option value="2">Level 2 (20% off)</option>
                        <option value="3">Level 3 (30% off)</option>
                        <option value="4">Level 4 (35% off)</option>
                        <option value="5">Level 5 (40% off)</option>
                    </select>
                </div>
                <div>
                    <label for="filter-meta-tier"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Grade
                    </label>
                    <select id="filter-meta-tier" name="meta_tier" x-model="filters.metaTier"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Grades</option>
                        <option value="S+">S+</option>
                        <option value="S">S</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    
    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
        <div>
            Showing <span class="font-semibold" x-text="((currentPage - 1) * itemsPerPage) + 1"></span>
            to <span class="font-semibold" x-text="Math.min(currentPage * itemsPerPage, filteredSkills.length)"></span>
            of <span class="font-semibold" x-text="filteredSkills.length"></span> skills
        </div>
        <div>
            <span class="font-semibold" x-text="acquiredSkills.length"></span> acquired
        </div>
    </div>

    
    <div id="skills-list" class="card bg-white dark:bg-gray-800">
        <div class="card-body">
            <div x-show="paginatedSkills.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4">No skills found matching your filters</p>
            </div>

            <div x-show="paginatedSkills.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="skill in paginatedSkills" :key="skill.id">
                    <div class="relative p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-lg cursor-pointer"
                        :class="{
                            'border-success-500 bg-success-50 dark:bg-success-900/20': skill.is_acquired,
                            'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-500 dark:hover:border-primary-400':
                                !skill
                                .is_acquired
                        }"
                        @click="viewSkillDetails(skill)">

                        
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-bold text-gray-900 dark:text-white truncate mb-1"
                                    x-text="skill.name"></h4>
                                <div class="flex items-center gap-2 flex-wrap">
                                    
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold"
                                        :class="{
                                            'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300': getStatAffinity(
                                                skill) === 'speed',
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': getStatAffinity(
                                                skill) === 'stamina',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300': getStatAffinity(
                                                skill) === 'power',
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': getStatAffinity(
                                                skill) === 'guts',
                                            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300': getStatAffinity(
                                                skill) === 'wisdom'
                                        }">
                                        <span x-text="getStatAffinityDisplay(skill).icon"></span>
                                        <span x-text="getStatAffinityDisplay(skill).label"></span>
                                    </span>

                                    
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': getSkillGrade(
                                                skill) === 'S+',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': getSkillGrade(
                                                skill) === 'S',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': getSkillGrade(
                                                skill) === 'A',
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': getSkillGrade(
                                                skill) === 'B',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': getSkillGrade(
                                                skill) === 'C'
                                        }"
                                        x-text="getSkillGrade(skill)">
                                    </span>

                                    
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': skill
                                                .skill_type === 'speed',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': skill
                                                .skill_type === 'passive',
                                            'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300': skill
                                                .skill_type === 'recovery',
                                            'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300': skill
                                                .skill_type === 'debuff',
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': skill
                                                .skill_type === 'unique'
                                        }"
                                        x-text="getSkillTypeDisplay(skill.skill_type)">
                                    </span>

                                    
                                    <span x-show="skill.rarity !== 'normal'"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200': skill
                                                .rarity === 'rare',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200': skill
                                                .rarity === 'unique'
                                        }"
                                        x-text="skill.rarity.charAt(0).toUpperCase() + skill.rarity.slice(1)">
                                    </span>
                                </div>
                            </div>

                            
                            <div x-show="skill.is_acquired" class="shrink-0">
                                <div
                                    class="bg-success-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Acquired</span>
                                </div>
                            </div>
                        </div>

                        
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-2"
                            x-text="skill.description || 'No description available'"></p>

                        
                        <div
                            class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                            
                            <div class="flex flex-col gap-1">
                                <template x-if="skill.available_hints > 0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs text-gray-500 dark:text-gray-400 line-through tabular-nums"
                                            x-text="skill.base_sp_cost + ' SP'"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">→</span>
                                        <span
                                            class="font-bold text-lg text-success-600 dark:text-success-400 tabular-nums"
                                            x-text="skill.discounted_cost + ' SP'"></span>
                                        <span class="text-xs font-semibold text-success-600 dark:text-success-400"
                                            x-text="`-${Math.round((skill.sp_savings / skill.base_sp_cost) * 100)}%`">
                                        </span>
                                    </div>
                                </template>
                                <template x-if="!skill.available_hints || skill.available_hints === 0">
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-lg text-gray-900 dark:text-white tabular-nums"
                                            x-text="skill.base_sp_cost"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">SP</span>
                                    </div>
                                </template>
                                <template x-if="skill.available_hints > 0">
                                    <div class="text-xs text-success-600 dark:text-success-400 font-medium"
                                        x-text="`Save ${skill.sp_savings} SP!`">
                                    </div>
                                </template>
                            </div>

                            
                            <div x-show="skill.available_hints > 0" class="flex flex-col items-end gap-1">
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-medium" :class="getHintLevelColor(skill.available_hints)"
                                        x-text="getHintStars(skill.available_hints)">
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-primary-600 dark:text-primary-400"
                                    x-text="`Lv${skill.available_hints}`">
                                </span>
                            </div>
                        </div>

                        
                        <template x-if="getActivationCondition(skill)">
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="text-yellow-500">⚡</span>
                                    <span x-text="getActivationCondition(skill)"></span>
                                </div>
                            </div>
                        </template>

                        
                        <template x-if="getEvolutionInfo(skill)">
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-purple-500">🔄</span>
                                    <span class="text-gray-600 dark:text-gray-400">Can evolve to:</span>
                                    <span class="font-semibold text-purple-600 dark:text-purple-400"
                                        x-text="getEvolutionInfo(skill).targetName"></span>
                                </div>
                            </div>
                        </template>

                        
                        <div x-show="!skill.is_acquired" class="mt-3">
                            <button @click.stop="acquireSkill(skill)" :disabled="loading"
                                class="w-full btn btn-sm btn-primary flex items-center justify-center gap-2"
                                :class="{ 'opacity-50 cursor-not-allowed': loading }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Acquire Skill</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div x-show="totalPages > 1" class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold" x-text="currentPage"></span> of <span class="font-semibold"
                x-text="totalPages"></span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="goToPage(1)" :disabled="currentPage === 1"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700">
                First
            </button>
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700">
                Previous
            </button>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700">
                Next
            </button>
            <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700">
                Last
            </button>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/skills/partials/inventory.blade.php ENDPATH**/ ?>