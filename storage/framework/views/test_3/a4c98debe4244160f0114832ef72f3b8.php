<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'errors' => [],
    'title' => 'Errors Occurred',
    'type' => 'error', // error, warning, info
    'showResolution' => true,
    'dismissible' => true,
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
    'errors' => [],
    'title' => 'Errors Occurred',
    'type' => 'error', // error, warning, info
    'showResolution' => true,
    'dismissible' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $typeStyles = [
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'icon_bg' => 'bg-red-100 dark:bg-red-900/30',
            'icon_color' => 'text-red-600 dark:text-red-400',
            'title_color' => 'text-red-800 dark:text-red-200',
            'text_color' => 'text-red-700 dark:text-red-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'icon_color' => 'text-yellow-600 dark:text-yellow-400',
            'title_color' => 'text-yellow-800 dark:text-yellow-200',
            'text_color' => 'text-yellow-700 dark:text-yellow-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'icon_bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'icon_color' => 'text-blue-600 dark:text-blue-400',
            'title_color' => 'text-blue-800 dark:text-blue-200',
            'text_color' => 'text-blue-700 dark:text-blue-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
    ];

    $style = $typeStyles[$type] ?? $typeStyles['error'];

    // Common error resolutions
    $resolutions = [
        'Invalid JSON' => 'Check your JSON syntax. Use a JSON validator to find formatting errors.',
        'Column count mismatch' => 'Ensure all rows have the same number of columns as the header row.',
        'Invalid numeric value' => 'Make sure numeric fields contain only numbers (0-1200 for stats).',
        'Invalid scenario type' => 'Use "ura_finale" or "unity_cup" for scenario type.',
        'File not found' => 'The file may have been moved or deleted. Try uploading again.',
        'Permission denied' => 'Check file permissions or try a different file.',
        'Checksum mismatch' => 'The file may be corrupted. Try downloading or creating a new backup.',
        'Version incompatible' =>
            'This backup was created with a different version. Contact support for migration assistance.',
        'Duplicate entry' => 'A record with this name already exists. Use a different name or enable overwrite mode.',
        'Required field missing' => 'Make sure all required fields are filled in.',
        'Rate limit exceeded' => 'Too many requests. Please wait a moment and try again.',
        'Connection timeout' => 'The server took too long to respond. Check your connection and try again.',
    ];
?>

<?php if(!empty($errors)): ?>
    <div <?php echo e($attributes->merge(['class' => "error-display rounded-lg border p-4 {$style['bg']} {$style['border']}"])); ?>

        x-data="{ expanded: false, dismissed: false }" x-show="!dismissed" x-transition>

        <div class="flex items-start gap-3">
            
            <div class="shrink-0">
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full <?php echo e($style['icon_bg']); ?> <?php echo e($style['icon_color']); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php echo $style['icon']; ?>

                    </svg>
                </span>
            </div>

            
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium <?php echo e($style['title_color']); ?>">
                    <?php echo e($title); ?>

                    <?php if(count($errors) > 1): ?>
                        <span class="font-normal">(<?php echo e(count($errors)); ?> issues)</span>
                    <?php endif; ?>
                </h3>

                
                <div class="mt-2">
                    <?php if(count($errors) <= 3): ?>
                        <ul class="list-disc list-inside space-y-1 text-sm <?php echo e($style['text_color']); ?>">
                            <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        
                        <ul class="list-disc list-inside space-y-1 text-sm <?php echo e($style['text_color']); ?>">
                            <?php $__currentLoopData = array_slice($errors, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <div x-show="expanded" x-collapse>
                            <ul class="list-disc list-inside space-y-1 text-sm <?php echo e($style['text_color']); ?> mt-1">
                                <?php $__currentLoopData = array_slice($errors, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <button type="button" @click="expanded = !expanded"
                            class="mt-2 text-sm font-medium <?php echo e($style['icon_color']); ?> hover:underline">
                            <span x-text="expanded ? 'Show less' : 'Show <?php echo e(count($errors) - 2); ?> more'"></span>
                        </button>
                    <?php endif; ?>
                </div>

                
                <?php if($showResolution): ?>
                    <?php
                        $matchedResolutions = [];
                        foreach ($errors as $error) {
                            foreach ($resolutions as $pattern => $resolution) {
                                if (stripos($error, $pattern) !== false) {
                                    $matchedResolutions[$pattern] = $resolution;
                                }
                            }
                        }
                    ?>

                    <?php if(!empty($matchedResolutions)): ?>
                        <div class="mt-3 pt-3 border-t <?php echo e($style['border']); ?>">
                            <h4 class="text-sm font-medium <?php echo e($style['title_color']); ?> mb-2">
                                💡 Suggested Resolutions
                            </h4>
                            <ul class="space-y-2 text-sm <?php echo e($style['text_color']); ?>">
                                <?php $__currentLoopData = $matchedResolutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern => $resolution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span><strong><?php echo e($pattern); ?>:</strong> <?php echo e($resolution); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                
                <?php if($slot->isNotEmpty()): ?>
                    <div class="mt-3 pt-3 border-t <?php echo e($style['border']); ?>">
                        <?php echo e($slot); ?>

                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($dismissible): ?>
                <button type="button" @click="dismissed = true"
                    class="shrink-0 <?php echo e($style['icon_color']); ?> hover:opacity-75 transition-opacity">
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/data-management/error-display.blade.php ENDPATH**/ ?>