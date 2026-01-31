<div class="space-y-4">
    <!-- Search and Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="external-search" class="form-label text-xs">Search External Database</label>
            <input type="text" id="external-search" x-model="externalFilters.query"
                @input.debounce.500ms="searchExternalCharacters()" placeholder="Search by name (e.g., Special Week)"
                class="form-input">
        </div>
        <div class="sm:w-40">
            <label for="external-category" class="form-label text-xs">Category</label>
            <select id="external-category" x-model="externalFilters.category" @change="searchExternalCharacters()"
                class="form-select">
                <option value="">All</option>
                <option value="main">Main</option>
                <option value="support">Support</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" @click="searchExternalCharacters()" :disabled="externalLoading"
                class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': externalLoading }" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="externalLoading" class="flex items-center justify-center p-8">
        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-sm font-medium">Searching external database...</span>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="externalError && !externalLoading"
        class="p-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">Failed to load external characters</p>
                <p class="text-xs text-red-700 dark:text-red-300 mt-1" x-text="externalError"></p>
            </div>
            <button @click="externalError = null" class="text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Results Grid -->
    <div x-show="!externalLoading && !externalError && externalCharacters.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <template x-for="character in externalCharacters" :key="character.id">
            <button type="button" @click="selectExternalCharacter(character)"
                :aria-pressed="formData.external_id === character.id"
                class="text-left p-4 rounded-lg border-2 transition-all hover:bg-gray-50 dark:hover:bg-gray-700/50"
                :class="formData.external_id === character.id ?
                    'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                    'border-gray-200 dark:border-gray-700'">
                <div class="flex items-start gap-3">
                    <img :src="character.image" :alt="character.name" loading="lazy" decoding="async"
                        x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(character.name) + '&background=' + character.color.replace('#', '') + '&color=fff'"
                        class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="character.name"></p>
                            <span x-show="character.category"
                                class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                                x-text="character.category"></span>
                        </div>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="character.name_jp"></span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full"
                                :style="`background-color: ${character.color}`"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">ID: <span
                                    x-text="character.id"></span></span>
                        </div>
                    </div>
                </div>
            </button>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!externalLoading && !externalError && externalCharacters.length === 0 && externalSearched"
        class="p-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">No characters found</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Try adjusting your search query or filters</p>
    </div>

    <!-- Initial State -->
    <div x-show="!externalLoading && !externalError && externalCharacters.length === 0 && !externalSearched"
        class="p-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Search External Database</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Enter a character name to search umapyoi.net database</p>
    </div>

    <!-- Hidden inputs for external data -->
    <input type="hidden" name="external_source_id" :value="formData.external_id || ''">
    <input type="hidden" name="external_source" :value="formData.external_source || ''">
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/partials/external-api-search.blade.php ENDPATH**/ ?>