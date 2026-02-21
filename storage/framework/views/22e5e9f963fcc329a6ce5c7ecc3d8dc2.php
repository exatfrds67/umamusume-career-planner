<div class="relative inline-block <?php echo e($sizeClasses()); ?>" <?php echo e($attributes); ?>>
    <div class="relative w-full h-full <?php echo e($roundedClasses()); ?> <?php echo e($borderClasses()); ?> bg-gray-100 dark:bg-gray-800">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
            <img 
                src="<?php echo e($image); ?>" 
                alt="<?php echo e($alt); ?>"
                class="w-full h-full object-cover"
                loading="lazy"
            />
        <?php else: ?>
            
            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                <svg class="w-1/2 h-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($badge): ?>
            <div class="absolute <?php echo e($badgePositionClasses()); ?> m-1 z-10">
                <?php echo $badge; ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        
        <?php echo e($slot); ?>

    </div>
</div><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/character-portrait.blade.php ENDPATH**/ ?>