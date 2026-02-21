<?php if (isset($component)) { $__componentOriginal2fc2b3debcdc6a1746a205def1fe04f4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2fc2b3debcdc6a1746a205def1fe04f4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-management.error-display','data' => ['errors' => ['Error 1', 'Error 2'],'title' => 'Import Errors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-management.error-display'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['errors' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Error 1', 'Error 2']),'title' => 'Import Errors']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2fc2b3debcdc6a1746a205def1fe04f4)): ?>
<?php $attributes = $__attributesOriginal2fc2b3debcdc6a1746a205def1fe04f4; ?>
<?php unset($__attributesOriginal2fc2b3debcdc6a1746a205def1fe04f4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2fc2b3debcdc6a1746a205def1fe04f4)): ?>
<?php $component = $__componentOriginal2fc2b3debcdc6a1746a205def1fe04f4; ?>
<?php unset($__componentOriginal2fc2b3debcdc6a1746a205def1fe04f4); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larF5B3.blade.php ENDPATH**/ ?>