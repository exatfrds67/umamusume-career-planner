

<?php $__env->startSection('title', 'Performance Dashboard - APM'); ?>

<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [
        ['label' => 'Analytics & Reports', 'url' => route('reports.index')],
        ['label' => 'Performance Dashboard'],
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

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" x-data="apmDashboard()">
        
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Performance Dashboard
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Real-time application performance monitoring
                        </p>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <!-- Time Window Selector (SPEC-008) -->
                        <div class="flex items-center gap-2">
                            <label for="time-window" class="text-sm text-gray-500 dark:text-gray-400">Window:</label>
                            <select id="time-window" x-model="timeWindow" @change="refreshDashboard()"
                                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="5m">5 min</option>
                                <option value="15m">15 min</option>
                                <option value="1h">1 hour</option>
                                <option value="6h">6 hours</option>
                                <option value="24h">24 hours</option>
                            </select>
                        </div>

                        <!-- Live Polling Toggle (SPEC-008) -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Live:</span>
                            <button @click="togglePolling()"
                                :class="isPolling ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                role="switch" :aria-checked="isPolling">
                                <span :class="isPolling ? 'translate-x-5' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span x-show="isPolling" class="text-xs text-green-500 flex items-center gap-1">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <span x-text="pollInterval/1000 + 's'"></span>
                            </span>
                        </div>

                        <!-- Polling Interval Selector -->
                        <div x-show="isPolling" class="flex items-center gap-2">
                            <label for="poll-interval" class="text-sm text-gray-500 dark:text-gray-400">Interval:</label>
                            <select id="poll-interval" x-model.number="pollInterval" @change="restartPolling()"
                                class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="5000">5s</option>
                                <option value="10000">10s</option>
                                <option value="30000">30s</option>
                                <option value="60000">60s</option>
                            </select>
                        </div>

                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Last updated: <span id="last-updated" x-text="lastUpdated"><?php echo e(now()->format('H:i:s')); ?></span>
                        </span>
                        <button @click="refreshDashboard()" :disabled="isLoading"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span x-text="isLoading ? 'Refreshing...' : 'Refresh'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Application Health Score</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Overall system health based on key
                                    metrics</p>
                            </div>
                            <div class="text-right">
                                <div id="health-score"
                                    class="text-5xl font-bold <?php echo e($data['health_score']['status'] === 'excellent' ? 'text-green-500' : ($data['health_score']['status'] === 'good' ? 'text-blue-500' : ($data['health_score']['status'] === 'fair' ? 'text-yellow-500' : 'text-red-500'))); ?>">
                                    <?php echo e($data['health_score']['score']); ?>

                                </div>
                                <div id="health-status"
                                    class="text-sm font-medium uppercase <?php echo e($data['health_score']['status'] === 'excellent' ? 'text-green-500' : ($data['health_score']['status'] === 'good' ? 'text-blue-500' : ($data['health_score']['status'] === 'fair' ? 'text-yellow-500' : 'text-red-500'))); ?>">
                                    <?php echo e($data['health_score']['status']); ?>

                                </div>
                            </div>
                        </div>

                        
                        <div class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <?php $__currentLoopData = $data['health_score']['components']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        <?php echo e(str_replace('_', ' ', $name)); ?>

                                    </div>
                                    <div
                                        class="mt-1 text-2xl font-semibold <?php echo e($component['status'] === 'excellent' ? 'text-green-500' : ($component['status'] === 'good' ? 'text-blue-500' : ($component['status'] === 'warning' ? 'text-yellow-500' : 'text-red-500'))); ?>">
                                        <?php echo e($component['score']); ?>

                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo e(is_numeric($component['value']) ? number_format($component['value'], 2) : $component['value']); ?>

                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Requests
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(number_format($data['overview']['total_requests'])); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Response
                                        Time</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(number_format($data['overview']['avg_response_time_ms'], 2)); ?>ms</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 <?php echo e($data['overview']['error_rate'] > 5 ? 'text-red-400' : 'text-gray-400'); ?>"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Error Rate
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold <?php echo e($data['overview']['error_rate'] > 5 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                        <?php echo e(number_format($data['overview']['error_rate'], 2)); ?>%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 <?php echo e($data['overview']['cache_hit_rate'] < 70 ? 'text-yellow-400' : 'text-gray-400'); ?>"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Cache Hit
                                        Rate
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold <?php echo e($data['overview']['cache_hit_rate'] < 70 ? 'text-yellow-500' : 'text-gray-900 dark:text-white'); ?>">
                                        <?php echo e(number_format($data['overview']['cache_hit_rate'], 2)); ?>%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Database Performance</h3>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Queries</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['database']['total_queries'])); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slow Queries</dt>
                                <dd
                                    class="mt-1 text-2xl font-semibold <?php echo e($data['database']['slow_queries'] > 10 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                    <?php echo e($data['database']['slow_queries']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Query Time</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['database']['avg_query_time_ms'], 2)); ?>ms</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Hit Rate</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['database']['cache_hit_rate'], 2)); ?>%</dd>
                            </div>
                        </div>
                        <?php if($data['database']['recommendations_count'] > 0): ?>
                            <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    <strong><?php echo e($data['database']['recommendations_count']); ?></strong> index recommendations
                                    available
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Cache Performance</h3>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd
                                    class="mt-1 text-2xl font-semibold <?php echo e($data['cache']['healthy'] ? 'text-green-500' : 'text-red-500'); ?>">
                                    <?php echo e($data['cache']['healthy'] ? 'Healthy' : 'Unhealthy'); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Latency</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['cache']['latency_ms'], 2)); ?>ms</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Used</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($data['cache']['memory']['used']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Usage</dt>
                                <dd
                                    class="mt-1 text-2xl font-semibold <?php echo e($data['cache']['memory']['usage_percent'] > 80 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                    <?php echo e(number_format($data['cache']['memory']['usage_percent'], 1)); ?>%</dd>
                            </div>
                        </div>
                        <?php if(!empty($data['cache']['recommendations'])): ?>
                            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    <strong><?php echo e(count($data['cache']['recommendations'])); ?></strong> optimization
                                    recommendations
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">API Performance</h3>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">P95 Response Time</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['api']['overview']['p95_response_time_ms'], 2)); ?>ms</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">P99 Response Time</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['api']['overview']['p99_response_time_ms'], 2)); ?>ms</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slow Request Rate</dt>
                                <dd
                                    class="mt-1 text-2xl font-semibold <?php echo e($data['api']['overview']['slow_request_rate'] > 10 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                    <?php echo e(number_format($data['api']['overview']['slow_request_rate'], 2)); ?>%</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Requests/min</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['api']['overview']['requests_per_minute'], 2)); ?></dd>
                            </div>
                        </div>
                        <?php if(!empty($data['api']['bottlenecks'])): ?>
                            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-md">
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    <strong><?php echo e(count($data['api']['bottlenecks'])); ?></strong> bottlenecks identified
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">System Resources</h3>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Usage</dt>
                                <dd
                                    class="mt-1 text-2xl font-semibold <?php echo e($data['system']['memory']['usage_percent'] > 80 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                    <?php echo e(number_format($data['system']['memory']['usage_percent'], 1)); ?>%</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Used</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($data['system']['memory']['current_mb'], 1)); ?>MB</dd>
                            </div>
                            <?php if($data['system']['disk']): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Disk Usage</dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold <?php echo e($data['system']['disk']['usage_percent'] > 80 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">
                                        <?php echo e(number_format($data['system']['disk']['usage_percent'], 1)); ?>%</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Disk Free</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(number_format($data['system']['disk']['free_gb'], 1)); ?>GB</dd>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            PHP <?php echo e($data['system']['php']['version']); ?> | Memory Limit:
                            <?php echo e($data['system']['php']['memory_limit']); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Recent Alerts</h3>
                        <?php if(empty($data['alerts'])): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No recent alerts</p>
                        <?php else: ?>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <?php $__currentLoopData = $data['alerts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="flex items-start p-3 rounded-md <?php echo e($alert['severity'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20' : ($alert['severity'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-blue-50 dark:bg-blue-900/20')); ?>">
                                        <div class="shrink-0">
                                            <?php if($alert['severity'] === 'critical'): ?>
                                                <svg class="h-5 w-5 text-red-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            <?php elseif($alert['severity'] === 'warning'): ?>
                                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            <?php else: ?>
                                                <svg class="h-5 w-5 text-blue-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p
                                                class="text-sm font-medium <?php echo e($alert['severity'] === 'critical' ? 'text-red-800 dark:text-red-200' : ($alert['severity'] === 'warning' ? 'text-yellow-800 dark:text-yellow-200' : 'text-blue-800 dark:text-blue-200')); ?>">
                                                <?php echo e($alert['message']); ?>

                                            </p>
                                            <p
                                                class="mt-1 text-xs <?php echo e($alert['severity'] === 'critical' ? 'text-red-600 dark:text-red-300' : ($alert['severity'] === 'warning' ? 'text-yellow-600 dark:text-yellow-300' : 'text-blue-600 dark:text-blue-300')); ?>">
                                                <?php echo e(\Carbon\Carbon::parse($alert['timestamp'])->diffForHumans()); ?>

                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Performance
                            Regressions</h3>
                        <?php if(empty($data['regressions'])): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No performance regressions detected</p>
                        <?php else: ?>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <?php $__currentLoopData = $data['regressions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regression): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-start p-3 rounded-md bg-orange-50 dark:bg-orange-900/20">
                                        <div class="shrink-0">
                                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $regression['metric']))); ?>:
                                                <?php echo e(number_format($regression['deviation_percent'], 1)); ?>% deviation
                                            </p>
                                            <p class="text-xs text-orange-600 dark:text-orange-300">
                                                Baseline: <?php echo e(number_format($regression['baseline'], 2)); ?> → Current:
                                                <?php echo e(number_format($regression['current'], 2)); ?>

                                            </p>
                                            <p class="mt-1 text-xs text-orange-600 dark:text-orange-300">
                                                <?php echo e(\Carbon\Carbon::parse($regression['detected_at'])->diffForHumans()); ?> |
                                                Status: <?php echo e(ucfirst($regression['status'])); ?>

                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    
    <script>
        window.apmDashboardData = {
            lastUpdated: '<?php echo e(now()->format('H:i:s')); ?>'
        };
    </script>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/performance/dashboard.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/performance/dashboard.blade.php ENDPATH**/ ?>