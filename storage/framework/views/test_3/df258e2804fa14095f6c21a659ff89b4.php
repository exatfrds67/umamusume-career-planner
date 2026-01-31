

<?php $__env->startSection('title', 'Print Report - ' . ($career->character?->name ?? 'Unknown')); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8 print:p-0">
        
        <div class="mb-6 flex items-center justify-between print:hidden">
            <a href="<?php echo e(route('reports.career', $career)); ?>"
                class="inline-flex items-center text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Report
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Save as PDF
            </button>
        </div>

        
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 print:shadow-none print:border-0 print:p-0">
            
            <div class="text-center mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($pdfData['title']); ?></h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 mt-2"><?php echo e($pdfData['subtitle']); ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                    Generated: <?php echo e(\Carbon\Carbon::parse($pdfData['generated_at'])->format('F j, Y \a\t g:i A')); ?>

                </p>
            </div>

            
            <?php $__currentLoopData = $pdfData['sections']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-8 print:break-inside-avoid">
                    <h2
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <?php echo e($section['title']); ?>

                    </h2>

                    <?php if(is_array($section['content'])): ?>
                        <?php if(isset($section['content'][0]) && is_string($section['content'][0])): ?>
                            
                            <ul class="space-y-2">
                                <?php $__currentLoopData = $section['content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2">
                                        <span class="text-primary-600 dark:text-primary-400">•</span>
                                        <span class="text-gray-700 dark:text-gray-300"><?php echo e($item); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php elseif(isset($section['content'][0]) && is_array($section['content'][0])): ?>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <?php $__currentLoopData = $section['content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($key); ?></p>
                                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <?php echo e($value); ?></p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php $__currentLoopData = $section['content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <span class="text-gray-600 dark:text-gray-400"><?php echo e($key); ?></span>
                                        <?php if(is_array($value)): ?>
                                            <div class="text-right">
                                                <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span
                                                        class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full mr-1 mb-1">
                                                        <?php echo e($item); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <span
                                                class="font-semibold text-gray-900 dark:text-white"><?php echo e($value); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-700 dark:text-gray-300"><?php echo e($section['content']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div
                class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-500 dark:text-gray-500">
                <p>Umamusume Career Planner - Career Report</p>
                <p>This report was automatically generated based on career performance data.</p>
            </div>
        </div>
    </div>

    <?php $__env->startPush('styles'); ?>
        <style>
            @media print {
                body {
                    background: white !important;
                    color: black !important;
                }

                .dark\:bg-gray-800 {
                    background: white !important;
                }

                .dark\:text-white,
                .dark\:text-gray-300,
                .dark\:text-gray-400 {
                    color: black !important;
                }

                .dark\:bg-gray-700\/50 {
                    background: #f3f4f6 !important;
                }
            }
        </style>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/reports/pdf.blade.php ENDPATH**/ ?>