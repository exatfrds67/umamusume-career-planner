{{--
Component: SkillShopList
Purpose: Searchable, filterable list of skills with type/tier filtering
Props:
  - skills (Collection|array, required): Skill data collection
  - showSearch (bool, optional): Show search input (default: true)
  - showFilters (bool, optional): Show filter panel (default: true)
  - selectable (bool, optional): Allow skill selection (default: false)
Usage:
  <x-skill-shop-list :skills="$skills" />
Accessibility: WCAG 2.2 AA compliant, keyboard navigation
--}}

@props([
    'skills' => [],
    'showSearch' => true,
    'showFilters' => true,
    'selectable' => false,
])

@php
    $skillArray = $skills instanceof \Illuminate\Support\Collection 
        ? $skills->toArray() 
        : $skills;
@endphp

<div 
    x-data="{
        skills: {{ json_encode($skillArray) }},
        filteredSkills: [],
        searchQuery: '',
        sortBy: 'name',
        filters: {
            type: [],
            tier: [],
            spCost: 'all'
        },
        selectedSkills: [],
        
        init() {
            this.applyFilters();
        },
        
        applyFilters() {
            let result = this.skills;
            
            // Search filter
            if (this.searchQuery.length > 0) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(skill => 
                    skill.name.toLowerCase().includes(query) ||
                    (skill.description && skill.description.toLowerCase().includes(query))
                );
            }
            
            // Type filter
            if (this.filters.type.length > 0) {
                result = result.filter(skill => 
                    this.filters.type.includes(skill.type)
                );
            }
            
            // Tier filter
            if (this.filters.tier.length > 0) {
                result = result.filter(skill => 
                    this.filters.tier.includes(skill.tier)
                );
            }
            
            // SP Cost filter
            if (this.filters.spCost !== 'all') {
                const ranges = {
                    'low': [0, 100],
                    'medium': [101, 200],
                    'high': [201, 999]
                };
                const [min, max] = ranges[this.filters.spCost];
                result = result.filter(skill => 
                    skill.sp_cost >= min && skill.sp_cost <= max
                );
            }
            
            // Sort
            result = this.sortSkills(result);
            
            this.filteredSkills = result;
        },
        
        sortSkills(skills) {
            const sorted = [...skills];
            
            switch(this.sortBy) {
                case 'name':
                    return sorted.sort((a, b) => a.name.localeCompare(b.name));
                case 'sp_cost':
                    return sorted.sort((a, b) => a.sp_cost - b.sp_cost);
                case 'tier':
                    return sorted.sort((a, b) => {
                        const tierOrder = { 'S': 4, 'A': 3, 'B': 2, 'C': 1 };
                        return (tierOrder[b.tier] || 0) - (tierOrder[a.tier] || 0);
                    });
                default:
                    return sorted;
            }
        },
        
        toggleFilter(type, value) {
            const index = this.filters[type].indexOf(value);
            if (index > -1) {
                this.filters[type].splice(index, 1);
            } else {
                this.filters[type].push(value);
            }
            this.applyFilters();
        },
        
        clearFilters() {
            this.searchQuery = '';
            this.filters = { type: [], tier: [], spCost: 'all' };
            this.sortBy = 'name';
            this.applyFilters();
        },
        
        toggleSkillSelection(skillId) {
            const index = this.selectedSkills.indexOf(skillId);
            if (index > -1) {
                this.selectedSkills.splice(index, 1);
            } else {
                this.selectedSkills.push(skillId);
            }
            this.$dispatch('skills-selected', { skills: this.selectedSkills });
        },
        
        get activeFilterCount() {
            return this.filters.type.length + this.filters.tier.length + 
                   (this.filters.spCost !== 'all' ? 1 : 0) +
                   (this.searchQuery.length > 0 ? 1 : 0);
        }
    }"
    {{ $attributes->merge(['class' => 'skill-shop-list space-y-6']) }}
>
    {{-- Controls Bar --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        {{-- Search Input --}}
        @if($showSearch)
            <div class="w-full sm:w-auto sm:flex-1">
                <x-search-input 
                    name="skill-search"
                    placeholder="Search skills..."
                    x-model="searchQuery"
                    @input="applyFilters()"
                />
            </div>
        @endif
        
        {{-- Sort & Filter Controls --}}
        <div class="flex gap-3 items-center w-full sm:w-auto">
            <x-sort-dropdown
                name="skill-sort"
                :options="[
                    'name' => 'Name (A-Z)',
                    'sp_cost' => 'SP Cost (Low-High)',
                    'tier' => 'Tier (S-C)'
                ]"
                x-model="sortBy"
                @change="applyFilters()"
            />
            
            @if($showFilters)
                <button 
                    @click="$refs.filterPanel.classList.toggle('hidden')"
                    class="btn btn-secondary inline-flex items-center gap-2"
                    aria-label="Toggle filters"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                    <span 
                        x-show="activeFilterCount > 0"
                        x-text="activeFilterCount"
                        class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-primary-500 text-white"
                    ></span>
                </button>
            @endif
        </div>
    </div>
    
    {{-- Filter Panel (Collapsible) --}}
    @if($showFilters)
        <div x-ref="filterPanel" class="hidden border-t border-neutral-200 dark:border-neutral-700 pt-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Skill Type Filters --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                        Skill Type
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['speed', 'acceleration', 'stamina', 'power', 'guts', 'wit'] as $type)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                                :class="filters.type.includes('{{ $type }}') 
                                    ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900/20 dark:border-primary-600 dark:text-primary-300' 
                                    : 'bg-white border-neutral-300 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-700'"
                            >
                                <input 
                                    type="checkbox" 
                                    class="sr-only"
                                    @change="toggleFilter('type', '{{ $type }}')"
                                    :checked="filters.type.includes('{{ $type }}')"
                                >
                                <span class="text-sm font-medium capitalize">{{ $type }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- Tier Filters --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                        Tier
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['S', 'A', 'B', 'C'] as $tier)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                                :class="filters.tier.includes('{{ $tier }}') 
                                    ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900/20 dark:border-primary-600 dark:text-primary-300' 
                                    : 'bg-white border-neutral-300 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-700'"
                            >
                                <input 
                                    type="checkbox" 
                                    class="sr-only"
                                    @change="toggleFilter('tier', '{{ $tier }}')"
                                    :checked="filters.tier.includes('{{ $tier }}')"
                                >
                                <span class="text-sm font-medium">{{ $tier }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- SP Cost Range --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                        SP Cost
                    </label>
                    <select 
                        x-model="filters.spCost"
                        @change="applyFilters()"
                        class="form-select rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
                    >
                        <option value="all">All Costs</option>
                        <option value="low">Low (0-100)</option>
                        <option value="medium">Medium (101-200)</option>
                        <option value="high">High (201+)</option>
                    </select>
                </div>
            </div>
            
            {{-- Clear Filters --}}
            <div class="mt-4 flex justify-end">
                <button 
                    @click="clearFilters()"
                    class="btn btn-outline btn-sm"
                    x-show="activeFilterCount > 0"
                >
                    Clear All Filters
                </button>
            </div>
            
            {{-- Active Filter Badges --}}
            <div x-show="activeFilterCount > 0" class="mt-4 flex flex-wrap gap-2">
                <template x-for="type in filters.type" :key="'type-' + type">
                    <x-filter-badge 
                        x-bind:label="'Type: ' + type"
                        x-on:click="toggleFilter('type', type)"
                    />
                </template>
                <template x-for="tier in filters.tier" :key="'tier-' + tier">
                    <x-filter-badge 
                        x-bind:label="'Tier: ' + tier"
                        x-on:click="toggleFilter('tier', tier)"
                    />
                </template>
                <template x-if="filters.spCost !== 'all'">
                    <x-filter-badge 
                        x-bind:label="'SP: ' + filters.spCost"
                        x-on:click="filters.spCost = 'all'; applyFilters()"
                    />
                </template>
            </div>
        </div>
    @endif
    
    {{-- Results Summary --}}
    <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-400">
        <p>
            <span class="font-semibold" x-text="filteredSkills.length"></span>
            <span x-text="filteredSkills.length === 1 ? 'skill' : 'skills'"></span>
            <template x-if="activeFilterCount > 0">
                <span>
                    (filtered from <span x-text="skills.length"></span>)
                </span>
            </template>
        </p>
        
        @if($selectable)
            <p x-show="selectedSkills.length > 0" class="font-semibold text-primary-600 dark:text-primary-400">
                <span x-text="selectedSkills.length"></span> selected
            </p>
        @endif
    </div>
    
    {{-- Skill List --}}
    <div 
        x-show="filteredSkills.length > 0"
        class="space-y-3"
    >
        <template x-for="skill in filteredSkills" :key="skill.id">
            <div 
                class="skill-item p-4 rounded-lg border transition-all"
                :class="{
                    'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:shadow-md': !selectedSkills.includes(skill.id),
                    'border-primary-500 bg-primary-50 dark:bg-primary-900/20': selectedSkills.includes(skill.id) && {{ $selectable ? 'true' : 'false' }}
                }"
                @if($selectable)
                    @click="toggleSkillSelection(skill.id)"
                    role="button"
                    tabindex="0"
                    @keydown.enter="toggleSkillSelection(skill.id)"
                    @keydown.space.prevent="toggleSkillSelection(skill.id)"
                @endif
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100" x-text="skill.name"></h3>
                            <span 
                                class="px-2 py-0.5 text-xs font-bold rounded uppercase"
                                :class="{
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': skill.tier === 'S',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': skill.tier === 'A',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': skill.tier === 'B',
                                    'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-300': skill.tier === 'C'
                                }"
                                x-text="skill.tier"
                            ></span>
                        </div>
                        
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3" x-text="skill.description"></p>
                        
                        <div class="flex flex-wrap gap-3 text-xs">
                            <span class="inline-flex items-center gap-1 text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold capitalize" x-text="skill.type"></span>
                            </span>
                            <span class="inline-flex items-center gap-1 text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold">SP:</span>
                                <span x-text="skill.sp_cost"></span>
                            </span>
                        </div>
                    </div>
                    
                    @if($selectable)
                        <div class="flex items-center">
                            <div 
                                class="w-6 h-6 rounded border-2 flex items-center justify-center transition-all"
                                :class="selectedSkills.includes(skill.id) 
                                    ? 'border-primary-500 bg-primary-500' 
                                    : 'border-neutral-300 dark:border-neutral-600'"
                            >
                                <svg 
                                    x-show="selectedSkills.includes(skill.id)"
                                    class="w-4 h-4 text-white" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </template>
    </div>
    
    {{-- Empty State --}}
    <div 
        x-show="filteredSkills.length === 0"
        class="text-center py-12"
    >
        <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-neutral-100">
            No skills found
        </h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Try adjusting your search or filters
        </p>
        <button 
            x-show="activeFilterCount > 0"
            @click="clearFilters()"
            class="mt-4 btn btn-primary"
        >
            Clear Filters
        </button>
    </div>
</div>
