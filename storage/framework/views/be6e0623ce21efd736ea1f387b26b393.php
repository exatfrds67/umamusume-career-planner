
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Chart',
    'data' => [],
    'labels' => [],
    'colors' => ['#3B82F6'],
    'height' => 'h-64',
    'animated' => true,
    'responsive' => true,
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
    'title' => 'Chart',
    'data' => [],
    'labels' => [],
    'colors' => ['#3B82F6'],
    'height' => 'h-64',
    'animated' => true,
    'responsive' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($title); ?></h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Trend analysis and progression</p>
    </div>

    
    <div class="<?php echo e($height); ?> relative">
        <canvas id="lineChart_<?php echo e(uniqid()); ?>" x-data="lineChart(
            <?php echo e(json_encode($data)); ?>,
            <?php echo e(json_encode($labels)); ?>,
            <?php echo e(json_encode($colors)); ?>,
            <?php echo e($animated ? 'true' : 'false'); ?>,
            <?php echo e($responsive ? 'true' : 'false'); ?>

        )" x-init="init()" class="w-full"
            role="img" :aria-label="`<?php echo e($title); ?> chart showing progression over time`"></canvas>
    </div>

    
    <div class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Current</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="lastDataPoint">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Average</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="Math.round(averageValue)">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Peak</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="maxValue">
            </span>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('de4fe75c-f5c0-40b3-bd2a-cfce2e7db3df')): $__env->markAsRenderedOnce('de4fe75c-f5c0-40b3-bd2a-cfce2e7db3df'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/line-chart.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/line-chart.blade.php ENDPATH**/ ?>