

<?php $__env->startSection('title', 'Training Predictions'); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Training', 'url' => route('training.predictions')], ['label' => 'Predictions']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">Training Predictions</h1>
                    <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300">
                        AI-powered training recommendations with multi-agent analysis
                    </p>
                </div>
                <?php if($selectedCharacter): ?>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshPredictions()"
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2"
                            aria-label="Refresh predictions">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Refresh</span>
                        </button>
                        <button onclick="clearCache()"
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2"
                            aria-label="Clear cache">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Clear Cache</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Character Selection -->
        <section class="card rounded-xl p-6 mb-6 animate-fade-in-delay-1">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Select Character</h2>
            <form method="GET" action="<?php echo e(route('training.predictions')); ?>">
                <div>
                    <label for="character_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Character
                    </label>
                    <div class="relative">
                        <select id="character_id" name="character_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white appearance-none"
                            onchange="this.form.submit()">
                            <option value="">-- Select a character --</option>
                            <?php $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $char): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($char->id); ?>"
                                    <?php echo e($selectedCharacter && $selectedCharacter->id === $char->id ? 'selected' : ''); ?>>
                                    <?php echo e($char->name); ?> (<?php echo e(ucfirst($char->scenario_type)); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <?php if($selectedCharacter): ?>
            <?php echo $__env->make('training.partials.status-bar', ['character' => $selectedCharacter], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('training.partials.character-overview', ['character' => $selectedCharacter], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('training.partials.ai-advisor-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('training.partials.predictions-grid', ['character' => $selectedCharacter], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <div class="card rounded-xl p-12 text-center animate-fade-in-delay-2">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Character Selected</h3>
                <p class="text-gray-700 dark:text-gray-300">Please select a character to view training predictions</p>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/training/predictions.js']); ?>
    <script>
        window.refreshPredictions = async function() {
            const appEl = document.getElementById('training-predictions-app');
            if (!appEl) return;
            const characterId = appEl.dataset.characterId;
            const apiUrl = appEl.dataset.apiUrl;
            document.getElementById('predictions-loading')?.classList.remove('hidden');
            document.getElementById('predictions-grid')?.classList.add('hidden');
            document.getElementById('predictions-error')?.classList.add('hidden');
            try {
                const {
                    fetchPredictionsWithRetry,
                    updatePredictionsUI
                } = await import('/resources/js/pages/training/predictions.js');
                const predictions = await fetchPredictionsWithRetry(apiUrl, characterId);
                updatePredictionsUI(predictions);
            } catch (error) {
                const {
                    showPredictionsError
                } = await import('/resources/js/pages/training/predictions.js');
                showPredictionsError(error.message);
            }
        };

        window.clearCache = async function() {
            const appEl = document.getElementById('training-predictions-app');
            if (!appEl) return;
            const characterId = appEl.dataset.characterId;
            try {
                const response = await fetch(`/api/training-predictions/cache/${characterId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    }
                });
                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            message: 'Cache cleared successfully'
                        }
                    }));
                    await window.refreshPredictions();
                } else {
                    throw new Error('Failed to clear cache');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to clear cache: ' + error.message
                    }
                }));
            }
        };

        window.showAIDetails = function() {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'info',
                    message: 'AI analysis details coming soon'
                }
            }));
        };
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/training/predictions.blade.php ENDPATH**/ ?>