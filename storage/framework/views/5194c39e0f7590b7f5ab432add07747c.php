<?php if (isset($component)) { $__componentOriginal331cc4c5db637be147c41b13ae4f08eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal331cc4c5db637be147c41b13ae4f08eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-grid','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Content here <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal331cc4c5db637be147c41b13ae4f08eb)): ?>
<?php $attributes = $__attributesOriginal331cc4c5db637be147c41b13ae4f08eb; ?>
<?php unset($__attributesOriginal331cc4c5db637be147c41b13ae4f08eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal331cc4c5db637be147c41b13ae4f08eb)): ?>
<?php $component = $__componentOriginal331cc4c5db637be147c41b13ae4f08eb; ?>
<?php unset($__componentOriginal331cc4c5db637be147c41b13ae4f08eb); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/4b68f2f7610adcddf146e97092c7bc54.blade.php ENDPATH**/ ?>