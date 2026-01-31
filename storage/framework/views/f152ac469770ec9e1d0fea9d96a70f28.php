<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['current', 'total', 'showStage', 'showProgress', 'size']));

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

foreach (array_filter((['current', 'total', 'showStage', 'showProgress', 'size']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-col gap-2 w-full ' . $getSizeClasses()])); ?>>
    
    <div class="flex items-center gap-3 w-full">
        
        <div
            class="shrink-0 w-10 h-10 rounded-full bg-linear-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd" />
            </svg>
        </div>

        
        <div class="flex-1 min-w-0">
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Turn <?php echo e($current); ?>

                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    / <?php echo e($total); ?>

                </span>
            </div>

            <?php if($showStage): ?>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-sm font-semibold <?php echo e($getStageColorClasses()); ?>">
                        <?php echo e($getStage()); ?> Year
                    </span>

                    
                    <span class="text-xs">
                        <?php echo e(match ($getStage()) {
                            'Junior' => '🌱',
                            'Classic' => '⭐',
                            'Senior' => '👑',
                            default => '📅',
                        }); ?>

                    </span>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="text-right shrink-0">
            <div class="text-lg font-bold text-gray-700 dark:text-gray-300">
                <?php echo e(number_format($getProgressPercentage(), 1)); ?>%
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Complete
            </div>
        </div>
    </div>

    
    <?php if($showProgress): ?>
        <div class="w-full space-y-2">
            <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                
                <div class="absolute inset-0 flex">
                    
                    <div class="flex-1 border-r-2 border-white dark:border-gray-800"></div>
                    
                    <div class="flex-1 border-r-2 border-white dark:border-gray-800"></div>
                    
                    <div class="flex-1"></div>
                </div>

                
                <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500 ease-out <?php echo e(match ($getStage()) {
                    'Junior' => 'bg-linear-to-r from-green-400 to-green-500',
                    'Classic' => 'bg-linear-to-r from-blue-400 to-blue-500',
                    'Senior' => 'bg-linear-to-r from-purple-400 to-purple-500',
                    default => 'bg-gray-500',
                }); ?>"
                    style="width: <?php echo e($getProgressPercentage()); ?>%" role="progressbar"
                    aria-valuenow="<?php echo e($current); ?>" aria-valuemin="1" aria-valuemax="<?php echo e($total); ?>"
                    aria-label="Turn <?php echo e($current); ?> of <?php echo e($total); ?>">
                    
                    <div
                        class="absolute inset-0 bg-linear-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                    </div>
                </div>
            </div>

            
            <div class="flex justify-between text-xs font-medium">
                <span class="text-green-600 dark:text-green-400">Junior (1-24)</span>
                <span class="text-blue-600 dark:text-blue-400">Classic (25-48)</span>
                <span class="text-purple-600 dark:text-purple-400">Senior (49-78)</span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (! $__env->hasRenderedOnce('a226e391-22c5-4506-a172-6c0c69bfedd8')): $__env->markAsRenderedOnce('a226e391-22c5-4506-a172-6c0c69bfedd8'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/turn-counter.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/turn-counter.blade.php ENDPATH**/ ?>