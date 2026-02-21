
            <?php if (isset($component)) { $__componentOriginal86c599c6365f7d91b21c041b5df22cf9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal86c599c6365f7d91b21c041b5df22cf9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wizard-layout','data' => ['steps' => ['Done', 'Current', 'Future'],'currentStep' => 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wizard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Done', 'Current', 'Future']),'current-step' => 1]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
        <?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/884c73b6f81bbd832b97569a7d5998c0.blade.php ENDPATH**/ ?>