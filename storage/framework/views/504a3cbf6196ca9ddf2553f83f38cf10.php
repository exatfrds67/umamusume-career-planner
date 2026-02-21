

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'columns' => 3,
    'gap' => 'md',
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
    'columns' => 3,
    'gap' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $gapClasses = [
        'sm' => 'gap-2 sm:gap-3',
        'md' => 'gap-4 sm:gap-6',
        'lg' => 'gap-6 sm:gap-8',
    ];
    
    $columnClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    ];
    
    $gapClass = $gapClasses[$gap] ?? $gapClasses['md'];
    $colClass = $columnClasses[$columns] ?? $columnClasses[3];
?>

<div 
    <?php echo e($attributes->merge([
        'class' => "grid {$colClass} {$gapClass}",
        'role' => 'region',
    ])); ?>

>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/dashboard-grid.blade.php ENDPATH**/ ?>