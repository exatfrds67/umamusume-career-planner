{{--
Component: CharacterList
Purpose: Filterable grid of character cards with search and sorting
Props:
  - characters (Collection|array, required): Character data collection
  - columns (int, optional): Grid columns (default: 4)
  - showFilters (bool, optional): Show filter panel (default: true)
  - showSearch (bool, optional): Show search input (default: true)
  - showSort (bool, optional): Show sort dropdown (default: true)
Usage:
  <x-character-list :characters="$characters" />
Accessibility: WCAG 2.2 AA compliant, keyboard navigation, ARIA labels
--}}

@props([
    'characters' => [],
    'columns' => 4,
    'showFilters' => true,
    'showSearch' => true,
    'showSort' => true,
])

@php
    $characterArray = $characters instanceof \Illuminate\Support\Collection 
        ? $characters->toArray() 
        : $characters;
@endphp

<div 
    x-data="{
        characters: {{ json_encode($characterArray) }},
        filteredCharacters: [],
        searchQuery: '',
        sortBy: 'name',
        filters: {
            rarity: [],
            aptitudes: []
        },
        
        init() {
            this.applyFilters();
        },
        
        applyFilters() {
            let result = this.characters;
            
            // Search filter
            if (this.searchQuery.length > 0) {
                const query = this.searchQuery.toLowerCase();
                result = result.filter(char => 
                    char.name.toLowerCase().includes(query) ||
                    (char.title && char.title.toLowerCase().includes(query))
                );
            }
            
            // Rarity filter
            if (this.filters.rarity.length > 0) {
                result = result.filter(char => 
                    this.filters.rarity.includes(char.rarity)
                );
            }
            
            // Aptitude filter (if provided)
            if (this.filters.aptitudes.length > 0) {
                result = result.filter(char => {
                    if (!char.aptitudes) return false;
                    return this.filters.aptitudes.some(apt => 
                        Object.values(char.aptitudes).includes(apt)
                    );
                });
            }
            
            // Sort
            result = this.sortCharacters(result);
            
            this.filteredCharacters = result;
        },
        
        sortCharacters(chars) {
            const sorted = [...chars];
            
            switch(this.sortBy) {
                case 'name':
                    return sorted.sort((a, b) => a.name.localeCompare(b.name));
                case 'rarity':
                    return sorted.sort((a, b) => b.rarity - a.rarity);
                case 'recent':
                    return sorted.sort((a, b) => b.id - a.id);
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
            this.filters = { rarity: [], aptitudes: [] };
            this.sortBy = 'name';
            this.applyFilters();
        },
        
        get activeFilterCount() {
            return this.filters.rarity.length + this.filters.aptitudes.length + 
                   (this.searchQuery.length > 0 ? 1 : 0);
        }
    }"
    {{ $attributes->merge(['class' => 'character-list space-y-6']) }}
>
    {{-- Controls Bar --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        {{-- Search Input --}}
        @if($showSearch)
            <div class="w-full sm:w-auto sm:flex-1">
                <x-search-input 
                    name="character-search"
                    placeholder="Search characters..."
                    x-model="searchQuery"
                    @input="applyFilters()"
                />
            </div>
        @endif
        
        {{-- Sort & Filter Controls --}}
        <div class="flex gap-3 items-center w-full sm:w-auto">
            @if($showSort)
                <x-sort-dropdown
                    name="character-sort"
                    :options="[
                        'name' => 'Name (A-Z)',
                        'rarity' => 'Rarity (High-Low)',
                        'recent' => 'Recently Added'
                    ]"
                    x-model="sortBy"
                    @change="applyFilters()"
                />
            @endif
            
            @if($showFilters && $showFilters !== false)
                <button 
                    @click="$refs.filterPanel.classList.toggle('hidden')"
                    class="btn btn-secondary inline-flex items-center gap-2"
                    aria-label="Toggle filters"
                    :aria-expanded="!$refs.filterPanel.classList.contains('hidden')"
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
        <div x-ref="filterPanel" class="hidden border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex flex-wrap gap-4 items-start">
                {{-- Rarity Filters --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Rarity
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([1, 2, 3] as $rarity)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                                :class="filters.rarity.includes({{ $rarity }}) 
                                    ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900/20 dark:border-primary-600 dark:text-primary-300' 
                                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                            >
                                <input 
                                    type="checkbox" 
                                    class="sr-only"
                                    @change="toggleFilter('rarity', {{ $rarity }})"
                                    :checked="filters.rarity.includes({{ $rarity }})"
                                >
                                <span class="text-sm font-medium">★{{ $rarity }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- Clear Filters --}}
                <div class="flex items-end">
                    <button 
                        @click="clearFilters()"
                        class="btn btn-outline btn-sm"
                        x-show="activeFilterCount > 0"
                    >
                        Clear All
                    </button>
                </div>
            </div>
            
            {{-- Active Filter Badges --}}
            <div x-show="activeFilterCount > 0" class="mt-4 flex flex-wrap gap-2">
                <template x-for="rarity in filters.rarity" :key="'rarity-' + rarity">
                    <x-filter-badge 
                        x-bind:label="'Rarity: ★' + rarity"
                        x-on:click="toggleFilter('rarity', rarity)"
                    />
                </template>
            </div>
        </div>
    @endif
    
    {{-- Results Summary --}}
    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
        <p>
            <span class="font-semibold" x-text="filteredCharacters.length"></span>
            <span x-text="filteredCharacters.length === 1 ? 'character' : 'characters'"></span>
            <template x-if="activeFilterCount > 0">
                <span>
                    (filtered from <span x-text="characters.length"></span>)
                </span>
            </template>
        </p>
    </div>
    
    {{-- Character Grid --}}
    <div 
        x-show="filteredCharacters.length > 0"
        class="grid gap-6"
        :class="{
            'grid-cols-1': {{ $columns }} === 1,
            'grid-cols-1 sm:grid-cols-2': {{ $columns }} === 2,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': {{ $columns }} === 3,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4': {{ $columns }} === 4,
        }"
    >
        <template x-for="character in filteredCharacters" :key="character.id">
            <div>
                <x-character-card
                    x-bind:character="character"
                    show-stats
                />
            </div>
        </template>
    </div>
    
    {{-- Empty State --}}
    <div 
        x-show="filteredCharacters.length === 0"
        class="text-center py-12"
    >
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            No characters found
        </h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
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
