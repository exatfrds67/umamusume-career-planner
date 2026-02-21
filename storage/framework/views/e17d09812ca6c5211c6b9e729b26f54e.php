

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
        <!-- Breadcrumb -->
        <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [
            ['label' => 'Training Predictions', 'url' => route('training.predictions')],
            ['label' => $characterName],
        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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

        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?php echo e(route('training.predictions')); ?>"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Training Predictions
            </a>
        </div>

        <!-- Character Header -->
        <header class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?php echo e($characterName); ?>

            </h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stats Column -->
                <section aria-labelledby="stats-heading">
                    <h2 id="stats-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Current Stats
                    </h2>
                    <ul class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <li class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <?php if (isset($component)) { $__componentOriginal070c43828614d5473fa15c4afcadb7c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal070c43828614d5473fa15c4afcadb7c7 = $attributes; } ?>
<?php $component = App\View\Components\TypeIcon::resolve(['size' => 'sm'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('type-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\TypeIcon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stat' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stat)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal070c43828614d5473fa15c4afcadb7c7)): ?>
<?php $attributes = $__attributesOriginal070c43828614d5473fa15c4afcadb7c7; ?>
<?php unset($__attributesOriginal070c43828614d5473fa15c4afcadb7c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal070c43828614d5473fa15c4afcadb7c7)): ?>
<?php $component = $__componentOriginal070c43828614d5473fa15c4afcadb7c7; ?>
<?php unset($__componentOriginal070c43828614d5473fa15c4afcadb7c7); ?>
<?php endif; ?>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400 capitalize"><?php echo e($stat); ?></span>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo e($characterStats[$stat] ?? 0); ?>

                                </span>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </section>

                <!-- Status Column -->
                <section aria-labelledby="status-heading">
                    <h2 id="status-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Status</h2>
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-400 block mb-2">Energy</span>
                            <?php if (isset($component)) { $__componentOriginal253604e995c446f07ac8dd33a933f013 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal253604e995c446f07ac8dd33a933f013 = $attributes; } ?>
<?php $component = App\View\Components\EnergyGauge::resolve(['size' => 'sm'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('energy-gauge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\EnergyGauge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($energyLevel)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal253604e995c446f07ac8dd33a933f013)): ?>
<?php $attributes = $__attributesOriginal253604e995c446f07ac8dd33a933f013; ?>
<?php unset($__attributesOriginal253604e995c446f07ac8dd33a933f013); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal253604e995c446f07ac8dd33a933f013)): ?>
<?php $component = $__componentOriginal253604e995c446f07ac8dd33a933f013; ?>
<?php unset($__componentOriginal253604e995c446f07ac8dd33a933f013); ?>
<?php endif; ?>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-400 block mb-2">Mood</span>
                            <?php if (isset($component)) { $__componentOriginaldb7206c0cc2f25560ae425ce53df19bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc = $attributes; } ?>
<?php $component = App\View\Components\ConditionBadge::resolve(['condition' => $moodStatus] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ConditionBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc)): ?>
<?php $attributes = $__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc; ?>
<?php unset($__attributesOriginaldb7206c0cc2f25560ae425ce53df19bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldb7206c0cc2f25560ae425ce53df19bc)): ?>
<?php $component = $__componentOriginaldb7206c0cc2f25560ae425ce53df19bc; ?>
<?php unset($__componentOriginaldb7206c0cc2f25560ae425ce53df19bc); ?>
<?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Scenario Column -->
                <section aria-labelledby="scenario-heading">
                    <h2 id="scenario-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Scenario
                    </h2>
                    <p class="text-sm text-gray-900 dark:text-white capitalize">
                        <?php echo e(str_replace('_', ' ', $scenarioType)); ?>

                    </p>
                </section>

                <!-- Support Cards Column -->
                <section aria-labelledby="support-cards-heading">
                    <h2 id="support-cards-heading" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Support Cards</h2>
                    <p class="text-sm text-gray-900 dark:text-white">
                        <span class="font-bold"><?php echo e($supportCardCount); ?></span> / 6 equipped
                    </p>
                </section>
            </div>
        </header>

        <!-- Training Predictions -->
        <section id="training-predictions-app" aria-label="Training Predictions" data-character-id="<?php echo e($characterId); ?>"
            data-scenario-type="<?php echo e($scenarioType); ?>" data-api-url="<?php echo e(route('api.training-predictions.batch')); ?>">
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500" role="status"
                    aria-label="Loading"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading training predictions...</p>
            </div>
        </section>
    </main>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/training/show.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/training/show.blade.php ENDPATH**/ ?>