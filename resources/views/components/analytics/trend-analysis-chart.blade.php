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
                    <input type="checkbox" id="display-confidence-toggle" name="display_confidence"
                        x-model="displayConfidence" @change="updateChart()"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span>Confidence Interval</span>
                </label>
            @endif

            @if ($showPrediction)
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                    <input type="checkbox" id="display-prediction-toggle" name="display_prediction"
                        x-model="displayPrediction" @change="updateChart()"
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

{{-- JS extracted to resources/js/components/analytics/trend-analysis-chart.js --}}
@pushOnce('scripts')
    @vite('resources/js/components/analytics/trend-analysis-chart.js')
@endPushOnce
