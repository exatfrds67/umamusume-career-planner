<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showPercentage' => true,
    'size' => 'md', // sm, md, lg
    'color' => 'primary', // primary, success, warning, error
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
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showPercentage' => true,
    'size' => 'md', // sm, md, lg
    'color' => 'primary', // primary, success, warning, error
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;
    
    $sizes = [
        'sm' => 'h-1.5',
        'md' => 'h-2.5',
        'lg' => 'h-4',
    ];
    
    $colors = [
        'primary' => 'bg-primary-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
?>

<div <?php echo e($attributes->merge(['class' => 'progress-bar-wrapper'])); ?>>
    <?php if($label): ?>
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($label); ?></span>
            <?php if($showPercentage): ?>
                <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($percentage, 0)); ?>%</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div 
        class="w-full bg-gray-200 dark:bg-gray-700 rounded-full <?php echo e($sizeClass); ?> overflow-hidden"
        role="progressbar"
        aria-valuenow="<?php echo e($value); ?>"
        aria-valuemin="0"
        aria-valuemax="<?php echo e($max); ?>"
        aria-label="<?php echo e($label ?? 'Progress'); ?>"
    >
        <div 
            class="<?php echo e($colorClass); ?> <?php echo e($sizeClass); ?> rounded-full transition-all duration-300 ease-out" 
            style="width: <?php echo e($percentage); ?>%"
        ></div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ui/progress-bar.blade.php ENDPATH**/ ?>