

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label',
    'checked' => false,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'size' => 'md',
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
    'name',
    'label',
    'checked' => false,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $toggleId = $name . '-toggle-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    $isChecked = old($name, $checked);
    
    $sizes = [
        'sm' => ['track' => 'w-8 h-4', 'thumb' => 'h-3 w-3', 'translate' => 'translate-x-4'],
        'md' => ['track' => 'w-11 h-6', 'thumb' => 'h-5 w-5', 'translate' => 'translate-x-5'],
        'lg' => ['track' => 'w-14 h-7', 'thumb' => 'h-6 w-6', 'translate' => 'translate-x-7'],
    ];
    
    $sizeConfig = $sizes[$size] ?? $sizes['md'];
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
?>

<div 
    <?php echo e($attributes->only('class')->merge(['class' => 'form-group'])); ?>

    x-data="{ enabled: <?php echo e($isChecked ? 'true' : 'false'); ?> }"
>
    <div class="flex items-center justify-between gap-4">
        
        <div class="flex flex-col">
            <label for="<?php echo e($toggleId); ?>" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer <?php echo e($disabled ? 'opacity-50 cursor-not-allowed' : ''); ?>">
                <?php echo e($label); ?>

            </label>
            
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hint && !$hasError): ?>
                <p id="<?php echo e($hintId); ?>" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    <?php echo e($hint); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        
        <button
            type="button"
            id="<?php echo e($toggleId); ?>"
            role="switch"
            :aria-checked="enabled.toString()"
            @click="enabled = !enabled"
            <?php if($disabled): ?> disabled aria-disabled="true" <?php endif; ?>
            <?php if($hasError): ?> aria-invalid="true" <?php endif; ?>
            <?php if($ariaDescribedBy): ?> aria-describedby="<?php echo e($ariaDescribedBy); ?>" <?php endif; ?>
            :class="enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
            class="<?php echo e($sizeConfig['track']); ?> relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 <?php echo e($disabled ? 'opacity-50 cursor-not-allowed' : ''); ?> <?php echo e($hasError ? 'ring-2 ring-error-500' : ''); ?>"
        >
            <span class="sr-only"><?php echo e($label); ?></span>
            <span
                aria-hidden="true"
                :class="enabled ? '<?php echo e($sizeConfig['translate']); ?>' : 'translate-x-0'"
                class="<?php echo e($sizeConfig['thumb']); ?> pointer-events-none inline-block transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            ></span>
        </button>
        
        
        <input 
            type="hidden" 
            name="<?php echo e($name); ?>" 
            :value="enabled ? '1' : '0'"
        />
    </div>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasError): ?>
        <p id="<?php echo e($errorId); ?>" class="mt-1.5 text-sm text-error-600 dark:text-error-400 flex items-center gap-1" role="alert">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?php echo e($error); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/form/toggle.blade.php ENDPATH**/ ?>