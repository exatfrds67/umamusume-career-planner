<?php if (isset($component)) { $__componentOriginale08b08ebed7295039e76389dab2f0e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale08b08ebed7295039e76389dab2f0e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.text-input','data' => ['name' => 'email','error' => 'Required']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','error' => 'Required']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale08b08ebed7295039e76389dab2f0e3a)): ?>
<?php $attributes = $__attributesOriginale08b08ebed7295039e76389dab2f0e3a; ?>
<?php unset($__attributesOriginale08b08ebed7295039e76389dab2f0e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale08b08ebed7295039e76389dab2f0e3a)): ?>
<?php $component = $__componentOriginale08b08ebed7295039e76389dab2f0e3a; ?>
<?php unset($__componentOriginale08b08ebed7295039e76389dab2f0e3a); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/058702a610d307563a65aea216b41e12.blade.php ENDPATH**/ ?>