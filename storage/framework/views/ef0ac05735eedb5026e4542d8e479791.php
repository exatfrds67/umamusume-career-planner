<?php if (isset($component)) { $__componentOriginal16d40a11d8cca540249588bdb639d32b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16d40a11d8cca540249588bdb639d32b = $attributes; } ?>
<?php $component = App\View\Components\MemoriesGrid::resolve(['items' => $items,'showLockIcons' => false] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('memories-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\MemoriesGrid::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal16d40a11d8cca540249588bdb639d32b)): ?>
<?php $attributes = $__attributesOriginal16d40a11d8cca540249588bdb639d32b; ?>
<?php unset($__attributesOriginal16d40a11d8cca540249588bdb639d32b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal16d40a11d8cca540249588bdb639d32b)): ?>
<?php $component = $__componentOriginal16d40a11d8cca540249588bdb639d32b; ?>
<?php unset($__componentOriginal16d40a11d8cca540249588bdb639d32b); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larF776.blade.php ENDPATH**/ ?>