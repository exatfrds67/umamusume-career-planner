

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 animate-fade-in">
        <!-- Page Header with Welcome Message -->
        <div class="glass-card rounded-xl p-6 md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 sm:truncate sm:text-3xl sm:tracking-tight">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm">
                    <?php if($hasCharacters && $selectedCharacter): ?>
                        Managing: <span
                            class="font-medium text-primary-600 dark:text-primary-400"><?php echo e($selectedCharacter->name); ?></span>
                    <?php else: ?>
                        Welcome! Create your first character to get started.
                    <?php endif; ?>
                </p>
            </div>
            <div class="mt-4 flex items-center gap-4 md:ml-4 md:mt-0">
                <?php if($hasCharacters): ?>
                    <!-- Character Selector -->
                    <div class="relative">
                        <label for="character-selector" class="sr-only">Select character</label>
                        <select id="character-selector"
                            class="form-select rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm pr-10 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            aria-label="Select character"
                            data-url="<?php echo e(route('dashboard')); ?>"
                            onchange="window.location.href = this.dataset.url + '?character=' + this.value;">
                            <?php $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $character): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($character->id); ?>"
                                    <?php echo e($selectedCharacter && $selectedCharacter->id === $character->id ? 'selected' : ''); ?>>
                                    <?php echo e($character->name); ?> - <?php echo e(ucfirst($character->scenario_type ?? 'Unknown')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <a href="<?php echo e(route('characters.create')); ?>"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Character
                </a>
            </div>
        </div>

        <?php if(!$hasCharacters): ?>
            <!-- Empty State -->
            <?php if (isset($component)) { $__componentOriginal50f6691cb7e71446f1706a70a912a0e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal50f6691cb7e71446f1706a70a912a0e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.empty-state','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal50f6691cb7e71446f1706a70a912a0e8)): ?>
<?php $attributes = $__attributesOriginal50f6691cb7e71446f1706a70a912a0e8; ?>
<?php unset($__attributesOriginal50f6691cb7e71446f1706a70a912a0e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal50f6691cb7e71446f1706a70a912a0e8)): ?>
<?php $component = $__componentOriginal50f6691cb7e71446f1706a70a912a0e8; ?>
<?php unset($__componentOriginal50f6691cb7e71446f1706a70a912a0e8); ?>
<?php endif; ?>
        <?php else: ?>
            <!-- Key Metrics Overview -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 animate-fade-in-delay-1">
                <!-- Current Turn -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Current Turn
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            <?php echo e($metrics['currentTurn']); ?> / <?php echo e($metrics['maxTurns']); ?>

                                        </div>
                                        <div
                                            class="ml-2 flex items-baseline text-sm font-semibold <?php echo e($metrics['trackStatus'] === 'Ahead' ? 'text-success-600 dark:text-success-400' : ($metrics['trackStatus'] === 'On Track' ? 'text-primary-600 dark:text-primary-400' : 'text-warning-600 dark:text-warning-400')); ?>">
                                            <?php echo e($metrics['trackStatus']); ?>

                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Grade -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-secondary-600 dark:text-secondary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Overall Grade
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            <?php echo e($metrics['overallGrade']); ?></div>
                                        <div
                                            class="ml-2 flex items-baseline text-sm font-semibold text-primary-600 dark:text-primary-400">
                                            Target: <?php echo e($metrics['targetGrade']); ?>

                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skills Acquired -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Skills
                                        Acquired</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            <?php echo e($metrics['skillsAcquired']); ?> / <?php echo e($metrics['targetSkills']); ?>

                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold">
                                            SP: <?php echo e($metrics['skillPoints']); ?>

                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Race -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Next Race</dt>
                                    <dd class="flex items-baseline">
                                        <?php if($metrics['nextRace']): ?>
                                            <div class="text-sm font-semibold">
                                                <?php echo e($metrics['nextRace']); ?></div>
                                            <div
                                                class="ml-2 flex items-baseline text-xs font-semibold text-warning-600 dark:text-warning-400">
                                                <?php echo e($metrics['turnsUntilRace']); ?> turns
                                            </div>
                                        <?php else: ?>
                                            <div class="text-sm font-semibold">No race
                                                scheduled</div>
                                        <?php endif; ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main 2-Column Grid (per WF-001 wireframe) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-delay-2">

                <!-- Left Column: Progress & Schedule (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Goals Widget -->
                    <?php if (isset($component)) { $__componentOriginalac808f85cc5326abf5a81a3571390faf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac808f85cc5326abf5a81a3571390faf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.goals-widget','data' => ['shortTermGoal' => $goals['shortTerm']['goal'],'shortTermProgress' => $goals['shortTerm']['progress'],'longTermGoal' => $goals['longTerm']['goal'],'longTermProgress' => $goals['longTerm']['progress'],'characterId' => $selectedCharacter?->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.goals-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['shortTermGoal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($goals['shortTerm']['goal']),'shortTermProgress' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($goals['shortTerm']['progress']),'longTermGoal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($goals['longTerm']['goal']),'longTermProgress' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($goals['longTerm']['progress']),'characterId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedCharacter?->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac808f85cc5326abf5a81a3571390faf)): ?>
<?php $attributes = $__attributesOriginalac808f85cc5326abf5a81a3571390faf; ?>
<?php unset($__attributesOriginalac808f85cc5326abf5a81a3571390faf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac808f85cc5326abf5a81a3571390faf)): ?>
<?php $component = $__componentOriginalac808f85cc5326abf5a81a3571390faf; ?>
<?php unset($__componentOriginalac808f85cc5326abf5a81a3571390faf); ?>
<?php endif; ?>

                    <!-- Training Suggestions -->
                    <?php if (isset($component)) { $__componentOriginald4d262936a8a45fbfc3742f894145213 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4d262936a8a45fbfc3742f894145213 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.training-suggestions','data' => ['suggestions' => $trainingSuggestions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.training-suggestions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['suggestions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trainingSuggestions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4d262936a8a45fbfc3742f894145213)): ?>
<?php $attributes = $__attributesOriginald4d262936a8a45fbfc3742f894145213; ?>
<?php unset($__attributesOriginald4d262936a8a45fbfc3742f894145213); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4d262936a8a45fbfc3742f894145213)): ?>
<?php $component = $__componentOriginald4d262936a8a45fbfc3742f894145213; ?>
<?php unset($__componentOriginald4d262936a8a45fbfc3742f894145213); ?>
<?php endif; ?>

                    <!-- Upcoming Races -->
                    <?php if (isset($component)) { $__componentOriginala9b13acf20bde8c9cb468ca29e6362f4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9b13acf20bde8c9cb468ca29e6362f4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.upcoming-races','data' => ['races' => $races]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.upcoming-races'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['races' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($races)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9b13acf20bde8c9cb468ca29e6362f4)): ?>
<?php $attributes = $__attributesOriginala9b13acf20bde8c9cb468ca29e6362f4; ?>
<?php unset($__attributesOriginala9b13acf20bde8c9cb468ca29e6362f4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9b13acf20bde8c9cb468ca29e6362f4)): ?>
<?php $component = $__componentOriginala9b13acf20bde8c9cb468ca29e6362f4; ?>
<?php unset($__componentOriginala9b13acf20bde8c9cb468ca29e6362f4); ?>
<?php endif; ?>

                    <!-- Recent Results Timeline -->
                    <?php if (isset($component)) { $__componentOriginaleaf130611427a9343a5cc749c516c1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleaf130611427a9343a5cc749c516c1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.recent-results','data' => ['results' => $recentResults]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.recent-results'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['results' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentResults)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleaf130611427a9343a5cc749c516c1ac)): ?>
<?php $attributes = $__attributesOriginaleaf130611427a9343a5cc749c516c1ac; ?>
<?php unset($__attributesOriginaleaf130611427a9343a5cc749c516c1ac); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleaf130611427a9343a5cc749c516c1ac)): ?>
<?php $component = $__componentOriginaleaf130611427a9343a5cc749c516c1ac; ?>
<?php unset($__componentOriginaleaf130611427a9343a5cc749c516c1ac); ?>
<?php endif; ?>
                </div>

                <!-- Right Column: Stats & Advisories (1/3 width) -->
                <div class="flex flex-col gap-6 h-full">
                    <!-- Character Stats Card -->
                    <?php if (isset($component)) { $__componentOriginal781f3f3f5878cac3ccd157e2f0c42cd4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal781f3f3f5878cac3ccd157e2f0c42cd4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-snapshot','data' => ['stats' => $stats,'character' => $selectedCharacter]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-snapshot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats),'character' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedCharacter)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal781f3f3f5878cac3ccd157e2f0c42cd4)): ?>
<?php $attributes = $__attributesOriginal781f3f3f5878cac3ccd157e2f0c42cd4; ?>
<?php unset($__attributesOriginal781f3f3f5878cac3ccd157e2f0c42cd4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal781f3f3f5878cac3ccd157e2f0c42cd4)): ?>
<?php $component = $__componentOriginal781f3f3f5878cac3ccd157e2f0c42cd4; ?>
<?php unset($__componentOriginal781f3f3f5878cac3ccd157e2f0c42cd4); ?>
<?php endif; ?>

                    <!-- Mood/Energy Widget -->
                    <?php if (isset($component)) { $__componentOriginalcc6922ecc8ae1650528c9afd44ceb954 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcc6922ecc8ae1650528c9afd44ceb954 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.mood-energy-widget','data' => ['mood' => $moodEnergy['mood'],'energy' => $moodEnergy['energy'],'maxEnergy' => $moodEnergy['maxEnergy']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.mood-energy-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mood' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moodEnergy['mood']),'energy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moodEnergy['energy']),'maxEnergy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moodEnergy['maxEnergy'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcc6922ecc8ae1650528c9afd44ceb954)): ?>
<?php $attributes = $__attributesOriginalcc6922ecc8ae1650528c9afd44ceb954; ?>
<?php unset($__attributesOriginalcc6922ecc8ae1650528c9afd44ceb954); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcc6922ecc8ae1650528c9afd44ceb954)): ?>
<?php $component = $__componentOriginalcc6922ecc8ae1650528c9afd44ceb954; ?>
<?php unset($__componentOriginalcc6922ecc8ae1650528c9afd44ceb954); ?>
<?php endif; ?>

                    <!-- AI Advisor Card -->
                    <?php if (isset($component)) { $__componentOriginal953af1926776c5d7877910a3202b1605 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal953af1926776c5d7877910a3202b1605 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.ai-advisor-card','data' => ['class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.ai-advisor-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal953af1926776c5d7877910a3202b1605)): ?>
<?php $attributes = $__attributesOriginal953af1926776c5d7877910a3202b1605; ?>
<?php unset($__attributesOriginal953af1926776c5d7877910a3202b1605); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal953af1926776c5d7877910a3202b1605)): ?>
<?php $component = $__componentOriginal953af1926776c5d7877910a3202b1605; ?>
<?php unset($__componentOriginal953af1926776c5d7877910a3202b1605); ?>
<?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="animate-fade-in-delay-3">
                <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="<?php echo e($selectedCharacter ? route('training.predictions.show', $selectedCharacter) : route('training.predictions')); ?>"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17 9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Start Training</span>
                        <span class="mt-1 block text-xs">Begin your next training
                            session</span>
                    </a>
                    <a href="<?php echo e(route('races.index')); ?>"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">View Races</span>
                        <span class="mt-1 block text-xs">Check race schedule &
                            results</span>
                    </a>
                    <a href="<?php echo e(route('skills.index')); ?>"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Manage Skills</span>
                        <span class="mt-1 block text-xs">View & optimize skill
                            loadout</span>
                    </a>
                    <a href="<?php echo e(route('support-cards.index')); ?>"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Support Deck</span>
                        <span class="mt-1 block text-xs">Build & optimize your deck</span>
                    </a>
                </div>
            </div>

            <!-- Additional Resources -->
            <div class="glass-card-alt rounded-lg p-6 animate-fade-in-delay-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Need Help Getting Started?
                        </h3>
                        <p class="text-sm">Check out our guides and tutorials to optimize
                            your training strategy.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="<?php echo e(route('about')); ?>"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/50 dark:text-primary-300 dark:hover:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/dashboard.blade.php ENDPATH**/ ?>