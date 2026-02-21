
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Backup & Restore</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Create backups and restore your data with encryption
                support.</p>
        </div>
        <a href="<?php echo e(route('backup.index')); ?>"
            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Open Backup Manager
        </a>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6 border border-green-200 dark:border-green-800">
            <div class="flex items-start gap-4">
                <span class="text-4xl">💾</span>
                <div class="flex-1">
                    <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">Create Backup</h4>
                    <p class="text-sm text-green-700 dark:text-green-300 mb-4">Create a complete backup of all your data
                        with optional compression and encryption.</p>
                    <a href="<?php echo e(route('backup.index')); ?>#create"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Create New Backup
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-4">
                <span class="text-4xl">♻️</span>
                <div class="flex-1">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Restore Data</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">Restore your data from a previous backup
                        with integrity verification.</p>
                    <a href="<?php echo e(route('backup.index')); ?>#restore"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Restore from Backup
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4">
            <span class="text-3xl">🗜️</span>
            <p class="mt-2 font-medium text-gray-900 dark:text-white">Compression</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">ZIP compression for smaller files</p>
        </div>
        <div class="text-center p-4">
            <span class="text-3xl">🔐</span>
            <p class="mt-2 font-medium text-gray-900 dark:text-white">Encryption</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">AES-256 encryption support</p>
        </div>
        <div class="text-center p-4">
            <span class="text-3xl">📅</span>
            <p class="mt-2 font-medium text-gray-900 dark:text-white">Scheduling</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Automated daily/weekly backups</p>
        </div>
        <div class="text-center p-4">
            <span class="text-3xl">✅</span>
            <p class="mt-2 font-medium text-gray-900 dark:text-white">Verification</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Checksum integrity checks</p>
        </div>
    </div>

    
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📊</span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Backup Status</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="quickStats.total_backups"></span> backups created
                        <template x-if="quickStats.last_backup">
                            <span> • Last backup: <span x-text="formatDate(quickStats.last_backup)"></span></span>
                        </template>
                    </p>
                </div>
            </div>
            <a href="<?php echo e(route('backup.index')); ?>"
                class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                View All Backups →
            </a>
        </div>
    </div>

    
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="font-medium text-green-800 dark:text-green-200">Backup Best Practices</h4>
                <ul class="mt-2 text-sm text-green-700 dark:text-green-300 space-y-1">
                    <li>• Create backups before major data changes</li>
                    <li>• Enable encryption for sensitive data</li>
                    <li>• Set up automated weekly backups</li>
                    <li>• Keep at least 3 recent backups</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/data-management/partials/backup.blade.php ENDPATH**/ ?>