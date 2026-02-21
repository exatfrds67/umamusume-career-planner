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
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Database Maintenance</h1>

        <!-- Actions -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <form method="POST" action="<?php echo e(route('admin.database.optimize')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Optimize Tables</button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.database.backup')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Create
                    Backup</button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.database.migrate')); ?>"
                onsubmit="return confirm('Run migrations?')">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Run
                    Migrations</button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.database.fresh')); ?>"
                onsubmit="return confirm('This will DELETE ALL DATA! Are you sure?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Fresh
                    + Seed</button>
            </form>
        </div>

        <!-- Migration Status -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Migration Status</h2>
            <pre class="overflow-x-auto rounded bg-gray-100 p-4 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300"><?php echo e($migrationStatus['output']); ?></pre>
        </div>

        <!-- Tables -->
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Database Tables</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Table</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Rows</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Size</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Engine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo e($table['name']); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e(number_format($table['rows'])); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($table['size']); ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($table['engine']); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
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
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/admin/database/maintenance.blade.php ENDPATH**/ ?>