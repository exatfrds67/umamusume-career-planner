<?php if (isset($component)) { $__componentOriginalce9409fda176ced8c4220431b57f43b6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce9409fda176ced8c4220431b57f43b6 = $attributes; } ?>
<?php $component = App\View\Components\PotentialBadge::resolve(['level' => 7] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('potential-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PotentialBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce9409fda176ced8c4220431b57f43b6)): ?>
<?php $attributes = $__attributesOriginalce9409fda176ced8c4220431b57f43b6; ?>
<?php unset($__attributesOriginalce9409fda176ced8c4220431b57f43b6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce9409fda176ced8c4220431b57f43b6)): ?>
<?php $component = $__componentOriginalce9409fda176ced8c4220431b57f43b6; ?>
<?php unset($__componentOriginalce9409fda176ced8c4220431b57f43b6); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larB791.blade.php ENDPATH**/ ?>