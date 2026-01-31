

<?php $__env->startSection('title', 'Data Import'); ?>

<?php $__env->startSection('content'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // State
            let selectedType = null;
            let previewData = null;
            let templates = {};

            // Elements
            const importTypeBtns = document.querySelectorAll('.import-type-btn');
            const importTypeInput = document.getElementById('import-type');
            const inputTabs = document.querySelectorAll('.input-tab');
            const pasteTab = document.getElementById('paste-tab');
            const fileTab = document.getElementById('file-tab');
            const pasteContent = document.getElementById('paste-content');
            const fileInput = document.getElementById('file-input');
            const fileInfo = document.getElementById('file-info');
            const fileName = document.getElementById('file-name');
            const clearFileBtn = document.getElementById('clear-file');
            const previewBtn = document.getElementById('preview-btn');
            const previewSection = document.getElementById('preview-section');
            const resultsSection = document.getElementById('results-section');
            const importBtn = document.getElementById('import-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const tryAgainBtn = document.getElementById('try-again-btn');
            const templateContent = document.getElementById('template-content');
            const templatePlaceholder = document.getElementById('template-placeholder');

            // Import type selection
            importTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    importTypeBtns.forEach(b => {
                        b.classList.remove('border-primary-500', 'bg-primary-50',
                            'dark:bg-primary-900/20');
                        b.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('border-primary-500', 'bg-primary-50',
                        'dark:bg-primary-900/20');
                    this.setAttribute('aria-pressed', 'true');
                    selectedType = this.dataset.type;
                    importTypeInput.value = selectedType;
                    updatePreviewButton();
                    loadTemplates(selectedType);
                });
            });

            // Tab switching
            inputTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    inputTabs.forEach(t => {
                        t.classList.remove('border-primary-500', 'text-primary-600',
                            'dark:text-primary-400');
                        t.classList.add('border-transparent', 'text-gray-500');
                        t.setAttribute('aria-selected', 'false');
                    });
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-primary-500', 'text-primary-600',
                        'dark:text-primary-400');
                    this.setAttribute('aria-selected', 'true');

                    const tabName = this.dataset.tab;
                    if (tabName === 'paste') {
                        pasteTab.classList.remove('hidden');
                        fileTab.classList.add('hidden');
                    } else {
                        pasteTab.classList.add('hidden');
                        fileTab.classList.remove('hidden');
                    }
                    updatePreviewButton();
                });
            });

            // File input handling
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    fileName.textContent = file.name;
                    fileInfo.classList.remove('hidden');
                    updatePreviewButton();
                }
            });

            // Clear file
            clearFileBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileInfo.classList.add('hidden');
                updatePreviewButton();
            });

            // Paste content change
            pasteContent.addEventListener('input', function() {
                updatePreviewButton();
            });

            // Update preview button state
            function updatePreviewButton() {
                const hasType = selectedType !== null;
                const hasContent = pasteContent.value.trim() !== '' || fileInput.files.length > 0;
                previewBtn.disabled = !(hasType && hasContent);
            }

            // Preview button click
            previewBtn.addEventListener('click', async function() {
                if (!selectedType) {
                    showToast('Please select an import type', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('import_type', selectedType);

                if (fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
                } else {
                    formData.append('content', pasteContent.value);
                }

                previewBtn.disabled = true;
                previewBtn.innerHTML = `
            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;

                try {
                    const response = await fetch('/api/import/preview', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: formData,
                    });

                    const result = await response.json();

                    if (result.success) {
                        previewData = result.data.preview;
                        displayPreview(result.data);
                        previewSection.classList.remove('hidden');
                        resultsSection.classList.add('hidden');
                    } else {
                        showToast(result.message || 'Preview failed', 'error');
                        if (result.errors) {
                            console.error('Preview errors:', result.errors);
                        }
                    }
                } catch (error) {
                    console.error('Preview error:', error);
                    showToast('An error occurred during preview', 'error');
                } finally {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview Import
            `;
                    updatePreviewButton();
                }
            });

            // Display preview results
            function displayPreview(data) {
                const preview = data.preview;

                // Update summary
                document.getElementById('total-records').textContent = preview.total_records;
                document.getElementById('valid-records').textContent = preview.valid_records;
                document.getElementById('invalid-records').textContent = preview.invalid_records;
                document.getElementById('format-detected').textContent = data.format_detected || '-';

                // Update warnings
                const warningsSection = document.getElementById('preview-warnings');
                const warningList = document.getElementById('warning-list');
                if (preview.warnings && preview.warnings.length > 0) {
                    warningList.innerHTML = preview.warnings.map(w => `<li>${escapeHtml(w)}</li>`).join('');
                    warningsSection.classList.remove('hidden');
                } else {
                    warningsSection.classList.add('hidden');
                }

                // Build preview table
                const headersRow = document.getElementById('preview-headers');
                const tbody = document.getElementById('preview-body');

                // Get field mapping for headers
                const fieldMapping = preview.field_mapping || {};
                const fields = Object.keys(fieldMapping);

                // Build headers
                headersRow.innerHTML = `
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
            ${fields.map(f => `<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">${escapeHtml(fieldMapping[f]?.label || f)}</th>`).join('')}
        `;

                // Build rows
                tbody.innerHTML = preview.records.map(record => {
                    const statusClass = record.valid ? 'text-green-600 dark:text-green-400' :
                        'text-red-600 dark:text-red-400';
                    const statusIcon = record.valid ?
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

                    const rowClass = record.valid ? '' : 'bg-red-50 dark:bg-red-900/10';

                    return `
                <tr class="${rowClass}">
                    <td class="px-4 py-3 whitespace-nowrap ${statusClass}" title="${record.errors?.join(', ') || 'Valid'}">${statusIcon}</td>
                    ${fields.map(f => `<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${escapeHtml(record.data[f] ?? '-')}</td>`).join('')}
                </tr>
            `;
                }).join('');

                // Update import button
                importBtn.disabled = preview.valid_records === 0;
                document.getElementById('import-count').textContent = preview.valid_records;
            }

            // Import button click
            importBtn.addEventListener('click', async function() {
                if (!previewData || previewData.valid_records === 0) {
                    showToast('No valid records to import', 'error');
                    return;
                }

                const validRecords = previewData.records
                    .filter(r => r.valid)
                    .map(r => r.data);

                importBtn.disabled = true;
                importBtn.innerHTML = `
            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Importing...
        `;

                try {
                    const response = await fetch('/api/import/execute', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: JSON.stringify({
                            import_type: selectedType,
                            data: validRecords,
                        }),
                    });

                    const result = await response.json();

                    previewSection.classList.add('hidden');
                    resultsSection.classList.remove('hidden');

                    if (result.success) {
                        document.getElementById('results-success').classList.remove('hidden');
                        document.getElementById('results-error').classList.add('hidden');
                        document.getElementById('results-message').textContent = result.message;
                    } else {
                        document.getElementById('results-success').classList.add('hidden');
                        document.getElementById('results-error').classList.remove('hidden');
                        document.getElementById('error-message').textContent = result.message ||
                            'Import failed';
                        document.getElementById('error-list').innerHTML = (result.errors || [])
                            .map(e => `<li>${escapeHtml(e)}</li>`)
                            .join('');
                    }
                } catch (error) {
                    console.error('Import error:', error);
                    previewSection.classList.add('hidden');
                    resultsSection.classList.remove('hidden');
                    document.getElementById('results-success').classList.add('hidden');
                    document.getElementById('results-error').classList.remove('hidden');
                    document.getElementById('error-message').textContent =
                        'An error occurred during import';
                    document.getElementById('error-list').innerHTML = '';
                } finally {
                    importBtn.disabled = false;
                    importBtn.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Import <span id="import-count">${previewData?.valid_records || 0}</span> Records
            `;
                }
            });

            // Cancel button
            cancelBtn.addEventListener('click', function() {
                previewSection.classList.add('hidden');
                previewData = null;
            });

            // Try again button
            tryAgainBtn.addEventListener('click', function() {
                resultsSection.classList.add('hidden');
                previewData = null;
            });

            // Load templates for selected type
            async function loadTemplates(type) {
                try {
                    const response = await fetch(`/api/import/templates?type=${type}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    const result = await response.json();

                    if (result.success && result.data.templates) {
                        templates = result.data.templates;
                        document.getElementById('csv-template').textContent = templates.csv || '';
                        document.getElementById('json-template').textContent = templates.json || '';
                        templateContent.classList.remove('hidden');
                        templatePlaceholder.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Failed to load templates:', error);
                }
            }

            // Copy template buttons
            document.querySelectorAll('.copy-template').forEach(btn => {
                btn.addEventListener('click', function() {
                    const format = this.dataset.format;
                    const template = templates[format];
                    if (template) {
                        navigator.clipboard.writeText(template).then(() => {
                            showToast('Template copied to clipboard', 'success');
                        }).catch(() => {
                            showToast('Failed to copy template', 'error');
                        });
                    }
                });
            });

            // Utility functions
            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                const div = document.createElement('div');
                div.textContent = String(text);
                return div.innerHTML;
            }

            function showToast(message, type = 'info') {
                // Simple toast implementation - can be enhanced with a toast library
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300 ${
            type === 'success' ? 'bg-green-600 text-white' :
            type === 'error' ? 'bg-red-600 text-white' :
            'bg-gray-800 text-white'
        }`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            // Drag and drop support for file upload
            const dropZone = document.querySelector('#file-tab .border-dashed');
            if (dropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.add('border-primary-500', 'bg-primary-50',
                            'dark:bg-primary-900/20');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.remove('border-primary-500', 'bg-primary-50',
                            'dark:bg-primary-900/20');
                    }, false);
                });

                dropZone.addEventListener('drop', function(e) {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        fileName.textContent = files[0].name;
                        fileInfo.classList.remove('hidden');
                        updatePreviewButton();
                    }
                }, false);
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/import/index.blade.php ENDPATH**/ ?>