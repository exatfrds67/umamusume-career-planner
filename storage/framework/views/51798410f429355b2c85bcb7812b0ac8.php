<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tools' => []]));

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

foreach (array_filter((['tools' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="tool-usage-indicator" x-data="toolUsageIndicator()" x-init="initialize()">

    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Active Tools:</span>

        <template x-for="tool in activeTools" :key="tool.id">
            <div
                class="flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-gray-700 rounded-full border border-blue-200 dark:border-blue-800 shadow-sm">
                
                <svg class="w-3 h-3 text-blue-600 dark:text-blue-400 animate-pulse" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>

                
                <span class="text-xs font-medium text-gray-900 dark:text-white" x-text="tool.name"></span>

                
                <template x-if="tool.status === 'running'">
                    <svg class="w-3 h-3 text-blue-600 dark:text-blue-400 animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </template>

                
                <template x-if="tool.duration">
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="`${tool.duration}s`"></span>
                </template>
            </div>
        </template>

        
        <template x-if="activeTools.length === 0">
            <span class="text-xs text-gray-500 dark:text-gray-400">None</span>
        </template>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('0e43a979-c9ff-43f1-a6e8-45ead0454c15')): $__env->markAsRenderedOnce('0e43a979-c9ff-43f1-a6e8-45ead0454c15'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/ai/tool-usage-indicator.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ai/tool-usage-indicator.blade.php ENDPATH**/ ?>