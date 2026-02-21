<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['recommendation', 'expanded' => false, 'showActions' => true]));

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

foreach (array_filter((['recommendation', 'expanded' => false, 'showActions' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $priorityColors = [
        'critical' => 'red',
        'high' => 'orange',
        'medium' => 'blue',
        'low' => 'neutral',
    ];

    $priorityIcons = [
        'critical' => '🚨',
        'high' => '⭐',
        'medium' => '💡',
        'low' => 'ℹ️',
    ];

    $color = $priorityColors[$recommendation->priority] ?? 'neutral';
    $icon = $priorityIcons[$recommendation->priority] ?? '•';

    $cardId = 'recommendation-' . uniqid();
?>

<div x-data="{
    expanded: <?php echo \Illuminate\Support\Js::from($expanded)->toHtml() ?>,
    dismissed: false
}" x-show="!dismissed" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95"
    <?php echo e($attributes->merge(['class' => 'recommendation-card rounded-lg border-2 transition-all duration-300 hover:shadow-lg bg-white dark:bg-neutral-800 border-' . $color . '-200 dark:border-' . $color . '-700'])); ?>

    role="article" aria-labelledby="<?php echo e($cardId); ?>-title" aria-expanded="false"
    x-bind:aria-expanded="expanded.toString()">
    
    <button @click="expanded = !expanded"
        class="w-full text-left p-4 focus:outline-none focus:ring-2 focus:ring-<?php echo e($color); ?>-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 rounded-t-lg"
        aria-controls="<?php echo e($cardId); ?>-content" aria-label="Toggle recommendation details">
        <div class="flex items-start justify-between gap-3">
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xl" role="img"
                        aria-label="<?php echo e(ucfirst($recommendation->priority)); ?> priority"><?php echo e($icon); ?></span>
                    <h3 id="<?php echo e($cardId); ?>-title"
                        class="text-lg font-bold text-neutral-900 dark:text-neutral-100 truncate">
                        <?php echo e($recommendation->action); ?>

                    </h3>
                </div>

                
                <div class="flex items-center gap-3 text-sm">
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 dark:bg-<?php echo e($color); ?>-900/30 dark:text-<?php echo e($color); ?>-300">
                        Priority: <?php echo e(ucfirst($recommendation->priority)); ?>

                    </span>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recommendation->confidence_score) && $recommendation->confidence_score): ?>
                        <span class="text-neutral-600 dark:text-neutral-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>Confidence: <?php echo e(number_format($recommendation->confidence_score * 100)); ?>%</span>
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="shrink-0 mt-1">
                <svg class="w-5 h-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </button>

    
    <div id="<?php echo e($cardId); ?>-content" x-show="expanded" x-collapse
        class="border-t border-<?php echo e($color); ?>-200 dark:border-<?php echo e($color); ?>-700">
        <div class="p-4 space-y-4">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recommendation->reasoning) && $recommendation->reasoning): ?>
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Reasoning
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = explode("\n", $recommendation->reasoning); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim($line)): ?>
                                <div class="flex items-start gap-2">
                                    <span class="text-<?php echo e($color); ?>-500 mt-0.5 shrink-0"
                                        aria-hidden="true">•</span>
                                    <span class="flex-1"><?php echo e(trim($line, '• ')); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recommendation->expected_outcomes) && !empty($recommendation->expected_outcomes)): ?>
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Expected Outcomes
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recommendation->expected_outcomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $outcome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <div class="flex items-start gap-2">
                                <span class="text-success-500 mt-0.5 shrink-0" aria-hidden="true">✓</span>
                                <span class="flex-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_string($key) && !is_numeric($key)): ?>
                                        <strong><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php echo e(is_array($outcome) ? implode(', ', $outcome) : $outcome); ?>

                                </span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recommendation->risks) && !empty($recommendation->risks)): ?>
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Risks
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recommendation->risks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <div class="flex items-start gap-2">
                                <span class="text-warning-500 mt-0.5 shrink-0" aria-hidden="true">⚠</span>
                                <span class="flex-1"><?php echo e($risk); ?></span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showActions): ?>
                <div class="flex items-center gap-2 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                    <button type="button" wire:click="applyRecommendation('<?php echo e($recommendation->id ?? ''); ?>')"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-<?php echo e($color); ?>-600 text-white hover:bg-<?php echo e($color); ?>-700 focus:outline-none focus:ring-2 focus:ring-<?php echo e($color); ?>-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Apply this recommendation">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Apply Recommendation
                    </button>

                    <button type="button" @click="dismissed = true"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Dismiss this recommendation">
                        Dismiss
                    </button>

                    <button type="button" wire:click="provideFeedback('<?php echo e($recommendation->id ?? ''); ?>')"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Provide feedback on this recommendation">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        Feedback
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('ceb02818-3927-46a3-9e7a-4d10269805aa')): $__env->markAsRenderedOnce('ceb02818-3927-46a3-9e7a-4d10269805aa'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/ai/recommendation-card.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ai/recommendation-card.blade.php ENDPATH**/ ?>