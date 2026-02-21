<?php if (isset($component)) { $__componentOriginalc3e303cff3801c198e2032bc73b32791 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc3e303cff3801c198e2032bc73b32791 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-management.operation-history','data' => ['operations' => [],'emptyMessage' => 'No operations yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-management.operation-history'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['operations' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([]),'empty-message' => 'No operations yet']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc3e303cff3801c198e2032bc73b32791)): ?>
<?php $attributes = $__attributesOriginalc3e303cff3801c198e2032bc73b32791; ?>
<?php unset($__attributesOriginalc3e303cff3801c198e2032bc73b32791); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc3e303cff3801c198e2032bc73b32791)): ?>
<?php $component = $__componentOriginalc3e303cff3801c198e2032bc73b32791; ?>
<?php unset($__componentOriginalc3e303cff3801c198e2032bc73b32791); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larA201.blade.php ENDPATH**/ ?>