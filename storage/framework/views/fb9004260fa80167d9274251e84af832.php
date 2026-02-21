<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'stat',
    'current',
    'max',
    'target',
    'factorBonus',
    'showIcon',
    'showPercentage',
    'showSoftCap',
    'showLabel',
    'size',
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
    'stat',
    'current',
    'max',
    'target',
    'factorBonus',
    'showIcon',
    'showPercentage',
    'showSoftCap',
    'showLabel',
    'size',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'stat-bar-container'])); ?>>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?>
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showIcon): ?>
                    <div
                        class="stat-icon-<?php echo e($getStatColor()); ?> w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold">
                        <?php echo e(strtoupper(substr($getStatLabel(), 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    <?php echo e($getStatLabel()); ?>

                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100 tabular-nums">
                    <?php echo e(number_format($current)); ?>

                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAboveSoftCap()): ?>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
                        (<?php echo e(number_format($getEffectiveValue())); ?> eff.)
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPercentage): ?>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                        <?php echo e(number_format($getPercentage(), 1)); ?>%
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div
        class="relative w-full <?php echo e($size === 'sm' ? 'h-2' : ($size === 'lg' ? 'h-4' : 'h-3')); ?> bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-visible">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSoftCap && $getSoftCapPercentage() < 100): ?>
            <div class="absolute top-0 bottom-0 w-0.5 bg-neutral-400 dark:bg-neutral-500 z-10"
                style="left: <?php echo e($getSoftCapPercentage()); ?>%" title="Soft cap at 1200 (diminishing returns above this)">
                <div
                    class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-neutral-400 dark:bg-neutral-500 rounded-full">
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($getTargetPercentage() !== null): ?>
            <div class="absolute top-0 bottom-0 w-0.5 bg-<?php echo e($getStatColor()); ?>-600 dark:bg-<?php echo e($getStatColor()); ?>-400 z-10 opacity-50"
                style="left: <?php echo e($getTargetPercentage()); ?>%" title="Target: <?php echo e(number_format($target)); ?>">
                <div
                    class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-<?php echo e($getStatColor()); ?>-600 dark:bg-<?php echo e($getStatColor()); ?>-400 rounded-full opacity-50">
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="h-full bg-gradient-to-r from-<?php echo e($getStatColor()); ?>-400 to-<?php echo e($getStatColor()); ?>-500 rounded-full transition-all duration-300 ease-out relative overflow-hidden"
            style="width: <?php echo e(min(100, $getPercentage())); ?>%">
            
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer">
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAboveSoftCap()): ?>
                <div class="absolute top-0 right-0 bottom-0 bg-<?php echo e($getStatColor()); ?>-600/50 dark:bg-<?php echo e($getStatColor()); ?>-700/50"
                    style="width: <?php echo e((($current - 1200) / $current) * 100); ?>%"
                    title="Diminishing returns (50% effectiveness)"></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($factorBonus && $factorBonus > 0): ?>
        <div
            class="mt-1 flex items-center gap-1 text-xs text-<?php echo e($getStatColor()); ?>-600 dark:text-<?php echo e($getStatColor()); ?>-400">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z" />
            </svg>
            <span>+<?php echo e($factorBonus); ?> from factors</span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php if (! $__env->hasRenderedOnce('d7db5575-6fc4-4f75-b546-0035f0ecd87c')): $__env->markAsRenderedOnce('d7db5575-6fc4-4f75-b546-0035f0ecd87c'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/stat-bar.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/stat-bar.blade.php ENDPATH**/ ?>