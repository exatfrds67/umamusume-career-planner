<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'operations' => [],
    'showPagination' => true,
    'pagination' => null,
    'emptyMessage' => 'No operations found.',
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
    'operations' => [],
    'showPagination' => true,
    'pagination' => null,
    'emptyMessage' => 'No operations found.',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusColors = [
        'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'cancelled' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    ];

    $typeIcons = [
        'import' => '📥',
        'export' => '📤',
        'migration' => '🔄',
        'backup' => '💾',
        'restore' => '♻️',
    ];
?>

<div <?php echo e($attributes->merge(['class' => 'operation-history'])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($operations)): ?>
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?php echo e($emptyMessage); ?></p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $operations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div
                    class="operation-item flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-colors">
                    <div class="flex items-center gap-4">
                        
                        <span class="text-2xl" role="img"
                            aria-label="<?php echo e($operation['operation_type'] ?? 'operation'); ?>">
                            <?php echo e($typeIcons[$operation['operation_type'] ?? 'import'] ?? '📋'); ?>

                        </span>

                        
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-gray-900 dark:text-white capitalize">
                                    <?php echo e(str_replace('_', ' ', $operation['operation_type'] ?? 'Operation')); ?>

                                </h4>
                                <span
                                    class="px-2 py-0.5 text-xs font-medium rounded-full <?php echo e($statusColors[$operation['status'] ?? 'pending']); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $operation['status'] ?? 'pending'))); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($operation['message'] ?? 'No details available'); ?>

                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($operation['results'])): ?>
                                <div class="mt-1 flex gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($operation['results']['imported'])): ?>
                                        <span><?php echo e($operation['results']['imported']); ?> imported</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($operation['results']['exported'])): ?>
                                        <span><?php echo e($operation['results']['exported']); ?> exported</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($operation['results']['failed'])): ?>
                                        <span class="text-red-500"><?php echo e($operation['results']['failed']); ?> failed</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($operation['completed_at'])): ?>
                                <?php echo e(\Carbon\Carbon::parse($operation['completed_at'])->diffForHumans()); ?>

                            <?php elseif(isset($operation['created_at'])): ?>
                                <?php echo e(\Carbon\Carbon::parse($operation['created_at'])->diffForHumans()); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($operation['operation_id'])): ?>
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                <?php echo e(substr($operation['operation_id'], 0, 8)); ?>...
                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPagination && $pagination && $pagination['total_pages'] > 1): ?>
            <div class="mt-4 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <?php echo e(($pagination['current_page'] - 1) * $pagination['per_page'] + 1); ?>

                    to
                    <?php echo e(min($pagination['current_page'] * $pagination['per_page'], $pagination['total'])); ?>

                    of <?php echo e($pagination['total']); ?> results
                </p>
                <div class="flex gap-2">
                    <button type="button"
                        class="pagination-btn px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        data-page="<?php echo e($pagination['current_page'] - 1); ?>"
                        <?php echo e($pagination['current_page'] <= 1 ? 'disabled' : ''); ?>>
                        Previous
                    </button>
                    <button type="button"
                        class="pagination-btn px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        data-page="<?php echo e($pagination['current_page'] + 1); ?>"
                        <?php echo e(!$pagination['has_more'] ? 'disabled' : ''); ?>>
                        Next
                    </button>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/data-management/operation-history.blade.php ENDPATH**/ ?>