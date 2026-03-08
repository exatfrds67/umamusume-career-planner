{{-- Phase 6: Data Table Component for Plan Management --}}
@props([
    'columns' => [], // array of {key, label, sortable, width}
    'rows' => [], // array of data rows
    'sortable' => true,
    'paginated' => true,
    'itemsPerPage' => 10,
    'searchable' => true,
])

<div
    x-data="{
        searchQuery: '',
        sortBy: null,
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: {{ $itemsPerPage }},

        get filteredRows() {
            let filtered = {{ json_encode($rows) }};

            // Apply search filter
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(row => {
                    return Object.values(row).some(value =>
                        String(value).toLowerCase().includes(query)
                    );
                });
            }

            // Apply sorting
            if (this.sortBy) {
                filtered = filtered.sort((a, b) => {
                    const aVal = a[this.sortBy];
                    const bVal = b[this.sortBy];

                    if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            return filtered;
        },

        get paginatedRows() {
            if (!{{ $paginated }}) return this.filteredRows;

            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredRows.slice(start, start + this.itemsPerPage);
        },

        get totalPages() {
            return Math.ceil(this.filteredRows.length / this.itemsPerPage);
        },

        toggleSort(column) {
            if (!{{ $sortable }}) return;
            if (this.sortBy === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDirection = 'asc';
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
    }"
    class="space-y-4"
>
    <!-- Search Bar -->
    @if ($searchable)
        <div class="flex items-center gap-2">
            <input
                type="text"
                x-model="searchQuery"
                placeholder="Search plans..."
                class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-blue-500"
            />
            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                <span x-text="filteredRows.length"></span> results
            </span>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <table class="w-full">
            <!-- Header -->
            <thead class="bg-neutral-50 dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                <tr>
                    @foreach ($columns as $col)
                        <th
                            @if($col['sortable'] ?? false)
                                @click="toggleSort('{{ $col['key'] }}')"
                                class="cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700 transition"
                            @else
                                class="bg-inherit"
                            @endif
                            style="@if(isset($col['width']))width: {{ $col['width']}}@endif"
                        >
                            <div class="px-4 py-3 text-left text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                                <span>{{ $col['label'] }}</span>
                                @if($col['sortable'] ?? false)
                                    <span x-show="sortBy === '{{ $col['key'] }}'" class="text-blue-600">
                                        <span x-show="sortDirection === 'asc'">↑</span>
                                        <span x-show="sortDirection === 'desc'">↓</span>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th class="px-4 py-3 text-right text-sm font-semibold text-neutral-900 dark:text-white">Actions</th>
                </tr>
            </thead>

            <!-- Body -->
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                <template x-for="row in paginatedRows" :key="JSON.stringify(row)">
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                        @foreach ($columns as $col)
                            <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                                <span x-text="row['{{ $col['key'] }}']"></span>
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right">
                            <button
                                @click="$dispatch('row-selected', { row: row })"
                                class="text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 text-sm font-medium"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                </template>

                <!-- Empty State -->
                <template x-if="paginatedRows.length === 0">
                    <tr>
                        <td :colspan="$cols + 1" class="px-4 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            <p class="text-sm">No plans found</p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($paginated)
        <template x-if="totalPages > 1">
            <div class="flex items-center justify-between">
                <span class="text-sm text-neutral-600 dark:text-neutral-400">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>
                <div class="flex gap-2">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-3 py-1.5 border border-neutral-300 dark:border-neutral-600 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        Previous
                    </button>
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 border border-neutral-300 dark:border-neutral-600 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        Next
                    </button>
                </div>
            </div>
        </template>
    @endif
</div>
