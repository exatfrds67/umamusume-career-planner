

<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Support Cards']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>

    <div class="space-y-6" x-data="supportCardManager()">
        <!-- Header -->
        <header class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support Cards</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse and manage your support card collection
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <button @click="showExternalImport = !showExternalImport" class="btn btn-success"
                    :class="{ 'ring-2 ring-green-500': showExternalImport }" aria-expanded="showExternalImport">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span x-show="!showExternalImport">Import from API</span>
                    <span x-show="showExternalImport">Close Import</span>
                </button>
            </div>
        </header>

        <!-- External API Import Panel -->
        <div x-show="showExternalImport" x-transition
            class="card bg-linear-to-br from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 p-6 rounded-lg shadow-lg border-2 border-green-200 dark:border-green-800">
            <?php echo $__env->make('support-cards.partials.external-import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <!-- Filters -->
        <aside class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
            aria-label="Filters">
            <h2 class="sr-only">Collection Filters</h2>
            <form method="GET" action="<?php echo e(route('support-cards.index')); ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search -->
                    <div class="md:col-span-3">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="<?php echo e(request('search')); ?>"
                                class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Search by name or character...">
                        </div>
                    </div>

                    <!-- Card Type -->
                    <div class="md:col-span-2">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Type
                        </label>
                        <select id="type" name="type"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = $cardTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>" <?php echo e(request('type') === $type ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($type)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Rarity -->
                    <div class="md:col-span-1">
                        <label for="rarity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Rarity
                        </label>
                        <select id="rarity" name="rarity"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            <?php $__currentLoopData = $rarities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rarity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($rarity); ?>" <?php echo e(request('rarity') === $rarity ? 'selected' : ''); ?>>
                                    <?php echo e($rarity); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Meta Tier -->
                    <div class="md:col-span-1">
                        <label for="tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tier
                        </label>
                        <select id="tier" name="tier"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            <?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tier); ?>" <?php echo e(request('tier') === $tier ? 'selected' : ''); ?>>
                                    <?php echo e($tier); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Bond Level (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="bond_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Bond
                        </label>
                        <select id="bond_level" name="bond_level"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Any</option>
                            <option value="100" <?php echo e(request('bond_level') === '100' ? 'selected' : ''); ?>>100</option>
                            <option value="80" <?php echo e(request('bond_level') === '80' ? 'selected' : ''); ?>>80+</option>
                            <option value="50" <?php echo e(request('bond_level') === '50' ? 'selected' : ''); ?>>50+</option>
                            <option value="low" <?php echo e(request('bond_level') === 'low' ? 'selected' : ''); ?>>&lt;50</option>
                        </select>
                    </div>

                    <!-- Limit Break (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="limit_break" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            LB
                        </label>
                        <select id="limit_break" name="limit_break"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Any</option>
                            <option value="4" <?php echo e(request('limit_break') === '4' ? 'selected' : ''); ?>>4★</option>
                            <option value="3" <?php echo e(request('limit_break') === '3' ? 'selected' : ''); ?>>3★</option>
                            <option value="2" <?php echo e(request('limit_break') === '2' ? 'selected' : ''); ?>>2★</option>
                            <option value="1" <?php echo e(request('limit_break') === '1' ? 'selected' : ''); ?>>1★</option>
                            <option value="0" <?php echo e(request('limit_break') === '0' ? 'selected' : ''); ?>>0★</option>
                        </select>
                    </div>

                    <!-- Sort (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sort
                        </label>
                        <select id="sort" name="sort"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="name" <?php echo e(request('sort', 'name') === 'name' ? 'selected' : ''); ?>>Name</option>
                            <option value="rarity" <?php echo e(request('sort') === 'rarity' ? 'selected' : ''); ?>>Rarity</option>
                            <option value="tier" <?php echo e(request('sort') === 'tier' ? 'selected' : ''); ?>>Tier</option>
                            <option value="type" <?php echo e(request('sort') === 'type' ? 'selected' : ''); ?>>Type</option>
                            <option value="recent" <?php echo e(request('sort') === 'recent' ? 'selected' : ''); ?>>Recent</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="md:col-span-2 flex gap-2 h-full items-end pb-0.5">
                        <button type="submit" class="btn btn-secondary flex-1 h-9.5 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        <?php if(request()->anyFilled(['search', 'type', 'rarity', 'tier', 'bond_level', 'limit_break', 'sort'])): ?>
                            <a href="<?php echo e(route('support-cards.index')); ?>" class="btn btn-outline px-3"
                                title="Clear Filters">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </aside>

        <!-- Collection Stats Widget (WF-010 requirement) -->
        <!-- Collection Stats Widget -->
        <aside class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
            aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">Collection Statistics</h2>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400"><?php echo e($cards->total()); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Cards</div>
                    </div>
                    <div class="h-8 border-l border-gray-200 dark:border-gray-600"></div>
                    <div class="flex gap-4">
                        <?php
                            $rarityCounts = $cards->getCollection()->groupBy(fn($c) => $c->rarity)->map->count();
                        ?>
                        <?php $__currentLoopData = ['SSR' => 'text-yellow-500', 'SR' => 'text-purple-500', 'R' => 'text-blue-500']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="text-center">
                                <div class="text-lg font-semibold <?php echo e($color); ?>"><?php echo e($rarityCounts->get($r, 0)); ?>

                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($r); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="h-8 border-l border-gray-200 dark:border-gray-600"></div>
                    <div class="flex gap-3 text-xs">
                        <?php
                            $typeCounts = $cards->getCollection()->groupBy(fn($c) => $c->card_type)->map->count();
                        ?>
                        <?php $__currentLoopData = $typeCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <?php echo e(ucfirst($type)); ?>: <?php echo e($count); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php if(auth()->check()): ?>
                    <a href="<?php echo e(route('characters.index')); ?>" class="btn btn-primary flex items-center gap-2"
                        title="Select a character to build a deck">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Build Deck
                    </a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Cards Grid -->
        <section aria-label="Support Card List">
            <?php if($cards->count() > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal71947eb8901f631847d24c34fd782a3c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal71947eb8901f631847d24c34fd782a3c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.support-card-tile','data' => ['card' => $card]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('support-card-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['card' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal71947eb8901f631847d24c34fd782a3c)): ?>
<?php $attributes = $__attributesOriginal71947eb8901f631847d24c34fd782a3c; ?>
<?php unset($__attributesOriginal71947eb8901f631847d24c34fd782a3c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71947eb8901f631847d24c34fd782a3c)): ?>
<?php $component = $__componentOriginal71947eb8901f631847d24c34fd782a3c; ?>
<?php unset($__componentOriginal71947eb8901f631847d24c34fd782a3c); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-6">
                    <?php echo e($cards->links()); ?>

                </div>
            <?php else: ?>
                <div
                    class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No cards found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Try adjusting your filters or search criteria.
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/support-cards/index.blade.php ENDPATH**/ ?>