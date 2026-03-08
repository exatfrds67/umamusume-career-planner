{{--
    Comparison Table Component
    
    Displays career comparison data in a sortable, filterable table.
    Supports multi-career comparison with key metrics highlighting.
    
    Requirements: 15.4, 25.3 (Task 5.2.3)
    WCAG 2.2 AA Compliant
    
    @props
    - careers: array - Array of career data to compare
    - columns: array - Column configuration
    - sortable: bool - Enable column sorting (default: true)
    - filterable: bool - Enable filtering (default: true)
    - highlightBest: bool - Highlight best values (default: true)
--}}

@props([
    'careers' => [],
    'columns' => null,
    'sortable' => true,
    'filterable' => true,
    'highlightBest' => true,
])

@php
    $defaultColumns = [
        ['key' => 'career_name', 'label' => 'Career', 'sortable' => true, 'type' => 'text'],
        ['key' => 'scenario_type', 'label' => 'Scenario', 'sortable' => true, 'type' => 'badge'],
        [
            'key' => 'total_stat_points',
            'label' => 'Total Stats',
            'sortable' => true,
            'type' => 'number',
            'highlight' => 'max',
        ],
        [
            'key' => 'efficiency_rating',
            'label' => 'Efficiency',
            'sortable' => true,
            'type' => 'percentage',
            'highlight' => 'max',
        ],
        [
            'key' => 'race_win_rate',
            'label' => 'Win Rate',
            'sortable' => true,
            'type' => 'percentage',
            'highlight' => 'max',
        ],
        [
            'key' => 'training_failures',
            'label' => 'Failures',
            'sortable' => true,
            'type' => 'number',
            'highlight' => 'min',
        ],
        ['key' => 'skills_acquired', 'label' => 'Skills', 'sortable' => true, 'type' => 'number', 'highlight' => 'max'],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'type' => 'status'],
    ];

    $columns = $columns ?? $defaultColumns;
    $tableId = 'comparison-table-' . uniqid();
@endphp

<div x-data="comparisonTable({
    careers: {{ json_encode($careers) }},
    columns: {{ json_encode($columns) }},
    sortable: {{ $sortable ? 'true' : 'false' }},
    filterable: {{ $filterable ? 'true' : 'false' }},
    highlightBest: {{ $highlightBest ? 'true' : 'false' }}
})" {{ $attributes->merge(['class' => 'comparison-table-wrapper']) }}>
    {{-- Table Header with Search and Filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                Career Comparison
            </h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                <span x-text="filteredCareers.length"></span> of <span x-text="careers.length"></span> careers
            </p>
        </div>

        @if ($filterable)
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Search Input --}}
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Search careers..."
                        class="w-full sm:w-64 pl-10 pr-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        aria-label="Search careers">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                {{-- Scenario Filter --}}
                <select x-model="scenarioFilter"
                    class="px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    aria-label="Filter by scenario">
                    <option value="">All Scenarios</option>
                    <option value="ura_finale">URA Finale</option>
                    <option value="unity_cup">Unity Cup</option>
                </select>

                {{-- Status Filter --}}
                <select x-model="statusFilter"
                    class="px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="abandoned">Abandoned</option>
                </select>
            </div>
        @endif
    </div>

    {{-- Table Container --}}
    <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-700">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700" role="grid"
            aria-label="Career comparison table">
            <thead class="bg-neutral-50 dark:bg-neutral-800">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider {{ $sortable && ($column['sortable'] ?? false) ? 'cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700 select-none' : '' }}"
                            @if ($sortable && ($column['sortable'] ?? false)) @click="sortBy('{{ $column['key'] }}')"
                                :aria-sort="sortColumn === '{{ $column['key'] }}' ? (sortDirection === 'asc' ? 'ascending' : 'descending') : 'none'"
                                role="columnheader"
                                tabindex="0"
                                @keydown.enter="sortBy('{{ $column['key'] }}')"
                                @keydown.space.prevent="sortBy('{{ $column['key'] }}')" @endif>
                            <div class="flex items-center gap-2">
                                <span>{{ $column['label'] }}</span>
                                @if ($sortable && ($column['sortable'] ?? false))
                                    <span class="flex flex-col">
                                        <svg class="w-3 h-3 transition-colors"
                                            :class="sortColumn === '{{ $column['key'] }}' && sortDirection === 'asc' ?
                                                'text-primary-500' : 'text-neutral-400'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 12l5-5 5 5H5z" />
                                        </svg>
                                        <svg class="w-3 h-3 -mt-1 transition-colors"
                                            :class="sortColumn === '{{ $column['key'] }}' && sortDirection === 'desc' ?
                                                'text-primary-500' : 'text-neutral-400'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 8l5 5 5-5H5z" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                <template x-for="(career, index) in filteredCareers" :key="career.career_id || index">
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-900/20': highlightBest && isBestPerformer(career) }">
                        @foreach ($columns as $column)
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @switch($column['type'] ?? 'text')
                                    @case('text')
                                        <span class="text-neutral-900 dark:text-white font-medium"
                                            x-text="career.{{ $column['key'] }} || 'N/A'"></span>
                                    @break

                                    @case('number')
                                        <span class="font-semibold"
                                            :class="highlightBest && isHighlighted(career, '{{ $column['key'] }}',
                                                    '{{ $column['highlight'] ?? 'max' }}') ?
                                                'text-green-600 dark:text-green-400' : 'text-neutral-900 dark:text-white'"
                                            x-text="formatNumber(career.{{ $column['key'] }})"></span>
                                    @break

                                    @case('percentage')
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-300"
                                                    :class="getPercentageColor(career.{{ $column['key'] }})"
                                                    :style="'width: ' + Math.min(100, career.{{ $column['key'] }} || 0) + '%'">
                                                </div>
                                            </div>
                                            <span class="font-semibold min-w-12"
                                                :class="highlightBest && isHighlighted(career, '{{ $column['key'] }}',
                                                        '{{ $column['highlight'] ?? 'max' }}') ?
                                                    'text-green-600 dark:text-green-400' :
                                                    'text-neutral-900 dark:text-white'"
                                                x-text="formatPercentage(career.{{ $column['key'] }})"></span>
                                        </div>
                                    @break

                                    @case('badge')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="getScenarioBadgeClass(career.{{ $column['key'] }})"
                                            x-text="formatScenario(career.{{ $column['key'] }})"></span>
                                    @break

                                    @case('status')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="getStatusBadgeClass(career.{{ $column['key'] }})"
                                            x-text="formatStatus(career.{{ $column['key'] }})"></span>
                                    @break

                                    @default
                                        <span class="text-neutral-900 dark:text-white"
                                            x-text="career.{{ $column['key'] }} || 'N/A'"></span>
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                </template>

                {{-- Empty State --}}
                <tr x-show="filteredCareers.length === 0">
                    <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center">
                        <div class="text-neutral-500 dark:text-neutral-400">
                            <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <p class="font-medium">No careers found</p>
                            <p class="text-sm mt-1">Try adjusting your search or filters</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Summary Row --}}
    <div x-show="filteredCareers.length > 0" class="mt-4 p-4 bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Summary Statistics</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-neutral-500 dark:text-neutral-400">Avg Efficiency:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-white"
                    x-text="calculateAverage('efficiency_rating') + '%'"></span>
            </div>
            <div>
                <span class="text-neutral-500 dark:text-neutral-400">Avg Win Rate:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-white"
                    x-text="calculateAverage('race_win_rate') + '%'"></span>
            </div>
            <div>
                <span class="text-neutral-500 dark:text-neutral-400">Avg Stats:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-white"
                    x-text="formatNumber(calculateAverage('total_stat_points'))"></span>
            </div>
            <div>
                <span class="text-neutral-500 dark:text-neutral-400">Total Failures:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-white"
                    x-text="calculateSum('training_failures')"></span>
            </div>
        </div>
    </div>
</div>

{{-- JS extracted to resources/js/components/analytics/comparison-table.js --}}
@pushOnce('scripts')
    @vite('resources/js/components/analytics/comparison-table.js')
@endPushOnce
