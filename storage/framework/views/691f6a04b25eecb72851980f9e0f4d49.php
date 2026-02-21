<?php if (isset($component)) { $__componentOriginal097491bc1de829a06e0f87e08566650e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal097491bc1de829a06e0f87e08566650e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.performance-dashboard','data' => ['metrics' => $metrics]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.performance-dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['metrics' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($metrics)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal097491bc1de829a06e0f87e08566650e)): ?>
<?php $attributes = $__attributesOriginal097491bc1de829a06e0f87e08566650e; ?>
<?php unset($__attributesOriginal097491bc1de829a06e0f87e08566650e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal097491bc1de829a06e0f87e08566650e)): ?>
<?php $component = $__componentOriginal097491bc1de829a06e0f87e08566650e; ?>
<?php unset($__componentOriginal097491bc1de829a06e0f87e08566650e); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larD31E.blade.php ENDPATH**/ ?>