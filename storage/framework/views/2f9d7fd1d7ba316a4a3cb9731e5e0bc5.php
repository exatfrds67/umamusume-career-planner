

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'steps' => [],
    'currentStep' => 0,
    'title' => null,
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
    'steps' => [],
    'currentStep' => 0,
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $totalSteps = count($steps);
    $progressPercent = $totalSteps > 0 ? (($currentStep + 1) / $totalSteps) * 100 : 0;
?>

<div 
    <?php echo e($attributes->merge([
        'class' => 'wizard-layout w-full max-w-4xl mx-auto',
    ])); ?>

    role="region"
    aria-label="<?php echo e($title ?? 'Multi-step form'); ?>"
>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title || isset($header)): ?>
        <div class="wizard-header mb-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($header); ?></h2>
            <?php elseif($title): ?>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($title); ?></h2>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="wizard-progress mb-8" role="progressbar" aria-valuenow="<?php echo e($currentStep + 1); ?>" aria-valuemin="1" aria-valuemax="<?php echo e($totalSteps); ?>" aria-label="Step <?php echo e($currentStep + 1); ?> of <?php echo e($totalSteps); ?>">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Step <?php echo e($currentStep + 1); ?> of <?php echo e($totalSteps); ?>

            </span>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                <?php echo e(round($progressPercent)); ?>%
            </span>
        </div>
        <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div 
                class="h-full bg-primary-600 rounded-full transition-all duration-300 ease-out"
                style="width: <?php echo e($progressPercent); ?>%"
            ></div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($steps) > 0): ?>
        <nav class="wizard-steps mb-8" aria-label="Wizard steps">
            <ol class="flex items-center justify-between">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stepLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php
                        $isCompleted = $index < $currentStep;
                        $isCurrent = $index === $currentStep;
                        $isPending = $index > $currentStep;
                        
                        $stepClasses = match(true) {
                            $isCompleted => 'bg-primary-600 text-white',
                            $isCurrent => 'bg-primary-600 text-white ring-2 ring-primary-300 ring-offset-2',
                            default => 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                        };
                        
                        $labelClasses = match(true) {
                            $isCompleted => 'text-primary-600 dark:text-primary-400',
                            $isCurrent => 'text-primary-600 dark:text-primary-400 font-semibold',
                            default => 'text-gray-500 dark:text-gray-400',
                        };
                    ?>
                    
                    <li class="flex flex-col items-center flex-1 <?php echo e($index < count($steps) - 1 ? 'relative' : ''); ?>">
                        
                        <div 
                            class="flex items-center justify-center w-10 h-10 rounded-full <?php echo e($stepClasses); ?> text-sm font-medium transition-all duration-200"
                            aria-current="<?php echo e($isCurrent ? 'step' : 'false'); ?>"
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCompleted): ?>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="sr-only">Completed:</span>
                            <?php else: ?>
                                <?php echo e($index + 1); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        
                        <span class="mt-2 text-xs sm:text-sm <?php echo e($labelClasses); ?> text-center max-w-[80px] truncate">
                            <?php echo e($stepLabel); ?>

                        </span>
                        
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index < count($steps) - 1): ?>
                            <div class="absolute top-5 left-1/2 w-full h-0.5 <?php echo e($isCompleted ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'); ?>" style="transform: translateX(50%); width: calc(100% - 2.5rem);"></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ol>
        </nav>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="wizard-content bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        <?php echo e($slot); ?>

    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
        <div class="wizard-footer mt-6 flex items-center justify-between">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/wizard-layout.blade.php ENDPATH**/ ?>