                            {{-- Filters Row --}}
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label for="trainee-search" class="form-label text-xs">Search</label>
                                    <input type="text" id="trainee-search" x-model="filters.query"
                                        placeholder="Search by name..." class="form-input form-input-sm">
                                </div>
                                <div class="sm:w-36">
                                    <label for="trainee-strategy" class="form-label text-xs">Style</label>
                                    <select id="trainee-strategy" x-model="filters.strategy" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        <option value="Escape">Escape</option>
                                        <option value="Leader">Leader</option>
                                        <option value="Insert">Insert</option>
                                        <option value="Tracking">Tracking</option>
                                    </select>
                                </div>
                                <div class="sm:w-36">
                                    <label for="trainee-distance" class="form-label text-xs">Distance</label>
                                    <select id="trainee-distance" x-model="filters.distance" class="form-select form-select-sm">
                                        <option value="">Any</option>
                                        <option value="Sprint">Sprint</option>
                                        <option value="Mile">Mile</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Long">Long</option>
                                    </select>
                                </div>
                                <div class="sm:w-32">
                                    <label for="trainee-surface" class="form-label text-xs">Surface</label>
                                    <select id="trainee-surface" x-model="filters.surface" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        <option value="Turf">Turf</option>
                                        <option value="Dirt">Dirt</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Results Count --}}
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Showing <span class="font-semibold text-neutral-700 dark:text-neutral-200" x-text="filteredTrainees().length"></span>
                                    of <span x-text="trainees.length"></span> characters
                                </p>
                                <button type="button"
                                    x-show="filters.query || filters.strategy || filters.distance || filters.surface"
                                    @click="filters.query = ''; filters.rarity = ''; filters.distance = ''; filters.surface = ''; filters.strategy = ''"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                    Clear filters
                                </button>
                            </div>

                            {{-- Scrollable Character Grid --}}
                            <div class="max-h-105 overflow-y-auto rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white/50 dark:bg-neutral-900/30"
                                role="listbox" aria-label="Character selection list">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-2">
                                    <template x-for="trainee in filteredTrainees()" :key="trainee.id">
                                        <button type="button" @click="selectTrainee(trainee)"
                                            role="option"
                                            :aria-selected="formData.trainee && formData.trainee.id === trainee.id ? 'true' : 'false'"
                                            class="text-left p-3 rounded-lg border transition-all hover:bg-neutral-50 dark:hover:bg-neutral-700/50 focus-visible:outline-2 focus-visible:outline-primary-500"
                                            :class="formData.trainee && formData.trainee.id === trainee.id ?
                                                'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-500/30' :
                                                'border-neutral-200 dark:border-neutral-700'">
                                            <div class="flex items-center gap-3">
                                                {{-- Avatar with initials fallback --}}
                                                <div class="shrink-0 w-10 h-10 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center overflow-hidden">
                                                    <img :src="resolveCharacterImageUrl(trainee)" :alt="trainee.name"
                                                        loading="lazy" decoding="async" width="40" height="40"
                                                        x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex'"
                                                        class="w-10 h-10 object-cover">
                                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300 hidden items-center justify-center w-full h-full"
                                                        x-text="trainee.name ? trainee.name.substring(0, 2).toUpperCase() : '??'"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate"
                                                        x-text="trainee.name"></p>
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                                            x-text="trainee.distance"></span>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300"
                                                            x-text="trainee.surface"></span>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"
                                                            x-text="trainee.style"></span>
                                                    </div>
                                                </div>
                                                {{-- Selection indicator --}}
                                                <div x-show="formData.trainee && formData.trainee.id === trainee.id"
                                                    class="shrink-0 w-5 h-5 rounded-full bg-primary-500 flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                {{-- Empty State --}}
                                <div x-show="filteredTrainees().length === 0"
                                    class="p-8 text-center" role="status">
                                    <svg class="mx-auto h-8 w-8 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">No characters match your filters.</p>
                                    <button type="button" @click="filters.query = ''; filters.rarity = ''; filters.distance = ''; filters.surface = ''; filters.strategy = ''"
                                        class="mt-2 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                        Clear all filters
                                    </button>
                                </div>
                            </div>