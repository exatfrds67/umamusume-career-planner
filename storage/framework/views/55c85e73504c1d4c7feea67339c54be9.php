<?php if (isset($component)) { $__componentOriginal7362f64b7b94514516424a1b35bd5def = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7362f64b7b94514516424a1b35bd5def = $attributes; } ?>
<?php $component = App\View\Components\Ai\RecommendationCard::resolve(['recommendation' => $recommendation] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.recommendation-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Ai\RecommendationCard::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7362f64b7b94514516424a1b35bd5def)): ?>
<?php $attributes = $__attributesOriginal7362f64b7b94514516424a1b35bd5def; ?>
<?php unset($__attributesOriginal7362f64b7b94514516424a1b35bd5def); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7362f64b7b94514516424a1b35bd5def)): ?>
<?php $component = $__componentOriginal7362f64b7b94514516424a1b35bd5def; ?>
<?php unset($__componentOriginal7362f64b7b94514516424a1b35bd5def); ?>
<?php endif; ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\storage\framework\views/de4141313f50b549070926b4c65d2870.blade.php ENDPATH**/ ?>