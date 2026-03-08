{{-- History Tab Content --}}
<div class="space-y-6" x-init="loadHistory()">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Operation History</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">View all import, export, migration, and backup
                operations.</p>
        </div>
        <button type="button" @click="loadHistory()"
            class="inline-flex items-center px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            Refresh
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="history-filter-operation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Operation Type</label>
                <select id="history-filter-operation" x-model="historyFilters.operation_type" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white text-sm">
                    <option value="">All Types</option>
                    <option value="import">Import</option>
                    <option value="export">Export</option>
                    <option value="migration">Migration</option>
                    <option value="backup">Backup</option>
                    <option value="restore">Restore</option>
                </select>
            </div>
            <div>
                <label for="history-filter-status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                <select id="history-filter-status" x-model="historyFilters.status" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label for="history-filter-date-from" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">From Date</label>
                <input id="history-filter-date-from" type="date" x-model="historyFilters.date_from" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white text-sm">
            </div>
            <div>
                <label for="history-filter-date-to" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">To Date</label>
                <input id="history-filter-date-to" type="date" x-model="historyFilters.date_to" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white text-sm">
            </div>
        </div>
        <div class="mt-3 flex justify-end">
            <button type="button"
                @click="historyFilters = {operation_type: '', status: '', date_from: '', date_to: ''}; loadHistory()"
                class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200">
                Clear Filters
            </button>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="text-center py-8">
        <svg class="animate-spin h-8 w-8 mx-auto text-primary-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Loading history...</p>
    </div>

    {{-- History List --}}
    <div x-show="!loading">
        <template x-if="history.length === 0">
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">No operations found matching your filters.</p>
            </div>
        </template>

        <div class="space-y-3">
            <template x-for="op in history" :key="op.id">
                <div
                    class="flex items-center justify-between p-4 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg shrink-0" aria-hidden="true"
                            :class="{
                                'bg-primary-100 dark:bg-primary-900/30': op.operation_type === 'import',
                                'bg-blue-100 dark:bg-blue-900/30': op.operation_type === 'export',
                                'bg-orange-100 dark:bg-orange-900/30': op.operation_type === 'migration',
                                'bg-purple-100 dark:bg-purple-900/30': op.operation_type === 'backup',
                                'bg-green-100 dark:bg-green-900/30': op.operation_type === 'restore',
                                'bg-neutral-100 dark:bg-neutral-700': !['import','export','migration','backup','restore'].includes(op.operation_type)
                            }">
                            {{-- Import icon --}}
                            <svg x-show="op.operation_type === 'import'" class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            {{-- Export icon --}}
                            <svg x-show="op.operation_type === 'export'" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/></svg>
                            {{-- Migration icon --}}
                            <svg x-show="op.operation_type === 'migration'" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            {{-- Backup icon --}}
                            <svg x-show="op.operation_type === 'backup'" class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            {{-- Restore icon --}}
                            <svg x-show="op.operation_type === 'restore'" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            {{-- Fallback icon --}}
                            <svg x-show="!['import','export','migration','backup','restore'].includes(op.operation_type)" class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-neutral-900 dark:text-white capitalize"
                                    x-text="op.operation_type.replace('_', ' ')"></h4>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    :class="op.status === 'completed' ?
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : op
                                        .status === 'failed' ?
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                        'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300'"
                                    x-text="op.status.replace('_', ' ')"></span>
                            </div>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400" x-text="op.message"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400"
                            x-text="formatDate(op.completed_at || op.created_at)"></p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono"
                            x-text="op.operation_id ? op.operation_id.substring(0, 8) + '...' : ''"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <template x-if="historyPagination && historyPagination.total_pages > 1">
            <div class="mt-4 flex items-center justify-between">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Showing <span x-text="(historyPagination.current_page - 1) * historyPagination.per_page + 1"></span>
                    to <span
                        x-text="Math.min(historyPagination.current_page * historyPagination.per_page, historyPagination.total)"></span>
                    of <span x-text="historyPagination.total"></span> results
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="loadHistory(historyPagination.current_page - 1)"
                        :disabled="historyPagination.current_page <= 1"
                        class="px-3 py-1 text-sm rounded border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button type="button" @click="loadHistory(historyPagination.current_page + 1)"
                        :disabled="!historyPagination.has_more"
                        class="px-3 py-1 text-sm rounded border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
