{{-- Skill Inventory Tab --}}
<div class="space-y-6">
    {{-- Filters --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="skill-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Search Skills
                    </label>
                    <input type="text" id="skill-search" x-model="filters.searchQuery"
                        placeholder="Search by name or description..."
                        class="form-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                </div>

                {{-- Skill Type Filter --}}
                <div>
                    <label for="skill-type-filter"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Skill Type
                    </label>
                    <select id="skill-type-filter" x-model="filters.skillType"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Types</option>
                        <option value="speed">Speed</option>
                        <option value="passive">Passive</option>
                        <option value="recovery">Recovery</option>
                        <option value="debuff">Debuff</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>

                {{-- Rarity Filter --}}
                <div>
                    <label for="rarity-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Rarity
                    </label>
                    <select id="rarity-filter" x-model="filters.rarity"
                        class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Rarities</option>
                        <option value="normal">Normal</option>
                        <option value="rare">Rare</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Acquired Skills Section --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Acquired Skills
                <span class="ml-2 text-sm font-normal text-gray-500" x-text="`(${acquiredSkills.length})`"></span>
            </h3>
        </div>
        <div class="card-body">
            <div x-show="acquiredSkills.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No skills acquired yet</p>
            </div>

            <div x-show="acquiredSkills.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="skill in acquiredSkills" :key="skill.id">
                    <div
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        {{-- Skill Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="skill.name"></h4>
                                <div class="flex items-center gap-2 mt-1">
                                    {{-- Rarity Badge --}}
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="{
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': skill
                                                .rarity === 'normal',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': skill
                                                .rarity === 'rare',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': skill
                                                .rarity === 'unique'
                                        }"
                                        x-text="skill.rarity.charAt(0).toUpperCase() + skill.rarity.slice(1)"></span>

                                    {{-- Meta Tier Badge --}}
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': skill
                                                .meta_tier === 'S+' || skill.meta_tier === 'S',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': skill
                                                .meta_tier === 'A',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': skill
                                                .meta_tier === 'B',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': skill
                                                .meta_tier === 'C'
                                        }"
                                        x-text="skill.meta_tier"></span>

                                    {{-- Evolution Badge --}}
                                    <span x-show="skill.is_evolution"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Evolved
                                    </span>
                                </div>
                            </div>

                            {{-- SP Cost --}}
                            <div class="text-right ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">SP Cost</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="skill.final_sp_cost || skill.base_sp_cost"></p>
                                <p x-show="skill.sp_saved > 0" class="text-xs text-green-600 dark:text-green-400"
                                    x-text="`Saved: ${skill.sp_saved}`"></p>
                            </div>
                        </div>

                        {{-- Skill Description --}}
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="skill.description"></p>

                        {{-- Skill Effects --}}
                        <div x-show="skill.effects" class="mb-3">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Effects:</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400" x-text="skill.effects"></p>
                        </div>

                        {{-- Hint Progress --}}
                        <div x-show="skill.hint_count > 0" class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Hints Used</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="`${skill.hint_count}/2`"></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full transition-all"
                                    :style="`width: ${(skill.hint_count / 2) * 100}%`"></div>
                            </div>
                        </div>

                        {{-- Performance Stats --}}
                        <div x-show="skill.races_used > 0" class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Races Used</p>
                                    <p class="font-semibold text-gray-900 dark:text-white" x-text="skill.races_used">
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Effectiveness</p>
                                    <p class="font-semibold text-gray-900 dark:text-white"
                                        x-text="skill.effectiveness_rating || 'N/A'"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 mt-3">
                            <button @click="viewSkillDetails(skill)" class="flex-1 btn btn-sm btn-secondary">
                                View Details
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Available Skills Section --}}
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Available Skills
                <span class="ml-2 text-sm font-normal text-gray-500"
                    x-text="`(${filteredSkills.filter(s => !s.is_acquired).length})`"></span>
            </h3>
        </div>
        <div class="card-body">
            <div x-show="filteredSkills.filter(s => !s.is_acquired).length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No available skills match your filters</p>
            </div>

            <div x-show="filteredSkills.filter(s => !s.is_acquired).length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="skill in filteredSkills.filter(s => !s.is_acquired)" :key="skill.id">
                    <div
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        {{-- Skill Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="skill.name"></h4>
                                <div class="flex items-center gap-2 mt-1">
                                    {{-- Rarity Badge --}}
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="{
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': skill
                                                .rarity === 'normal',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': skill
                                                .rarity === 'rare',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': skill
                                                .rarity === 'unique'
                                        }"
                                        x-text="skill.rarity.charAt(0).toUpperCase() + skill.rarity.slice(1)"></span>

                                    {{-- Meta Tier Badge --}}
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': skill
                                                .meta_tier === 'S+' || skill.meta_tier === 'S',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': skill
                                                .meta_tier === 'A',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': skill
                                                .meta_tier === 'B',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': skill
                                                .meta_tier === 'C'
                                        }"
                                        x-text="skill.meta_tier"></span>
                                </div>
                            </div>

                            {{-- SP Cost --}}
                            <div class="text-right ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Base SP</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white"
                                    x-text="skill.base_sp_cost"></p>
                            </div>
                        </div>

                        {{-- Skill Description --}}
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="skill.description"></p>

                        {{-- Hint Progress --}}
                        <div x-show="skill.available_hints > 0"
                            class="mb-3 p-2 bg-green-50 dark:bg-green-900/20 rounded">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-green-700 dark:text-green-400 font-medium">Hints Available</span>
                                <span class="font-bold text-green-900 dark:text-green-300"
                                    x-text="`${skill.available_hints}/2`"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-green-600 dark:text-green-400">Discounted Cost</span>
                                <span class="font-bold text-green-900 dark:text-green-300"
                                    x-text="skill.discounted_cost"></span>
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 mt-1"
                                x-text="`Save ${skill.sp_savings} SP!`"></div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 mt-3">
                            <button @click="viewSkillDetails(skill)" class="flex-1 btn btn-sm btn-secondary">
                                View Details
                            </button>
                            <button @click="acquireSkill(skill)"
                                :disabled="!character || (character.available_sp || 0) < (skill.discounted_cost || skill
                                    .base_sp_cost)"
                                class="flex-1 btn btn-sm btn-primary"
                                :class="{ 'opacity-50 cursor-not-allowed': !character || (character.available_sp || 0) < (skill
                                        .discounted_cost || skill.base_sp_cost) }">
                                Acquire
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
