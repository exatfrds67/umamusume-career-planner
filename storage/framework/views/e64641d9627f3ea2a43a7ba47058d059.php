<?php if (isset($component)) { $__componentOriginalaee1ea17b6836e335cc4e2cd0804f87c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaee1ea17b6836e335cc4e2cd0804f87c = $attributes; } ?>
<?php $component = App\View\Components\ProgressBar::resolve(['current' => 25,'max' => 100,'showValues' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ProgressBar::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaee1ea17b6836e335cc4e2cd0804f87c)): ?>
<?php $attributes = $__attributesOriginalaee1ea17b6836e335cc4e2cd0804f87c; ?>
<?php unset($__attributesOriginalaee1ea17b6836e335cc4e2cd0804f87c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaee1ea17b6836e335cc4e2cd0804f87c)): ?>
<?php $component = $__componentOriginalaee1ea17b6836e335cc4e2cd0804f87c; ?>
<?php unset($__componentOriginalaee1ea17b6836e335cc4e2cd0804f87c); ?>
<?php endif; ?><?php /**PATH C:\Users\exatf\AppData\Local\Temp/larB60C.blade.php ENDPATH**/ ?>