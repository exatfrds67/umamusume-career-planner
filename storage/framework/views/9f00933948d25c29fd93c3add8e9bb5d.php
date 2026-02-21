<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'currentTurn' => null,
    'maxTurns' => null,
    'spAvailable' => null,
    'storageMode' => null,
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
    'currentTurn' => null,
    'maxTurns' => null,
    'spAvailable' => null,
    'storageMode' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $turnValue = $currentTurn !== null ? (string) $currentTurn : '—';
    $turnMax = $maxTurns !== null ? (string) $maxTurns : '—';
    $spValue = $spAvailable !== null ? number_format((int) $spAvailable) : '—';
    $modeValue = $storageMode ? ucfirst((string) $storageMode) : '—';
    $modeVariant = match (strtolower((string) $storageMode)) {
        'local' => 'warning',
        'account' => 'primary',
        default => 'secondary',
    };
?>

<div id="topStatusBar" <?php echo e($attributes->merge(['class' => 'w-full border-b border-gray-200/80 dark:border-gray-700/80 bg-white/70 dark:bg-gray-800/70 backdrop-blur-md'])); ?>>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2 text-xs sm:text-sm">
        <div class="flex items-center gap-3">
            <span class="text-gray-500 dark:text-gray-400">Turn</span>
            <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($turnValue); ?></span>
            <span class="text-gray-400 dark:text-gray-500">/</span>
            <span class="text-gray-600 dark:text-gray-300"><?php echo e($turnMax); ?></span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-gray-500 dark:text-gray-400">SP</span>
            <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($spValue); ?></span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-gray-500 dark:text-gray-400">Storage</span>
            <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => $modeVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($modeVariant)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($modeValue); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/top-status-bar.blade.php ENDPATH**/ ?>