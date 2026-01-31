<?php if (isset($component)) { $__componentOriginalc7e4a6088f64dcacd86716bfe2c6fb33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7e4a6088f64dcacd86716bfe2c6fb33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.stat-progression-chart','data' => ['data' => $data]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.stat-progression-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($data)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7e4a6088f64dcacd86716bfe2c6fb33)): ?>
<?php $attributes = $__attributesOriginalc7e4a6088f64dcacd86716bfe2c6fb33; ?>
<?php unset($__attributesOriginalc7e4a6088f64dcacd86716bfe2c6fb33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7e4a6088f64dcacd86716bfe2c6fb33)): ?>
<?php $component = $__componentOriginalc7e4a6088f64dcacd86716bfe2c6fb33; ?>
<?php unset($__componentOriginalc7e4a6088f64dcacd86716bfe2c6fb33); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar801C.blade.php ENDPATH**/ ?>