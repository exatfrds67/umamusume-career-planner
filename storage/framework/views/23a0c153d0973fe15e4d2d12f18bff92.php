<?php if (isset($component)) { $__componentOriginal5ca8221419a9c3252452bc24f23d35fe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ca8221419a9c3252452bc24f23d35fe = $attributes; } ?>
<?php $component = App\View\Components\AptitudeDisplay::resolve(['type' => 'turf','grade' => 'A'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('aptitude-display'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AptitudeDisplay::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5ca8221419a9c3252452bc24f23d35fe)): ?>
<?php $attributes = $__attributesOriginal5ca8221419a9c3252452bc24f23d35fe; ?>
<?php unset($__attributesOriginal5ca8221419a9c3252452bc24f23d35fe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5ca8221419a9c3252452bc24f23d35fe)): ?>
<?php $component = $__componentOriginal5ca8221419a9c3252452bc24f23d35fe; ?>
<?php unset($__componentOriginal5ca8221419a9c3252452bc24f23d35fe); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larDEBA.blade.php ENDPATH**/ ?>