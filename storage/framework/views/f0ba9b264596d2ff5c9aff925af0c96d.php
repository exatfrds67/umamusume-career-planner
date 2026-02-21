

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md',
    'color' => 'primary',
    'label' => 'Loading',
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
    'size' => 'md',
    'color' => 'primary',
    'label' => 'Loading',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
        'xl' => 'h-12 w-12',
    ];
    
    $colors = [
        'primary' => 'text-primary-600 dark:text-primary-400',
        'gray' => 'text-gray-400 dark:text-gray-500',
        'white' => 'text-white',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
?>

<svg
    <?php echo e($attributes->merge([
        'class' => "{$sizeClass} {$colorClass} animate-spin motion-reduce:animate-[spin_1.5s_linear_infinite]",
        'role' => 'status',
        'aria-label' => $label,
    ])); ?>

    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    <span class="sr-only"><?php echo e($label); ?></span>
</svg>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/spinner.blade.php ENDPATH**/ ?>