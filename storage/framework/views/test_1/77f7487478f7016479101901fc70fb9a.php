<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'shortTermGoal' => 'No short-term goal set',
    'shortTermProgress' => 0,
    'longTermGoal' => 'No long-term goal set',
    'longTermProgress' => 0,
    'characterId' => null,
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
    'shortTermGoal' => 'No short-term goal set',
    'shortTermProgress' => 0,
    'longTermGoal' => 'No long-term goal set',
    'longTermProgress' => 0,
    'characterId' => null,
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
                Current Goals
            </h3>
            <a href="<?php echo e($characterId ? route('characters.edit', $characterId) : route('characters.index')); ?>"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                Edit Goals
            </a>
        </div>

        <div class="space-y-5">
            
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/50">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Short-term</span>
                </div>
                <p class="text-sm text-gray-900 dark:text-white mb-2"><?php echo e($shortTermGoal); ?></p>
                <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $shortTermProgress,'max' => 100,'color' => 'primary','size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shortTermProgress),'max' => 100,'color' => 'primary','size' => 'md']); ?>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($shortTermProgress); ?>% complete</p>
            </div>

            
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-secondary-100 dark:bg-secondary-900/50">
                        <svg class="w-4 h-4 text-secondary-600 dark:text-secondary-400" fill="none"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Long-term</span>
                </div>
                <p class="text-sm text-gray-900 dark:text-white mb-2"><?php echo e($longTermGoal); ?></p>
                <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $longTermProgress,'max' => 100,'color' => 'success','size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($longTermProgress),'max' => 100,'color' => 'success','size' => 'md']); ?>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($longTermProgress); ?>% complete</p>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/dashboard/goals-widget.blade.php ENDPATH**/ ?>