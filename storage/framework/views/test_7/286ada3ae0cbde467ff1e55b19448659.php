

<?php $__env->startSection('title', 'OCR Extraction Results'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            OCR Extraction Results
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Review and correct extracted data before importing.
                        </p>
                    </div>
                    <a href="<?php echo e(route('ocr.upload')); ?>"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                        Back to Upload
                    </a>
                </div>
            </div>

            <?php if($extraction): ?>
                <!-- Extraction Info Card -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Screen Type</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e(ucfirst(str_replace('_', ' ', $extraction->data_type))); ?>

                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Confidence Score</p>
                            <div class="flex items-center">
                                <p
                                    class="text-lg font-semibold <?php echo e($extraction->confidence_score >= 0.7 ? 'text-green-600' : ($extraction->confidence_score >= 0.5 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                    <?php echo e(number_format($extraction->confidence_score * 100, 1)); ?>%
                                </p>
                                <?php if($extraction->confidence_score < 0.7): ?>
                                    <span class="ml-2 text-xs text-yellow-600 dark:text-yellow-400">
                                        ⚠️ Review recommended
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Processed</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e($extraction->processed_at?->diffForHumans() ?? 'Processing...'); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <!-- Screenshot Preview -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Screenshot Preview</h2>
                    <div class="flex justify-center">
                        <img src="<?php echo e(Storage::url($extraction->image_path)); ?>" alt="Uploaded screenshot"
                            loading="lazy" decoding="async"
                            class="max-w-full h-auto rounded-lg border border-gray-300 dark:border-gray-600"
                            style="max-height: 500px;">
                    </div>
                </div>

                <!-- Extracted Data Form -->
                <form id="correction-form" method="POST" action="<?php echo e(route('ocr.import', $extraction->id)); ?>">
                    <?php echo csrf_field(); ?>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Extracted Data
                            <span class="text-sm font-normal text-gray-600 dark:text-gray-400">(Editable)</span>
                        </h2>

                        <?php if($extraction->data_type === 'character_stats'): ?>
                            <?php echo $__env->make('ocr.partials.character-stats-form', [
                                'data' => $extraction->parsed_data,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php elseif($extraction->data_type === 'training_session'): ?>
                            <?php echo $__env->make('ocr.partials.training-session-form', [
                                'data' => $extraction->parsed_data,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php elseif($extraction->data_type === 'race_result'): ?>
                            <?php echo $__env->make('ocr.partials.race-result-form', ['data' => $extraction->parsed_data], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php elseif($extraction->data_type === 'skill_list'): ?>
                            <?php echo $__env->make('ocr.partials.skill-list-form', ['data' => $extraction->parsed_data], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <p>Unknown screen type: <?php echo e($extraction->data_type); ?></p>
                                <p class="text-sm mt-2">Cannot display extraction form.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Raw Text (Collapsible) -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <button type="button" id="toggle-raw-text"
                            class="w-full flex items-center justify-between text-left focus:outline-none"
                            aria-expanded="false" aria-controls="raw-text-content">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Raw OCR Text</h2>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div id="raw-text-content" class="hidden mt-4">
                            <pre
                                class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 overflow-x-auto whitespace-pre-wrap"><?php echo e($extraction->extracted_text); ?></pre>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <button type="submit" name="action" value="import"
                                class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors font-medium">
                                Import Data
                            </button>
                            <button type="submit" name="action" value="save_draft"
                                class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors font-medium">
                                Save as Draft
                            </button>
                        </div>
                        <button type="button"
                            onclick="if(confirm('Are you sure you want to discard this extraction?')) { window.location.href='<?php echo e(route('ocr.upload')); ?>'; }"
                            class="px-6 py-3 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 focus:outline-none transition-colors font-medium">
                            Discard
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No extraction found.</p>
                    <a href="<?php echo e(route('ocr.upload')); ?>"
                        class="inline-block px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                        Upload Screenshot
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle raw text visibility
                const toggleButton = document.getElementById('toggle-raw-text');
                const rawTextContent = document.getElementById('raw-text-content');

                if (toggleButton && rawTextContent) {
                    toggleButton.addEventListener('click', function() {
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        this.setAttribute('aria-expanded', !isExpanded);
                        rawTextContent.classList.toggle('hidden');

                        const icon = this.querySelector('svg');
                        if (icon) {
                            icon.classList.toggle('rotate-180');
                        }
                    });
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/ocr/results.blade.php ENDPATH**/ ?>