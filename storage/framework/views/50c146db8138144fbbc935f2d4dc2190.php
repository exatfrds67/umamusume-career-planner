

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Select an option',
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
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
    'options' => [],
    'value' => null,
    'placeholder' => 'Select an option',
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $selectId = $name . '-select-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    $selectedValue = old($name, $value);
    
    $selectClasses = 'block w-full rounded-lg border px-4 py-2.5 text-sm transition-colors duration-150 ' .
        'appearance-none bg-no-repeat bg-right pr-10 cursor-pointer ' .
        'focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-error-500 text-error-900 focus:border-error-500 focus:ring-error-500/20 bg-error-50 dark:bg-error-900/10 dark:text-error-400 dark:border-error-500' 
            : 'border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-gray-800'
        ) .
        ($disabled ? ' opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-700' : '');
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
    
    // Normalize options to [{value, label}] format
    $normalizedOptions = collect($options)->map(function ($item, $key) {
        if (is_array($item) && isset($item['value'])) {
            return $item;
        }
        return ['value' => $key, 'label' => $item];
    })->values()->all();
?>

<div <?php echo e($attributes->only('class')->merge(['class' => 'form-group'])); ?>>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label for="<?php echo e($selectId); ?>" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            <?php echo e($label); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($required): ?>
                <span class="text-error-500 ml-0.5" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    
    <div class="relative">
        <select
            <?php echo e($attributes->except('class')); ?>

            name="<?php echo e($name); ?>"
            id="<?php echo e($selectId); ?>"
            <?php if($required): ?> required aria-required="true" <?php endif; ?>
            <?php if($disabled): ?> disabled aria-disabled="true" <?php endif; ?>
            <?php if($hasError): ?> aria-invalid="true" <?php endif; ?>
            <?php if($ariaDescribedBy): ?> aria-describedby="<?php echo e($ariaDescribedBy); ?>" <?php endif; ?>
            class="<?php echo e($selectClasses); ?>"
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($placeholder): ?>
                <option value="" disabled <?php echo e($selectedValue === null || $selectedValue === '' ? 'selected' : ''); ?>>
                    <?php echo e($placeholder); ?>

                </option>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $normalizedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <option 
                    value="<?php echo e($option['value']); ?>" 
                    <?php echo e((string)$selectedValue === (string)$option['value'] ? 'selected' : ''); ?>

                >
                    <?php echo e($option['label']); ?>

                </option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
        
        
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
    
    
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
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/form/select-dropdown.blade.php ENDPATH**/ ?>