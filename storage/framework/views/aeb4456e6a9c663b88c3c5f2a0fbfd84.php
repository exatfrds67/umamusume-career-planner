<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'stats' => [
        'speed' => 0,
        'stamina' => 0,
        'power' => 0,
        'guts' => 0,
        'wit' => 0,
    ],
    'character' => null,
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
    'stats' => [
        'speed' => 0,
        'stamina' => 0,
        'power' => 0,
        'guts' => 0,
        'wit' => 0,
    ],
    'character' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statConfig = [
        'speed' => ['label' => 'Speed', 'type' => 'speed'],
        'stamina' => ['label' => 'Stamina', 'type' => 'stamina'],
        'power' => ['label' => 'Power', 'type' => 'power'],
        'guts' => ['label' => 'Guts', 'type' => 'guts'],
        'wit' => ['label' => 'Wisdom', 'type' => 'wisdom'],
    ];

    // Helper function to get grade from stat value
    $getGrade = function ($value) {
        return match (true) {
            $value >= 1200 => 'SS',
            $value >= 1100 => 'S',
            $value >= 1000 => 'A+',
            $value >= 900 => 'A',
            $value >= 800 => 'B+',
            $value >= 700 => 'B',
            $value >= 600 => 'C+',
            $value >= 500 => 'C',
            $value >= 400 => 'D+',
            $value >= 300 => 'D',
            $value >= 200 => 'E+',
            $value >= 100 => 'E',
            $value >= 50 => 'F',
            default => 'G+',
        };
    };
?>

<div <?php echo e($attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow'])); ?>>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Stat Snapshot</h3>
            <?php if($character): ?>
                <a href="<?php echo e(route('characters.show', $character)); ?>"
                    class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                    View Details
                </a>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <?php $__currentLoopData = $statConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $value = $stats[$key] ?? 0;
                ?>
                <?php if (isset($component)) { $__componentOriginaldf052d5a616cfc8666e4c93016234de5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf052d5a616cfc8666e4c93016234de5 = $attributes; } ?>
<?php $component = App\View\Components\StatBar::resolve(['stat' => $key,'current' => $value,'max' => 2000,'showIcon' => true,'showPercentage' => true,'showSoftCap' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\StatBar::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf052d5a616cfc8666e4c93016234de5)): ?>
<?php $attributes = $__attributesOriginaldf052d5a616cfc8666e4c93016234de5; ?>
<?php unset($__attributesOriginaldf052d5a616cfc8666e4c93016234de5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf052d5a616cfc8666e4c93016234de5)): ?>
<?php $component = $__componentOriginaldf052d5a616cfc8666e4c93016234de5; ?>
<?php unset($__componentOriginaldf052d5a616cfc8666e4c93016234de5); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($character && $character->current_turn > 0): ?>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Stats</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?php echo e(array_sum($stats)); ?>

                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/dashboard/stats-snapshot.blade.php ENDPATH**/ ?>