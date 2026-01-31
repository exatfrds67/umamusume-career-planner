

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'careers' => [],
    'columns' => null,
    'sortable' => true,
    'filterable' => true,
    'highlightBest' => true,
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
    'careers' => [],
    'columns' => null,
    'sortable' => true,
    'filterable' => true,
    'highlightBest' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
?>

<div x-data="comparisonTable({
    careers: <?php echo e(json_encode($careers)); ?>,
    columns: <?php echo e(json_encode($columns)); ?>,
    sortable: <?php echo e($sortable ? 'true' : 'false'); ?>,
    filterable: <?php echo e($filterable ? 'true' : 'false'); ?>,
    highlightBest: <?php echo e($highlightBest ? 'true' : 'false'); ?>

})" <?php echo e($attributes->merge(['class' => 'comparison-table-wrapper'])); ?>>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Career Comparison
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span x-text="filteredCareers.length"></span> of <span x-text="careers.length"></span> careers
            </p>
        </div>

        <?php if($filterable): ?>
            <div class="flex flex-col sm:flex-row gap-3">
                
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Search careers..."
                        class="w-full sm:w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        aria-label="Search careers">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                
                <select x-model="scenarioFilter"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    aria-label="Filter by scenario">
                    <option value="">All Scenarios</option>
                    <option value="ura_finale">URA Finale</option>
                    <option value="unity_cup">Unity Cup</option>
                </select>

                
                <select x-model="statusFilter"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="abandoned">Abandoned</option>
                </select>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="grid"
            aria-label="Career comparison table">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider <?php echo e($sortable && ($column['sortable'] ?? false) ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 select-none' : ''); ?>"
                            <?php if($sortable && ($column['sortable'] ?? false)): ?> @click="sortBy('<?php echo e($column['key']); ?>')"
                                :aria-sort="sortColumn === '<?php echo e($column['key']); ?>' ? (sortDirection === 'asc' ? 'ascending' : 'descending') : 'none'"
                                role="columnheader"
                                tabindex="0"
                                @keydown.enter="sortBy('<?php echo e($column['key']); ?>')"
                                @keydown.space.prevent="sortBy('<?php echo e($column['key']); ?>')" <?php endif; ?>>
                            <div class="flex items-center gap-2">
                                <span><?php echo e($column['label']); ?></span>
                                <?php if($sortable && ($column['sortable'] ?? false)): ?>
                                    <span class="flex flex-col">
                                        <svg class="w-3 h-3 transition-colors"
                                            :class="sortColumn === '<?php echo e($column['key']); ?>' && sortDirection === 'asc' ?
                                                'text-primary-500' : 'text-gray-400'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 12l5-5 5 5H5z" />
                                        </svg>
                                        <svg class="w-3 h-3 -mt-1 transition-colors"
                                            :class="sortColumn === '<?php echo e($column['key']); ?>' && sortDirection === 'desc' ?
                                                'text-primary-500' : 'text-gray-400'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 8l5 5 5-5H5z" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="(career, index) in filteredCareers" :key="career.career_id || index">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-900/20': highlightBest && isBestPerformer(career) }">
                        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <?php switch($column['type'] ?? 'text'):
                                    case ('text'): ?>
                                        <span class="text-gray-900 dark:text-white font-medium"
                                            x-text="career.<?php echo e($column['key']); ?> || 'N/A'"></span>
                                    <?php break; ?>

                                    <?php case ('number'): ?>
                                        <span class="font-semibold"
                                            :class="highlightBest && isHighlighted(career, '<?php echo e($column['key']); ?>',
                                                    '<?php echo e($column['highlight'] ?? 'max'); ?>') ?
                                                'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white'"
                                            x-text="formatNumber(career.<?php echo e($column['key']); ?>)"></span>
                                    <?php break; ?>

                                    <?php case ('percentage'): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-300"
                                                    :class="getPercentageColor(career.<?php echo e($column['key']); ?>)"
                                                    :style="'width: ' + Math.min(100, career.<?php echo e($column['key']); ?> || 0) + '%'">
                                                </div>
                                            </div>
                                            <span class="font-semibold min-w-[3rem]"
                                                :class="highlightBest && isHighlighted(career, '<?php echo e($column['key']); ?>',
                                                        '<?php echo e($column['highlight'] ?? 'max'); ?>') ?
                                                    'text-green-600 dark:text-green-400' :
                                                    'text-gray-900 dark:text-white'"
                                                x-text="formatPercentage(career.<?php echo e($column['key']); ?>)"></span>
                                        </div>
                                    <?php break; ?>

                                    <?php case ('badge'): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="getScenarioBadgeClass(career.<?php echo e($column['key']); ?>)"
                                            x-text="formatScenario(career.<?php echo e($column['key']); ?>)"></span>
                                    <?php break; ?>

                                    <?php case ('status'): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="getStatusBadgeClass(career.<?php echo e($column['key']); ?>)"
                                            x-text="formatStatus(career.<?php echo e($column['key']); ?>)"></span>
                                    <?php break; ?>

                                    <?php default: ?>
                                        <span class="text-gray-900 dark:text-white"
                                            x-text="career.<?php echo e($column['key']); ?> || 'N/A'"></span>
                                <?php endswitch; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </template>

                
                <tr x-show="filteredCareers.length === 0">
                    <td colspan="<?php echo e(count($columns)); ?>" class="px-4 py-8 text-center">
                        <div class="text-gray-500 dark:text-gray-400">
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

    
    <div x-show="filteredCareers.length > 0" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Summary Statistics</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Avg Efficiency:</span>
                <span class="ml-2 font-semibold text-gray-900 dark:text-white"
                    x-text="calculateAverage('efficiency_rating') + '%'"></span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Avg Win Rate:</span>
                <span class="ml-2 font-semibold text-gray-900 dark:text-white"
                    x-text="calculateAverage('race_win_rate') + '%'"></span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Avg Stats:</span>
                <span class="ml-2 font-semibold text-gray-900 dark:text-white"
                    x-text="formatNumber(calculateAverage('total_stat_points'))"></span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Total Failures:</span>
                <span class="ml-2 font-semibold text-gray-900 dark:text-white"
                    x-text="calculateSum('training_failures')"></span>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('c2cff292-174c-4d85-920c-fe3aa560e362')): $__env->markAsRenderedOnce('c2cff292-174c-4d85-920c-fe3aa560e362');
$__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('comparisonTable', (config) => ({
                careers: config.careers || [],
                columns: config.columns || [],
                sortable: config.sortable,
                filterable: config.filterable,
                highlightBest: config.highlightBest,

                searchQuery: '',
                scenarioFilter: '',
                statusFilter: '',
                sortColumn: '',
                sortDirection: 'asc',

                get filteredCareers() {
                    let result = [...this.careers];

                    // Apply search filter
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        result = result.filter(c =>
                            (c.career_name || '')
                            .toLowerCase().includes(query) ||
                            (c.character_name || '').toLowerCase().includes(query)
                        );
                    }

                    // Apply scenario filter
                    if (this.scenarioFilter) {
                        result = result.filter(c => c.scenario_type === this.scenarioFilter);
                    }

                    // Apply status filter
                    if (this.statusFilter) {
                        result = result.filter(c => c.status === this.statusFilter);
                    }

                    // Apply sorting
                    if (this.sortColumn) {
                        result.sort((a, b) => {
                            let aVal = a[this.sortColumn];
                            let bVal = b[this.sortColumn];

                            // Handle null/undefined
                            if (aVal == null) aVal = 0;
                            if (bVal == null) bVal = 0;

                            // Numeric comparison
                            if (typeof aVal === 'number' && typeof bVal === 'number') {
                                return this.sortDirection === 'asc' ? aVal - bVal : bVal -
                                    aVal;
                            }

                            // String comparison
                            aVal = String(aVal).toLowerCase();
                            bVal = String(bVal).toLowerCase();

                            if (this.sortDirection === 'asc') {
                                return aVal.localeCompare(bVal);
                            }
                            return bVal.localeCompare(aVal);
                        });
                    }

                    return result;
                },

                sortBy(column) {
                    if (this.sortColumn === column) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortColumn = column;
                        this.sortDirection = 'asc';
                    }
                },

                isHighlighted(career, key, type) {
                    if (!this.highlightBest || this.filteredCareers.length < 2) return false;

                    const values = this.filteredCareers.map(c => c[key] || 0);
                    const careerValue = career[key] || 0;

                    if (type === 'max') {
                        return careerValue === Math.max(...values);
                    } else if (type === 'min') {
                        return careerValue === Math.min(...values);
                    }
                    return false;
                },

                isBestPerformer(career) {
                    if (!this.highlightBest || this.filteredCareers.length < 2) return false;

                    // Simple scoring: efficiency + win rate
                    const scores = this.filteredCareers.map(c => ({
                        id: c.career_id,
                        score: (c.efficiency_rating || 0) + (c.race_win_rate || 0)
                    }));

                    const maxScore = Math.max(...scores.map(s => s.score));
                    return (career.efficiency_rating || 0) + (career.race_win_rate || 0) === maxScore;
                },

                formatNumber(value) {
                    if (value == null) return '0';
                    return new Intl.NumberFormat().format(Math.round(value));
                },

                formatPercentage(value) {
                    if (value == null) return '0%';
                    return Math.round(value) + '%';
                },

                formatScenario(value) {
                    if (!value) return 'Unknown';
                    return value.replace('_', ' ');
                },

                formatStatus(value) {
                    if (!value) return 'Unknown';
                    return value.replace('_', ' ');
                },

                getPercentageColor(value) {
                    if (value >= 70) return 'bg-green-500';
                    if (value >= 50) return 'bg-yellow-500';
                    return 'bg-red-500';
                },

                getScenarioBadgeClass(scenario) {
                    const classes = {
                        'ura_finale': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                        'unity_cup': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                    };
                    return classes[scenario] ||
                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                },

                getStatusBadgeClass(status) {
                    const classes = {
                        'completed': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                        'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                        'abandoned': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                    };
                    return classes[status] ||
                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                },

                calculateAverage(key) {
                    if (this.filteredCareers.length === 0) return 0;
                    const sum = this.filteredCareers.reduce((acc, c) => acc + (c[key] || 0), 0);
                    return Math.round(sum / this.filteredCareers.length * 10) / 10;
                },

                calculateSum(key) {
                    return this.filteredCareers.reduce((acc, c) => acc + (c[key] || 0), 0);
                },
            }));
        });
    </script>
<?php $__env->stopPush(); endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/analytics/comparison-table.blade.php ENDPATH**/ ?>