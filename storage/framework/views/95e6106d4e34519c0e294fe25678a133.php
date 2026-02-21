

<?php $__env->startSection('content'); ?>
    <main class="space-y-6">
        <!-- Breadcrumbs & Header -->
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [
                    ['label' => 'Races', 'url' => route('races.index')],
                    ['label' => $race->race_name]
                ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?php echo e($race->race_name); ?>

                    <span class="ml-2 px-2.5 py-0.5 rounded text-sm font-bold 
                        <?php echo e($race->race_grade === 'G1' ? 'bg-yellow-100 text-yellow-800' : 
                           ($race->race_grade === 'G2' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800')); ?>"
                           aria-label="Grade <?php echo e($race->race_grade); ?>">
                        <?php echo e($race->race_grade); ?>

                    </span>
                </h1>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->turn_number): ?>
                <div class="text-sm font-medium text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full"
                     role="status" aria-label="Current Turn">
                    Turn <?php echo e($race->turn_number); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </header>

        <!-- Race Info Card -->
        <section class="card bg-white dark:bg-gray-800 overflow-hidden" aria-labelledby="race-info-title">
            <h2 id="race-info-title" class="sr-only">Race Information</h2>
            <div class="md:flex">
                <div class="p-8 md:w-1/2 bg-linear-to-br from-primary-600 to-primary-800 text-white flex flex-col justify-center">
                    <div class="uppercase tracking-wide text-sm font-semibold text-primary-200">Course Details</div>
                    <div class="mt-2 text-3xl font-extrabold" aria-label="Distance"><?php echo e($race->distance_meters); ?>m</div>
                    <dl class="mt-1 text-xl text-primary-100 flex items-center gap-2">
                         <div class="flex items-center">
                            <dt class="sr-only">Surface</dt>
                            <dd><?php echo e($race->surface); ?></dd>
                         </div>
                         <span aria-hidden="true">•</span>
                         <div class="flex items-center">
                            <dt class="sr-only">Category</dt>
                            <dd><?php echo e($race->distance_category); ?></dd>
                         </div>
                         <span aria-hidden="true">•</span>
                         <div class="flex items-center">
                            <dt class="sr-only">Weather</dt>
                            <dd><?php echo e($race->weather ?? 'Unknown Weather'); ?></dd>
                         </div>
                    </dl>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->race_conditions): ?>
                        <div class="mt-6">
                            <h3 class="text-sm font-bold text-primary-200 uppercase">Conditions</h3>
                            <ul class="mt-2 space-y-1 text-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $race->race_conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <?php echo e($condition); ?>

                                    </li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div class="p-8 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Character Snapshot</h3>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->character): ?>
                        <?php
                            $avatarPath = null;
                            if ($race->character) {
                                $attributes = $race->character->getAttributes();
                                $avatarPath = $attributes['avatar_path'] ?? null;
                            }
                        ?>
                        <article class="flex items-center gap-4 mb-6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avatarPath): ?>
                                <img src="<?php echo e($avatarPath); ?>" alt="<?php echo e($race->character->name); ?>" loading="lazy" decoding="async" class="w-16 h-16 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400" aria-hidden="true">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white"><?php echo e($race->character->name); ?></h4>
                                <dl class="text-xs text-gray-500 flex gap-2">
                                    <div class="flex gap-1">
                                        <dt>Condition:</dt>
                                        <dd><?php echo e($race->character_condition ?? 'Unknown'); ?></dd>
                                    </div>
                                    <span aria-hidden="true">|</span>
                                    <div class="flex gap-1">
                                        <dt>Mood:</dt>
                                        <dd><?php echo e($race->motivation ?? 'Normal'); ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </article>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-5 gap-2 text-center" role="list" aria-label="Character Stats">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php $val = $race->{$stat . '_at_race'} ?? 0; ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2" role="listitem">
                                    <dt class="text-xs text-gray-500 uppercase"><?php echo e($stat); ?></dt>
                                    <dd class="font-bold text-gray-900 dark:text-white"><?php echo e($val); ?></dd>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-gray-500 italic">No character data recorded for this race.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->finish_position): ?>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700" role="status">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">Race Result</h4>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-4xl font-extrabold 
                                    <?php echo e($race->finish_position == 1 ? 'text-yellow-500' : 'text-gray-900 dark:text-white'); ?>"
                                    aria-label="Position <?php echo e($race->finish_position); ?>">
                                    <?php echo e($race->finish_position); ?>

                                </span>
                                <span class="text-gray-500 font-medium text-lg">
                                    <?php echo e(\Illuminate\Support\Number::ordinal($race->finish_position)); ?> Place
                                </span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->finish_time): ?>
                                <p class="text-sm text-gray-500 mt-1">Time: <?php echo e($race->finish_time); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Preparation & Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->preparation_strategy): ?>
                <section class="card bg-white dark:bg-gray-800 h-full" aria-labelledby="prep-strategy-title">
                    <div class="card-header">
                        <h3 id="prep-strategy-title" class="font-medium">Preparation Strategy</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $race->preparation_strategy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $strat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li><?php echo e($strat); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($race->performance_analysis): ?>
                 <section class="card bg-white dark:bg-gray-800 h-full" aria-labelledby="perf-analysis-title">
                    <div class="card-header">
                        <h3 id="perf-analysis-title" class="font-medium">Performance Analysis</h3>
                    </div>
                    <div class="card-body">
                         <div class="prose dark:prose-invert text-sm">
                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($race->performance_analysis)): ?>
                                <ul class="list-disc list-inside">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $race->performance_analysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analysis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <li><?php echo e($analysis); ?></li>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </ul>
                             <?php else: ?>
                                <?php echo e($race->performance_analysis); ?>

                             <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                         </div>
                    </div>
                </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/races/show.blade.php ENDPATH**/ ?>