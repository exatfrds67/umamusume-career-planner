{{--
    Performance Dashboard Component
    
    Displays key performance metrics in a dashboard layout.
    Shows efficiency ratings, success rates, and performance trends.
    
    Requirements: 15.4, 25.3 (Task 5.2.3)
    WCAG 2.2 AA Compliant
    
    @props
    - metrics: array - Performance metrics data
    - showTrend: bool - Show trend indicators (default: true)
    - compact: bool - Use compact layout (default: false)
--}}

@props([
    'metrics' => [],
    'showTrend' => true,
    'compact' => false,
])

@php
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
        default => 'text-neutral-600 dark:text-neutral-400',
    };
@endphp

<div {{ $attributes->merge(['class' => 'performance-dashboard']) }} role="region" aria-label="Performance Dashboard">
    {{-- Dashboard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">
                Performance Overview
            </h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Key metrics and performance indicators
            </p>
        </div>

        @if ($showTrend && isset($trend['trend']))
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-neutral-100 dark:bg-neutral-800">
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Trend:</span>
                <span class="{{ $trendColor }} flex items-center gap-1">
                    @if ($trend['trend'] === 'improving')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    @elseif($trend['trend'] === 'declining')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    @endif
                    <span class="text-sm font-semibold capitalize">{{ $trend['trend'] }}</span>
                    @if (isset($trend['improvement_rate']) && $trend['improvement_rate'] != 0)
                        <span
                            class="text-xs">({{ $trend['improvement_rate'] > 0 ? '+' : '' }}{{ $trend['improvement_rate'] }}%)</span>
                    @endif
                </span>
            </div>
        @endif
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-2 {{ $compact ? 'md:grid-cols-3 lg:grid-cols-6' : 'md:grid-cols-3' }} gap-4">
        {{-- Overall Efficiency --}}
        <x-analytics.metric-card :value="$defaultMetrics['overall_efficiency']['value']" label="Overall Efficiency" unit="%" icon="chart-bar"
            :color="$defaultMetrics['overall_efficiency']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['overall_efficiency']['value'] >= 50
                    ? 'warning'
                    : 'error')" :compact="$compact" />

        {{-- Success Rate --}}
        <x-analytics.metric-card :value="$defaultMetrics['success_rate']['value']" label="Success Rate" unit="%" icon="check-circle"
            :color="$defaultMetrics['success_rate']['value'] >= 70
                ? 'success'
                : ($defaultMetrics['success_rate']['value'] >= 50
                    ? 'warning'
                    : 'error')" :compact="$compact" />

        {{-- Completion Rate --}}
        <x-analytics.metric-card :value="$defaultMetrics['completion_rate']['value']" label="Completion Rate" unit="%" icon="flag"
            :color="$defaultMetrics['completion_rate']['value'] >= 80
                ? 'success'
                : ($defaultMetrics['completion_rate']['value'] >= 60
                    ? 'warning'
                    : 'error')" :compact="$compact" />

        {{-- Average Final Grade --}}
        <x-analytics.metric-card :value="$defaultMetrics['average_final_grade']['value']" label="Avg Final Grade" unit="" icon="star" color="primary"
            :compact="$compact" />

        {{-- Total Careers --}}
        <x-analytics.metric-card :value="$defaultMetrics['total_careers']['value']" label="Total Careers" unit="" icon="collection"
            color="secondary" :compact="$compact" />

        {{-- Completed Careers --}}
        <x-analytics.metric-card :value="$defaultMetrics['completed_careers']['value']" label="Completed" unit="" icon="badge-check" color="success"
            :compact="$compact" />
    </div>

    {{-- Scenario Breakdown --}}
    @if (isset($metrics['metrics_by_scenario']) && !empty($metrics['metrics_by_scenario']))
        <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                Performance by Scenario
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($metrics['metrics_by_scenario'] as $scenario => $data)
                    <div class="glass-card-inner rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-neutral-900 dark:text-white capitalize">
                                {{ str_replace('_', ' ', $scenario) }}
                            </h4>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $data['count'] ?? 0 }} careers
                            </span>
                        </div>

                        <div class="space-y-3">
                            {{-- Success Rate Bar --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-400">Success Rate</span>
                                    <span
                                        class="font-medium text-neutral-900 dark:text-white">{{ $data['success_rate'] ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500 {{ ($data['success_rate'] ?? 0) >= 70 ? 'bg-green-500' : (($data['success_rate'] ?? 0) >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                        style="width: {{ min(100, $data['success_rate'] ?? 0) }}%" role="progressbar"
                                        aria-valuenow="{{ $data['success_rate'] ?? 0 }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            {{-- Efficiency Bar --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-400">Avg Efficiency</span>
                                    <span
                                        class="font-medium text-neutral-900 dark:text-white">{{ $data['avg_efficiency'] ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500 bg-blue-500"
                                        style="width: {{ min(100, $data['avg_efficiency'] ?? 0) }}%" role="progressbar"
                                        aria-valuenow="{{ $data['avg_efficiency'] ?? 0 }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            {{-- Completed Count --}}
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-400">Completed</span>
                                <span class="font-medium text-neutral-900 dark:text-white">
                                    {{ $data['completed'] ?? 0 }} / {{ $data['count'] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent Performance --}}
    @if (isset($trend['recent_performance']) && !empty($trend['recent_performance']))
        <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                Recent Performance
            </h3>
            <div class="flex items-end gap-2 h-24">
                @foreach ($trend['recent_performance'] as $index => $perf)
                    @php
                        $height = min(100, max(10, $perf['efficiency'] ?? 0));
                        $color =
                            ($perf['efficiency'] ?? 0) >= 70
                                ? 'bg-green-500'
                                : (($perf['efficiency'] ?? 0) >= 50
                                    ? 'bg-yellow-500'
                                    : 'bg-red-500');
                    @endphp
                    <div class="flex-1 {{ $color }} rounded-t transition-all duration-300 hover:opacity-80"
                        style="height: {{ $height }}%"
                        title="Career #{{ $perf['career_id'] ?? $index + 1 }}: {{ $perf['efficiency'] ?? 0 }}% efficiency"
                        role="img"
                        aria-label="Career {{ $perf['career_id'] ?? $index + 1 }} efficiency: {{ $perf['efficiency'] ?? 0 }}%">
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                <span>Oldest</span>
                <span>Most Recent</span>
            </div>
        </div>
    @endif
</div>
