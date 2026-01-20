{{--
    Metric Card Component
    
    Displays a single metric with icon, value, and optional trend.
    Used within the Performance Dashboard component.
    
    Requirements: 15.4, 25.3 (Task 5.2.3)
    WCAG 2.2 AA Compliant
    
    @props
    - value: mixed - The metric value
    - label: string - Metric label
    - unit: string - Unit suffix (%, pts, etc.)
    - icon: string - Icon name
    - color: string - Color theme (primary, success, warning, error, secondary)
    - trend: array|null - Trend data with direction and value
    - compact: bool - Use compact layout
--}}

@props([
    'value' => 0,
    'label' => 'Metric',
    'unit' => '',
    'icon' => 'chart-bar',
    'color' => 'primary',
    'trend' => null,
    'compact' => false,
])

@php
    $colorClasses = [
        'primary' => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'value' => 'text-blue-700 dark:text-blue-300',
        ],
        'secondary' => [
            'bg' => 'bg-purple-100 dark:bg-purple-900/30',
            'icon' => 'text-purple-600 dark:text-purple-400',
            'value' => 'text-purple-700 dark:text-purple-300',
        ],
        'success' => [
            'bg' => 'bg-green-100 dark:bg-green-900/30',
            'icon' => 'text-green-600 dark:text-green-400',
            'value' => 'text-green-700 dark:text-green-300',
        ],
        'warning' => [
            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'icon' => 'text-yellow-600 dark:text-yellow-400',
            'value' => 'text-yellow-700 dark:text-yellow-300',
        ],
        'error' => [
            'bg' => 'bg-red-100 dark:bg-red-900/30',
            'icon' => 'text-red-600 dark:text-red-400',
            'value' => 'text-red-700 dark:text-red-300',
        ],
    ];

    $colors = $colorClasses[$color] ?? $colorClasses['primary'];

    $icons = [
        'chart-bar' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
        'check-circle' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'flag' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>',
        'star' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>',
        'collection' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>',
        'badge-check' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>',
        'trending-up' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
        'trending-down' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>',
    ];

    $iconPath = $icons[$icon] ?? $icons['chart-bar'];
@endphp

<div {{ $attributes->merge(['class' => 'glass-card-inner rounded-lg ' . ($compact ? 'p-3' : 'p-4')]) }} role="group"
    aria-label="{{ $label }}">
    <div class="flex items-start {{ $compact ? 'gap-2' : 'gap-3' }}">
        {{-- Icon --}}
        <div class="{{ $colors['bg'] }} {{ $compact ? 'p-2' : 'p-3' }} rounded-lg shrink-0">
            <svg class="{{ $colors['icon'] }} {{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" aria-hidden="true">
                {!! $iconPath !!}
            </svg>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">
                {{ $label }}
            </p>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="{{ $compact ? 'text-lg' : 'text-2xl' }} font-bold {{ $colors['value'] }}">
                    {{ is_numeric($value) ? number_format($value, is_float($value) ? 1 : 0) : $value }}
                </span>
                @if ($unit)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $unit }}</span>
                @endif
            </div>

            {{-- Trend Indicator --}}
            @if ($trend)
                <div class="flex items-center gap-1 mt-1">
                    @if (($trend['direction'] ?? 'stable') === 'up')
                        <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        <span class="text-xs text-green-600 dark:text-green-400">
                            +{{ $trend['value'] ?? 0 }}{{ $trend['unit'] ?? '%' }}
                        </span>
                    @elseif(($trend['direction'] ?? 'stable') === 'down')
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        <span class="text-xs text-red-600 dark:text-red-400">
                            -{{ $trend['value'] ?? 0 }}{{ $trend['unit'] ?? '%' }}
                        </span>
                    @else
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Stable</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
