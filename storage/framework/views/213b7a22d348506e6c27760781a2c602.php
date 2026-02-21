

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'metrics' => [],
    'showTrend' => true,
    'compact' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'metrics' => [],
    'showTrend' => true,
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $defaultMetrics = [
        'overall_efficiency' => ['value' => 0, 'label' => 'Overall Efficiency', 'unit' => '%', 'icon' => 'chart-bar'],
        'success_rate' => ['value' => 0, 'label' => 'Success Rate', 'unit' => '%', 'icon' => 'check-circle'],
        'completion_rate' => ['value' => 0, 'label' => 'Completion Rate', 'unit' => '%', 'icon' => 'flag'],
        'average_final_grade' => ['value' => 'N/A', 'label' => 'Avg Final Grade', 'unit' => '', 'icon' => 'star'],
        'total_careers' => ['value' => 0, 'label' => 'Total Careers', 'unit' => '', 'icon' => 'collection'],
        'completed_careers' => ['value' => 0, 'label' => 'Completed', 'unit' => '', 'icon' => 'badge-check'],
    ];

    // Merge with provided metrics
    foreach ($defaultMetrics as $key => $default) {
        if (isset($metrics[$key])) {
            $defaultMetrics[$key]['value'] = $metrics[$key];
        }
    }

    $trend = $metrics['performance_trend'] ?? ['trend' => 'stable', 'improvement_rate' => 0];
    $trendIcon = match ($trend['trend'] ?? 'stable') {
        'improving' => 'trending-up',
        'declining' => 'trending-down',
        default => 'minus',
    };
    $trendColor = match ($trend['trend'] ?? 'stable') {
        'improving' => 'text-green-600 dark:text-green-400',
        'declining' => 'text-red-600 dark:text-red-400',
        default => 'text-gray-600 dark:text-gray-400',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'performance-dashboard'])); ?> role="region" aria-label="Performance Dashboard">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Performance Overview
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Key metrics and performance indicators
            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrend && isset($trend['trend'])): ?>
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Trend:</span>
                <span class="<?php echo e($trendColor); ?> flex items-center gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trend['trend'] === 'improving'): ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    <?php elseif($trend['trend'] === 'declining'): ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-sm font-semibold capitalize"><?php echo e($trend['trend']); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($trend['improvement_rate']) && $trend['improvement_rate'] != 0): ?>
                        <span
                            class="text-xs">(<?php echo e($trend['improvement_rate'] > 0 ? '+' : ''); ?><?php echo e($trend['improvement_rate']); ?>%)</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="grid grid-cols-2 <?php echo e($compact ? 'md:grid-cols-3 lg:grid-cols-6' : 'md:grid-cols-3'); ?> gap-4">
        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['overall_efficiency']['value'],'label' => 'Overall Efficiency','unit' => '%','icon' => 'chart-bar','color' => $defaultMetrics['overall_efficiency']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['overall_efficiency']['value'] >= 50
                    ? 'warning'
                    : 'error'),'compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['overall_efficiency']['value']),'label' => 'Overall Efficiency','unit' => '%','icon' => 'chart-bar','color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['overall_efficiency']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['overall_efficiency']['value'] >= 50
                    ? 'warning'
                    : 'error')),'compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['success_rate']['value'],'label' => 'Success Rate','unit' => '%','icon' => 'check-circle','color' => $defaultMetrics['success_rate']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['success_rate']['value'] >= 50
                    ? 'warning'
                    : 'error'),'compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['success_rate']['value']),'label' => 'Success Rate','unit' => '%','icon' => 'check-circle','color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['success_rate']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['success_rate']['value'] >= 50
                    ? 'warning'
                    : 'error')),'compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['completion_rate']['value'],'label' => 'Completion Rate','unit' => '%','icon' => 'flag','color' => $defaultMetrics['completion_rate']['value'] >= 80
                ? 'success'
                : ($defaultMetrics['completion_rate']['value'] >= 60
                    ? 'warning'
                    : 'error'),'compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['completion_rate']['value']),'label' => 'Completion Rate','unit' => '%','icon' => 'flag','color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['completion_rate']['value'] >= 80
                ? 'success'
                : ($defaultMetrics['completion_rate']['value'] >= 60
                    ? 'warning'
                    : 'error')),'compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['average_final_grade']['value'],'label' => 'Avg Final Grade','unit' => '','icon' => 'star','color' => 'primary','compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['average_final_grade']['value']),'label' => 'Avg Final Grade','unit' => '','icon' => 'star','color' => 'primary','compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['total_careers']['value'],'label' => 'Total Careers','unit' => '','icon' => 'collection','color' => 'secondary','compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['total_careers']['value']),'label' => 'Total Careers','unit' => '','icon' => 'collection','color' => 'secondary','compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.metric-card','data' => ['value' => $defaultMetrics['completed_careers']['value'],'label' => 'Completed','unit' => '','icon' => 'badge-check','color' => 'success','compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($defaultMetrics['completed_careers']['value']),'label' => 'Completed','unit' => '','icon' => 'badge-check','color' => 'success','compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $attributes = $__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__attributesOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372)): ?>
<?php $component = $__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372; ?>
<?php unset($__componentOriginalfaa5013d1fcf499cd346fe7b6eef9372); ?>
<?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($metrics['metrics_by_scenario']) && !empty($metrics['metrics_by_scenario'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Performance by Scenario
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $metrics['metrics_by_scenario']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scenario => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="glass-card-inner rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900 dark:text-white capitalize">
                                <?php echo e(str_replace('_', ' ', $scenario)); ?>

                            </h4>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e($data['count'] ?? 0); ?> careers
                            </span>
                        </div>

                        <div class="space-y-3">
                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Success Rate</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"><?php echo e($data['success_rate'] ?? 0); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500 <?php echo e(($data['success_rate'] ?? 0) >= 70 ? 'bg-green-500' : (($data['success_rate'] ?? 0) >= 50 ? 'bg-yellow-500' : 'bg-red-500')); ?>"
                                        style="width: <?php echo e(min(100, $data['success_rate'] ?? 0)); ?>%" role="progressbar"
                                        aria-valuenow="<?php echo e($data['success_rate'] ?? 0); ?>" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Avg Efficiency</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"><?php echo e($data['avg_efficiency'] ?? 0); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500 bg-blue-500"
                                        style="width: <?php echo e(min(100, $data['avg_efficiency'] ?? 0)); ?>%" role="progressbar"
                                        aria-valuenow="<?php echo e($data['avg_efficiency'] ?? 0); ?>" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Completed</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    <?php echo e($data['completed'] ?? 0); ?> / <?php echo e($data['count'] ?? 0); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($trend['recent_performance']) && !empty($trend['recent_performance'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Recent Performance
            </h3>
            <div class="flex items-end gap-2 h-24">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trend['recent_performance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $perf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php
                        $height = min(100, max(10, $perf['efficiency'] ?? 0));
                        $color =
                            ($perf['efficiency'] ?? 0) >= 70
                                ? 'bg-green-500'
                                : (($perf['efficiency'] ?? 0) >= 50
                                    ? 'bg-yellow-500'
                                    : 'bg-red-500');
                    ?>
                    <div class="flex-1 <?php echo e($color); ?> rounded-t transition-all duration-300 hover:opacity-80"
                        style="height: <?php echo e($height); ?>%"
                        title="Career #<?php echo e($perf['career_id'] ?? $index + 1); ?>: <?php echo e($perf['efficiency'] ?? 0); ?>% efficiency"
                        role="img"
                        aria-label="Career <?php echo e($perf['career_id'] ?? $index + 1); ?> efficiency: <?php echo e($perf['efficiency'] ?? 0); ?>%">
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                <span>Oldest</span>
                <span>Most Recent</span>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/analytics/performance-dashboard.blade.php ENDPATH**/ ?>