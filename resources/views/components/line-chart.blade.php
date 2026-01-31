{{--
Component: LineChart
Purpose: Trend visualization using Chart.js integration

Props:
  - title (string): Chart title
  - data (array): Data points array
  - labels (array): X-axis labels
  - colors (array): Line colors for multiple datasets
  - height (string): Chart container height (default: h-64)
  - animated (bool): Enable animations (default: true)
  - responsive (bool): Make responsive (default: true)

Usage:
  <x-line-chart 
      title="Stat Progression" 
      :data="$statData" 
      :labels="$months"
      :colors="['#EF4444', '#3B82F6']"
  />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'title' => 'Chart',
    'data' => [],
    'labels' => [],
    'colors' => ['#3B82F6'],
    'height' => 'h-64',
    'animated' => true,
    'responsive' => true,
])

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    {{-- Header --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Trend analysis and progression</p>
    </div>

    {{-- Chart Container --}}
    <div class="{{ $height }} relative">
        <canvas id="lineChart_{{ uniqid() }}" x-data="lineChart(
            {{ json_encode($data) }},
            {{ json_encode($labels) }},
            {{ json_encode($colors) }},
            {{ $animated ? 'true' : 'false' }},
            {{ $responsive ? 'true' : 'false' }}
        )" x-init="init()" class="w-full"
            role="img" :aria-label="`{{ $title }} chart showing progression over time`"></canvas>
    </div>

    {{-- Data Summary Stats --}}
    <div class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Current</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="lastDataPoint">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Average</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="Math.round(averageValue)">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Peak</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" x-text="maxValue">
            </span>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/components/line-chart.js'])
@endonce
