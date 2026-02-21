<?php if (isset($component)) { $__componentOriginal2e5a662c188018aa8f4e7a735a755fe5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e5a662c188018aa8f4e7a735a755fe5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.trend-analysis-chart','data' => ['data' => $data]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.trend-analysis-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($data)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e5a662c188018aa8f4e7a735a755fe5)): ?>
<?php $attributes = $__attributesOriginal2e5a662c188018aa8f4e7a735a755fe5; ?>
<?php unset($__attributesOriginal2e5a662c188018aa8f4e7a735a755fe5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e5a662c188018aa8f4e7a735a755fe5)): ?>
<?php $component = $__componentOriginal2e5a662c188018aa8f4e7a735a755fe5; ?>
<?php unset($__componentOriginal2e5a662c188018aa8f4e7a735a755fe5); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar9441.blade.php ENDPATH**/ ?>