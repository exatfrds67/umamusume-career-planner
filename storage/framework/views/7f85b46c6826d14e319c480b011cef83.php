
            <?php if (isset($component)) { $__componentOriginal86c599c6365f7d91b21c041b5df22cf9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal86c599c6365f7d91b21c041b5df22cf9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wizard-layout','data' => ['steps' => ['Info'],'currentStep' => 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wizard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Info']),'current-step' => 0]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('header', null, []); ?> Custom Header Title <?php $__env->endSlot(); ?>
                Content
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal86c599c6365f7d91b21c041b5df22cf9)): ?>
<?php $attributes = $__attributesOriginal86c599c6365f7d91b21c041b5df22cf9; ?>
<?php unset($__attributesOriginal86c599c6365f7d91b21c041b5df22cf9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal86c599c6365f7d91b21c041b5df22cf9)): ?>
<?php $component = $__componentOriginal86c599c6365f7d91b21c041b5df22cf9; ?>
<?php unset($__componentOriginal86c599c6365f7d91b21c041b5df22cf9); ?>
<?php endif; ?>
        <?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/b1a2493b81f1c60ed5e848da8f04004c.blade.php ENDPATH**/ ?>