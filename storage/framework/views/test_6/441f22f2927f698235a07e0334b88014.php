

<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <!-- Header -->
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Race Calendar</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View and manage race history and schedule</p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Filters Sidebar -->
            <aside class="lg:col-span-1 space-y-4" aria-labelledby="filters-heading">
                <div class="card bg-white dark:bg-gray-800">
                    <div class="card-header">
                        <h2 id="filters-heading" class="font-medium text-gray-900 dark:text-white">Filters</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo e(route('races.index')); ?>" method="GET" class="space-y-4">
                            <!-- Grade Filter -->
                            <div>
                                <label for="grade" class="form-label">Grade</label>
                                <select name="grade" id="grade" class="form-select">
                                    <option value="">All Grades</option>
                                    <?php $__currentLoopData = ['G1', 'G2', 'G3', 'OP', 'Pre-OP']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($grade); ?>" <?php echo e(request('grade') === $grade ? 'selected' : ''); ?>><?php echo e($grade); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Distance Filter -->
                            <div>
                                <label for="distance" class="form-label">Distance</label>
                                <select name="distance" id="distance" class="form-select">
                                    <option value="">All Distances</option>
                                    <?php $__currentLoopData = ['Sprint', 'Mile', 'Medium', 'Long']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($dist); ?>" <?php echo e(request('distance') === $dist ? 'selected' : ''); ?>><?php echo e($dist); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Surface Filter -->
                            <div>
                                <label for="surface" class="form-label">Surface</label>
                                <select name="surface" id="surface" class="form-select">
                                    <option value="">All Surfaces</option>
                                    <option value="Turf" <?php echo e(request('surface') === 'Turf' ? 'selected' : ''); ?>>Turf</option>
                                    <option value="Dirt" <?php echo e(request('surface') === 'Dirt' ? 'selected' : ''); ?>>Dirt</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-full justify-center">
                                Apply Filters
                            </button>
                            
                            <?php if(request()->hasAny(['grade', 'distance', 'surface'])): ?>
                                <a href="<?php echo e(route('races.index')); ?>" class="btn btn-secondary w-full justify-center text-center">
                                    Clear Filters
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Race List -->
            <section class="lg:col-span-3" aria-labelledby="race-list-heading">
                <h2 id="race-list-heading" class="sr-only">Race List</h2>
                <?php if($races->isEmpty()): ?>
                    <div class="card bg-white dark:bg-gray-800">
                        <div class="card-body text-center py-12">
                             <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No races found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or start a new career run.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4" role="list">
                        <?php $__currentLoopData = $races; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $race): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li role="listitem">
                                <a href="<?php echo e(route('races.show', $race)); ?>" class="block card bg-white dark:bg-gray-800 hover:ring-2 hover:ring-primary-500 focus:ring-2 focus:ring-primary-500 focus:outline-none transition-all cursor-pointer group h-full">
                                    <article class="card-body h-full flex flex-col">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="badge 
                                                        <?php echo e($race->race_grade === 'G1' ? 'badge-warning' : 
                                                           ($race->race_grade === 'G2' ? 'badge-error' : 
                                                           ($race->race_grade === 'G3' ? 'badge-success' : 'badge-primary'))); ?>">
                                                        <?php echo e($race->race_grade); ?>

                                                    </span>
                                                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
                                                        <?php echo e($race->race_name); ?>

                                                    </h3>
                                                </div>
                                                <p class="text-sm text-gray-500">
                                                    <?php echo e($race->distance_meters); ?>m • <?php echo e($race->surface); ?> • <?php echo e($race->distance_category); ?>

                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <?php if($race->finish_position): ?>
                                                    <div class="text-lg font-bold <?php echo e($race->finish_position == 1 ? 'text-yellow-600' : 'text-gray-700 dark:text-gray-300'); ?>">
                                                        <?php echo e($race->finish_position); ?><sup><?php echo e(match($race->finish_position % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' }); ?></sup>
                                                    </div>
                                                    <div class="text-xs text-gray-500">Result</div>
                                                <?php else: ?>
                                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Scheduled</div>
                                                    <div class="text-xs text-gray-500">Turn <?php echo e($race->turn_number); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-3 text-xs text-gray-500 mt-auto pt-4">
                                            <?php if($race->weather): ?>
                                                <span class="flex items-center gap-1" title="Weather: <?php echo e($race->weather); ?>">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                                    </svg>
                                                    <?php echo e($race->weather); ?>

                                                </span>
                                            <?php endif; ?>
                                            <?php if($race->character): ?>
                                                <span class="flex items-center gap-1" title="Character: <?php echo e($race->character->name); ?>">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <?php echo e($race->character->name); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    
                    <div class="mt-6">
                        <?php echo e($races->links()); ?>

                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/races/index.blade.php ENDPATH**/ ?>