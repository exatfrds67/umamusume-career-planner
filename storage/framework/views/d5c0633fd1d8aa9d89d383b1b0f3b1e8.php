<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => '••••••••',
    'autocomplete' => 'current-password',
    'required' => true,
    'value' => '',
    'showToggle' => true,
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
    'id' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => '••••••••',
    'autocomplete' => 'current-password',
    'required' => true,
    'value' => '',
    'showToggle' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div>
    <label for="<?php echo e($id); ?>" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        <?php echo e($label); ?>

    </label>
    <div class="relative">
        <input id="<?php echo e($id); ?>" name="<?php echo e($name); ?>" type="password" autocomplete="<?php echo e($autocomplete); ?>"
            <?php if($required): ?> required <?php endif; ?> value="<?php echo e($value); ?>"
            class="appearance-none relative block w-full px-3 py-2 <?php echo e($showToggle ? 'pr-10' : ''); ?> border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm bg-white dark:bg-gray-800"
            placeholder="<?php echo e($placeholder); ?>" <?php echo e($attributes); ?>>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showToggle): ?>
            <button type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-md"
                onclick="togglePasswordVisibility('<?php echo e($id); ?>')" aria-label="Toggle password visibility"
                title="Show/hide password">
                <svg id="<?php echo e($id); ?>-eye-open"
                    class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="<?php echo e($id); ?>-eye-closed"
                    class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors hidden"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('54f5cfb6-19aa-4d52-ae40-8fb0a7d85edf')): $__env->markAsRenderedOnce('54f5cfb6-19aa-4d52-ae40-8fb0a7d85edf'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/password-input.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/password-input.blade.php ENDPATH**/ ?>