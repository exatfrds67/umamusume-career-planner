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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Queue Monitor</h1>
            <form method="POST" action="<?php echo e(route('admin.queue.restart')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Restart
                    Workers</button>
            </form>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed Jobs</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['failed_count']); ?></p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Jobs</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['jobs_count']); ?></p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <form method="POST" action="<?php echo e(route('admin.queue.retry-all')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Retry All
                    Failed</button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.queue.flush')); ?>"
                onsubmit="return confirm('Clear all failed jobs?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Clear All
                    Failed</button>
            </form>
        </div>

        <!-- Failed Jobs -->
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Failed Jobs</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            ID</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Queue</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Failed At</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $failedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo e($job->id); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($job->queue); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($job->failed_at); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="<?php echo e(route('admin.queue.retry', $job->id)); ?>"
                                        class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400">Retry</button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('admin.queue.delete', $job->id)); ?>"
                                        class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No failed jobs.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <?php echo e($failedJobs->links()); ?>

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
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/admin/queue/index.blade.php ENDPATH**/ ?>