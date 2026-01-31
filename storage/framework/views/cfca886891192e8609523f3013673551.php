

<?php $__env->startSection('title', 'Historical Tracking & Benchmarking'); ?>

<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [
        ['label' => 'Analytics & Reports', 'url' => route('reports.index')],
        ['label' => 'Historical Tracking'],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Historical Tracking & Benchmarking</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Analyze your long-term performance trends and compare against community benchmarks.
            </p>
        </div>

        
        <?php if(isset($longTermTrends['error'])): ?>
            <div
                class="mb-6 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Insufficient Data</h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            <?php echo e($longTermTrends['message'] ?? 'Complete more careers to unlock historical tracking features.'); ?>

                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(!isset($longTermTrends['error'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Careers</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                <?php echo e($longTermTrends['trend_summary']['total_careers'] ?? 0); ?>

                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div
                            class="p-3 rounded-full <?php echo e(($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'improving' ? 'bg-green-100 dark:bg-green-900' : (($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'declining' ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-700')); ?>">
                            <?php if(($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'improving'): ?>
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            <?php elseif(($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'declining'): ?>
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                </svg>
                            <?php else: ?>
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overall Trend</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white capitalize">
                                <?php echo e($longTermTrends['trend_summary']['overall_trend'] ?? 'N/A'); ?>

                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                <?php echo e($successRates['overall_success_rate']['success_rate'] ?? 0); ?>%
                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Percentile Rank</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                <?php echo e($benchmarkComparison['percentile_rankings']['overall_percentile'] ?? 'N/A'); ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(!isset($longTermTrends['error'])): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Evolution</h2>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $longTermTrends['performance_evolution'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evolution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Career
                                        #<?php echo e($evolution['career_id']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($evolution['completed_at']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Score:
                                        <?php echo e(number_format($evolution['score'], 1)); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Efficiency:
                                        <?php echo e($evolution['efficiency']); ?>%</p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Success Rate Analysis</h2>
                    <?php if(isset($successRates['overall_success_rate'])): ?>
                        <div class="space-y-4">
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    <?php echo e($successRates['overall_success_rate']['success_rate']); ?>%
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overall Success Rate</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    95% CI: <?php echo e($successRates['overall_success_rate']['confidence_interval_95']['lower']); ?>%
                                    - <?php echo e($successRates['overall_success_rate']['confidence_interval_95']['upper']); ?>%
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <?php $__currentLoopData = $successRates['success_by_scenario'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scenario => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                            <?php echo e(str_replace('_', ' ', $scenario)); ?></p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <?php echo e($data['success_rate']); ?>%</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($data['sample_size']); ?>

                                            careers</p>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Benchmark Comparison</h2>
                    <?php if(isset($benchmarkComparison['benchmark_comparison'])): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = ['efficiency', 'win_rate', 'total_stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($benchmarkComparison['benchmark_comparison'][$metric])): ?>
                                    <?php $data = $benchmarkComparison['benchmark_comparison'][$metric]; ?>
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex justify-between items-center mb-2">
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-white capitalize"><?php echo e(str_replace('_', ' ', $metric)); ?></span>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full <?php echo e($data['status'] === 'above_average' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($data['status'] === 'below_average' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200')); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $data['status']))); ?>

                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">You: <?php echo e($data['user']); ?></span>
                                            <span class="text-gray-500 dark:text-gray-400">Avg:
                                                <?php echo e($data['benchmark']); ?></span>
                                            <span
                                                class="<?php echo e($data['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                                                <?php echo e($data['difference'] >= 0 ? '+' : ''); ?><?php echo e($data['difference']); ?>

                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Improvement Velocity</h2>
                    <?php if(isset($longTermTrends['improvement_velocity'])): ?>
                        <?php $velocity = $longTermTrends['improvement_velocity']; ?>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                                    <p
                                        class="text-2xl font-bold <?php echo e($velocity['velocity'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                                        <?php echo e($velocity['velocity'] >= 0 ? '+' : ''); ?><?php echo e($velocity['velocity']); ?>

                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Points/Career</p>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        <?php echo e($velocity['projected_next_score']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Projected Next</p>
                                </div>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <span class="font-medium">Trend:</span>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $velocity['velocity_trend']))); ?>

                                </p>
                                <?php if($velocity['time_to_mastery']): ?>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                        <span class="font-medium">Est. careers to mastery:</span>
                                        <?php echo e($velocity['time_to_mastery']); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(!isset($longTermTrends['error']) && isset($benchmarkComparison['improvement_areas'])): ?>
            <div
                class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Areas for Improvement</h2>
                <?php if(!empty($benchmarkComparison['improvement_areas'])): ?>
                    <ul class="space-y-2">
                        <?php $__currentLoopData = $benchmarkComparison['improvement_areas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-yellow-500 mr-2 mt-0.5 shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300"><?php echo e($area); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">Great job! No significant improvement areas identified.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        
        <?php if(!isset($longTermTrends['error']) && isset($benchmarkComparison['performance_summary'])): ?>
            <div
                class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Summary</h2>
                <?php $summary = $benchmarkComparison['performance_summary']; ?>

                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-blue-800 dark:text-blue-200"><?php echo e($summary['overall_assessment'] ?? ''); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Strengths</h3>
                        <?php if(!empty($summary['strengths'])): ?>
                            <ul class="space-y-1">
                                <?php $__currentLoopData = $summary['strengths']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $strength): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <?php echo e($strength); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Keep working to develop your strengths!</p>
                        <?php endif; ?>
                    </div>

                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Notable Achievements</h3>
                        <?php if(!empty($summary['notable_achievements'])): ?>
                            <ul class="space-y-1">
                                <?php $__currentLoopData = $summary['notable_achievements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4 text-yellow-500 mr-2" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <?php echo e($achievement); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Complete more careers to unlock
                                achievements!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="mt-8 flex justify-end">
            <button onclick="clearHistoricalCache()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
        </div>
    </div>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/historical/index.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/historical/index.blade.php ENDPATH**/ ?>