<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['race', 'readiness', 'winProb', 'isEntered', 'clickable', 'size']));

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

foreach (array_filter((['race', 'readiness', 'winProb', 'isEntered', 'clickable', 'size']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge([
    'class' =>
        'relative rounded-lg border-2 transition-all duration-200 ' .
        $getSizeClasses() .
        ' ' .
        ($isEntered
            ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-950'
            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800') .
        ' ' .
        ($clickable ? 'cursor-pointer hover:shadow-lg hover:scale-[1.02]' : ''),
])); ?>

    role="<?php echo e($clickable ? 'button' : 'article'); ?>" tabindex="<?php echo e($clickable ? '0' : '-1'); ?>">
    
    <div
        class="absolute -top-3 -right-3 px-3 py-1 rounded-full <?php echo e($getGradeColorClasses()); ?> font-bold text-sm shadow-lg">
        <?php echo e($race['grade'] ?? 'OP'); ?>

    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEntered): ?>
        <div
            class="absolute -top-3 -left-3 px-3 py-1 rounded-full bg-blue-500 text-white font-bold text-xs shadow-lg flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            Entered
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2 pr-12">
        <?php echo e($race['name'] ?? 'Race Name'); ?>

    </h3>

    
    <div class="grid grid-cols-2 gap-2 mb-3">
        
        <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span><?php echo e($race['distance'] ?? '2000'); ?>m</span>
        </div>

        
        <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
            <span><?php echo e($getTrackIcon()); ?></span>
            <span class="capitalize"><?php echo e($race['track'] ?? 'Turf'); ?></span>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($race['style'])): ?>
            <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                        clip-rule="evenodd" />
                </svg>
                <span class="capitalize"><?php echo e($race['style']); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($race['turn'])): ?>
            <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd" />
                </svg>
                <span>Turn <?php echo e($race['turn']); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($readiness !== null || $winProb !== null): ?>
        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3 space-y-2">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($readiness !== null): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Readiness</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300 <?php echo e($readiness >= 80 ? 'bg-green-500' : ($readiness >= 60 ? 'bg-amber-500' : 'bg-red-500')); ?>"
                                style="width: <?php echo e($readiness); ?>%"></div>
                        </div>
                        <span class="text-sm font-bold <?php echo e($getReadinessColorClasses()); ?> min-w-15 text-right">
                            <?php echo e($readiness); ?>%
                        </span>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($winProb !== null): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Win Chance</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-linear-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-300"
                                style="width: <?php echo e($winProb); ?>%"></div>
                        </div>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400 min-w-15 text-right">
                            <?php echo e(number_format($winProb, 1)); ?>%
                        </span>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($race['weather']) || isset($race['condition'])): ?>
        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <?php if(isset($race['weather'])): ?>
                <span class="flex items-center gap-1">
                    <?php echo e(match ($race['weather']) {
                        'sunny' => '☀️',
                        'cloudy' => '☁️',
                        'rainy' => '🌧️',
                        'snowy' => '❄️',
                        default => '🌤️',
                    }); ?>

                    <?php echo e(ucfirst($race['weather'])); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($race['condition'])): ?>
                <span class="flex items-center gap-1">
                    •
                    <?php echo e(ucfirst($race['condition'])); ?> Track
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/race-card.blade.php ENDPATH**/ ?>