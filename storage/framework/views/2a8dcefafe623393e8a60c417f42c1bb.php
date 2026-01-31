

<?php $__env->startSection('title', 'Data Import'); ?>

<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Import Data']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>

    <div class="container mx-auto px-4 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Import</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Import your career data from various formats including CSV, JSON, or copy-paste text.
            </p>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Step 1: Select Import Type
                </span>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <?php $__currentLoopData = $importTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                        class="import-type-btn p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        data-type="<?php echo e($type); ?>" aria-pressed="false">
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
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <input type="hidden" id="import-type" name="import_type" value="">
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Step 2: Provide Data
                </span>
            </h2>

            
            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                <nav class="-mb-px flex space-x-8" aria-label="Input method tabs">
                    <button type="button"
                        class="input-tab active border-primary-500 text-primary-600 dark:text-primary-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-tab="paste" aria-selected="true">
                        Copy & Paste
                    </button>
                    <button type="button"
                        class="input-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-tab="file" aria-selected="false">
                        File Upload
                    </button>
                </nav>
            </div>

            
            <div id="paste-tab" class="tab-content">
                <div class="mb-4">
                    <label for="paste-content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Paste your data (CSV, JSON, or key-value format)
                    </label>
                    <textarea id="paste-content" name="content" rows="10"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"
                        placeholder="Paste your data here...

Examples:
CSV: name,speed,stamina,power,guts,wit
JSON: {&quot;name&quot;: &quot;Character&quot;, &quot;speed&quot;: 800}
Key-Value: Name: Character
           Speed: 800"></textarea>
                </div>

                
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Format will be automatically detected. Supported: CSV, JSON, TSV, Key-Value pairs.
                </div>
            </div>

            
            <div id="file-tab" class="tab-content hidden">
                <div
                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                    <input type="file" id="file-input" name="file" accept=".csv,.json,.txt" class="hidden">
                    <label for="file-input" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48">
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500">Click to
                                upload</span>
                            or drag and drop
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                            CSV, JSON, or TXT up to 10MB
                        </p>
                    </label>
                </div>
                <div id="file-info" class="mt-4 hidden">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span id="file-name" class="text-sm text-gray-700 dark:text-gray-300"></span>
                        </div>
                        <button type="button" id="clear-file" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex justify-end mb-6">
            <button type="button" id="preview-btn"
                class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                </svg>
                Preview Import
            </button>
        </div>

        
        <div id="preview-section" class="hidden">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        Step 3: Review & Import
                    </span>
                </h2>

                
                <div id="preview-summary" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Records</div>
                        <div id="total-records" class="text-2xl font-bold text-gray-900 dark:text-white">0</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-sm text-green-600 dark:text-green-400">Valid Records</div>
                        <div id="valid-records" class="text-2xl font-bold text-green-700 dark:text-green-300">0</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                        <div class="text-sm text-red-600 dark:text-red-400">Invalid Records</div>
                        <div id="invalid-records" class="text-2xl font-bold text-red-700 dark:text-red-300">0</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Format Detected</div>
                        <div id="format-detected" class="text-2xl font-bold text-blue-700 dark:text-blue-300 uppercase">-
                        </div>
                    </div>
                </div>

                
                <div id="preview-warnings" class="hidden mb-6">
                    <div
                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Warnings</h3>
                                <ul id="warning-list"
                                    class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 list-disc list-inside"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="preview-headers">
                                
                            </tr>
                        </thead>
                        <tbody id="preview-body"
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            
                        </tbody>
                    </table>
                </div>

                
                <div class="mt-6 flex justify-end gap-4">
                    <button type="button" id="cancel-btn"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="import-btn"
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Import <span id="import-count">0</span> Records
                    </button>
                </div>
            </div>
        </div>

        
        <div id="results-section" class="hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div id="results-success" class="hidden">
                    <div class="text-center py-8">
                        <div
                            class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Import Successful!</h3>
                        <p id="results-message" class="text-gray-600 dark:text-gray-400"></p>
                        <div class="mt-6">
                            <a href="<?php echo e(route('dashboard')); ?>"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                <div id="results-error" class="hidden">
                    <div class="text-center py-8">
                        <div
                            class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Import Failed</h3>
                        <p id="error-message" class="text-gray-600 dark:text-gray-400 mb-4"></p>
                        <ul id="error-list"
                            class="text-sm text-red-600 dark:text-red-400 text-left max-w-md mx-auto list-disc list-inside">
                        </ul>
                        <div class="mt-6">
                            <button type="button" id="try-again-btn"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Import Templates & Help
                </span>
            </h2>

            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Use these templates as a starting point for your import data. Select an import type above to see
                    specific templates.
                </p>

                <div id="template-content" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CSV Format</h4>
                            <pre id="csv-template" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"></pre>
                            <button type="button"
                                class="copy-template mt-2 text-sm text-primary-600 dark:text-primary-400 hover:underline"
                                data-format="csv">
                                Copy CSV Template
                            </button>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">JSON Format</h4>
                            <pre id="json-template" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"></pre>
                            <button type="button"
                                class="copy-template mt-2 text-sm text-primary-600 dark:text-primary-400 hover:underline"
                                data-format="json">
                                Copy JSON Template
                            </button>
                        </div>
                    </div>
                </div>

                <div id="template-placeholder" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Select an import type above to see templates
                </div>
            </div>
        </div>
    </div>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/import/index.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/import/index.blade.php ENDPATH**/ ?>