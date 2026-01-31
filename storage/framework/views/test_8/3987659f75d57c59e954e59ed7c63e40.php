

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
            <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    
    <?php if($phases): ?>
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
    <?php endif; ?>

    
    <div class="sr-only">
        <table>
            <caption>Stat progression data table</caption>
            <thead>
                <tr>
                    <th>Turn</th>
                    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e(ucfirst($stat)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($point['turn'] ?? 'N/A'); ?></td>
                        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e($point[$stat] ?? 0); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('a5c7790f-01a0-4713-a938-571766179539')): $__env->markAsRenderedOnce('a5c7790f-01a0-4713-a938-571766179539');
$__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('statProgressionChart', (config) => ({
                chart: null,
                loading: true,
                data: config.data || [],
                stats: config.stats || [],
                statColors: config.statColors || {},
                visibleStats: [...(config.stats || [])],
                showLegend: config.showLegend,
                interactive: config.interactive,
                phases: config.phases,
                phaseMarkers: config.phaseMarkers || [],

                init() {
                    this.$nextTick(() => {
                        this.createChart();
                        this.loading = false;
                    });

                    // Watch for theme changes
                    window.addEventListener('theme-changed', () => {
                        this.updateChartTheme();
                    });
                },

                createChart() {
                    const ctx = document.getElementById(config.chartId);
                    if (!ctx) return;

                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                    const textColor = isDark ? '#e5e7eb' : '#374151';

                    // Prepare datasets
                    const datasets = this.stats.map(stat => ({
                        label: stat.charAt(0).toUpperCase() + stat.slice(1),
                        data: this.data.map(d => d[stat] || 0),
                        borderColor: this.statColors[stat]?.line || '#6b7280',
                        backgroundColor: this.statColors[stat]?.fill ||
                            'rgba(107, 114, 128, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        hidden: !this.visibleStats.includes(stat),
                    }));

                    // Prepare labels (turns)
                    const labels = this.data.map(d => `Turn ${d.turn || d.turn_number || 0}`);

                    // Phase annotations
                    const annotations = this.phases ? this.createPhaseAnnotations(isDark) : {};

                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: this.showLegend,
                                    position: 'bottom',
                                    labels: {
                                        color: textColor,
                                        usePointStyle: true,
                                        padding: 20,
                                    },
                                },
                                tooltip: {
                                    enabled: this.interactive,
                                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                    titleColor: isDark ? '#ffffff' : '#111827',
                                    bodyColor: isDark ? '#e5e7eb' : '#374151',
                                    borderColor: isDark ? '#374151' : '#e5e7eb',
                                    borderWidth: 1,
                                    padding: 12,
                                    displayColors: true,
                                    callbacks: {
                                        title: (items) => items[0]?.label || '',
                                        label: (context) => {
                                            return `${context.dataset.label}: ${context.parsed.y}`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: gridColor
                                    },
                                    ticks: {
                                        color: textColor
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    max: 1200,
                                    grid: {
                                        color: gridColor
                                    },
                                    ticks: {
                                        color: textColor,
                                        stepSize: 200,
                                    },
                                },
                            },
                        },
                    });
                },

                createPhaseAnnotations(isDark) {
                    return {
                        annotations: {
                            juniorPhase: {
                                type: 'box',
                                xMin: 0,
                                xMax: 23,
                                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                                borderWidth: 0,
                            },
                            classicPhase: {
                                type: 'box',
                                xMin: 24,
                                xMax: 47,
                                backgroundColor: 'rgba(168, 85, 247, 0.05)',
                                borderWidth: 0,
                            },
                            seniorPhase: {
                                type: 'box',
                                xMin: 48,
                                xMax: 71,
                                backgroundColor: 'rgba(34, 197, 94, 0.05)',
                                borderWidth: 0,
                            },
                        },
                    };
                },

                toggleStat(stat) {
                    const index = this.visibleStats.indexOf(stat);
                    if (index > -1) {
                        this.visibleStats.splice(index, 1);
                    } else {
                        this.visibleStats.push(stat);
                    }

                    // Update chart visibility
                    if (this.chart) {
                        const datasetIndex = this.stats.indexOf(stat);
                        if (datasetIndex > -1) {
                            this.chart.setDatasetVisibility(datasetIndex, this.visibleStats.includes(
                                stat));
                            this.chart.update();
                        }
                    }
                },

                updateChartTheme() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.createChart();
                    }
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                },
            }));
        });
    </script>
<?php $__env->stopPush(); endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/analytics/stat-progression-chart.blade.php ENDPATH**/ ?>