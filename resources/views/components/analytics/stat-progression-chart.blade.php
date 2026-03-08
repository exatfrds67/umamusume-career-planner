{{--
    Stat Progression Chart Component
    
    Displays stat progression over time with interactive timeline.
    Uses Chart.js for rendering with Alpine.js for interactivity.
    
    Requirements: 15.4, 25.3 (Task 5.2.3)
    WCAG 2.2 AA Compliant
    
    @props
    - data: array - Stat progression data with turns and values
    - stats: array - Which stats to display (default: all)
    - height: string - Chart height (default: '300px')
    - showLegend: bool - Show legend (default: true)
    - interactive: bool - Enable tooltips and hover (default: true)
    - phases: bool - Show career phase markers (default: true)
--}}

@props([
    'data' => [],
    'stats' => ['speed', 'stamina', 'power', 'guts', 'wit'],
    'height' => '300px',
    'showLegend' => true,
    'interactive' => true,
    'phases' => true,
    'careerId' => null,
])

@php
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
@endphp

<div x-data="statProgressionChart({
    chartId: '{{ $chartId }}',
    data: {{ json_encode($data) }},
    stats: {{ json_encode($stats) }},
    statColors: {{ json_encode($statColors) }},
    showLegend: {{ $showLegend ? 'true' : 'false' }},
    interactive: {{ $interactive ? 'true' : 'false' }},
    phases: {{ $phases ? 'true' : 'false' }},
    phaseMarkers: {{ json_encode($phaseMarkers) }}
})"
    {{ $attributes->merge(['class' => 'stat-progression-chart glass-card-inner rounded-lg p-4']) }} role="figure"
    aria-label="Stat progression chart showing character development over time">
    {{-- Chart Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                Stat Progression
            </h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Track your character's growth over time
            </p>
        </div>

        {{-- Stat Toggle Buttons --}}
        <div class="flex flex-wrap gap-2" role="group" aria-label="Toggle stat visibility">
            @foreach ($stats as $stat)
                <button type="button" @click="toggleStat('{{ $stat }}')"
                    :class="visibleStats.includes('{{ $stat }}') ?
                        'opacity-100 ring-2 ring-offset-2' :
                        'opacity-50 hover:opacity-75'"
                    class="px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $statColors[$stat]['line'] }}; color: white;"
                    :aria-pressed="visibleStats.includes('{{ $stat }}')"
                    aria-label="Toggle {{ ucfirst($stat) }} visibility">
                    {{ ucfirst($stat) }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Chart Container --}}
    <div class="relative" style="height: {{ $height }};">
        <canvas id="{{ $chartId }}" role="img"
            aria-label="Line chart showing stat progression across career turns"></canvas>

        {{-- Loading State --}}
        <div x-show="loading"
            class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-neutral-800/50 rounded-lg">
            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
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

        {{-- No Data State --}}
        <div x-show="!loading && (!data || data.length === 0)"
            class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-neutral-500 dark:text-neutral-400">
                <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                <p>No progression data available</p>
            </div>
        </div>
    </div>

    {{-- Phase Legend --}}
    @if ($phases)
        <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-wrap items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
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
    @endif

    {{-- Accessible Data Table (Screen Reader) --}}
    <div class="sr-only">
        <table>
            <caption>Stat progression data table</caption>
            <thead>
                <tr>
                    <th>Turn</th>
                    @foreach ($stats as $stat)
                        <th>{{ ucfirst($stat) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $point)
                    <tr>
                        <td>{{ $point['turn'] ?? 'N/A' }}</td>
                        @foreach ($stats as $stat)
                            <td>{{ $point[$stat] ?? 0 }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endPushOnce

@once
    @vite(['resources/js/components/analytics/stat-progression-chart.js'])
@endonce
