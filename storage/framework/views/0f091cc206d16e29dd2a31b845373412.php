<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" <?php echo e($attributes); ?>>
    <div class="<?php echo e($layoutClasses()); ?>">
        <!-- Image Section -->
        <?php if($image): ?>
            <div class="<?php echo e($imageSectionClasses()); ?>">
                <div class="relative w-full <?php echo e($imageSizeClasses()); ?> overflow-hidden rounded-lg <?php echo e($showBorder ? 'border-2 border-gray-300 dark:border-gray-600' : ''); ?> bg-gray-100 dark:bg-gray-700">
                    <img 
                        src="<?php echo e($image); ?>" 
                        alt="<?php echo e($name); ?>"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </div>
            </div>
        <?php endif; ?>

        <!-- Content Section -->
        <div class="<?php echo e($contentSectionClasses()); ?> py-4">
            <?php if($name || $title): ?>
                <div class="mb-4">
                    <?php if($name): ?>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                            <?php echo e($name); ?>

                        </h2>
                    <?php endif; ?>
                    
                    <?php if($title): ?>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">
                            <?php echo e($title); ?>

                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Slot for additional content -->
            <div class="space-y-6">
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
</div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/character-profile.blade.php ENDPATH**/ ?>