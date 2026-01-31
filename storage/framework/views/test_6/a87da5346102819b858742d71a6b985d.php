

<?php $__env->startSection('title', 'Data Export'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Export</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Export your career data in multiple formats including JSON, CSV, and PDF.
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
                    Step 1: Select Export Type
                </span>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <?php $__currentLoopData = $exportTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                        class="export-type-btn p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
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

                                <?php case ('full_backup'): ?>
                                    💾
                                <?php break; ?>
                            <?php endswitch; ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($label); ?></span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <input type="hidden" id="export-type" name="export_type" value="">
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    Step 2: Select Format
                </span>
            </h2>

            <div class="grid grid-cols-3 gap-4">
                <?php $__currentLoopData = $supportedFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                        class="format-btn p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        data-format="<?php echo e($format); ?>" aria-pressed="false">
                        <div class="text-2xl mb-2">
                            <?php switch($format):
                                case ('json'): ?>
                                    📄
                                <?php break; ?>

                                <?php case ('csv'): ?>
                                    📊
                                <?php break; ?>

                                <?php case ('pdf'): ?>
                                    📑
                                <?php break; ?>
                            <?php endswitch; ?>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 uppercase"><?php echo e($format); ?></span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <?php switch($format):
                                case ('json'): ?>
                                    Structured data format
                                <?php break; ?>

                                <?php case ('csv'): ?>
                                    Spreadsheet compatible
                                <?php break; ?>

                                <?php case ('pdf'): ?>
                                    Printable report
                                <?php break; ?>
                            <?php endswitch; ?>
                        </p>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <input type="hidden" id="export-format" name="format" value="json">
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Step 3: Apply Filters (Optional)
                </span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div>
                    <label for="filter-scenario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Scenario Type
                    </label>
                    <select id="filter-scenario" name="filters[scenario_type]"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Scenarios</option>
                        <option value="ura_finale">URA Finale</option>
                        <option value="unity_cup">Unity Cup</option>
                    </select>
                </div>

                
                <div>
                    <label for="filter-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status
                    </label>
                    <select id="filter-status" name="filters[status]"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="abandoned">Abandoned</option>
                    </select>
                </div>

                
                <div>
                    <label for="filter-date-from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date Range
                    </label>
                    <div class="flex gap-2">
                        <input type="date" id="filter-date-from" name="filters[date_from]"
                            class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="From">
                        <input type="date" id="filter-date-to" name="filters[date_to]"
                            class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="To">
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex justify-end gap-4 mb-6">
            <button type="button" id="preview-btn"
                class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                </svg>
                Preview
            </button>
            <button type="button" id="export-btn"
                class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export Data
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
                        Export Preview
                    </span>
                </h2>

                
                <div id="preview-summary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Records</div>
                        <div id="total-records" class="text-2xl font-bold text-gray-900 dark:text-white">0</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Export Type</div>
                        <div id="preview-type" class="text-2xl font-bold text-blue-700 dark:text-blue-300">-</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-sm text-green-600 dark:text-green-400">Format</div>
                        <div id="preview-format" class="text-2xl font-bold text-green-700 dark:text-green-300 uppercase">-
                        </div>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="preview-headers"></tr>
                        </thead>
                        <tbody id="preview-body"
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                    </table>
                </div>

                <div id="preview-more" class="hidden mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Showing first 10 records. Full export will include all <span id="preview-total">0</span> records.
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Export History
                </span>
            </h2>

            <div id="export-history" class="space-y-2">
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Loading export history...</p>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Scheduled Exports
                    </span>
                </h2>
                <button type="button" id="schedule-btn"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Schedule Export
                </button>
            </div>

            <div id="scheduled-exports" class="space-y-2">
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No scheduled exports.</p>
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
                    Export Templates
                </span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors cursor-pointer template-card"
                        data-template="<?php echo e($key); ?>">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-1"><?php echo e($template['name']); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($template['description']); ?></p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <?php $__currentLoopData = $template['types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span
                                    class="px-2 py-0.5 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded">
                                    <?php echo e($type); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div id="schedule-modal" class="hidden fixed inset-0 bg-black/50 z-50" style="display: none;">
        <div class="fixed inset-0 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Schedule Automated Export</h3>

                <form id="schedule-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Export Type</label>
                        <select id="schedule-type" name="export_type" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <?php $__currentLoopData = $exportTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Format</label>
                        <select id="schedule-format" name="format"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <?php $__currentLoopData = $supportedFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($format); ?>"><?php echo e(strtoupper($format)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequency</label>
                        <select id="schedule-frequency" name="frequency" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div id="day-of-week-container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Day of Week</label>
                        <select id="schedule-day-of-week" name="day_of_week"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                        <input type="time" id="schedule-time" name="time" value="00:00"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="schedule-email" name="email_notification"
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <label for="schedule-email" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Send email notification when export is ready
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" id="cancel-schedule-btn"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Schedule Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // State
                let selectedType = null;
                let selectedFormat = 'json';

                // Elements
                const exportTypeBtns = document.querySelectorAll('.export-type-btn');
                const formatBtns = document.querySelectorAll('.format-btn');
                const exportTypeInput = document.getElementById('export-type');
                const exportFormatInput = document.getElementById('export-format');
                const previewBtn = document.getElementById('preview-btn');
                const exportBtn = document.getElementById('export-btn');
                const previewSection = document.getElementById('preview-section');
                const scheduleBtn = document.getElementById('schedule-btn');
                const scheduleModal = document.getElementById('schedule-modal');
                const cancelScheduleBtn = document.getElementById('cancel-schedule-btn');
                const scheduleForm = document.getElementById('schedule-form');
                const scheduleFrequency = document.getElementById('schedule-frequency');
                const dayOfWeekContainer = document.getElementById('day-of-week-container');
                const templateCards = document.querySelectorAll('.template-card');

                // Export type selection
                exportTypeBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        exportTypeBtns.forEach(b => {
                            b.classList.remove('border-primary-500', 'bg-primary-50',
                                'dark:bg-primary-900/20');
                            b.setAttribute('aria-pressed', 'false');
                        });
                        this.classList.add('border-primary-500', 'bg-primary-50',
                            'dark:bg-primary-900/20');
                        this.setAttribute('aria-pressed', 'true');
                        selectedType = this.dataset.type;
                        exportTypeInput.value = selectedType;
                        updateButtons();
                    });
                });

                // Format selection
                formatBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        formatBtns.forEach(b => {
                            b.classList.remove('border-primary-500', 'bg-primary-50',
                                'dark:bg-primary-900/20');
                            b.setAttribute('aria-pressed', 'false');
                        });
                        this.classList.add('border-primary-500', 'bg-primary-50',
                            'dark:bg-primary-900/20');
                        this.setAttribute('aria-pressed', 'true');
                        selectedFormat = this.dataset.format;
                        exportFormatInput.value = selectedFormat;
                    });
                });

                // Select JSON by default
                document.querySelector('.format-btn[data-format="json"]')?.click();

                // Update button states
                function updateButtons() {
                    const hasType = selectedType !== null;
                    previewBtn.disabled = !hasType;
                    exportBtn.disabled = !hasType;
                }

                // Get filters
                function getFilters() {
                    const filters = {};
                    const scenario = document.getElementById('filter-scenario').value;
                    const status = document.getElementById('filter-status').value;
                    const dateFrom = document.getElementById('filter-date-from').value;
                    const dateTo = document.getElementById('filter-date-to').value;

                    if (scenario) filters.scenario_type = scenario;
                    if (status) filters.status = status;
                    if (dateFrom) filters.date_from = dateFrom;
                    if (dateTo) filters.date_to = dateTo;

                    return filters;
                }

                // Preview button click
                previewBtn.addEventListener('click', async function() {
                    if (!selectedType) {
                        showToast('Please select an export type', 'error');
                        return;
                    }

                    previewBtn.disabled = true;
                    previewBtn.innerHTML =
                        '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';

                    try {
                        const response = await fetch('/api/export/preview', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                            },
                            body: JSON.stringify({
                                export_type: selectedType,
                                filters: getFilters(),
                            }),
                        });

                        const result = await response.json();

                        if (result.success) {
                            displayPreview(result.data);
                            previewSection.classList.remove('hidden');
                        } else {
                            showToast(result.message || 'Preview failed', 'error');
                        }
                    } catch (error) {
                        console.error('Preview error:', error);
                        showToast('An error occurred during preview', 'error');
                    } finally {
                        previewBtn.disabled = false;
                        previewBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Preview';
                    }
                });

                // Display preview
                function displayPreview(data) {
                    document.getElementById('total-records').textContent = data.total_records;
                    document.getElementById('preview-type').textContent = selectedType.replace('_', ' ');
                    document.getElementById('preview-format').textContent = selectedFormat;

                    const headers = document.getElementById('preview-headers');
                    const body = document.getElementById('preview-body');
                    headers.innerHTML = '';
                    body.innerHTML = '';

                    if (data.preview && data.preview.length > 0) {
                        // Generate headers
                        const fieldMapping = data.field_mapping || {};
                        const keys = Object.keys(data.preview[0]).slice(0, 6);
                        keys.forEach(key => {
                            const th = document.createElement('th');
                            th.className =
                                'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider';
                            th.textContent = fieldMapping[key]?.label || key.replace(/_/g, ' ');
                            headers.appendChild(th);
                        });

                        // Generate rows
                        data.preview.forEach(item => {
                            const tr = document.createElement('tr');
                            keys.forEach(key => {
                                const td = document.createElement('td');
                                td.className = 'px-4 py-3 text-sm text-gray-900 dark:text-white';
                                let value = item[key];
                                if (typeof value === 'object' && value !== null) {
                                    value = JSON.stringify(value).substring(0, 50) + '...';
                                }
                                td.textContent = value ?? '-';
                                tr.appendChild(td);
                            });
                            body.appendChild(tr);
                        });

                        if (data.total_records > data.preview_records) {
                            document.getElementById('preview-more').classList.remove('hidden');
                            document.getElementById('preview-total').textContent = data.total_records;
                        } else {
                            document.getElementById('preview-more').classList.add('hidden');
                        }
                    }
                }

                // Export button click
                exportBtn.addEventListener('click', async function() {
                    if (!selectedType) {
                        showToast('Please select an export type', 'error');
                        return;
                    }

                    exportBtn.disabled = true;
                    exportBtn.innerHTML =
                        '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Exporting...';

                    try {
                        const response = await fetch('/api/export/generate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                            },
                            body: JSON.stringify({
                                export_type: selectedType,
                                format: selectedFormat,
                                filters: getFilters(),
                                save_to_file: false,
                            }),
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Download the content
                            downloadContent(result.data.content, selectedType, selectedFormat);
                            showToast(result.message, 'success');
                            loadExportHistory();
                        } else {
                            showToast(result.message || 'Export failed', 'error');
                        }
                    } catch (error) {
                        console.error('Export error:', error);
                        showToast('An error occurred during export', 'error');
                    } finally {
                        exportBtn.disabled = false;
                        exportBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>Export Data';
                    }
                });

                // Download content
                function downloadContent(content, type, format) {
                    const mimeTypes = {
                        json: 'application/json',
                        csv: 'text/csv',
                        pdf: 'text/html'
                    };
                    const blob = new Blob([content], {
                        type: mimeTypes[format] || 'text/plain'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `export_${type}_${new Date().toISOString().slice(0,10)}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }

                // Schedule modal
                scheduleBtn.addEventListener('click', () => scheduleModal.classList.remove('hidden'));
                cancelScheduleBtn.addEventListener('click', () => scheduleModal.classList.add('hidden'));
                scheduleModal.addEventListener('click', (e) => {
                    if (e.target === scheduleModal) scheduleModal.classList.add('hidden');
                });

                // Frequency change
                scheduleFrequency.addEventListener('change', function() {
                    dayOfWeekContainer.classList.toggle('hidden', this.value !== 'weekly');
                });

                // Schedule form submit
                scheduleForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    data.email_notification = formData.has('email_notification');

                    try {
                        const response = await fetch('/api/export/schedule', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                            },
                            body: JSON.stringify(data),
                        });

                        const result = await response.json();
                        if (result.success) {
                            showToast(result.message, 'success');
                            scheduleModal.classList.add('hidden');
                            loadScheduledExports();
                        } else {
                            showToast(result.message || 'Failed to schedule export', 'error');
                        }
                    } catch (error) {
                        console.error('Schedule error:', error);
                        showToast('An error occurred', 'error');
                    }
                });

                // Template cards
                templateCards.forEach(card => {
                    card.addEventListener('click', function() {
                        const template = this.dataset.template;
                        // Auto-select the appropriate export type based on template
                        const templateConfig = <?php echo json_encode($templates, 15, 512) ?>;
                        if (templateConfig[template] && templateConfig[template].types.length > 0) {
                            const type = templateConfig[template].types[0];
                            document.querySelector(`.export-type-btn[data-type="${type}"]`)?.click();
                        }
                    });
                });

                // Load export history
                async function loadExportHistory() {
                    try {
                        const response = await fetch('/api/export/history', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                        });
                        const result = await response.json();
                        const container = document.getElementById('export-history');

                        if (result.success && result.data.history.length > 0) {
                            container.innerHTML = result.data.history.map(item => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">${item.export_type}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">${item.format.toUpperCase()}</span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">${new Date(item.created_at).toLocaleString()}</span>
                    </div>
                `).join('');
                        } else {
                            container.innerHTML =
                                '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No export history.</p>';
                        }
                    } catch (error) {
                        console.error('Load history error:', error);
                    }
                }

                // Load scheduled exports
                async function loadScheduledExports() {
                    try {
                        const response = await fetch('/api/export/schedules', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                        });
                        const result = await response.json();
                        const container = document.getElementById('scheduled-exports');

                        if (result.success && result.data.schedules.length > 0) {
                            container.innerHTML = result.data.schedules.map(item => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">${item.config.export_type}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">${item.config.frequency}</span>
                        </div>
                        <button onclick="deleteSchedule('${item.schedule_id}')" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                `).join('');
                        } else {
                            container.innerHTML =
                                '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No scheduled exports.</p>';
                        }
                    } catch (error) {
                        console.error('Load schedules error:', error);
                    }
                }

                // Delete schedule
                window.deleteSchedule = async function(scheduleId) {
                    if (!confirm('Are you sure you want to delete this scheduled export?')) return;
                    try {
                        const response = await fetch(`/api/export/schedule/${scheduleId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                        });
                        const result = await response.json();
                        if (result.success) {
                            showToast('Scheduled export deleted', 'success');
                            loadScheduledExports();
                        } else {
                            showToast(result.message || 'Failed to delete', 'error');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        showToast('An error occurred', 'error');
                    }
                };

                // Toast notification
                function showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    toast.className =
                        `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white`;
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }

                // Initial load
                loadExportHistory();
                loadScheduledExports();
            });
        </script>
    <?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/export/index.blade.php ENDPATH**/ ?>