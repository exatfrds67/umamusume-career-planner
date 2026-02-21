<?php if (isset($component)) { $__componentOriginale0f1cdd055772eb1d4a99981c240763e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0f1cdd055772eb1d4a99981c240763e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Application Logs</h1>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.logs.download')); ?>"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Download Logs</a>
                <form method="POST" action="<?php echo e(route('admin.logs.clear')); ?>" class="inline"
                    onsubmit="return confirm('Clear old logs?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Clear Old
                        Logs</button>
                </form>
            </div>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400">Log file size: <?php echo e($fileSize); ?></div>

        <!-- Filters -->
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search logs..."
                class="flex-1 rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <select name="level"
                class="rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Levels</option>
                <option value="error" <?php echo e(request('level') == 'error' ? 'selected' : ''); ?>>Error</option>
                <option value="warning" <?php echo e(request('level') == 'warning' ? 'selected' : ''); ?>>Warning</option>
                <option value="info" <?php echo e(request('level') == 'info' ? 'selected' : ''); ?>>Info</option>
                <option value="debug" <?php echo e(request('level') == 'debug' ? 'selected' : ''); ?>>Debug</option>
            </select>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Filter</button>
        </form>

        <!-- Logs -->
        <div class="space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                    <?php echo e($log['level'] === 'ERROR' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' : ''); ?>

                                    <?php echo e($log['level'] === 'WARNING' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' : ''); ?>

                                    <?php echo e($log['level'] === 'INFO' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200' : ''); ?>

                                    <?php echo e($log['level'] === 'DEBUG' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : ''); ?>">
                                    <?php echo e($log['level']); ?>

                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($log['timestamp']); ?></span>
                            </div>
                            <p class="mt-2 text-sm text-gray-900 dark:text-white"><?php echo e($log['message']); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log['context']): ?>
                                <pre class="mt-2 overflow-x-auto rounded bg-gray-100 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300"><?php echo e(trim($log['context'])); ?></pre>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="rounded-lg bg-white p-8 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">No logs found.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0f1cdd055772eb1d4a99981c240763e)): ?>
<?php $attributes = $__attributesOriginale0f1cdd055772eb1d4a99981c240763e; ?>
<?php unset($__attributesOriginale0f1cdd055772eb1d4a99981c240763e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0f1cdd055772eb1d4a99981c240763e)): ?>
<?php $component = $__componentOriginale0f1cdd055772eb1d4a99981c240763e; ?>
<?php unset($__componentOriginale0f1cdd055772eb1d4a99981c240763e); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/admin/logs/index.blade.php ENDPATH**/ ?>