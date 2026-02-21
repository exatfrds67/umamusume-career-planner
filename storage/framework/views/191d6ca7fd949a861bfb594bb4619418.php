<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'mood' => 'good', // great, good, normal, bad, awful
    'energy' => 72,
    'maxEnergy' => 100,
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
    'mood' => 'good', // great, good, normal, bad, awful
    'energy' => 72,
    'maxEnergy' => 100,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $moodConfig = [
        'great' => ['emoji' => '😄', 'label' => 'Great', 'color' => 'text-success-600 dark:text-success-400'],
        'good' => ['emoji' => '🙂', 'label' => 'Good', 'color' => 'text-primary-600 dark:text-primary-400'],
        'normal' => ['emoji' => '😐', 'label' => 'Normal', 'color' => 'text-warning-600 dark:text-warning-400'],
        'bad' => ['emoji' => '😔', 'label' => 'Bad', 'color' => 'text-error-600 dark:text-error-400'],
        'awful' => ['emoji' => '😢', 'label' => 'Awful', 'color' => 'text-error-700 dark:text-error-500'],
    ];

    $currentMood = $moodConfig[$mood] ?? $moodConfig['normal'];
    $energyPercentage = $maxEnergy > 0 ? ($energy / $maxEnergy) * 100 : 0;

    $energyColor = match (true) {
        $energyPercentage >= 70 => 'success',
        $energyPercentage >= 40 => 'warning',
        default => 'error',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow w-full'])); ?>>
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Mood & Energy
        </h3>

        <div class="space-y-4 w-full">
            
            <div class="flex items-center justify-between w-full">
                <span class="text-sm text-gray-500 dark:text-gray-400">Mood</span>
                <?php if (isset($component)) { $__componentOriginaldb7206c0cc2f25560ae425ce53df19bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc = $attributes; } ?>
<?php $component = App\View\Components\ConditionBadge::resolve(['condition' => strtoupper($mood),'trend' => 'flat','turnsActive' => 0] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ConditionBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc)): ?>
<?php $attributes = $__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc; ?>
<?php unset($__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldb7206c0cc2f25560ae425ce53df19bc)): ?>
<?php $component = $__componentOriginaldb7206c0cc2f25560ae425ce53df19bc; ?>
<?php unset($__componentOriginaldb7206c0cc2f25560ae425ce53df19bc); ?>
<?php endif; ?>
            </div>

            
            <div class="w-full">
                <?php if (isset($component)) { $__componentOriginal253604e995c446f07ac8dd33a933f013 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal253604e995c446f07ac8dd33a933f013 = $attributes; } ?>
<?php $component = App\View\Components\EnergyGauge::resolve(['value' => $energyPercentage,'trend' => $energyPercentage >= 70 ? 'up' : ($energyPercentage >= 40 ? 'flat' : 'down')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('energy-gauge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\EnergyGauge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal253604e995c446f07ac8dd33a933f013)): ?>
<?php $attributes = $__attributesOriginal253604e995c446f07ac8dd33a933f013; ?>
<?php unset($__attributesOriginal253604e995c446f07ac8dd33a933f013); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal253604e995c446f07ac8dd33a933f013)): ?>
<?php $component = $__componentOriginal253604e995c446f07ac8dd33a933f013; ?>
<?php unset($__componentOriginal253604e995c446f07ac8dd33a933f013); ?>
<?php endif; ?>
            </div>

            
            <button type="button" class="w-full btn btn-outline btn-sm mt-2">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                Recovery Options
            </button>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/dashboard/mood-energy-widget.blade.php ENDPATH**/ ?>