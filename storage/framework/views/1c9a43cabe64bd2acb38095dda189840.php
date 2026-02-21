

<?php $__env->startSection('content'); ?>
    <?php
        /** @var \App\Models\Character $character */
    ?>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb Navigation -->
        <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Characters', 'url' => route('characters.index')], ['label' => $character->name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

        <!-- Header / Action Buttons -->
        <div class="flex items-center justify-end">
            <div class="flex space-x-3">
                <form action="<?php echo e(route('characters.toggle-pin', $character)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="btn <?php echo e($character->isPinnedBy(Auth::id()) ? 'btn-primary' : 'btn-secondary'); ?>"
                        title="<?php echo e($character->isPinnedBy(Auth::id()) ? 'Unpin character' : 'Pin character for quick access'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->isPinnedBy(Auth::id())): ?>
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                            </svg>
                            Pinned
                        <?php else: ?>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            Pin
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </form>

                <form action="<?php echo e(route('characters.rest', $character)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-secondary text-green-700 dark:text-green-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        Rest
                    </button>
                </form>

                <form action="<?php echo e(route('characters.next-turn', $character)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-secondary text-blue-700 dark:text-blue-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                        Next Turn
                    </button>
                </form>

                <a href="<?php echo e(route('characters.edit', $character)); ?>" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Character
                </a>
            </div>
        </div>

        <!-- Character Overview Card -->
        <section class="card overflow-visible rounded-xl" role="region" aria-labelledby="character-overview-heading">
            <div class="p-6 md:p-8 relative overflow-hidden">
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50 pointer-events-none"
                    aria-hidden="true">
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-start relative">
                    <!-- Avatar -->
                    <div class="shrink-0 relative">
                        <?php if (isset($component)) { $__componentOriginal78752d4664868f163d0460b49dc44fcb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78752d4664868f163d0460b49dc44fcb = $attributes; } ?>
<?php $component = App\View\Components\CharacterPortrait::resolve(['image' => $character->avatar_url,'alt' => $character->name,'size' => 'xl'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('character-portrait'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\CharacterPortrait::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78752d4664868f163d0460b49dc44fcb)): ?>
<?php $attributes = $__attributesOriginal78752d4664868f163d0460b49dc44fcb; ?>
<?php unset($__attributesOriginal78752d4664868f163d0460b49dc44fcb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78752d4664868f163d0460b49dc44fcb)): ?>
<?php $component = $__componentOriginal78752d4664868f163d0460b49dc44fcb; ?>
<?php unset($__componentOriginal78752d4664868f163d0460b49dc44fcb); ?>
<?php endif; ?>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 space-y-4 w-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h2 id="character-overview-heading"
                                        class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                                        <?php echo e($character->name); ?></h2>
                                    <?php if (isset($component)) { $__componentOriginal38ed160b843d185cc0ee7da813217a54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ed160b843d185cc0ee7da813217a54 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.grade-badge','data' => ['grade' => $character->getStatGrade($character->current_stats['speed'] ?? 0),'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.grade-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['grade' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getStatGrade($character->current_stats['speed'] ?? 0)),'size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $attributes = $__attributesOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__attributesOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ed160b843d185cc0ee7da813217a54)): ?>
<?php $component = $__componentOriginal38ed160b843d185cc0ee7da813217a54; ?>
<?php unset($__componentOriginal38ed160b843d185cc0ee7da813217a54); ?>
<?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->isPinnedBy(Auth::id())): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
                                            title="Pinned character">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                                            </svg>
                                            Pinned
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <?php echo e($character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'); ?>

                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-secondary-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Turn <?php echo e($character->current_turn); ?> (<?php echo e(ucfirst($character->career_stage)); ?>)
                                    </span>
                                </div>
                            </div>

                            <!-- AI Advisor (Desktop Position) -->
                            <div class="hidden lg:block w-80">
                                <?php if (isset($component)) { $__componentOriginal953af1926776c5d7877910a3202b1605 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal953af1926776c5d7877910a3202b1605 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.ai-advisor-card','data' => ['class' => 'shadow-sm border-0','lastTip' => $aiTip]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.ai-advisor-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'shadow-sm border-0','lastTip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aiTip)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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

                        <!-- Quick Progress Bars -->
                        <div
                            class="max-w-2xl bg-white/50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50 backdrop-blur-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-gray-500 dark:text-gray-400">Energy Level</span>
                                        <span
                                            class="<?php echo e($character->energy_level < 30 ? 'text-red-500' : 'text-green-500'); ?>"><?php echo e($character->energy_level); ?>%</span>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $character->energy_level,'max' => 100,'color' => ''.e($character->energy_level < 30 ? 'bg-red-500' : 'bg-green-500').'','size' => 'sm','showText' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->energy_level),'max' => 100,'color' => ''.e($character->energy_level < 30 ? 'bg-red-500' : 'bg-green-500').'','size' => 'sm','show-text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $attributes = $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $component = $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-gray-500 dark:text-gray-400">Goal Progress</span>
                                        <span class="text-primary-500"><?php echo e($character->getProgressPercentage()); ?>%</span>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.progress-bar','data' => ['value' => $character->getProgressPercentage(),'max' => 100,'color' => 'bg-primary-500','size' => 'sm','showText' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($character->getProgressPercentage()),'max' => 100,'color' => 'bg-primary-500','size' => 'sm','show-text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $attributes = $__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__attributesOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff)): ?>
<?php $component = $__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff; ?>
<?php unset($__componentOriginal59ef5994b22a1cc08ed5d50edefbc0ff); ?>
<?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- AI Advisor (Mobile Position) -->
                        <div class="lg:hidden mt-4">
                            <?php if (isset($component)) { $__componentOriginal953af1926776c5d7877910a3202b1605 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal953af1926776c5d7877910a3202b1605 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.ai-advisor-card','data' => ['lastTip' => $aiTip]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.ai-advisor-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['lastTip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aiTip)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left Column: Stats (Takes 2 columns) -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Detailed Stats Card -->
                <section class="card rounded-lg" role="region" aria-labelledby="stats-heading">
                    <header
                        class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                        <h2 id="stats-heading" class="text-lg font-bold text-gray-900 dark:text-white">Current Statistics
                        </h2>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Updated
                            <?php echo e($character->updated_at->diffForHumans()); ?></span>
                    </header>
                    <div class="card-body">
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $val = $character->getStat($stat);
                                    $target = $character->goals['target_stats'][$stat] ?? 0;
                                ?>
                                <?php if (isset($component)) { $__componentOriginaldf052d5a616cfc8666e4c93016234de5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf052d5a616cfc8666e4c93016234de5 = $attributes; } ?>
<?php $component = App\View\Components\StatBar::resolve(['stat' => $stat,'current' => $val,'target' => $target] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\StatBar::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf052d5a616cfc8666e4c93016234de5)): ?>
<?php $attributes = $__attributesOriginaldf052d5a616cfc8666e4c93016234de5; ?>
<?php unset($__attributesOriginaldf052d5a616cfc8666e4c93016234de5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf052d5a616cfc8666e4c93016234de5)): ?>
<?php $component = $__componentOriginaldf052d5a616cfc8666e4c93016234de5; ?>
<?php unset($__componentOriginaldf052d5a616cfc8666e4c93016234de5); ?>
<?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </section>
                <!-- Support Deck -->
                <section class="card rounded-lg" role="region" aria-labelledby="support-deck-heading">
                    <header
                        class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                        <h2 id="support-deck-heading" class="text-lg font-bold text-gray-900 dark:text-white">Support Deck
                        </h2>
                        <a href="<?php echo e(route('characters.deck-builder', $character)); ?>" class="btn btn-sm btn-primary"
                            aria-label="Manage Support Deck">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Manage Deck
                        </a>
                    </header>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->supportCards->count() > 0): ?>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $character->supportCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supportCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <?php
                                        $card = $supportCard->supportCard;
                                        $bondLevel = $supportCard->friendship_level ?? 0;
                                        $bondMax = 100;
                                        $bondPercentage = ($bondLevel / $bondMax) * 100;
                                    ?>
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                                        <div
                                            class="shrink-0 w-10 h-10 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-sm">
                                            <?php echo e(strtoupper(substr($card->card_type ?? 'S', 0, 1))); ?>

                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                <?php echo e($card->name ?? 'Unknown Card'); ?></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div
                                                    class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div class="h-full bg-primary-500 rounded-full transition-all"
                                                        style="<?php echo \Illuminate\Support\Arr::toCssStyles(['width' => $bondPercentage . '%']) ?>"></div>
                                                </div>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 tabular-nums"><?php echo e($bondLevel); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-sm text-gray-500 py-6">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="mb-3">No support cards equipped.</p>
                                <a href="<?php echo e(route('characters.deck-builder', $character)); ?>"
                                    class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Support Cards
                                </a>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>

                
                
            </div>

            <!-- Right Column: Stats Overview & Aptitudes -->
            <div class="space-y-6">
                <!-- Stats Overview -->
                <section class="card rounded-lg" role="region" aria-labelledby="stats-visualization-heading">
                    <header class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h2 id="stats-visualization-heading" class="text-lg font-bold text-gray-900 dark:text-white">Stats
                            Overview</h2>
                    </header>
                    <div class="card-body flex justify-center items-center py-4">
                        <?php if (isset($component)) { $__componentOriginal445997068f562fb1a30628a9fd5536d4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal445997068f562fb1a30628a9fd5536d4 = $attributes; } ?>
<?php $component = App\View\Components\StatRadarChart::resolve(['stats' => [
                            'speed' => $character->getStat('speed'),
                            'stamina' => $character->getStat('stamina'),
                            'power' => $character->getStat('power'),
                            'guts' => $character->getStat('guts'),
                            'wit' => $character->getStat('wit'),
                        ],'size' => 'sm'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-radar-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\StatRadarChart::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal445997068f562fb1a30628a9fd5536d4)): ?>
<?php $attributes = $__attributesOriginal445997068f562fb1a30628a9fd5536d4; ?>
<?php unset($__attributesOriginal445997068f562fb1a30628a9fd5536d4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal445997068f562fb1a30628a9fd5536d4)): ?>
<?php $component = $__componentOriginal445997068f562fb1a30628a9fd5536d4; ?>
<?php unset($__componentOriginal445997068f562fb1a30628a9fd5536d4); ?>
<?php endif; ?>
                    </div>
                </section>

                <!-- Aptitudes -->
                <section class="card rounded-lg" role="region" aria-labelledby="aptitudes-heading">
                    <header class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h2 id="aptitudes-heading" class="text-lg font-bold text-gray-900 dark:text-white">Aptitudes</h2>
                    </header>
                    <div class="card-body space-y-6">
                        <?php
                            $aptitudeGroups = [
                                'Distance' => $character->aptitudes->whereNotNull('distance_type'),
                                'Surface' => $character->aptitudes->whereNotNull('surface_type'),
                                'Running Style' => $character->aptitudes->whereNotNull('running_style'),
                            ];
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $aptitudeGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $aptitudes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aptitudes->count() > 0): ?>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                        <?php echo e($groupName); ?></h4>
                                    <div class="space-y-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $aptitudes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aptitude): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <?php
                                                $name =
                                                    $aptitude->distance_type ??
                                                    ($aptitude->surface_type ?? $aptitude->running_style);
                                                $name = ucfirst(str_replace('_', ' ', $name));
                                            ?>
                                            <?php if (isset($component)) { $__componentOriginal5ca8221419a9c3252452bc24f23d35fe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ca8221419a9c3252452bc24f23d35fe = $attributes; } ?>
<?php $component = App\View\Components\AptitudeDisplay::resolve(['type' => $name,'grade' => $aptitude->grade] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('aptitude-display'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AptitudeDisplay::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5ca8221419a9c3252452bc24f23d35fe)): ?>
<?php $attributes = $__attributesOriginal5ca8221419a9c3252452bc24f23d35fe; ?>
<?php unset($__attributesOriginal5ca8221419a9c3252452bc24f23d35fe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5ca8221419a9c3252452bc24f23d35fe)): ?>
<?php $component = $__componentOriginal5ca8221419a9c3252452bc24f23d35fe; ?>
<?php unset($__componentOriginal5ca8221419a9c3252452bc24f23d35fe); ?>
<?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->aptitudes->isEmpty()): ?>
                            <div class="text-center text-sm text-gray-500 py-4">No aptitude data available.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

        <!-- Skills, Race Schedule, and Inherited Factors Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Skills -->
            <section class="card rounded-lg" role="region" aria-labelledby="skills-heading">
                <header
                    class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                    <h2 id="skills-heading" class="text-lg font-bold text-gray-900 dark:text-white">Skills</h2>
                    <a href="<?php echo e(route('skills.index', ['character' => $character->id])); ?>" class="btn btn-sm btn-primary"
                        aria-label="Manage Skills">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Manage Skills
                    </a>
                </header>
                <div class="p-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->skills->count() > 0): ?>
                        <div class="space-y-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $character->skills->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div
                                    class="flex items-center justify-between p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-200"><?php echo e($skill->name); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skill->pivot && $skill->pivot->final_sp_cost): ?>
                                        <span
                                            class="text-xs font-mono text-gray-400"><?php echo e($skill->pivot->final_sp_cost); ?>pt</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-sm text-gray-500 py-6">
                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <p class="mb-3">No skills acquired yet.</p>
                            <a href="<?php echo e(route('skills.index', ['character' => $character->id])); ?>"
                                class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Browse Skills
                            </a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <!-- Race Schedule -->
            <section class="card rounded-lg" role="region" aria-labelledby="race-schedule-heading-bottom">
                <header
                    class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                    <h2 id="race-schedule-heading-bottom" class="text-lg font-bold text-gray-900 dark:text-white">Race
                        Schedule</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Upcoming</span>
                </header>
                <?php
                    $raceSchedule = $character->race_schedule ?? [];
                    $upcomingRaces = is_array($raceSchedule) ? array_slice($raceSchedule, 0, 5) : [];
                ?>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($upcomingRaces) > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $upcomingRaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $race): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $raceName = $race['name'] ?? 'Unknown Race';
                                    $raceGrade = $race['grade'] ?? 'G1';
                                    $raceTurn = $race['turn'] ?? 0;
                                    $readiness = $race['readiness'] ?? 'unknown';
                                    $readinessColors = [
                                        'excellent' =>
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'good' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        'fair' =>
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'poor' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'unknown' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
                                    ];
                                    $readinessColor = $readinessColors[$readiness] ?? $readinessColors['unknown'];
                                ?>
                                <div
                                    class="flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            <?php echo e($raceName); ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($raceGrade); ?> • Turn
                                            <?php echo e($raceTurn); ?></div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($readinessColor); ?> ml-2">
                                        <?php echo e(ucfirst($readiness)); ?>

                                    </span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-sm text-gray-500 py-6">
                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2">No races scheduled yet.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <!-- Inherited Factors -->
            <section class="card rounded-lg" role="region" aria-labelledby="inherited-factors-heading">
                <header
                    class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                    <h2 id="inherited-factors-heading" class="text-lg font-bold text-gray-900 dark:text-white">Inherited
                        Factors</h2>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $character)): ?>
                        <a href="<?php echo e(route('characters.factors.manage', $character)); ?>" class="btn btn-sm btn-primary"
                            aria-label="Manage Inherited Factors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Manage
                        </a>
                    <?php endif; ?>
                </header>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($character->factors->count() > 0): ?>
                        <?php
                            $factorsByType = $character->factors->groupBy('factor_type');
                            $factorTypeLabels = [
                                'blue_stats' => 'Stat Bonuses',
                                'red_aptitudes' => 'Aptitude Upgrades',
                                'green_unique_skills' => 'Unique Skills',
                                'white_normal_skills' => 'Normal Skills',
                            ];
                            $factorTypeColors = [
                                'blue_stats' => 'text-blue-600 dark:text-blue-400',
                                'red_aptitudes' => 'text-red-600 dark:text-red-400',
                                'green_unique_skills' => 'text-green-600 dark:text-green-400',
                                'white_normal_skills' => 'text-gray-600 dark:text-gray-400',
                            ];
                        ?>

                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $factorsByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $factors): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div>
                                    <h4
                                        class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                        <div
                                            class="w-2 h-2 rounded-full bg-<?php echo e(str_replace('_stats', '', str_replace('_aptitudes', '', str_replace('_unique_skills', '', str_replace('_normal_skills', '', $type))))); ?>-500">
                                        </div>
                                        <?php echo e($factorTypeLabels[$type] ?? ucfirst($type)); ?>

                                        <span class="text-gray-400">(<?php echo e($factors->count()); ?>)</span>
                                    </h4>
                                    <div class="space-y-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $factors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $factor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <div
                                                class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 <?php echo e(!$factor->is_active ? 'opacity-50' : ''); ?>">
                                                <div class="flex items-center gap-2">
                                                    <!-- Star Level -->
                                                    <div class="flex items-center">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 3; $i++): ?>
                                                            <svg class="w-3 h-3 <?php echo e($i <= (int) str_replace('_star', '', $factor->star_level) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'); ?>"
                                                                fill="currentColor" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>

                                                    <!-- Factor Name -->
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo e($factor->factor_name); ?>

                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <!-- Factor Value/Bonus -->
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($factor->factor_type === 'blue_stats'): ?>
                                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                                            +<?php echo e($factor->getStatBonus()); ?>

                                                        </span>
                                                    <?php elseif($factor->factor_type === 'red_aptitudes'): ?>
                                                        <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                                            +<?php echo e((int) str_replace('_star', '', $factor->star_level)); ?>

                                                            grade<?php echo e((int) str_replace('_star', '', $factor->star_level) > 1 ? 's' : ''); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span
                                                            class="text-xs font-bold <?php echo e($factorTypeColors[$factor->factor_type] ?? 'text-gray-600 dark:text-gray-400'); ?>">
                                                            <?php echo e((int) str_replace('_star', '', $factor->star_level)); ?>★
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <!-- Active Status -->
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$factor->is_active): ?>
                                                        <span
                                                            class="text-xs text-gray-400 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                                            Inactive
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>

                        <!-- Factor Summary -->
                        <?php
                            $activeFactors = $character->factors->where('is_active', true);
                            $totalStatBonuses = $activeFactors
                                ->where('factor_type', 'blue_stats')
                                ->reduce(function ($carry, $factor) {
                                    return $carry + $factor->getStatBonus();
                                }, 0);
                            $totalAptitudeUpgrades = $activeFactors
                                ->where('factor_type', 'red_aptitudes')
                                ->reduce(function ($carry, $factor) {
                                    return $carry + (int) str_replace('_star', '', $factor->star_level);
                                }, 0);
                            $uniqueSkills = $activeFactors->where('factor_type', 'green_unique_skills')->count();
                            $normalSkills = $activeFactors->where('factor_type', 'white_normal_skills')->count();
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeFactors->count() > 0): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Active
                                    Bonuses</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalStatBonuses > 0): ?>
                                        <div class="flex justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                            <span class="text-blue-700 dark:text-blue-300">Total Stat Bonus</span>
                                            <span
                                                class="font-bold text-blue-800 dark:text-blue-200">+<?php echo e($totalStatBonuses); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalAptitudeUpgrades > 0): ?>
                                        <div class="flex justify-between p-2 bg-red-50 dark:bg-red-900/20 rounded">
                                            <span class="text-red-700 dark:text-red-300">Aptitude Upgrades</span>
                                            <span
                                                class="font-bold text-red-800 dark:text-red-200">+<?php echo e($totalAptitudeUpgrades); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($uniqueSkills > 0): ?>
                                        <div class="flex justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                            <span class="text-green-700 dark:text-green-300">Unique Skills</span>
                                            <span
                                                class="font-bold text-green-800 dark:text-green-200"><?php echo e($uniqueSkills); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($normalSkills > 0): ?>
                                        <div class="flex justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                            <span class="text-gray-700 dark:text-gray-300">Normal Skills</span>
                                            <span
                                                class="font-bold text-gray-800 dark:text-gray-200"><?php echo e($normalSkills); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-sm text-gray-500 py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mb-2">No inherited factors.</p>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $character)): ?>
                                <a href="<?php echo e(route('characters.factors.manage', $character)); ?>"
                                    class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                                    Add Factors
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/show.blade.php ENDPATH**/ ?>