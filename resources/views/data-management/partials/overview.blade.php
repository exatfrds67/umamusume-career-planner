{{-- Overview Tab Content --}}
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" role="region" aria-label="Operation statistics">
        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Operations</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white" x-text="statistics.total_operations" aria-live="polite">0</p>
                </div>
                <div class="p-2 rounded-lg bg-neutral-200 dark:bg-neutral-600" aria-hidden="true">
                    <svg class="w-6 h-6 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 dark:text-green-400">Successful</p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300" x-text="statistics.successful_operations" aria-live="polite">0</p>
                </div>
                <div class="p-2 rounded-lg bg-green-200 dark:bg-green-800" aria-hidden="true">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 dark:text-red-400">Failed</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300" x-text="statistics.failed_operations" aria-live="polite">0</p>
                </div>
                <div class="p-2 rounded-lg bg-red-200 dark:bg-red-800" aria-hidden="true">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400">Records Processed</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300" x-text="statistics.records_processed" aria-live="polite">0</p>
                </div>
                <div class="p-2 rounded-lg bg-blue-200 dark:bg-blue-800" aria-hidden="true">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button type="button" @click="activeTab = 'import'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-neutral-800 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                <svg class="w-8 h-8 text-primary-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Import Data</span>
            </button>
            <button type="button" @click="activeTab = 'export'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-neutral-800 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                <svg class="w-8 h-8 text-primary-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Export Data</span>
            </button>
            <a href="{{ route('backup.index') }}"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-neutral-800 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                <svg class="w-8 h-8 text-primary-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Create Backup</span>
            </a>
            <button type="button" @click="activeTab = 'migration'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-neutral-800 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                <svg class="w-8 h-8 text-primary-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Migrate Legacy</span>
            </button>
        </div>
    </div>

    {{-- Recent Operations --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Recent Operations</h3>
            <button type="button" @click="activeTab = 'history'"
                class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                View All →
            </button>
        </div>
        <x-data-management.operation-history x-bind:operations="recentOperations" :show-pagination="false"
            empty-message="No recent operations. Start by importing or exporting data." />
    </div>

    {{-- Last Backup Info --}}
    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30" aria-hidden="true">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-neutral-900 dark:text-white">Last Backup</p>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400"
                        x-text="quickStats.last_backup ? formatDate(quickStats.last_backup) : 'No backups yet'"></p>
                </div>
            </div>
            <a href="{{ route('backup.index') }}"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                Create Backup
            </a>
        </div>
    </div>
</div>
