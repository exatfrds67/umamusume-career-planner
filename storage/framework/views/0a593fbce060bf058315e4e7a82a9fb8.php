<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['condition', 'trend', 'turnsActive', 'size', 'showTrend', 'showDuration']));

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

foreach (array_filter((['condition', 'trend', 'turnsActive', 'size', 'showTrend', 'showDuration']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-1.5'])); ?>>
    
    <div class="condition-badge <?php echo e($getSizeClasses()); ?> <?php echo e($getConditionColor()); ?> rounded-full font-semibold text-white shadow-md flex items-center gap-1.5 transition-all hover:scale-105"
        title="<?php echo e($getConditionDescription()); ?>">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($getTrendIcon()): ?>
            <span class="text-lg leading-none"><?php echo e($getTrendIcon()); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <span><?php echo e($condition); ?></span>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDuration && $turnsActive): ?>
            <span class="text-xs opacity-90">(<?php echo e($turnsActive); ?>T)</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('0e24108f-08bd-4f50-8713-7cef638d24a2')): $__env->markAsRenderedOnce('0e24108f-08bd-4f50-8713-7cef638d24a2'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/condition-badge.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/condition-badge.blade.php ENDPATH**/ ?>