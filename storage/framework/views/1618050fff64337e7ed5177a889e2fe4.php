<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['alertCount' => 0, 'size' => 'md']));

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

foreach (array_filter((['alertCount' => 0, 'size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $hasCriticalAlerts = $alertCount > 0;

    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };

    $badgeSizeClasses = match ($size) {
        'sm' => 'h-4 w-4 text-[10px]',
        'md' => 'h-5 w-5 text-xs',
        'lg' => 'h-6 w-6 text-sm',
        default => 'h-5 w-5 text-xs',
    };
?>

<button type="button"
    <?php echo e($attributes->merge([
        'class' =>
            '-m-2.5 p-2.5 relative transition-colors duration-200 ' .
            ($hasCriticalAlerts
                ? 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300'
                : 'text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-100'),
        'aria-label' => $hasCriticalAlerts
            ? "View {$alertCount} critical " . Str::plural('alert', $alertCount)
            : 'No critical alerts',
        'title' => $hasCriticalAlerts
            ? "{$alertCount} critical " . Str::plural('alert', $alertCount) . ' - Click to view'
            : 'No critical alerts',
    ])); ?>

    @click="$dispatch('open-advisory-panel', { section: 'alerts' })">
    <span class="sr-only">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasCriticalAlerts): ?>
            View <?php echo e($alertCount); ?> critical <?php echo e(Str::plural('alert', $alertCount)); ?>

        <?php else: ?>
            No critical alerts
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>

    
    <svg class="<?php echo e($sizeClasses); ?> <?php echo e($hasCriticalAlerts ? 'critical-alert-icon' : ''); ?>" fill="none"
        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasCriticalAlerts): ?>
        <span
            class="absolute -top-1 -right-1 <?php echo e($badgeSizeClasses); ?> flex items-center justify-center rounded-full bg-red-600 dark:bg-red-500 text-white font-bold shadow-lg critical-alert-badge ring-2 ring-white dark:ring-gray-800"
            aria-hidden="true">
            <?php echo e($alertCount > 99 ? '99+' : $alertCount); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</button>

<?php if (! $__env->hasRenderedOnce('24e2a9f6-e8df-4a3e-aaf2-433d87306567')): $__env->markAsRenderedOnce('24e2a9f6-e8df-4a3e-aaf2-433d87306567'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/ai/critical-alert-badge.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ai/critical-alert-badge.blade.php ENDPATH**/ ?>