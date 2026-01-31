<?php if (isset($component)) { $__componentOriginalb9d0986f79238f03d3f70413292615c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9d0986f79238f03d3f70413292615c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-management.progress-tracker','data' => ['operationId' => 'test-123','operationType' => 'import','status' => 'in_progress','progress' => 50,'message' => 'Processing...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-management.progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['operation-id' => 'test-123','operation-type' => 'import','status' => 'in_progress','progress' => 50,'message' => 'Processing...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9d0986f79238f03d3f70413292615c3)): ?>
<?php $attributes = $__attributesOriginalb9d0986f79238f03d3f70413292615c3; ?>
<?php unset($__attributesOriginalb9d0986f79238f03d3f70413292615c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9d0986f79238f03d3f70413292615c3)): ?>
<?php $component = $__componentOriginalb9d0986f79238f03d3f70413292615c3; ?>
<?php unset($__componentOriginalb9d0986f79238f03d3f70413292615c3); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar35D.blade.php ENDPATH**/ ?>