{{-- Overview Tab Content --}}
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Operations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="statistics.total_operations">0</p>
                </div>
                <span class="text-3xl">📊</span>
            </div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 dark:text-green-400">Successful</p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300"
                        x-text="statistics.successful_operations">0</p>
                </div>
                <span class="text-3xl">✅</span>
            </div>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 dark:text-red-400">Failed</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300" x-text="statistics.failed_operations">0
                    </p>
                </div>
                <span class="text-3xl">❌</span>
            </div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400">Records Processed</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300"
                        x-text="statistics.records_processed">0</p>
                </div>
                <span class="text-3xl">📝</span>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button type="button" @click="activeTab = 'import'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                <span class="text-3xl">📥</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Import Data</span>
            </button>
            <button type="button" @click="activeTab = 'export'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                <span class="text-3xl">📤</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Export Data</span>
            </button>
            <a href="{{ route('backup.index') }}"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                <span class="text-3xl">💾</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Create Backup</span>
            </a>
            <button type="button" @click="activeTab = 'migration'"
                class="flex flex-col items-center gap-2 p-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                <span class="text-3xl">🔄</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Migrate Legacy</span>
            </button>
        </div>
    </div>

    {{-- Recent Operations --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Operations</h3>
            <button type="button" @click="activeTab = 'history'"
                class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                View All →
            </button>
        </div>
        <x-data-management.operation-history x-bind:operations="recentOperations" :show-pagination="false"
            empty-message="No recent operations. Start by importing or exporting data." />
    </div>

    {{-- Last Backup Info --}}
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">💾</span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Last Backup</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400"
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
