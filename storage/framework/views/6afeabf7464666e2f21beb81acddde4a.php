<?php if (isset($component)) { $__componentOriginal39f2763c88a2b6449e1e2b6e6a25d4ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal39f2763c88a2b6449e1e2b6e6a25d4ae = $attributes; } ?>
<?php $component = App\View\Components\StarRating::resolve(['stars' => 3,'maxStars' => 5] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('star-rating'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\StarRating::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal39f2763c88a2b6449e1e2b6e6a25d4ae)): ?>
<?php $attributes = $__attributesOriginal39f2763c88a2b6449e1e2b6e6a25d4ae; ?>
<?php unset($__attributesOriginal39f2763c88a2b6449e1e2b6e6a25d4ae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal39f2763c88a2b6449e1e2b6e6a25d4ae)): ?>
<?php $component = $__componentOriginal39f2763c88a2b6449e1e2b6e6a25d4ae; ?>
<?php unset($__componentOriginal39f2763c88a2b6449e1e2b6e6a25d4ae); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar97D3.blade.php ENDPATH**/ ?>