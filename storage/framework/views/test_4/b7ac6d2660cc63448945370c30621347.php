<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rarity', 'size' => 'sm']));

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

foreach (array_filter((['rarity', 'size' => 'sm']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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

    $rarityColors = [
        'SSR' =>
            'bg-gradient-to-r from-yellow-100 to-amber-100 text-amber-800 dark:from-yellow-900 dark:to-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700',
        'SR' =>
            'bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 dark:from-purple-900 dark:to-pink-900 dark:text-purple-200 border border-purple-300 dark:border-purple-700',
        'R' =>
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600',
    ];

    $colorClass = $rarityColors[$rarity] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
?>

<span class="inline-flex items-center <?php echo e($sizeClass); ?> rounded font-bold <?php echo e($colorClass); ?>">
    <?php echo e($rarity); ?>

</span>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/support-card-rarity-badge.blade.php ENDPATH**/ ?>