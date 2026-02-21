<div class="flex items-center gap-2">
    <span 
        class="inline-flex items-center justify-center <?php echo e($sizeClasses()); ?> <?php echo e($colorClasses()); ?>"
        title="<?php echo e($label()); ?>"
        aria-label="<?php echo e($label()); ?> stat"
    >
        <?php echo e($iconSymbol()); ?>

    </span>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?>
        <span class="text-sm font-medium <?php echo e($colorClasses()); ?>">
            <?php echo e($label()); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/type-icon.blade.php ENDPATH**/ ?>