<?php if (isset($component)) { $__componentOriginal0ebc6257e0abf0714c5bdb3c5c443e66 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ebc6257e0abf0714c5bdb3c5c443e66 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.comparison-table','data' => ['careers' => $careers]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.comparison-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['careers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($careers)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ebc6257e0abf0714c5bdb3c5c443e66)): ?>
<?php $attributes = $__attributesOriginal0ebc6257e0abf0714c5bdb3c5c443e66; ?>
<?php unset($__attributesOriginal0ebc6257e0abf0714c5bdb3c5c443e66); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ebc6257e0abf0714c5bdb3c5c443e66)): ?>
<?php $component = $__componentOriginal0ebc6257e0abf0714c5bdb3c5c443e66; ?>
<?php unset($__componentOriginal0ebc6257e0abf0714c5bdb3c5c443e66); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larBCA2.blade.php ENDPATH**/ ?>