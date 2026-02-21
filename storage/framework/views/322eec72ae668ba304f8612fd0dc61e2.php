<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value', 'trend', 'showIcon', 'showTrend', 'size']));

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

foreach (array_filter((['value', 'trend', 'showIcon', 'showTrend', 'size']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex items-center gap-3 w-full ' . $getSizeClasses()])); ?>>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showIcon): ?>
        <div
            class="shrink-0 w-10 h-10 rounded-full <?php echo e(match ($getStatus()) {
                'high' => 'bg-green-100 dark:bg-green-900/30',
                'medium' => 'bg-amber-100 dark:bg-amber-900/30',
                'low' => 'bg-red-100 dark:bg-red-900/30',
                default => 'bg-gray-100 dark:bg-gray-800',
            }); ?> flex items-center justify-center">
            <svg class="w-6 h-6 <?php echo e($getColorClasses()); ?>" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex-1 min-w-0 w-full">
        
        <div class="flex items-center justify-between mb-1.5 w-full">
            <span id="energy-label" class="text-sm font-medium text-gray-700 dark:text-gray-300">Energy</span>
            <div class="flex items-center gap-1.5">
                <span class="text-lg font-bold <?php echo e($getColorClasses()); ?>">
                    <?php echo e($value); ?>%
                </span>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrend): ?>
                    <span class="text-xl font-bold <?php echo e($getTrendColorClasses()); ?>" title="Trend: <?php echo e($trend); ?>">
                        <?php echo e($getTrendArrow()); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden w-full">
            
            <div class="<?php echo e($getProgressColorClasses()); ?> h-full rounded-full transition-all duration-500 ease-out relative"
                style="width: <?php echo e($value); ?>%" role="progressbar" aria-valuenow="<?php echo e($value); ?>"
                aria-valuemin="0" aria-valuemax="100" aria-label="Energy: <?php echo e($value); ?>%"
                aria-labelledby="energy-label">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($getStatus() === 'high'): ?>
                    <div
                        class="absolute inset-0 bg-linear-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if($getStatus() === 'low'): ?>
                    <div class="absolute inset-0 bg-red-400 animate-pulse"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="absolute top-0 bottom-0 left-[40%] w-0.5 bg-amber-400/50" title="Low energy threshold"></div>
            <div class="absolute top-0 bottom-0 left-[70%] w-0.5 bg-green-400/50" title="Good energy threshold"></div>
        </div>

        
        <div class="mt-1 text-xs font-medium <?php echo e($getColorClasses()); ?>">
            <?php echo e(match ($getStatus()) {
                'high' => '✓ Good condition',
                'medium' => '⚠ Moderate energy',
                'low' => '⚠ Low energy - rest recommended',
                default => 'Unknown',
            }); ?>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('c19ad980-39c6-44aa-8aaa-1a7c32853bcb')): $__env->markAsRenderedOnce('c19ad980-39c6-44aa-8aaa-1a7c32853bcb'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/gauges.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/energy-gauge.blade.php ENDPATH**/ ?>