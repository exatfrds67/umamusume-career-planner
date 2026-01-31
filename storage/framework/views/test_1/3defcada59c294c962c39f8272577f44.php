

<?php $__env->startSection('content'); ?>
    <div class="space-y-6" x-data="characterManager()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    My Characters
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your Umamusume training career strategies
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <button @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'" class="btn btn-outline"
                    :aria-label="viewMode === 'grid' ? 'Switch to list view' : 'Switch to grid view'">
                    <svg x-show="viewMode === 'grid'" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="viewMode === 'list'" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <a href="<?php echo e(route('external-data.browse')); ?>" class="btn btn-outline">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Browse External Data
                </a>
                <a href="<?php echo e(route('characters.create')); ?>" class="btn btn-primary">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    New Character
                </a>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="<?php echo e(route('characters.index')); ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label for="search"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="<?php echo e(request('search')); ?>"
                                class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Search by name...">
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label for="scenario"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scenario</label>
                        <select id="scenario" name="scenario"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Scenarios</option>
                            <option value="ura_finale" <?php echo e(request('scenario') === 'ura_finale' ? 'selected' : ''); ?>>URA
                                Finale</option>
                            <option value="unity_cup" <?php echo e(request('scenario') === 'unity_cup' ? 'selected' : ''); ?>>Unity Cup
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="status"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select id="status" name="status"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Status</option>
                            <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed
                            </option>
                            <option value="archived" <?php echo e(request('status') === 'archived' ? 'selected' : ''); ?>>Archived
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        <?php if(request()->anyFilled(['search', 'scenario', 'status', 'sort'])): ?>
                            <a href="<?php echo e(route('characters.index')); ?>" class="btn btn-outline px-3" title="Clear Filters">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center gap-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php
                            $sortOptions = [
                                'updated_at' => 'Recently Updated',
                                'created_at' => 'Recently Created',
                                'name' => 'Name',
                                'progress' => 'Progress',
                            ];
                            $currentSort = request('sort', 'updated_at');
                        ?>
                        <?php $__currentLoopData = $sortOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="submit" name="sort" value="<?php echo e($value); ?>"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition-colors
                                    <?php echo e($currentSort === $value
                                        ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'); ?>">
                                <?php echo e($label); ?>

                                <?php if($currentSort === $value): ?>
                                    <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Content -->
        <?php if($characters->count() > 0): ?>
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $character): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('characters.show', $character)); ?>"
                        class="glass-card-alt rounded-lg shadow-sm hover:shadow-md transition-all overflow-hidden group">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <?php if($character->avatar_url): ?>
                                        <img src="<?php echo e($character->avatar_url); ?>" alt="<?php echo e($character->name); ?>"
                                            class="h-12 w-12 rounded-full object-cover shadow-sm ring-2 ring-white dark:ring-gray-800"
                                            loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div
                                            class="h-12 w-12 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">
                                            <?php echo e(strtoupper(substr($character->name, 0, 2))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                            <?php echo e($character->name); ?>

                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo e($character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'); ?>

                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php echo e($character->status === 'active' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-700 dark:text-gray-400'); ?>">
                                    <?php echo e(ucfirst($character->status)); ?>

                                </span>
                            </div>

                            <!-- Stat Grid -->
                            <div class="grid grid-cols-5 gap-2 mb-4">
                                <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400"><?php echo e(substr(ucfirst($stat), 0, 3)); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal38ed160b843d185cc0ee7da813217a54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ed160b843d185cc0ee7da813217a54 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.grade-badge','data' => ['grade' => $character->getStatGrade($character->getStat($stat)),'size' => 'sm','class' => 'mb-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.grade-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['grade' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getStatGrade($character->getStat($stat))),'size' => 'sm','class' => 'mb-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $attributes = $__attributesOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__attributesOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $component = $__componentOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__componentOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-gray-300"><?php echo e($character->getStat($stat)); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <!-- Progress -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Overall Progress</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"><?php echo e($character->getProgressPercentage()); ?>%</span>
                                </div>
                                <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $character->getProgressPercentage(),'max' => 100,'size' => 'sm','showPercentage' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getProgressPercentage()),'max' => 100,'size' => 'sm','show-percentage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $attributes = $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $component = $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <?php echo e($character->updated_at->diffForHumans()); ?>

                            </span>
                            <span
                                class="text-xs font-medium text-primary-600 dark:text-primary-400 group-hover:translate-x-1 transition-transform inline-flex items-center">
                                View Details
                                <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="space-y-3">
                <?php $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $character): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('characters.show', $character)); ?>"
                        class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 overflow-hidden group">
                        <div class="p-5">
                            <div class="flex items-center gap-6">
                                <!-- Avatar -->
                                <div class="shrink-0">
                                    <?php if($character->avatar_url): ?>
                                        <img src="<?php echo e($character->avatar_url); ?>" alt="<?php echo e($character->name); ?>"
                                            class="h-16 w-16 rounded-full object-cover shadow-sm ring-2 ring-white dark:ring-gray-800"
                                            loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div
                                            class="h-16 w-16 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">
                                            <?php echo e(strtoupper(substr($character->name, 0, 2))); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                            <?php echo e($character->name); ?>

                                        </h3>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php echo e($character->status === 'active' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-700 dark:text-gray-400'); ?>">
                                            <?php echo e(ucfirst($character->status)); ?>

                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span><?php echo e($character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'); ?></span>
                                        <span>•</span>
                                        <span><?php echo e($character->updated_at->diffForHumans()); ?></span>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="hidden lg:flex items-center gap-4">
                                    <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1"><?php echo e(substr(ucfirst($stat), 0, 3)); ?></span>
                                            <?php if (isset($component)) { $__componentOriginal38ed160b843d185cc0ee7da813217a54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ed160b843d185cc0ee7da813217a54 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.grade-badge','data' => ['grade' => $character->getStatGrade($character->getStat($stat)),'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.grade-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['grade' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getStatGrade($character->getStat($stat))),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $attributes = $__attributesOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__attributesOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $component = $__componentOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__componentOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <!-- Progress -->
                                <div class="hidden md:block w-32">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Progress</div>
                                    <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $character->getProgressPercentage(),'max' => 100,'size' => 'sm','showPercentage' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getProgressPercentage()),'max' => 100,'size' => 'sm','show-percentage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $attributes = $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $component = $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
                                    <div class="text-xs font-medium text-gray-900 dark:text-white mt-1">
                                        <?php echo e($character->getProgressPercentage()); ?>%</div>
                                </div>

                                <!-- Arrow -->
                                <div class="shrink-0">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 group-hover:translate-x-1 transition-all"
                                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-6">
                <?php echo e($characters->links()); ?>

            </div>
        <?php else: ?>
            <div
                class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No characters found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new character profile.
                </p>
                <div class="mt-6">
                    <a href="<?php echo e(route('characters.create')); ?>" class="btn btn-primary">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Create Character
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function characterManager() {
            return {
                viewMode: localStorage.getItem('characterViewMode') || 'grid',
                init() {
                    this.$watch('viewMode', value => {
                        localStorage.setItem('characterViewMode', value);
                    });
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/index.blade.php ENDPATH**/ ?>