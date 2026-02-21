<?php if (isset($component)) { $__componentOriginalcda656c328d9f3b1b35a13f7eac62cf7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcda656c328d9f3b1b35a13f7eac62cf7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.toggle','data' => ['name' => 'notifications','label' => 'Enable notifications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notifications','label' => 'Enable notifications']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcda656c328d9f3b1b35a13f7eac62cf7)): ?>
<?php $attributes = $__attributesOriginalcda656c328d9f3b1b35a13f7eac62cf7; ?>
<?php unset($__attributesOriginalcda656c328d9f3b1b35a13f7eac62cf7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcda656c328d9f3b1b35a13f7eac62cf7)): ?>
<?php $component = $__componentOriginalcda656c328d9f3b1b35a13f7eac62cf7; ?>
<?php unset($__componentOriginalcda656c328d9f3b1b35a13f7eac62cf7); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/dd1d5fdce7a438837fd676440db98cab.blade.php ENDPATH**/ ?>