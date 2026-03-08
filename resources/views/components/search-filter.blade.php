{{-- Phase 6: Advanced Search & Filter Component --}}
@props([
    'showSavedFilters' => true,
    'showSearchHistory' => true,
    'compact' => false,
])

<div
    x-data="searchFilter()"
    @init="init()"
    class="space-y-4"
>
    <!-- Search Bar -->
    <div class="relative">
        <input
            type="search"
            @input="handleSearch"
            placeholder="Search plans by character, scenario, notes, or tags..."
            class="w-full px-4 py-2.5 pl-10 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:outline-hidden focus:ring-2 focus:ring-blue-500"
            aria-label="Search plans"
        />
        <svg class="absolute left-3 top-3 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <template x-if="isSearching">
            <div class="absolute right-3 top-3">
                <div class="animate-spin">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </template>
    </div>

    <!-- Result Count -->
    <div class="text-sm text-neutral-600 dark:text-neutral-400">
        <span x-text="resultCount"></span> plan<span x-show="resultCount !== 1">s</span> found
    </div>

    <!-- Filters Section -->
    <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-4 space-y-4">
        <!-- Character Filter -->
        <div>
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">Character</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="char in availableCharacters.slice(0, 8)" :key="char">
                    <button
                        @click="toggleFilter('character', char)"
                        :class="selectedFilters.character.includes(char) ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
                        class="px-2.5 py-1 rounded-full text-xs font-medium transition hover:opacity-80"
                        x-text="char"
                    ></button>
                </template>
                <template x-if="availableCharacters.length > 8">
                    <button
                        @click="showAllCharacters = !showAllCharacters"
                        class="px-2.5 py-1 text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline"
                    >
                        +<span x-text="availableCharacters.length - 8"></span> more
                    </button>
                </template>
            </div>
        </div>

        <!-- Scenario Filter -->
        <div>
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">Scenario</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="scenario in availableScenarios" :key="scenario">
                    <button
                        @click="toggleFilter('scenario', scenario)"
                        :class="selectedFilters.scenario.includes(scenario) ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
                        class="px-2.5 py-1 rounded-full text-xs font-medium transition hover:opacity-80"
                        x-text="scenario"
                    ></button>
                </template>
            </div>
        </div>

        <!-- Status Filter -->
        <div>
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">Status</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="status in availableStatuses" :key="status">
                    <button
                        @click="toggleFilter('status', status)"
                        :class="selectedFilters.status.includes(status) ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
                        class="px-2.5 py-1 rounded-full text-xs font-medium transition hover:opacity-80 capitalize"
                        x-text="status"
                    ></button>
                </template>
            </div>
        </div>

        <!-- Tags Filter -->
        <div x-show="availableTags.length > 0">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">Tags</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="tag in availableTags.slice(0, 6)" :key="tag">
                    <button
                        @click="toggleFilter('tags', tag)"
                        :class="selectedFilters.tags.includes(tag) ? 'bg-blue-600 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white'"
                        class="px-2.5 py-1 rounded-full text-xs font-medium transition hover:opacity-80"
                        x-text="'#' + tag"
                    ></button>
                </template>
                <template x-if="availableTags.length > 6">
                    <button class="px-2.5 py-1 text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">
                        +<span x-text="availableTags.length - 6"></span> more
                    </button>
                </template>
            </div>
        </div>

        <!-- Clear Filters -->
        <button
            @click="clearAllFilters()"
            x-show="searchQuery || selectedFilters.character.length || selectedFilters.scenario.length || selectedFilters.status.length || selectedFilters.tags.length"
            class="w-full px-3 py-1.5 text-sm text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 transition"
        >
            Clear All Filters
        </button>
    </div>

    <!-- Actions -->
    <div class="flex gap-2">
        <button
            @click="exportResults()"
            :disabled="resultCount === 0"
            class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 disabled:bg-neutral-400 text-white rounded-lg transition"
        >
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export
        </button>

        <button
            @click="saveCurrentFilter(prompt('Filter name:'))"
            :disabled="!searchQuery && !selectedFilters.character.length"
            class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 disabled:bg-neutral-400 text-white rounded-lg transition"
        >
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z" />
            </svg>
            Save Filter
        </button>
    </div>

    <!-- Search History -->
    @if ($showSearchHistory)
        <div x-show="searchHistory.length > 0" class="pt-2 border-t border-neutral-200 dark:border-neutral-700">
            <h4 class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase mb-2">Recent Searches</h4>
            <div class="flex flex-wrap gap-2">
                <template x-for="search in searchHistory" :key="search">
                    <button
                        @click="searchQuery = search; $dispatch('search-updated')"
                        class="px-2 py-1 text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-white rounded hover:bg-neutral-300 dark:hover:bg-neutral-600 transition"
                        x-text="search"
                    ></button>
                </template>
            </div>
        </div>
    @endif

    <!-- Saved Filters -->
    @if ($showSavedFilters)
        <div x-show="savedFilters.length > 0" class="pt-2 border-t border-neutral-200 dark:border-neutral-700">
            <h4 class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase mb-2">Saved Filters</h4>
            <div class="space-y-1">
                <template x-for="filter in savedFilters" :key="filter.id">
                    <div class="flex items-center justify-between gap-2 p-2 bg-neutral-50 dark:bg-neutral-800 rounded">
                        <button
                            @click="applySavedFilter(filter.id)"
                            class="flex-1 text-left text-sm text-neutral-700 dark:text-neutral-300 hover:text-blue-600 dark:hover:text-blue-400 transition"
                            x-text="filter.name"
                        ></button>
                        <button
                            @click="deleteSavedFilter(filter.id)"
                            class="text-red-600 hover:text-red-800 dark:hover:text-red-400 text-sm"
                        >
                            ✕
                        </button>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>
