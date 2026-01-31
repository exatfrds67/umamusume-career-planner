
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import Data</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Import your career data from CSV, JSON, or text formats.
            </p>
        </div>
        <a href="<?php echo e(route('import.index')); ?>"
            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Open Full Import Page
        </a>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <?php $__currentLoopData = $importTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('import.index')); ?>?type=<?php echo e($type); ?>"
                class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center">
                <div class="text-2xl mb-2">
                    <?php switch($type):
                        case ('character'): ?>
                            👤
                        <?php break; ?>

                        <?php case ('career'): ?>
                            📊
                        <?php break; ?>

                        <?php case ('training_session'): ?>
                            🏃
                        <?php break; ?>

                        <?php case ('skill'): ?>
                            ⚡
                        <?php break; ?>

                        <?php case ('support_card'): ?>
                            🃏
                        <?php break; ?>
                    <?php endswitch; ?>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($label); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Supported Formats</h4>
        <div class="grid grid-cols-3 gap-4">
            <div class="flex items-center gap-2">
                <span
                    class="w-8 h-8 flex items-center justify-center bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded">
                    📄
                </span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">CSV</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Comma-separated values</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="w-8 h-8 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded">
                    { }
                </span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">JSON</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Structured data format</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="w-8 h-8 flex items-center justify-center bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded">
                    📝
                </span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Text</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Copy & paste friendly</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="font-medium text-blue-800 dark:text-blue-200">Import Tips</h4>
                <ul class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• Stat values should be between 0 and 1200</li>
                    <li>• Scenario types: "ura_finale" or "unity_cup"</li>
                    <li>• Preview your data before importing to catch errors</li>
                    <li>• Use templates for consistent formatting</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/data-management/partials/import.blade.php ENDPATH**/ ?>