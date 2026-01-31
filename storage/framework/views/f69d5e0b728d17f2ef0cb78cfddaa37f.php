<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['grade', 'size', 'showLabel', 'label']));

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

foreach (array_filter((['grade', 'size', 'showLabel', 'label']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-2'])); ?>>
    
    <div class="grade-badge <?php echo e($getSizeClasses()); ?> <?php echo e($getGradeColor()); ?> flex items-center justify-center rounded-full font-bold text-white shadow-md transition-transform hover:scale-110"
        title="<?php echo e($getGradeDescription()); ?>">
        <?php echo e($grade); ?>

    </div>

    
    <?php if($showLabel): ?>
        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
            <?php echo e($label ?? $getGradeDescription()); ?>

        </span>
    <?php endif; ?>
</div>

<?php if (! $__env->hasRenderedOnce('d6a93b65-a4da-489e-a1da-6205a5da4221')): $__env->markAsRenderedOnce('d6a93b65-a4da-489e-a1da-6205a5da4221'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/grade-badge.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/grade-badge.blade.php ENDPATH**/ ?>