<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['card']));

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

foreach (array_filter((['card']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 overflow-hidden group">
    <!-- Card Image/Placeholder -->
    <div
        class="relative h-48 bg-linear-to-br from-<?php echo e($card->card_type ? ($card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray'); ?>-400 to-<?php echo e($card->card_type ? ($card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray'); ?>-600">
        <?php if($card->artwork_url): ?>
            <img src="<?php echo e($card->artwork_url); ?>" alt="<?php echo e($card->name); ?>" loading="lazy" decoding="async"
                class="w-full h-full object-cover">
        <?php else: ?>
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-20 h-20 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        <?php endif; ?>

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex gap-2">
            <?php if (isset($component)) { $__componentOriginal17d26a380d74b4b685794c3f9361cf57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17d26a380d74b4b685794c3f9361cf57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.support-card-rarity-badge','data' => ['rarity' => $card->rarity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('support-card-rarity-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rarity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card->rarity)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17d26a380d74b4b685794c3f9361cf57)): ?>
<?php $attributes = $__attributesOriginal17d26a380d74b4b685794c3f9361cf57; ?>
<?php unset($__attributesOriginal17d26a380d74b4b685794c3f9361cf57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17d26a380d74b4b685794c3f9361cf57)): ?>
<?php $component = $__componentOriginal17d26a380d74b4b685794c3f9361cf57; ?>
<?php unset($__componentOriginal17d26a380d74b4b685794c3f9361cf57); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginale360eb2d8f31536122836e386c242fd3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale360eb2d8f31536122836e386c242fd3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.support-card-tier-badge','data' => ['tier' => $card->meta_tier]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('support-card-tier-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tier' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card->meta_tier)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale360eb2d8f31536122836e386c242fd3)): ?>
<?php $attributes = $__attributesOriginale360eb2d8f31536122836e386c242fd3; ?>
<?php unset($__attributesOriginale360eb2d8f31536122836e386c242fd3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale360eb2d8f31536122836e386c242fd3)): ?>
<?php $component = $__componentOriginale360eb2d8f31536122836e386c242fd3; ?>
<?php unset($__componentOriginale360eb2d8f31536122836e386c242fd3); ?>
<?php endif; ?>
        </div>

        <?php if($card->is_limited): ?>
            <div class="absolute top-2 right-2">
                <span
                    class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                    Limited
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Card Info -->
    <div class="p-4">
        <div class="mb-2">
            <h3
                class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                <?php echo e($card->name); ?>

            </h3>
            <?php if($card->character_name): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                    <?php echo e($card->character_name); ?>

                </p>
            <?php endif; ?>
        </div>

        <!-- Type Badge -->
        <div class="mb-3">
            <?php if (isset($component)) { $__componentOriginal629443c8123a4db0c07e9aa117c1d5b1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal629443c8123a4db0c07e9aa117c1d5b1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.support-card-type-badge','data' => ['type' => $card->card_type]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('support-card-type-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card->card_type)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal629443c8123a4db0c07e9aa117c1d5b1)): ?>
<?php $attributes = $__attributesOriginal629443c8123a4db0c07e9aa117c1d5b1; ?>
<?php unset($__attributesOriginal629443c8123a4db0c07e9aa117c1d5b1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal629443c8123a4db0c07e9aa117c1d5b1)): ?>
<?php $component = $__componentOriginal629443c8123a4db0c07e9aa117c1d5b1; ?>
<?php unset($__componentOriginal629443c8123a4db0c07e9aa117c1d5b1); ?>
<?php endif; ?>
        </div>

        <!-- Stats Preview -->
        <div class="grid grid-cols-3 gap-2 text-xs mb-3">
            <?php if($card->speed_bonus > 0): ?>
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Speed</div>
                    <div class="font-medium text-gray-900 dark:text-white">+<?php echo e($card->speed_bonus); ?></div>
                </div>
            <?php endif; ?>
            <?php if($card->stamina_bonus > 0): ?>
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Stamina</div>
                    <div class="font-medium text-gray-900 dark:text-white">+<?php echo e($card->stamina_bonus); ?></div>
                </div>
            <?php endif; ?>
            <?php if($card->power_bonus > 0): ?>
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Power</div>
                    <div class="font-medium text-gray-900 dark:text-white">+<?php echo e($card->power_bonus); ?></div>
                </div>
            <?php endif; ?>
            <?php if($card->guts_bonus > 0): ?>
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Guts</div>
                    <div class="font-medium text-gray-900 dark:text-white">+<?php echo e($card->guts_bonus); ?></div>
                </div>
            <?php endif; ?>
            <?php if($card->wit_bonus > 0): ?>
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Wit</div>
                    <div class="font-medium text-gray-900 dark:text-white">+<?php echo e($card->wit_bonus); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <a href="<?php echo e(route('support-cards.show', $card)); ?>" class="btn btn-sm btn-secondary flex-1">
                View Details
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/support-card-tile.blade.php ENDPATH**/ ?>