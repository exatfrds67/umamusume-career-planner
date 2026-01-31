<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => 0,
    'max' => 1200,
    'type' => 'speed', // speed, stamina, power, guts, wisdom
    'label' => null,
    'showValue' => true,
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
    'max' => 1200,
    'type' => 'speed', // speed, stamina, power, guts, wisdom
    'label' => null,
    'showValue' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $percentage = min(100, ($value / $max) * 100);
    $colors = [
        'speed' => 'bg-blue-500',
        'stamina' => 'bg-orange-500',
        'power' => 'bg-pink-500',
        'guts' => 'bg-yellow-500',
        'wisdom' => 'bg-green-500',
    ];
    $bgColor = $colors[$type] ?? 'bg-gray-500';
?>

<div <?php echo e($attributes->merge(['class' => 'stat-bar'])); ?>>
    <?php if($label): ?>
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize"><?php echo e($label); ?></span>
            <?php if($showValue): ?>
                <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($value)); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div class="<?php echo e($bgColor); ?> h-2.5 rounded-full transition-all duration-300" style="width: <?php echo e($percentage); ?>%"></div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ui/stat-bar.blade.php ENDPATH**/ ?>