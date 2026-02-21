

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'data' => [],
    'stats' => ['speed', 'stamina', 'power', 'guts', 'wit'],
    'height' => '300px',
    'showLegend' => true,
    'interactive' => true,
    'phases' => true,
    'careerId' => null,
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
    'data' => [],
    'stats' => ['speed', 'stamina', 'power', 'guts', 'wit'],
    'height' => '300px',
    'showLegend' => true,
    'interactive' => true,
    'phases' => true,
    'careerId' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $chartId = 'stat-progression-' . ($careerId ?? uniqid());

    $statColors = [
        'speed' => ['line' => '#3b82f6', 'fill' => 'rgba(59, 130, 246, 0.1)'],
        'stamina' => ['line' => '#f97316', 'fill' => 'rgba(249, 115, 22, 0.1)'],
        'power' => ['line' => '#ef4444', 'fill' => 'rgba(239, 68, 68, 0.1)'],
        'guts' => ['line' => '#ec4899', 'fill' => 'rgba(236, 72, 153, 0.1)'],
        'wit' => ['line' => '#22c55e', 'fill' => 'rgba(34, 197, 94, 0.1)'],
    ];

    $phaseMarkers = [
        ['turn' => 1, 'label' => 'Junior Start', 'color' => '#3b82f6'],
        ['turn' => 24, 'label' => 'Junior End', 'color' => '#3b82f6'],
        ['turn' => 25, 'label' => 'Classic Start', 'color' => '#a855f7'],
        ['turn' => 48, 'label' => 'Classic End', 'color' => '#a855f7'],
        ['turn' => 49, 'label' => 'Senior Start', 'color' => '#22c55e'],
        ['turn' => 72, 'label' => 'Senior End', 'color' => '#22c55e'],
    ];
?>

<div x-data="statProgressionChart({
    chartId: '<?php echo e($chartId); ?>',
    data: <?php echo e(json_encode($data)); ?>,
    stats: <?php echo e(json_encode($stats)); ?>,
    statColors: <?php echo e(json_encode($statColors)); ?>,
    showLegend: <?php echo e($showLegend ? 'true' : 'false'); ?>,
    interactive: <?php echo e($interactive ? 'true' : 'false'); ?>,
    phases: <?php echo e($phases ? 'true' : 'false'); ?>,
    phaseMarkers: <?php echo e(json_encode($phaseMarkers)); ?>

})"
    <?php echo e($attributes->merge(['class' => 'stat-progression-chart glass-card-inner rounded-lg p-4'])); ?> role="figure"
    aria-label="Stat progression chart showing character development over time">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Stat Progression
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Track your character's growth over time
            </p>
        </div>

        
        <div class="flex flex-wrap gap-2" role="group" aria-label="Toggle stat visibility">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <button type="button" @click="toggleStat('<?php echo e($stat); ?>')"
                    :class="visibleStats.includes('<?php echo e($stat); ?>') ?
                        'bg-opacity-100 ring-2 ring-offset-2' :
                        'bg-opacity-50 hover:bg-opacity-75'"
                    class="px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: <?php echo e($statColors[$stat]['line']); ?>; color: white;"
                    :aria-pressed="visibleStats.includes('<?php echo e($stat); ?>')"
                    aria-label="Toggle <?php echo e(ucfirst($stat)); ?> visibility">
                    <?php echo e(ucfirst($stat)); ?>

                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    
    <div class="relative" style="height: <?php echo e($height); ?>;">
        <canvas id="<?php echo e($chartId); ?>" role="img"
            aria-label="Line chart showing stat progression across career turns"></canvas>

        
        <div x-show="loading"
            class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 rounded-lg">
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Loading chart...</span>
            </div>
        </div>

        
        <div x-show="!loading && (!data || data.length === 0)"
            class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                <p>No progression data available</p>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($phases): ?>
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                <span class="font-medium">Career Phases:</span>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span>Junior (1-24)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                    <span>Classic (25-48)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span>Senior (49-72)</span>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="sr-only">
        <table>
            <caption>Stat progression data table</caption>
            <thead>
                <tr>
                    <th>Turn</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <th><?php echo e(ucfirst($stat)); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($point['turn'] ?? 'N/A'); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <td><?php echo e($point[$stat] ?? 0); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('b8b32135-f05d-4e4a-a1db-187f8ed7c5c1')): $__env->markAsRenderedOnce('b8b32135-f05d-4e4a-a1db-187f8ed7c5c1');
$__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); endif; ?>

<?php if (! $__env->hasRenderedOnce('65fc95da-78d8-41d5-84fd-a31770dadf58')): $__env->markAsRenderedOnce('65fc95da-78d8-41d5-84fd-a31770dadf58'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/analytics/stat-progression-chart.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/analytics/stat-progression-chart.blade.php ENDPATH**/ ?>