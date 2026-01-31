<?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => 75,'label' => 'Test','trend' => $trend]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 75,'label' => 'Test','trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trend)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar30DA.blade.php ENDPATH**/ ?>