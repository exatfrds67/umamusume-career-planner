<?php if (isset($component)) { $__componentOriginalb33cfa811a0213955a036b6bd5cbdc7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb33cfa811a0213955a036b6bd5cbdc7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.critical-alert-badge','data' => ['alertCount' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.critical-alert-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alert-count' => 5]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb33cfa811a0213955a036b6bd5cbdc7d)): ?>
<?php $attributes = $__attributesOriginalb33cfa811a0213955a036b6bd5cbdc7d; ?>
<?php unset($__attributesOriginalb33cfa811a0213955a036b6bd5cbdc7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb33cfa811a0213955a036b6bd5cbdc7d)): ?>
<?php $component = $__componentOriginalb33cfa811a0213955a036b6bd5cbdc7d; ?>
<?php unset($__componentOriginalb33cfa811a0213955a036b6bd5cbdc7d); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/e64cb13bc0451b6888c0ad199fdb74b1.blade.php ENDPATH**/ ?>