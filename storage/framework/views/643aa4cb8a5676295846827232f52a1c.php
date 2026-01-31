<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Ensure items is an array
    $items = is_array($items) ? $items : [];

    // Add home as first item if not present
    if (empty($items) || !isset($items[0]['label']) || $items[0]['label'] !== 'Home') {
        array_unshift($items, [
            'label' => 'Home',
            'url' => route('dashboard'),
            'icon' => 'home',
        ]);
    }
?>

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex items-center space-x-2 text-sm">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $isLast = $index === count($items) - 1;
                $label = $item['label'] ?? '';
                $url = $item['url'] ?? null;
                $icon = $item['icon'] ?? null;
            ?>

            <li class="flex items-center">
                <?php if(!$isLast): ?>
                    
                    <a href="<?php echo e($url); ?>"
                        class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded px-1"
                        <?php if($index === 0): ?> aria-label="Home" <?php endif; ?>>
                        <?php if($icon === 'home'): ?>
                            
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        <?php else: ?>
                            <span><?php echo e($label); ?></span>
                        <?php endif; ?>
                    </a>

                    
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-600 mx-1 shrink-0" fill="currentColor"
                        viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                <?php else: ?>
                    
                    <span class="font-semibold text-gray-900 dark:text-gray-100 px-1" aria-current="page">
                        <?php echo e($label); ?>

                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>