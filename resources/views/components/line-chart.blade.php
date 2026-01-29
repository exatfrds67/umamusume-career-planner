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
        <canvas 
            id="lineChart_{{ uniqid() }}"
            x-data="lineChart(
                {{ json_encode($data) }},
                {{ json_encode($labels) }},
                {{ json_encode($colors) }},
                {{ $animated ? 'true' : 'false' }},
                {{ $responsive ? 'true' : 'false' }}
            )"
            x-init="init()"
            class="w-full"
            role="img"
            :aria-label="`{{ $title }} chart showing progression over time`"
        ></canvas>
    </div>

    {{-- Data Summary Stats --}}
    <div class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Current</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white" 
                x-text="lastDataPoint">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Average</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white"
                x-text="Math.round(averageValue)">
            </span>
        </div>
        <div class="text-center">
            <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Peak</span>
            <span class="block text-lg font-bold text-gray-900 dark:text-white"
                x-text="maxValue">
            </span>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
window.Alpine && Alpine.data('lineChart', function(rawData, rawLabels, colors, shouldAnimate, shouldRespond) {
    return {
        chart: null,
        data: rawData,
        labels: rawLabels,
        colors: colors || ['#3B82F6'],
        animated: shouldAnimate !== false,
        responsive: shouldRespond !== false,
        
        get lastDataPoint() {
            if (Array.isArray(this.data[0])) {
                return this.data[0][this.data[0].length - 1] || 0;
            }
            return this.data[this.data.length - 1] || 0;
        },
        
        get averageValue() {
            if (Array.isArray(this.data[0])) {
                const flattened = this.data[0];
                return flattened.reduce((a, b) => a + b, 0) / flattened.length;
            }
            return this.data.reduce((a, b) => a + b, 0) / this.data.length;
        },
        
        get maxValue() {
            if (Array.isArray(this.data[0])) {
                return Math.max(...this.data[0]);
            }
            return Math.max(...this.data);
        },
        
        get minValue() {
            if (Array.isArray(this.data[0])) {
                return Math.min(...this.data[0]);
            }
            return Math.min(...this.data);
        },
        
        init() {
            this.$nextTick(() => {
                this.createChart();
            });
        },
        
        createChart() {
            const ctx = this.$el.getContext('2d');
            if (!ctx) return;
            
            const datasets = Array.isArray(this.data[0]) 
                ? this.data.map((dataset, idx) => ({
                    label: `Series ${idx + 1}`,
                    data: dataset,
                    borderColor: this.colors[idx] || '#3B82F6',
                    backgroundColor: this.hexToRgba(this.colors[idx] || '#3B82F6', 0.1),
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: this.colors[idx] || '#3B82F6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    fill: true
                  }))
                : [{
                    label: 'Data',
                    data: this.data,
                    borderColor: this.colors[0] || '#3B82F6',
                    backgroundColor: this.hexToRgba(this.colors[0] || '#3B82F6', 0.1),
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: this.colors[0] || '#3B82F6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    fill: true
                  }];
            
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: datasets
                },
                options: {
                    responsive: this.responsive,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: this.animated ? {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    } : false,
                    plugins: {
                        legend: {
                            display: datasets.length > 1,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            displayColors: true,
                            callbacks: {
                                label: (context) => {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += Math.round(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: document.documentElement.classList.contains('dark') 
                                    ? 'rgba(75, 85, 99, 0.2)' 
                                    : 'rgba(0, 0, 0, 0.05)',
                                drawBorder: true
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280',
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        },
        
        hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        },
        
        updateChart(newData, newLabels) {
            if (this.chart) {
                this.chart.data.labels = newLabels || this.labels;
                this.chart.data.datasets[0].data = newData || this.data;
                this.chart.update('active');
            }
        }
    };
});
</script>
@endpush
