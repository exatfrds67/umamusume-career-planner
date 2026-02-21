<?php if (isset($component)) { $__componentOriginal0d56272e151b61c739c6026f4156b193 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d56272e151b61c739c6026f4156b193 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert-banner','data' => ['type' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Success message <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d56272e151b61c739c6026f4156b193)): ?>
<?php $attributes = $__attributesOriginal0d56272e151b61c739c6026f4156b193; ?>
<?php unset($__attributesOriginal0d56272e151b61c739c6026f4156b193); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d56272e151b61c739c6026f4156b193)): ?>
<?php $component = $__componentOriginal0d56272e151b61c739c6026f4156b193; ?>
<?php unset($__componentOriginal0d56272e151b61c739c6026f4156b193); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/1a227919f3c2b8f885e1e4697183c040.blade.php ENDPATH**/ ?>