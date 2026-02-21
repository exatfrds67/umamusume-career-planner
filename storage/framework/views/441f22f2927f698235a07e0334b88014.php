<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Races']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['G1', 'G2', 'G3', 'OP', 'Pre-OP']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($grade); ?>"
                                            <?php echo e(request('grade') === $grade ? 'selected' : ''); ?>><?php echo e($grade); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>

                            <!-- Distance Filter -->
                            <div>
                                <label for="distance" class="form-label">Distance</label>
                                <select name="distance" id="distance" class="form-select">
                                    <option value="">All Distances</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Sprint', 'Mile', 'Medium', 'Long']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($dist); ?>"
                                            <?php echo e(request('distance') === $dist ? 'selected' : ''); ?>><?php echo e($dist); ?>

                                        </option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>

                            <!-- Surface Filter -->
                            <div>
                                <label for="surface" class="form-label">Surface</label>
                                <select name="surface" id="surface" class="form-select">
                                    <option value="">All Surfaces</option>
                                    <option value="Turf" <?php echo e(request('surface') === 'Turf' ? 'selected' : ''); ?>>Turf
                                    </option>
                                    <option value="Dirt" <?php echo e(request('surface') === 'Dirt' ? 'selected' : ''); ?>>Dirt
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-full justify-center">
                                Apply Filters
                            </button>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['grade', 'distance', 'surface'])): ?>
                                <a href="<?php echo e(route('races.index')); ?>"
                                    class="btn btn-secondary w-full justify-center text-center">
                                    Clear Filters
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Race List -->
            <section class="lg:col-span-3" aria-labelledby="race-list-heading">
                <h2 id="race-list-heading" class="sr-only">Race List</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($races->isEmpty()): ?>
                    <div class="card bg-white dark:bg-gray-800">
                        <div class="card-body text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No races found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or start a
                                new career run.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" role="list">
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $races; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $race): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php
                                $raceData = [
                                    'grade' => $race->race_grade,
                                    'name' => $race->race_name,
                                    'distance' => $race->distance_meters ?? $race->distance,
                                    'track' => $race->surface
                                        ? strtolower($race->surface)
                                        : ($race->track_type
                                            ? strtolower($race->track_type)
                                            : null),
                                    'style' => $race->running_style,
                                    'turn' => $race->turn_number,
                                    'weather' => $race->weather,
                                    'condition' => $race->track_condition,
                                ];
                            ?>
                            <a href="<?php echo e(route('races.show', $race)); ?>" class="block" role="listitem">
                                <?php if (isset($component)) { $__componentOriginalb0bb73e7ec69f4839ec4a194c604ee74 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb0bb73e7ec69f4839ec4a194c604ee74 = $attributes; } ?>
<?php $component = App\View\Components\RaceCard::resolve(['race' => $raceData,'readiness' => null,'winProb' => null] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('race-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\RaceCard::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb0bb73e7ec69f4839ec4a194c604ee74)): ?>
<?php $attributes = $__attributesOriginalb0bb73e7ec69f4839ec4a194c604ee74; ?>
<?php unset($__attributesOriginalb0bb73e7ec69f4839ec4a194c604ee74); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb0bb73e7ec69f4839ec4a194c604ee74)): ?>
<?php $component = $__componentOriginalb0bb73e7ec69f4839ec4a194c604ee74; ?>
<?php unset($__componentOriginalb0bb73e7ec69f4839ec4a194c604ee74); ?>
<?php endif; ?>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="mt-6">
                        <?php echo e($races->links()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/races/index.blade.php ENDPATH**/ ?>