

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'lines' => 3,
    'showImage' => false,
    'showAvatar' => false,
    'showActions' => false,
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
    'lines' => 3,
    'showImage' => false,
    'showAvatar' => false,
    'showActions' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div 
    <?php echo e($attributes->merge([
        'class' => 'animate-pulse rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm',
        'aria-hidden' => 'true',
    ])); ?>

    role="presentation"
>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImage): ?>
        <div class="h-40 w-full rounded-lg bg-gray-200 dark:bg-gray-700 mb-4"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAvatar): ?>
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700"></div>
                <div class="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    
    <div class="h-5 w-3/4 rounded bg-gray-200 dark:bg-gray-700 mb-4"></div>
    
    
    <div class="space-y-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < $lines; $i++): ?>
            <?php
                $widths = ['w-full', 'w-5/6', 'w-4/5', 'w-3/4', 'w-2/3'];
                $width = $widths[$i % count($widths)];
            ?>
            <div class="h-3 <?php echo e($width); ?> rounded bg-gray-200 dark:bg-gray-700"></div>
        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showActions): ?>
        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="h-9 w-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-9 w-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/skeleton-card.blade.php ENDPATH**/ ?>