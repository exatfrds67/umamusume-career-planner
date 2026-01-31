<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'races' => [],
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'races' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow'])); ?>>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Upcoming Races
            </h3>
            <a href="<?php echo e(route('races.index')); ?>"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                View All
            </a>
        </div>

        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $races; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $race): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold <?php echo e(($race['grade'] ?? 'G3') === 'G1' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'); ?>">
                                <?php echo e($race['grade'] ?? 'G3'); ?>

                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <?php echo e($race['name'] ?? 'Unknown Race'); ?>

                            </span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <?php if(isset($race['turnsAway'])): ?>
                                In <?php echo e($race['turnsAway']); ?> turn<?php echo e($race['turnsAway'] !== 1 ? 's' : ''); ?>

                            <?php elseif(isset($race['date'])): ?>
                                <?php echo e(\Carbon\Carbon::parse($race['date'])->format('M d, Y')); ?>

                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-3 ml-4">
                        <?php if (isset($component)) { $__componentOriginalc60c1a08058f2d65f561f9d8a34c2843 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc60c1a08058f2d65f561f9d8a34c2843 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.readiness-badge','data' => ['percentage' => $race['readiness'] ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.readiness-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percentage' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($race['readiness'] ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc60c1a08058f2d65f561f9d8a34c2843)): ?>
<?php $attributes = $__attributesOriginalc60c1a08058f2d65f561f9d8a34c2843; ?>
<?php unset($__attributesOriginalc60c1a08058f2d65f561f9d8a34c2843); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc60c1a08058f2d65f561f9d8a34c2843)): ?>
<?php $component = $__componentOriginalc60c1a08058f2d65f561f9d8a34c2843; ?>
<?php unset($__componentOriginalc60c1a08058f2d65f561f9d8a34c2843); ?>
<?php endif; ?>
                        <a href="<?php echo e(route('races.index')); ?>"
                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400"
                            title="View race details">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No upcoming races scheduled.
                    </p>
                    <a href="<?php echo e(route('races.index')); ?>"
                        class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        Schedule a race
                        <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/dashboard/upcoming-races.blade.php ENDPATH**/ ?>