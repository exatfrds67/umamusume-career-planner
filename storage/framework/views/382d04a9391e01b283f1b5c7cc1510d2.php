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
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>

        <!-- Environment Info -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Environment Information</h2>
            <dl class="grid grid-cols-2 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $environment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            <?php echo e(ucwords(str_replace('_', ' ', $key))); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <?php echo e(is_bool($value) ? ($value ? 'Yes' : 'No') : $value); ?></dd>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </dl>
        </div>

        <!-- System Health -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">System Health</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $health; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="rounded-lg border p-4 dark:border-gray-700">
                        <h3 class="mb-2 font-medium text-gray-900 dark:text-white"><?php echo e(ucfirst($service)); ?></h3>
                        <span
                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                            <?php echo e($status['status'] === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200' : ''); ?>

                            <?php echo e($status['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' : ''); ?>

                            <?php echo e($status['status'] === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' : ''); ?>

                            <?php echo e($status['status'] === 'not_configured' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : ''); ?>">
                            <?php echo e(ucfirst($status['status'])); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($status['message'])): ?>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400"><?php echo e($status['message']); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <!-- Cache Management -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Cache Management</h2>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="<?php echo e(route('admin.system-settings.clear-cache')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="all">
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Clear All
                        Caches</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.system-settings.clear-cache')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Config</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.system-settings.clear-cache')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="route">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Routes</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.system-settings.clear-cache')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="view">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Views</button>
                </form>
            </div>
        </div>

        <!-- Optimization -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Application Optimization</h2>
            <div class="flex gap-2">
                <form method="POST" action="<?php echo e(route('admin.system-settings.optimize')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Optimize
                        Application</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.system-settings.clear-optimization')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Clear
                        Optimization</button>
                </form>
            </div>
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
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/admin/system-settings/index.blade.php ENDPATH**/ ?>