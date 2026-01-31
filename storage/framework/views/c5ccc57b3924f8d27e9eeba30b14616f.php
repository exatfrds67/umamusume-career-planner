<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['character', 'showStats', 'showAptitudes', 'size', 'selectable']));

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

foreach (array_filter((['character', 'showStats', 'showAptitudes', 'size', 'selectable']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    <?php echo e($attributes->merge(['class' => 'character-card ' . $getSizeClasses() . ' rounded-xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 ' . ($selectable ? 'cursor-pointer' : '')])); ?>>
    
    <div
        class="relative aspect-3/4 overflow-hidden bg-linear-to-br from-neutral-200 to-neutral-300 dark:from-neutral-700 dark:to-neutral-800">
        <?php if($getAvatarUrl()): ?>
            <img src="<?php echo e($getAvatarUrl()); ?>" alt="<?php echo e($getName()); ?>" class="w-full h-full object-cover" loading="lazy" />
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-neutral-400 dark:text-neutral-600">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        <?php endif; ?>

        
        <div class="absolute top-3 right-3">
            <?php if (isset($component)) { $__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c = $attributes; } ?>
<?php $component = App\View\Components\GradeBadge::resolve(['grade' => $getGrade(),'size' => 'lg'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('grade-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GradeBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c)): ?>
<?php $attributes = $__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c; ?>
<?php unset($__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c)): ?>
<?php $component = $__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c; ?>
<?php unset($__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c); ?>
<?php endif; ?>
        </div>

        
        <?php if($selectable): ?>
            <div
                class="absolute inset-0 bg-primary-600/0 hover:bg-primary-600/20 transition-colors duration-200 flex items-center justify-center">
                <div class="opacity-0 hover:opacity-100 transition-opacity duration-200">
                    <div class="bg-white dark:bg-neutral-800 rounded-full p-3 shadow-lg">
                        <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="p-4 bg-white dark:bg-neutral-800">
        
        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2 truncate">
            <?php echo e($getName()); ?>

        </h3>

        
        <?php if($showStats): ?>
            <div class="space-y-2">
                <?php $__currentLoopData = $getStats(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-600 dark:text-neutral-400 capitalize"><?php echo e($stat); ?></span>
                        <span
                            class="font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums"><?php echo e(number_format($value)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="pt-2 border-t border-neutral-200 dark:border-neutral-700 flex items-center justify-between text-sm font-bold">
                    <span class="text-neutral-700 dark:text-neutral-300">Total</span>
                    <span
                        class="text-primary-600 dark:text-primary-400 tabular-nums"><?php echo e(number_format($getTotalStats())); ?></span>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($showAptitudes && isset($character['aptitudes'])): ?>
            <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-700">
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $character['aptitudes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c = $attributes; } ?>
<?php $component = App\View\Components\GradeBadge::resolve(['grade' => $grade,'size' => 'sm','showLabel' => true,'label' => ucfirst($type)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('grade-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GradeBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c)): ?>
<?php $attributes = $__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c; ?>
<?php unset($__attributesOriginal24fcf5a7c3e53964c8ac9de20a22a25c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c)): ?>
<?php $component = $__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c; ?>
<?php unset($__componentOriginal24fcf5a7c3e53964c8ac9de20a22a25c); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('ae36b374-5d9b-4557-bf27-e1fb447a5af7')): $__env->markAsRenderedOnce('ae36b374-5d9b-4557-bf27-e1fb447a5af7'); ?>
    <?php $__env->startPush('styles'); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/components/character-card.css']); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/character-card.blade.php ENDPATH**/ ?>