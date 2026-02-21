<?php if (isset($component)) { $__componentOriginal2806c0ce2db112d3890605e8d2999011 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2806c0ce2db112d3890605e8d2999011 = $attributes; } ?>
<?php $component = App\View\Components\CharacterProfile::resolve(['image' => '/image.jpg','name' => 'Test'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('character-profile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\CharacterProfile::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div>Custom Content</div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2806c0ce2db112d3890605e8d2999011)): ?>
<?php $attributes = $__attributesOriginal2806c0ce2db112d3890605e8d2999011; ?>
<?php unset($__attributesOriginal2806c0ce2db112d3890605e8d2999011); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2806c0ce2db112d3890605e8d2999011)): ?>
<?php $component = $__componentOriginal2806c0ce2db112d3890605e8d2999011; ?>
<?php unset($__componentOriginal2806c0ce2db112d3890605e8d2999011); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/lar7C02.blade.php ENDPATH**/ ?>