<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tier', 'size' => 'sm']));

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

foreach (array_filter((['tier', 'size' => 'sm']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizeClasses = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $tierColors = [
        'S+' =>
            'bg-gradient-to-r from-red-100 to-pink-100 text-red-800 dark:from-red-900 dark:to-pink-900 dark:text-red-200 border border-red-300 dark:border-red-700',
        'S' =>
            'bg-gradient-to-r from-orange-100 to-yellow-100 text-orange-800 dark:from-orange-900 dark:to-yellow-900 dark:text-orange-200 border border-orange-300 dark:border-orange-700',
        'A' =>
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-300 dark:border-green-700',
        'B' =>
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700',
        'C' =>
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600',
    ];

    $colorClass = $tierColors[$tier] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
?>

<span class="inline-flex items-center <?php echo e($sizeClass); ?> rounded font-bold <?php echo e($colorClass); ?>">
    <?php echo e($tier); ?>

</span>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/support-card-tier-badge.blade.php ENDPATH**/ ?>