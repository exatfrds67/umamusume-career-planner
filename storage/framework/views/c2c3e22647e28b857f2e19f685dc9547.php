

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'title' => null,
    'size' => 'md',
    'closeable' => true,
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
    'title' => null,
    'size' => 'md',
    'closeable' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    
    $modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
?>

<div
    x-data="{ 
        open: false,
        focusables() {
            return [...this.$refs.panel.querySelectorAll('a, button, input:not([type=hidden]), textarea, select, details, [tabindex]:not([tabindex=-1])')]
                .filter(el => !el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
        },
        firstFocusable() { return this.focusables()[0]; },
        lastFocusable() { return this.focusables().slice(-1)[0]; },
        trapFocus(e) {
            if (e.key !== 'Tab') return;
            
            const first = this.firstFocusable();
            const last = this.lastFocusable();
            
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }"
    x-init="
        $watch('open', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
                $nextTick(() => firstFocusable()?.focus());
            } else {
                document.body.style.overflow = '';
            }
        })
    "
    @open-modal.window="$event.detail === '<?php echo e($name); ?>' && (open = true)"
    @close-modal.window="$event.detail === '<?php echo e($name); ?>' && (open = false)"
    @keydown.escape.window="<?php if($closeable): ?> open = false <?php endif; ?>"
    <?php echo e($attributes->merge(['class' => ''])); ?>

>
    
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm"
        <?php if($closeable): ?> @click="open = false" <?php endif; ?>
        aria-hidden="true"
    ></div>
    
    
    <div
        x-show="open"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        <?php if($title): ?> aria-labelledby="modal-title-<?php echo e($name); ?>" <?php endif; ?>
    >
        <div class="flex min-h-full items-center justify-center p-4">
            
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-ref="panel"
                @keydown="trapFocus"
                @click.stop
                class="relative w-full <?php echo e($modalSize); ?> transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl transition-all"
            >
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title || $closeable): ?>
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title): ?>
                            <h3 id="modal-title-<?php echo e($name); ?>" class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e($title); ?>

                            </h3>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($closeable): ?>
                            <button
                                type="button"
                                @click="open = false"
                                class="rounded-lg p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                                aria-label="Close modal"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                
                <div class="px-6 py-4">
                    <?php echo e($slot); ?>

                </div>
                
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
                        <?php echo e($footer); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/modal.blade.php ENDPATH**/ ?>