                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label for="trainee-search" class="form-label text-xs">Search</label>
                                    <input type="text" id="trainee-search" x-model="filters.query"
                                        placeholder="Search trainee" class="form-input">
                                </div>
                                <div class="sm:w-40">
                                    <label for="trainee-rarity" class="form-label text-xs">Rarity</label>
                                    <select id="trainee-rarity" x-model="filters.rarity" class="form-select">
                                        <option value="">All</option>
                                        <option value="SSR">SSR</option>
                                        <option value="SR">SR</option>
                                        <option value="R">R</option>
                                    </select>
                                </div>
                                <div class="sm:w-48">
                                    <label for="trainee-distance" class="form-label text-xs">Distance</label>
                                    <select id="trainee-distance" x-model="filters.distance" class="form-select">
                                        <option value="">Any</option>
                                        <option value="Sprint">Sprint</option>
                                        <option value="Mile">Mile</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Long">Long</option>
                                    </select>
                                </div>
                                <div class="sm:w-40">
                                    <label for="trainee-surface" class="form-label text-xs">Surface</label>
                                    <select id="trainee-surface" x-model="filters.surface" class="form-select">
                                        <option value="">All</option>
                                        <option value="Turf">Turf</option>
                                        <option value="Dirt">Dirt</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="trainee in filteredTrainees()" :key="trainee.id">
                                    <button type="button" @click="selectTrainee(trainee)"
                                        :aria-pressed="formData.trainee && formData.trainee.id === trainee.id"
                                        class="text-left p-4 rounded-lg border-2 transition-all hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        :class="formData.trainee && formData.trainee.id === trainee.id ?
                                            'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                                            'border-gray-200 dark:border-gray-700'">
                                        <div class="flex items-start gap-3">
                                            <img :src="trainee.image" :alt="trainee.name"
                                                loading="lazy" decoding="async"
                                                x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(trainee.name) + '&background=random&color=fff'"
                                                class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white"
                                                        x-text="trainee.name"></p>
                                                    <span
                                                        class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                                                        x-text="trainee.rarity"></span>
                                                </div>
                                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200"
                                                        x-text="trainee.distance"></span>
                                                    <span
                                                        class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200"
                                                        x-text="trainee.surface"></span>
                                                    <span
                                                        class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200"
                                                        x-text="trainee.style"></span>
                                                </div>
                                                <div class="mt-3 grid grid-cols-5 gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                                    <template x-for="(value, stat) in trainee.growth" :key="stat">
                                                        <div class="text-center">
                                                            <div class="uppercase" x-text="stat"></div>
                                                            <div class="text-sm font-semibold text-gray-900 dark:text-white"
                                                                x-text="value + '%'">
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <div x-show="filteredTrainees().length === 0"
                                class="p-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                                No trainees match your filters.
                            </div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/partials/database-search.blade.php ENDPATH**/ ?>