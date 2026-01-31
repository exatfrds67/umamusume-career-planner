

<?php
    $characterName = data_get($character, 'name', 'Unknown Character');
    $characterStats = data_get($character, 'current_stats', []);
    $energyLevel = (int) data_get($character, 'energy_level', 0);
    $moodStatus = data_get($character, 'mood_status', 'normal');
    $scenarioType = data_get($character, 'scenario_type', 'ura_finale');
    $supportCardCount = data_get($character, 'supportCards', collect())->count();
    $characterId = data_get($character, 'id', '');
?>

<?php $__env->startSection('title', 'Training Predictions - ' . $characterName); ?>

<?php $__env->startSection('content'); ?>
    <main class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <a href="<?php echo e(route('training.predictions')); ?>"
                class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Training Predictions
            </a>
        </nav>

        <!-- Character Header -->
        <header class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?php echo e($characterName); ?>

            </h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stats Column -->
                <section aria-labelledby="stats-heading">
                    <h2 id="stats-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Current Stats</h2>
                    <ul class="space-y-2">
                        <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400 capitalize"><?php echo e($stat); ?></span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo e($characterStats[$stat] ?? 0); ?>

                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </section>

                <!-- Status Column -->
                <section aria-labelledby="status-heading">
                    <h2 id="status-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Status</h2>
                    <div class="space-y-4">
                        <div>
                            <span id="energy-label" class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Energy</span>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2" 
                                 role="progressbar" 
                                 aria-labelledby="energy-label" 
                                 aria-valuenow="<?php echo e($energyLevel); ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <div class="bg-green-500 h-2 rounded-full"
                                    :style="{ width: '<?php echo e($energyLevel); ?>%' }"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white mt-1 block"><?php echo e($energyLevel); ?>%</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-400 block">Mood</span>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white capitalize mt-1">
                                <?php echo e($moodStatus); ?>

                            </div>
                        </div>
                    </div>
                </section>

                <!-- Scenario Column -->
                <section aria-labelledby="scenario-heading">
                    <h2 id="scenario-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Scenario</h2>
                    <p class="text-sm text-gray-900 dark:text-white capitalize">
                        <?php echo e(str_replace('_', ' ', $scenarioType)); ?>

                    </p>
                </section>

                <!-- Support Cards Column -->
                <section aria-labelledby="support-cards-heading">
                    <h2 id="support-cards-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Support Cards</h2>
                    <p class="text-sm text-gray-900 dark:text-white">
                        <span class="font-bold"><?php echo e($supportCardCount); ?></span> / 6 equipped
                    </p>
                </section>
            </div>
        </header>

        <!-- Training Predictions -->
        <section id="training-predictions-app" aria-label="Training Predictions"
            data-character-id="<?php echo e($characterId); ?>"
            data-scenario-type="<?php echo e($scenarioType); ?>"
            data-api-url="<?php echo e(route('api.training-predictions.batch')); ?>">
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500" role="status" aria-label="Loading"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading training predictions...</p>
            </div>
        </section>
    </main>

    <?php $__env->startPush('scripts'); ?>
        <script type="module" src="<?php echo e(asset('js/training-predictions.js')); ?>"></script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/training/show.blade.php ENDPATH**/ ?>