
            <?php if (isset($component)) { $__componentOriginal86c599c6365f7d91b21c041b5df22cf9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal86c599c6365f7d91b21c041b5df22cf9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wizard-layout','data' => ['steps' => ['Step 1', 'Step 2', 'Step 3'],'currentStep' => 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wizard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Step 1', 'Step 2', 'Step 3']),'current-step' => 0]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Form content here
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
        <?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/b8cdb0321a4888c38b0a2d92e51a49a7.blade.php ENDPATH**/ ?>