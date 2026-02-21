<?php if (isset($component)) { $__componentOriginal78752d4664868f163d0460b49dc44fcb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78752d4664868f163d0460b49dc44fcb = $attributes; } ?>
<?php $component = App\View\Components\CharacterPortrait::resolve(['image' => '/test.jpg','alt' => 'Special Week'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('character-portrait'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\CharacterPortrait::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78752d4664868f163d0460b49dc44fcb)): ?>
<?php $attributes = $__attributesOriginal78752d4664868f163d0460b49dc44fcb; ?>
<?php unset($__attributesOriginal78752d4664868f163d0460b49dc44fcb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78752d4664868f163d0460b49dc44fcb)): ?>
<?php $component = $__componentOriginal78752d4664868f163d0460b49dc44fcb; ?>
<?php unset($__componentOriginal78752d4664868f163d0460b49dc44fcb); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larE84F.blade.php ENDPATH**/ ?>