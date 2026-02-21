<span class="inline-flex items-center rounded-full font-semibold <?php echo e($badgeColor()); ?> <?php echo e($sizeClasses()); ?>" <?php echo e($attributes); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?>
        <span class="opacity-90 mr-0.5">Lv</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <span><?php echo e($level); ?></span>
</span><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/potential-badge.blade.php ENDPATH**/ ?>