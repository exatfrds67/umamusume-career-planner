{{-- Skill Inventory Tab --}}
<div class="space-y-6">
    {{-- Filters --}}
    <div class="filter-surface">
        <div class="card-body space-y-4">
            {{-- Filter toggle for mobile --}}
            <div class="flex items-center justify-between sm:hidden">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors min-h-[44px]"
                    :aria-expanded="filtersOpen"
                    aria-controls="filter-panel">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span>Filters &amp; Sort</span>
                    <span
                        x-show="activeFilterCount > 0"
                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-500 text-white text-xs font-bold"
                        x-text="activeFilterCount"
                        aria-label="active filters"></span>
                </button>
                <button
                    x-show="activeFilterCount > 0"
                    type="button"
                    @click="clearAllFilters()"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 rounded px-1">Clear all</button>
            </div>

            <fieldset id="filter-panel" x-show="filtersOpen" x-transition class="!block sm:!block">
                <legend class="sr-only">Filter and sort skills</legend>
                <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
                <div class="sm:col-span-2 lg:col-span-2">
                    <label for="search-skills" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Search Skills
                    </label>
                    <div class="relative">
                        <input type="text" id="search-skills" name="search_skills" x-model="filters.searchQuery"
                            @input="resetPagination()" placeholder="Search by skill name or description"
                            class="form-input w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white pr-8"
                            aria-describedby="search-skills-hint">
                        <button x-show="filters.searchQuery" @click="filters.searchQuery = ''; resetPagination()"
                            type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                            aria-label="Clear search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <span id="search-skills-hint" class="sr-only">Searches both skill name and description</span>
                </div>
                <div>
                    <label for="filter-stat-affinity"
                        class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Stat
                    </label>
                    <select id="filter-stat-affinity" name="stat_affinity" x-model="filters.statAffinity"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="all">All Stats</option>
                        <option value="speed">🏃 SPD</option>
                        <option value="stamina">💪 STA</option>
                        <option value="power">⚡ POW</option>
                        <option value="guts">🔥 GUT</option>
                        <option value="wisdom">🧠 WIT</option>
                    </select>
                </div>
                <div>
                    <label for="filter-type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Type
                    </label>
                    <select id="filter-type" name="skill_type" x-model="filters.skillType" @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="all">All Types</option>
                        <option value="speed">Speed</option>
                        <option value="passive">Passive</option>
                        <option value="recovery">Recovery</option>
                        <option value="debuff">Debuff</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
                <div>
                    <label for="filter-rarity" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Rarity
                    </label>
                    <select id="filter-rarity" name="rarity" x-model="filters.rarity" @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="all">All Rarities</option>
                        <option value="normal">Normal</option>
                        <option value="rare">Rare</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
                <div>
                    <label for="filter-hint-level"
                        class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Hint Level
                    </label>
                    <select id="filter-hint-level" name="hint_level" x-model="filters.hintLevel"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="all">All Levels</option>
                        <option value="0">No Hints</option>
                        <option value="1">Lv 1 (10% off)</option>
                        <option value="2">Lv 2 (20% off)</option>
                        <option value="3">Lv 3 (30% off)</option>
                        <option value="4">Lv 4 (35% off)</option>
                        <option value="5">Lv 5 (40% off)</option>
                    </select>
                </div>
                <div>
                    <label for="filter-meta-tier"
                        class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Grade
                    </label>
                    <select id="filter-meta-tier" name="meta_tier" x-model="filters.metaTier"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="all">All Grades</option>
                        <option value="S+">S+</option>
                        <option value="S">S</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>

                {{-- Sort By --}}
                <div>
                    <label for="sort-by"
                        class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Sort By
                    </label>
                    <select id="sort-by" name="sort_by" x-model="sortBy"
                        @change="resetPagination()"
                        class="form-select w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="status">Status</option>
                        <option value="name_asc">Name A – Z</option>
                        <option value="name_desc">Name Z – A</option>
                        <option value="sp_cost_desc">SP Cost (High – Low)</option>
                        <option value="sp_cost_asc">SP Cost (Low – High)</option>
                        <option value="sp_savings_desc">SP Savings (High – Low)</option>
                        <option value="grade_desc">Grade (S+ first)</option>
                    </select>
                </div>
                </div>
            </fieldset>

            {{-- Desktop: clear all link shown when filters are active --}}
            <div class="hidden sm:flex items-center justify-end" x-show="activeFilterCount > 0">
                <button
                    type="button"
                    @click="clearAllFilters()"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 rounded px-1">
                    Clear all filters
                </button>
            </div>
        </div>
    </div>

    {{-- Results Summary + Interactive Status Chips --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-0 sm:justify-between text-sm text-neutral-600 dark:text-neutral-400">
        <div role="status" aria-live="polite" aria-atomic="true"
            x-text="`Showing ${((currentPage - 1) * itemsPerPage) + 1} to ${Math.min(currentPage * itemsPerPage, filteredSkills.length)} of ${filteredSkills.length} matching ${filteredSkills.length === 1 ? 'skill' : 'skills'}`">
        </div>

        {{-- Status quick-filter chips --}}
        <div class="flex items-center gap-2 flex-wrap" role="group" aria-label="Filter by status">
            {{-- Acquired chip --}}
            <button
                type="button"
                @click="toggleStatusFilter('acquired')"
                :aria-pressed="filters.statusFilter === 'acquired' ? 'true' : 'false'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all min-h-[36px] focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-1"
                :class="filters.statusFilter === 'acquired'
                    ? 'bg-success-500 border-success-500 text-white shadow-sm'
                    : 'bg-success-50 border-success-300 text-success-700 hover:bg-success-100 dark:bg-success-900/20 dark:border-success-700 dark:text-success-300 dark:hover:bg-success-900/40'">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span x-text="acquiredSkills.length + ' Acquired'"></span>
            </button>

            {{-- Planned chip --}}
            <button
                type="button"
                @click="toggleStatusFilter('planned')"
                :aria-pressed="filters.statusFilter === 'planned' ? 'true' : 'false'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all min-h-[36px] focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1"
                :class="filters.statusFilter === 'planned'
                    ? 'bg-amber-500 border-amber-500 text-white shadow-sm'
                    : 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/20 dark:border-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/40'">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span x-text="plannedSkills.length + ' Planned'"></span>
            </button>

            {{-- Clear status chip --}}
            <button
                type="button"
                x-show="filters.statusFilter !== 'all'"
                @click="toggleStatusFilter('all')"
                class="inline-flex items-center gap-1 px-2 py-1.5 rounded-full text-xs font-medium border border-neutral-300 dark:border-neutral-600 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-all min-h-[36px] focus:outline-none focus:ring-2 focus:ring-neutral-400"
                aria-label="Clear status filter">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>All</span>
            </button>
        </div>
    </div>

    {{-- Skills List --}}
    <div id="skills-list" class="filter-surface">
        <div class="card-body">
            <div x-show="paginatedSkills.length === 0" class="text-center py-12 text-neutral-500 dark:text-neutral-400">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4">No skills found matching your filters</p>
            </div>

            <div x-show="paginatedSkills.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="skill in paginatedSkills" :key="skill.id">
                    <div class="relative p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-lg cursor-pointer"
                        :class="{
                            'border-success-500 bg-success-50 dark:bg-neutral-800 dark:border-success-400': skill.is_acquired,
                            'border-amber-400 bg-amber-50 dark:bg-neutral-800 dark:border-amber-400': !skill.is_acquired && skill.is_planned,
                            'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:border-primary-500 dark:hover:border-primary-400':
                                !skill.is_acquired && !skill.is_planned
                        }"
                        @click="viewSkillDetails(skill)"
                        @keydown.enter.prevent="viewSkillDetails(skill)"
                        @keydown.space.prevent="viewSkillDetails(skill)"
                        role="button"
                        tabindex="0"
                        :aria-label="`${skill.name}${skill.is_acquired ? ' — Acquired' : skill.is_planned ? ' — Planned' : ''}. ${skill.available_hints > 0 ? skill.discounted_cost + ' SP discounted from ' + skill.base_sp_cost : skill.base_sp_cost + ' SP'}. Click to view details.`">

                        {{-- Header: Name, Grade, Type --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-neutral-900 dark:text-white line-clamp-2 leading-snug mb-1.5"
                                    :title="skill.name"
                                    x-text="skill.name"></h4>
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- Stat Affinity Badge (Primary) --}}
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-bold"
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
                                        <span aria-hidden="true" x-text="getStatAffinityDisplay(skill).icon"></span>
                                        <span x-text="getStatAffinityDisplay(skill).label"></span>
                                    </span>

                                    {{-- Grade Badge: shape icon + letter for colour-blind differentiation --}}
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': getSkillGrade(
                                                skill) === 'S+',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': getSkillGrade(
                                                skill) === 'S',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': getSkillGrade(
                                                skill) === 'A',
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': getSkillGrade(
                                                skill) === 'B',
                                            'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300': getSkillGrade(
                                                skill) === 'C'
                                        }"
                                        :aria-label="'Grade: ' + getSkillGrade(skill)">
                                        <span class="text-[0.55rem] leading-none opacity-75" aria-hidden="true"
                                            x-text="getGradeShapeChar(getSkillGrade(skill))"></span>
                                        <span x-text="getSkillGrade(skill)"></span>
                                    </span>

                                    {{-- Skill Type Badge --}}
                                    <span
                                        class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': skill
                                                .skill_type === 'speed',
                                            'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300': skill
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

                                    {{-- Rarity Badge (only if not normal) --}}
                                    <span x-show="skill.rarity !== 'normal'"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold"
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

                            {{-- Status Badge: Acquired or Planned --}}
                            <div class="shrink-0 flex flex-col items-end gap-1">
                                <div x-show="skill.is_acquired"
                                    class="bg-success-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Acquired</span>
                                </div>
                                <div x-show="!skill.is_acquired && skill.is_planned"
                                    class="bg-amber-400 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1 dark:bg-amber-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span>Planned</span>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mb-3 line-clamp-2"
                            x-text="skill.description || 'No description available'"></p>

                        {{-- Footer: SP Cost, Hints, Action Button --}}
                        <div
                            class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-700">
                            {{-- SP Cost: single canonical display --}}
                            <div class="flex items-center gap-2">
                                <template x-if="skill.available_hints > 0">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="text-xs text-neutral-400 dark:text-neutral-500 line-through tabular-nums"
                                            x-text="skill.base_sp_cost + ' SP'"></span>
                                        <span
                                            class="font-bold text-base text-success-600 dark:text-success-400 tabular-nums"
                                            x-text="skill.discounted_cost + ' SP'"
                                            :aria-label="'Cost: ' + skill.discounted_cost + ' SP, discounted from ' + skill.base_sp_cost"></span>
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-bold bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300"
                                            aria-hidden="true"
                                            x-text="'-' + Math.round((skill.sp_savings / skill.base_sp_cost) * 100) + '%'">
                                        </span>
                                    </div>
                                </template>
                                <template x-if="!skill.available_hints || skill.available_hints === 0">
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-base text-neutral-900 dark:text-white tabular-nums"
                                            x-text="skill.base_sp_cost"
                                            :aria-label="'Cost: ' + skill.base_sp_cost + ' SP'"></span>
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400" aria-hidden="true">SP</span>
                                    </div>
                                </template>
                            </div>

                            {{-- Hint Level Stars --}}
                            <div x-show="skill.available_hints > 0" class="flex items-center gap-1">
                                <span class="text-sm" :class="getHintLevelColor(skill.available_hints)"
                                    aria-hidden="true"
                                    x-text="getHintStars(skill.available_hints)">
                                </span>
                                <span class="text-xs font-semibold text-primary-600 dark:text-primary-400"
                                    :aria-label="'Hint level ' + skill.available_hints"
                                    x-text="'Lv' + skill.available_hints">
                                </span>
                            </div>
                        </div>

                        {{-- Activation Condition --}}
                        <template x-if="getActivationCondition(skill)">
                            <div class="mt-2 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <div class="flex items-center gap-2 text-xs text-neutral-600 dark:text-neutral-400">
                                    <span class="text-yellow-500">⚡</span>
                                    <span x-text="getActivationCondition(skill)"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Evolution Path --}}
                        <template x-if="getEvolutionInfo(skill)">
                            <div class="mt-2 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-purple-500">🔄</span>
                                    <span class="text-neutral-600 dark:text-neutral-400">Can evolve to:</span>
                                    <span class="font-semibold text-purple-600 dark:text-purple-400"
                                        x-text="getEvolutionInfo(skill).targetName"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Acquire Button (only for non-acquired catalog skills) --}}
                        <div x-show="!skill.is_acquired && !skill.is_metadata_only" class="mt-3">
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

    {{-- Pagination --}}
    <div x-show="totalPages > 1" class="flex items-center justify-between">
        <div class="text-sm text-neutral-600 dark:text-neutral-400">
            Page <span class="font-semibold" x-text="currentPage"></span> of <span class="font-semibold"
                x-text="totalPages"></span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="goToPage(1)" :disabled="currentPage === 1"
                aria-label="Go to first page"
                class="px-3 py-1 rounded border border-neutral-300 dark:border-neutral-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-neutral-50 dark:hover:bg-neutral-700">
                First
            </button>
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                aria-label="Go to previous page"
                class="px-3 py-1 rounded border border-neutral-300 dark:border-neutral-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-neutral-50 dark:hover:bg-neutral-700">
                Previous
            </button>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                aria-label="Go to next page"
                class="px-3 py-1 rounded border border-neutral-300 dark:border-neutral-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-neutral-50 dark:hover:bg-neutral-700">
                Next
            </button>
            <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages"
                aria-label="Go to last page"
                class="px-3 py-1 rounded border border-neutral-300 dark:border-neutral-600 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-neutral-50 dark:hover:bg-neutral-700">
                Last
            </button>
        </div>
    </div>
</div>
