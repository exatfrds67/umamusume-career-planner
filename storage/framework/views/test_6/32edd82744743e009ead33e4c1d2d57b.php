
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Migration</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Convert legacy data formats and migrate from external
                sources.</p>
        </div>
        <a href="<?php echo e(route('migration.index')); ?>"
            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Open Migration Tool
        </a>
    </div>

    
    <div>
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Supported Legacy Formats</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <?php $__currentLoopData = $legacyFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-600 text-center">
                    <div class="text-2xl mb-2">
                        <?php switch($format):
                            case ('v1_json'): ?>
                                📄
                            <?php break; ?>

                            <?php case ('v1_csv'): ?>
                                📊
                            <?php break; ?>

                            <?php case ('google_sheets'): ?>
                                📋
                            <?php break; ?>

                            <?php case ('excel'): ?>
                                📗
                            <?php break; ?>

                            <?php case ('custom'): ?>
                                ⚙️
                            <?php break; ?>
                        <?php endswitch; ?>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($label); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <span class="text-2xl">🔍</span>
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white">Auto Format Detection</h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Automatically detects the format of your legacy
                        data and suggests the best conversion approach.</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <span class="text-2xl">✅</span>
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white">Data Validation</h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Validates all data during migration to ensure
                        integrity and catch errors early.</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⚖️</span>
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white">Conflict Resolution</h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Smart conflict detection with options to skip,
                        overwrite, merge, or rename duplicate records.</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <span class="text-2xl">📦</span>
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white">Batch Processing</h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Process large datasets in batches with progress
                        tracking and pause/resume support.</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <div>
                <h4 class="font-medium text-yellow-800 dark:text-yellow-200">Before You Migrate</h4>
                <ul class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                    <li>• Create a backup of your current data first</li>
                    <li>• Review the format detection results before proceeding</li>
                    <li>• Use preview mode to check data transformation</li>
                    <li>• Large migrations may take several minutes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/data-management/partials/migration.blade.php ENDPATH**/ ?>