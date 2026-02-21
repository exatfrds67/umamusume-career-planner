<?php if (isset($component)) { $__componentOriginalfd3a6f8f1730f577643b0c9e9ee5a212 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd3a6f8f1730f577643b0c9e9ee5a212 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.skeleton-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('skeleton-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd3a6f8f1730f577643b0c9e9ee5a212)): ?>
<?php $attributes = $__attributesOriginalfd3a6f8f1730f577643b0c9e9ee5a212; ?>
<?php unset($__attributesOriginalfd3a6f8f1730f577643b0c9e9ee5a212); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd3a6f8f1730f577643b0c9e9ee5a212)): ?>
<?php $component = $__componentOriginalfd3a6f8f1730f577643b0c9e9ee5a212; ?>
<?php unset($__componentOriginalfd3a6f8f1730f577643b0c9e9ee5a212); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/a76ee2116509fcd99cbdf49ef918ad4d.blade.php ENDPATH**/ ?>