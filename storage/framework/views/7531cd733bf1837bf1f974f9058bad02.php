
            <?php if (isset($component)) { $__componentOriginalb0b35e0b561c401a0a35ce9e3aa58139 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb0b35e0b561c401a0a35ce9e3aa58139 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select-dropdown','data' => ['name' => 'stat','options' => [['value' => 'speed', 'label' => 'Speed'], ['value' => 'stamina', 'label' => 'Stamina']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'stat','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['value' => 'speed', 'label' => 'Speed'], ['value' => 'stamina', 'label' => 'Stamina']])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb0b35e0b561c401a0a35ce9e3aa58139)): ?>
<?php $attributes = $__attributesOriginalb0b35e0b561c401a0a35ce9e3aa58139; ?>
<?php unset($__attributesOriginalb0b35e0b561c401a0a35ce9e3aa58139); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb0b35e0b561c401a0a35ce9e3aa58139)): ?>
<?php $component = $__componentOriginalb0b35e0b561c401a0a35ce9e3aa58139; ?>
<?php unset($__componentOriginalb0b35e0b561c401a0a35ce9e3aa58139); ?>
<?php endif; ?>
        <?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/7fa3a5d6fcac1a116c4dcfacbd2cec49.blade.php ENDPATH**/ ?>