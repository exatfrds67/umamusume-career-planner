<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type', 'size' => 'sm']));

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

foreach (array_filter((['type', 'size' => 'sm']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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

    $typeColors = [
        'speed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'stamina' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'power' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'guts' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'wit' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'friend' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    ];

    $colorClass = $typeColors[strtolower($type)] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
?>

<span class="inline-flex items-center <?php echo e($sizeClass); ?> rounded font-medium <?php echo e($colorClass); ?>">
    <?php echo e(ucfirst($type)); ?>

</span>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/support-card-type-badge.blade.php ENDPATH**/ ?>