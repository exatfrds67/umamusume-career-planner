

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
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
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inputId = $name . '-input-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    
    $inputClasses = 'block w-full rounded-lg border px-4 py-2.5 text-sm transition-colors duration-150 ' .
        'focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-error-500 text-error-900 placeholder-error-400 focus:border-error-500 focus:ring-error-500/20 bg-error-50 dark:bg-error-900/10 dark:text-error-400 dark:border-error-500' 
            : 'border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-gray-800'
        ) .
        ($disabled ? ' opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-700' : '') .
        ($readonly ? ' bg-gray-50 dark:bg-gray-700/50' : '');
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
?>

<div <?php echo e($attributes->only('class')->merge(['class' => 'form-group'])); ?>>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label for="<?php echo e($inputId); ?>" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            <?php echo e($label); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($required): ?>
                <span class="text-error-500 ml-0.5" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    
    <input
        <?php echo e($attributes->except('class')); ?>

        type="<?php echo e($type); ?>"
        name="<?php echo e($name); ?>"
        id="<?php echo e($inputId); ?>"
        value="<?php echo e(old($name, $value)); ?>"
        <?php if($placeholder): ?> placeholder="<?php echo e($placeholder); ?>" <?php endif; ?>
        <?php if($required): ?> required aria-required="true" <?php endif; ?>
        <?php if($disabled): ?> disabled aria-disabled="true" <?php endif; ?>
        <?php if($readonly): ?> readonly <?php endif; ?>
        <?php if($hasError): ?> aria-invalid="true" <?php endif; ?>
        <?php if($ariaDescribedBy): ?> aria-describedby="<?php echo e($ariaDescribedBy); ?>" <?php endif; ?>
        class="<?php echo e($inputClasses); ?>"
    />
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasError): ?>
        <p id="<?php echo e($errorId); ?>" class="mt-1.5 text-sm text-error-600 dark:text-error-400 flex items-center gap-1" role="alert">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?php echo e($error); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hint && !$hasError): ?>
        <p id="<?php echo e($hintId); ?>" class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
            <?php echo e($hint); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/form/text-input.blade.php ENDPATH**/ ?>