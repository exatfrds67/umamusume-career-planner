<div class="inline-flex <?php echo e($layoutClasses()); ?>" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            <?php echo e($typeLabel()); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <div class="flex items-center gap-1">
        <span class="font-bold text-lg <?php echo e($gradeColor()); ?>">
            <?php echo e($grade); ?>

        </span>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bonus > 0): ?>
            <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                +<?php echo e($bonus); ?>%
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/aptitude-display.blade.php ENDPATH**/ ?>