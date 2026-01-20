{{-- History Tab Content --}}
<div class="space-y-6" x-init="loadHistory()">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Operation History</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">View all import, export, migration, and backup
                operations.</p>
        </div>
        <button type="button" @click="loadHistory()"
            class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            Refresh
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Operation Type</label>
                <select x-model="historyFilters.operation_type" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    <option value="">All Types</option>
                    <option value="import">Import</option>
                    <option value="export">Export</option>
                    <option value="migration">Migration</option>
                    <option value="backup">Backup</option>
                    <option value="restore">Restore</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select x-model="historyFilters.status" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                <input type="date" x-model="historyFilters.date_from" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                <input type="date" x-model="historyFilters.date_to" @change="loadHistory()"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
            </div>
        </div>
        <div class="mt-3 flex justify-end">
            <button type="button"
                @click="historyFilters = {operation_type: '', status: '', date_from: '', date_to: ''}; loadHistory()"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
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
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading history...</p>
    </div>

    {{-- History List --}}
    <div x-show="!loading">
        <template x-if="history.length === 0">
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No operations found matching your filters.</p>
            </div>
        </template>

        <div class="space-y-3">
            <template x-for="op in history" :key="op.id">
                <div
                    class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <span class="text-2xl"
                            x-text="op.operation_type === 'import' ? '📥' : op.operation_type === 'export' ? '📤' : op.operation_type === 'migration' ? '🔄' : op.operation_type === 'backup' ? '💾' : '♻️'"></span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-gray-900 dark:text-white capitalize"
                                    x-text="op.operation_type.replace('_', ' ')"></h4>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    :class="op.status === 'completed' ?
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : op
                                        .status === 'failed' ?
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                    x-text="op.status.replace('_', ' ')"></span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="op.message"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400"
                            x-text="formatDate(op.completed_at || op.created_at)"></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-mono"
                            x-text="op.operation_id ? op.operation_id.substring(0, 8) + '...' : ''"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <template x-if="historyPagination && historyPagination.total_pages > 1">
            <div class="mt-4 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span x-text="(historyPagination.current_page - 1) * historyPagination.per_page + 1"></span>
                    to <span
                        x-text="Math.min(historyPagination.current_page * historyPagination.per_page, historyPagination.total)"></span>
                    of <span x-text="historyPagination.total"></span> results
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="loadHistory(historyPagination.current_page - 1)"
                        :disabled="historyPagination.current_page <= 1"
                        class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button type="button" @click="loadHistory(historyPagination.current_page + 1)"
                        :disabled="!historyPagination.has_more"
                        class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
