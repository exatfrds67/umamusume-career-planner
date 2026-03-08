<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                Import from External API (umapyoi.net)
            </h2>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                Browse and import support cards from the official game database
            </p>
        </div>
        <div class="text-sm text-neutral-500 dark:text-neutral-400">
            <span x-show="!externalLoading && externalCards.length > 0">
                <strong x-text="filteredExternalCards().length"></strong> cards available
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <label for="external-rarity" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Rarity
            </label>
            <select id="external-rarity" x-model="externalFilters.rarity"
                class="form-select block w-full rounded-md border-neutral-300 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                <option value="">All Rarities</option>
                <option value="SSR">SSR</option>
                <option value="SR">SR</option>
                <option value="R">R</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label for="external-import-status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Import Status
            </label>
            <select id="external-import-status" x-model="externalFilters.importStatus"
                class="form-select block w-full rounded-md border-neutral-300 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                <option value="">All Cards</option>
                <option value="not_imported">Not Imported</option>
                <option value="imported">Already Imported</option>
            </select>
        </div>
        <div class="flex items-end">
            <button @click="loadExternalCards()" :disabled="externalLoading" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': externalLoading }" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="externalLoading" class="flex items-center justify-center py-12">
        <div class="flex items-center gap-3 text-neutral-600 dark:text-neutral-400">
            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-sm font-medium">Loading support cards from external API...</span>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="externalError && !externalLoading"
        class="p-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">Failed to load external support cards</p>
                <p class="text-xs text-red-700 dark:text-red-300 mt-1" x-text="externalError"></p>
            </div>
            <button @click="externalError = null" class="text-red-500 hover:text-red-700" aria-label="Dismiss error">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Cards Grid -->
    <div x-show="!externalLoading && !externalError && filteredExternalCards().length > 0"
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-3">
        <template x-for="card in filteredExternalCards()" :key="card.id">
            <div
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg hover:scale-105 transition-all group cursor-pointer">
                <!-- Card Image -->
                <div class="aspect-3/4 bg-neutral-100 dark:bg-neutral-700 relative">
                    <img :src="'https://gametora.com/images/umamusume/supports/tex_support_card_' + card.id + '.png'"
                        :alt="card.title_en || card.name" loading="lazy" decoding="async"
                        x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(card.title_en || card.name || 'Card') + '&background=random&color=fff'"
                        class="w-full h-full object-cover">

                    <!-- Rarity Badge -->
                    <div class="absolute top-1 right-1">
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded shadow-lg"
                            :class="{
                                'bg-yellow-400 text-yellow-900': card.rarity === 'SSR',
                                'bg-purple-400 text-purple-900': card.rarity === 'SR',
                                'bg-blue-400 text-blue-900': card.rarity === 'R'
                            }"
                            x-text="card.rarity"></span>
                    </div>

                    <!-- Import Status Badge -->
                    <div x-show="isImported(card.id)" class="absolute top-1 left-1">
                        <span
                            class="px-1.5 py-0.5 text-[10px] font-bold bg-green-500 text-white rounded shadow-lg flex items-center gap-0.5">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">Imported</span>
                        </span>
                    </div>

                    <!-- Hover Overlay with Info -->
                    <div
                        class="absolute inset-0 bg-black/75 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center">
                        <h4 class="text-xs font-semibold text-white line-clamp-2 mb-1"
                            x-text="card.title_en || card.name"></h4>
                        <p class="text-[10px] text-neutral-300 mb-2">
                            ID: <span x-text="card.id"></span>
                        </p>
                        <!-- Import Button -->
                        <button @click="importCard(card)" :disabled="isImported(card.id)"
                            class="w-full px-2 py-1 text-[10px] font-medium rounded transition-colors"
                            :class="isImported(card.id) ? 'bg-neutral-600 text-neutral-400 cursor-not-allowed' :
                                'bg-green-600 hover:bg-green-700 text-white'">
                            <span x-show="!isImported(card.id)">Import</span>
                            <span x-show="isImported(card.id)">Imported</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!externalLoading && !externalError && filteredExternalCards().length === 0 && externalCards.length > 0"
        class="text-center py-12">
        <svg class="w-12 h-12 mx-auto text-neutral-400 dark:text-neutral-600 mb-3" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        <p class="text-sm font-medium text-neutral-900 dark:text-white mb-1">No cards match your filters</p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">Try adjusting your filter settings</p>
    </div>

    <!-- Initial Empty State -->
    <div x-show="!externalLoading && !externalError && externalCards.length === 0" class="text-center py-12">
        <svg class="w-12 h-12 mx-auto text-neutral-400 dark:text-neutral-600 mb-3" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <p class="text-sm font-medium text-neutral-900 dark:text-white mb-1">No external cards loaded</p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">Click the refresh button to load cards from the API</p>
    </div>
</div>
