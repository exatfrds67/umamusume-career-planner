<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'grade' => 'B', // SS, S, A, B, C, D, E, F, G
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
    'grade' => 'B', // SS, S, A, B, C, D, E, F, G
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $gradeColors = [
        'SS' => 'bg-gradient-to-r from-yellow-400 to-amber-500 text-white',
        'S' => 'bg-gradient-to-r from-purple-500 to-pink-500 text-white',
        'A' => 'bg-red-500 text-white',
        'B' => 'bg-orange-500 text-white',
        'C' => 'bg-yellow-500 text-gray-900',
        'D' => 'bg-green-500 text-white',
        'E' => 'bg-blue-500 text-white',
        'F' => 'bg-gray-500 text-white',
        'G' => 'bg-gray-400 text-white',
    ];
    $colorClass = $gradeColors[$grade] ?? 'bg-gray-400 text-white';
?>

<span
    <?php echo e($attributes->merge(['class' => "inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded $colorClass"])); ?>>
    <?php echo e($grade); ?>

</span>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ui/grade-badge.blade.php ENDPATH**/ ?>