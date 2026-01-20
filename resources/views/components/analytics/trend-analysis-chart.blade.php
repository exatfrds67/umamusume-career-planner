{{--
    Trend Analysis Chart Component
    
    Displays trend analysis with confidence intervals for performance metrics.
    Shows historical trends, predictions, and statistical confidence bands.
    
    Requirements: 15.4, 25.3 (Task 5.2.3)
    WCAG 2.2 AA Compliant
    
    @props
    - data: array - Trend data with values and confidence intervals
    - metric: string - Metric being analyzed (efficiency, win_rate, etc.)
    - showConfidence: bool - Show confidence interval bands (default: true)
    - showPrediction: bool - Show future predictions (default: true)
    - height: string - Chart height (default: '300px')
--}}

@props([
    'data' => [],
    'metric' => 'efficiency',
    'showConfidence' => true,
    'showPrediction' => true,
    'height' => '300px',
    'title' => 'Performance Trend Analysis',
])

@php
    $chartId = 'trend-analysis-' . uniqid();

    $metricConfig = [
        'efficiency' => [
            'label' => 'Training Efficiency',
            'unit' => '%',
            'color' => '#3b82f6',
            'max' => 100,
        ],
        'win_rate' => [
            'label' => 'Race Win Rate',
            'unit' => '%',
            'color' => '#22c55e',
            'max' => 100,
        ],
        'stat_gain' => [
            'label' => 'Stat Gain per Turn',
            'unit' => 'pts',
            'color' => '#a855f7',
            'max' => 50,
        ],
        'sp_efficiency' => [
            'label' => 'SP Efficiency',
            'unit' => 'SP',
            'color' => '#f59e0b',
            'max' => 100,
        ],
    ];

    $config = $metricConfig[$metric] ?? $metricConfig['efficiency'];
@endphp

<div x-data="trendAnalysisChart({
    chartId: '{{ $chartId }}',
    data: {{ json_encode($data) }},
    metric: '{{ $metric }}',
    config: {{ json_encode($config) }},
    showConfidence: {{ $showConfidence ? 'true' : 'false' }},
    showPrediction: {{ $showPrediction ? 'true' : 'false' }}
})"
    {{ $attributes->merge(['class' => 'trend-analysis-chart glass-card-inner rounded-lg p-4']) }} role="figure"
    aria-label="Trend analysis chart showing {{ $config['label'] }} over time with confidence intervals">
    {{-- Chart Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $title }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $config['label'] }} trend with statistical analysis
            </p>
        </div>

        {{-- Controls --}}
        <div class="flex items-center gap-3">
            @if ($showConfidence)
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="displayConfidence" @change="updateChart()"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span>Confidence Interval</span>
                </label>
            @endif

            @if ($showPrediction)
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="displayPrediction" @change="updateChart()"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span>Prediction</span>
                </label>
            @endif
        </div>
    </div>

    {{-- Statistics Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white"
                x-text="stats.current + '{{ $config['unit'] }}'"></p>
        </div>
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Average</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white"
                x-text="stats.average + '{{ $config['unit'] }}'"></p>
        </div>
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Trend</p>
            <p class="text-lg font-bold"
                :class="stats.trendDirection === 'up' ? 'text-green-600 dark:text-green-400' : (stats
                    .trendDirection === 'down' ? 'text-red-600 dark:text-red-400' :
                    'text-gray-600 dark:text-gray-400')">
                <span x-show="stats.trendDirection === 'up'">↑</span>
                <span x-show="stats.trendDirection === 'down'">↓</span>
                <span x-show="stats.trendDirection === 'stable'">→</span>
                <span x-text="Math.abs(stats.trendValue) + '{{ $config['unit'] }}'"></span>
            </p>
        </div>
        <div class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Confidence</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="stats.confidence + '%'"></p>
        </div>
    </div>

    {{-- Chart Container --}}
    <div class="relative" style="height: {{ $height }};">
        <canvas id="{{ $chartId }}" role="img"
            aria-label="Line chart showing {{ $config['label'] }} trend over time"></canvas>

        {{-- Loading State --}}
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
                <span>Analyzing trends...</span>
            </div>
        </div>

        {{-- No Data State --}}
        <div x-show="!loading && (!data || data.length === 0)"
            class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
                <p>Insufficient data for trend analysis</p>
                <p class="text-sm mt-1">Complete more careers to see trends</p>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <span class="w-4 h-0.5" style="background-color: {{ $config['color'] }};"></span>
                <span>Actual {{ $config['label'] }}</span>
            </div>
            @if ($showConfidence)
                <div class="flex items-center gap-2" x-show="displayConfidence">
                    <span class="w-4 h-3 rounded opacity-30" style="background-color: {{ $config['color'] }};"></span>
                    <span>95% Confidence Interval</span>
                </div>
            @endif
            @if ($showPrediction)
                <div class="flex items-center gap-2" x-show="displayPrediction">
                    <span class="w-4 h-0.5 border-t-2 border-dashed"
                        style="border-color: {{ $config['color'] }};"></span>
                    <span>Predicted Trend</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Insights Panel --}}
    <div x-show="insights.length > 0"
        class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Trend Insights
        </h4>
        <ul class="space-y-1">
            <template x-for="insight in insights" :key="insight">
                <li class="text-sm text-blue-700 dark:text-blue-400 flex items-start gap-2">
                    <span class="text-blue-500 mt-1">•</span>
                    <span x-text="insight"></span>
                </li>
            </template>
        </ul>
    </div>

    {{-- Accessible Data Table (Screen Reader) --}}
    <div class="sr-only">
        <table>
            <caption>{{ $config['label'] }} trend data</caption>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Value</th>
                    <th>Lower Bound</th>
                    <th>Upper Bound</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $point)
                    <tr>
                        <td>{{ $point['label'] ?? 'N/A' }}</td>
                        <td>{{ $point['value'] ?? 0 }}{{ $config['unit'] }}</td>
                        <td>{{ $point['lower'] ?? 0 }}{{ $config['unit'] }}</td>
                        <td>{{ $point['upper'] ?? 0 }}{{ $config['unit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('trendAnalysisChart', (config) => ({
                chart: null,
                loading: true,
                data: config.data || [],
                metric: config.metric,
                metricConfig: config.config,
                showConfidence: config.showConfidence,
                showPrediction: config.showPrediction,
                displayConfidence: config.showConfidence,
                displayPrediction: config.showPrediction,

                stats: {
                    current: 0,
                    average: 0,
                    trendDirection: 'stable',
                    trendValue: 0,
                    confidence: 95,
                },

                insights: [],

                init() {
                    this.$nextTick(() => {
                        this.calculateStats();
                        this.generateInsights();
                        this.createChart();
                        this.loading = false;
                    });

                    // Watch for theme changes
                    window.addEventListener('theme-changed', () => {
                        this.updateChartTheme();
                    });
                },

                calculateStats() {
                    if (!this.data || this.data.length === 0) return;

                    const values = this.data.map(d => d.value || 0);

                    // Current value (last data point)
                    this.stats.current = Math.round(values[values.length - 1] * 10) / 10;

                    // Average
                    this.stats.average = Math.round((values.reduce((a, b) => a + b, 0) / values
                        .length) * 10) / 10;

                    // Trend calculation (linear regression slope)
                    if (values.length >= 2) {
                        const n = values.length;
                        const sumX = (n * (n - 1)) / 2;
                        const sumY = values.reduce((a, b) => a + b, 0);
                        const sumXY = values.reduce((sum, y, x) => sum + x * y, 0);
                        const sumX2 = (n * (n - 1) * (2 * n - 1)) / 6;

                        const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);

                        this.stats.trendValue = Math.round(slope * 10) / 10;
                        this.stats.trendDirection = slope > 0.5 ? 'up' : (slope < -0.5 ? 'down' :
                            'stable');
                    }
                },

                generateInsights() {
                    this.insights = [];

                    if (!this.data || this.data.length < 3) {
                        this.insights.push('Need more data points for detailed analysis.');
                        return;
                    }

                    // Trend insight
                    if (this.stats.trendDirection === 'up') {
                        this.insights.push(
                            `${this.metricConfig.label} is improving by approximately ${Math.abs(this.stats.trendValue)}${this.metricConfig.unit} per career.`
                            );
                    } else if (this.stats.trendDirection === 'down') {
                        this.insights.push(
                            `${this.metricConfig.label} is declining by approximately ${Math.abs(this.stats.trendValue)}${this.metricConfig.unit} per career.`
                            );
                    } else {
                        this.insights.push(
                            `${this.metricConfig.label} has remained stable across recent careers.`);
                    }

                    // Performance insight
                    if (this.stats.current > this.stats.average) {
                        const diff = Math.round((this.stats.current - this.stats.average) * 10) / 10;
                        this.insights.push(
                            `Current performance is ${diff}${this.metricConfig.unit} above your average.`
                            );
                    } else if (this.stats.current < this.stats.average) {
                        const diff = Math.round((this.stats.average - this.stats.current) * 10) / 10;
                        this.insights.push(
                            `Current performance is ${diff}${this.metricConfig.unit} below your average.`
                            );
                    }

                    // Consistency insight
                    const values = this.data.map(d => d.value || 0);
                    const variance = this.calculateVariance(values);
                    if (variance < 25) {
                        this.insights.push('Your performance has been very consistent.');
                    } else if (variance > 100) {
                        this.insights.push(
                            'Performance varies significantly between careers. Consider identifying what factors lead to better results.'
                            );
                    }
                },

                calculateVariance(values) {
                    if (values.length < 2) return 0;
                    const mean = values.reduce((a, b) => a + b, 0) / values.length;
                    return values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / values
                    .length;
                },

                createChart() {
                    const ctx = document.getElementById(config.chartId);
                    if (!ctx) return;

                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                    const textColor = isDark ? '#e5e7eb' : '#374151';

                    // Prepare labels
                    const labels = this.data.map((d, i) => d.label || `Career ${i + 1}`);

                    // Main data line
                    const mainData = this.data.map(d => d.value || 0);

                    // Confidence interval data
                    const upperBound = this.data.map(d => d.upper || d.value || 0);
                    const lowerBound = this.data.map(d => d.lower || d.value || 0);

                    // Prediction data (extend trend)
                    let predictionLabels = [];
                    let predictionData = [];

                    if (this.showPrediction && this.data.length >= 3) {
                        const lastValue = mainData[mainData.length - 1];
                        const slope = this.stats.trendValue;

                        for (let i = 1; i <= 3; i++) {
                            predictionLabels.push(`Predicted ${i}`);
                            predictionData.push(Math.max(0, Math.min(this.metricConfig.max, lastValue +
                                slope * i)));
                        }
                    }

                    const datasets = [
                        // Main trend line
                        {
                            label: this.metricConfig.label,
                            data: mainData,
                            borderColor: this.metricConfig.color,
                            backgroundColor: 'transparent',
                            borderWidth: 3,
                            tension: 0.3,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            pointBackgroundColor: this.metricConfig.color,
                            pointBorderColor: isDark ? '#1f2937' : '#ffffff',
                            pointBorderWidth: 2,
                        },
                    ];

                    // Confidence interval (filled area)
                    if (this.displayConfidence) {
                        datasets.unshift({
                            label: 'Upper Bound',
                            data: upperBound,
                            borderColor: 'transparent',
                            backgroundColor: this.metricConfig.color + '20',
                            fill: '+1',
                            tension: 0.3,
                            pointRadius: 0,
                        });

                        datasets.push({
                            label: 'Lower Bound',
                            data: lowerBound,
                            borderColor: 'transparent',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 0,
                        });
                    }

                    // Prediction line
                    if (this.displayPrediction && predictionData.length > 0) {
                        // Extend main data with nulls for prediction
                        const extendedMain = [...mainData, ...Array(predictionData.length).fill(null)];
                        datasets[0].data = extendedMain;

                        // Add prediction dataset
                        const predictionLine = [...Array(mainData.length - 1).fill(null), mainData[
                            mainData.length - 1], ...predictionData];
                        datasets.push({
                            label: 'Prediction',
                            data: predictionLine,
                            borderColor: this.metricConfig.color,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.3,
                            pointRadius: 4,
                            pointStyle: 'triangle',
                            pointBackgroundColor: this.metricConfig.color + '80',
                        });
                    }

                    const allLabels = [...labels, ...predictionLabels];

                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: allLabels,
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
                                    display: false,
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                                    titleColor: isDark ? '#ffffff' : '#111827',
                                    bodyColor: isDark ? '#e5e7eb' : '#374151',
                                    borderColor: isDark ? '#374151' : '#e5e7eb',
                                    borderWidth: 1,
                                    padding: 12,
                                    displayColors: true,
                                    filter: (item) => item.dataset.label !== 'Upper Bound' &&
                                        item.dataset.label !== 'Lower Bound',
                                    callbacks: {
                                        label: (context) => {
                                            if (context.parsed.y === null) return null;
                                            return `${context.dataset.label}: ${context.parsed.y}${this.metricConfig.unit}`;
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
                                    max: this.metricConfig.max,
                                    grid: {
                                        color: gridColor
                                    },
                                    ticks: {
                                        color: textColor,
                                        callback: (value) => value + this.metricConfig.unit,
                                    },
                                },
                            },
                        },
                    });
                },

                updateChart() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.createChart();
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
@endPushOnce
