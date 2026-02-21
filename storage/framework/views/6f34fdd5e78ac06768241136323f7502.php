<?php if (isset($component)) { $__componentOriginal0d56272e151b61c739c6026f4156b193 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d56272e151b61c739c6026f4156b193 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert-banner','data' => ['dismissible' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['dismissible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Message <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d56272e151b61c739c6026f4156b193)): ?>
<?php $attributes = $__attributesOriginal0d56272e151b61c739c6026f4156b193; ?>
<?php unset($__attributesOriginal0d56272e151b61c739c6026f4156b193); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d56272e151b61c739c6026f4156b193)): ?>
<?php $component = $__componentOriginal0d56272e151b61c739c6026f4156b193; ?>
<?php unset($__componentOriginal0d56272e151b61c739c6026f4156b193); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/2b3b8beef0c7e5c8ac9855659523c091.blade.php ENDPATH**/ ?>