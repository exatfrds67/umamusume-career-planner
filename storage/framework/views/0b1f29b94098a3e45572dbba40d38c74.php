
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

                Content
                 <?php $__env->slot('footer', null, []); ?> 
                    <button>Next</button>
                 <?php $__env->endSlot(); ?>
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
        <?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/231d7f7779f1e2e285b205bd0ef23538.blade.php ENDPATH**/ ?>