<div class="w-full" <?php echo e($attributes); ?>>
    <?php if($showLabel || $showValues): ?>
        <div class="flex items-center justify-between mb-1 text-sm">
            <?php if($showLabel): ?>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    <?php echo e($percentage()); ?>%
                </span>
            <?php endif; ?>
            
            <?php if($showValues): ?>
                <span class="text-gray-600 dark:text-gray-400 tabular-nums">
                    <?php echo e(number_format($current)); ?> / <?php echo e(number_format($max)); ?>

                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden <?php echo e($sizeClasses()); ?>">
        <div 
            class="<?php echo e($colorClasses()); ?> <?php echo e($sizeClasses()); ?> rounded-full <?php echo e($animated ? 'transition-all duration-500 ease-out' : ''); ?>"
            style="width: <?php echo e($percentage()); ?>%"
            role="progressbar"
            aria-valuenow="<?php echo e($current); ?>"
            aria-valuemin="0"
            aria-valuemax="<?php echo e($max); ?>"
            aria-label="Progress: <?php echo e($percentage()); ?>%"
        ></div>
    </div>
</div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/progress-bar.blade.php ENDPATH**/ ?>