

<div x-data="advisoryPanel()" x-init="isOpen = <?php echo \Illuminate\Support\Js::from($isOpen)->toHtml() ?>" class="advisory-panel-container" role="complementary"
    aria-label="AI Advisory Panel">
    
    <div x-show="!isOpen" class="fixed bottom-6 right-6 z-40">
        <button @click="togglePanel()"
            class="group relative flex items-center gap-3 px-5 py-3 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-primary-500/50"
            aria-label="Open AI Advisory Panel (Alt+A)" :aria-expanded="isOpen">
            
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="font-semibold">AI Advisory</span>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($criticalAlertCount > 0): ?>
                <span
                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-danger-500 text-white text-xs font-bold animate-pulse ring-4 ring-white dark:ring-neutral-900">
                    <?php echo e($criticalAlertCount); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <span
                class="absolute -bottom-8 right-0 text-xs text-neutral-600 dark:text-neutral-400 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Alt+A
            </span>
        </button>
    </div>

    
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed top-0 right-0 bottom-0 w-full md:w-[480px] lg:w-[560px] bg-white dark:bg-neutral-900 shadow-2xl z-50 flex flex-col"
        role="dialog" aria-modal="true" aria-labelledby="advisory-panel-title" @click.away="$wire.closePanel()">
        
        <div
            class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 shrink-0 bg-linear-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20">
            <div class="flex items-center gap-3">
                
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 dark:bg-primary-500 text-white relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    
                    <div wire:loading
                        class="absolute inset-0 flex items-center justify-center bg-primary-600 dark:bg-primary-500 rounded-full">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>

                
                <div>
                    <h2 id="advisory-panel-title" class="text-lg font-bold text-neutral-900 dark:text-white">
                        AI Advisory
                    </h2>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400">
                        Turn <?php echo e($turnNumber); ?> • <?php echo e(ucfirst($storageMode)); ?> Mode
                        <span wire:loading class="inline-flex items-center gap-1 ml-2">
                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>Updating...</span>
                        </span>
                    </p>
                </div>
            </div>

            
            <button @click="closePanel()"
                class="p-2 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                aria-label="Close advisory panel (Escape)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        
        <div class="flex-1 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasContent): ?>
                
                <div class="flex flex-col items-center justify-center h-full px-6 py-12 text-center">
                    <div
                        class="w-20 h-20 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-neutral-400 dark:text-neutral-600" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                        All Clear!
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-sm">
                        No critical alerts or recommendations at this time. Keep up the great work!
                    </p>
                </div>
            <?php else: ?>
                <div class="space-y-6 px-6 py-6">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($criticalAlerts->isNotEmpty()): ?>
                        <section aria-labelledby="critical-alerts-heading">
                            <button @click="toggleSection('alerts')"
                                class="w-full flex items-center justify-between mb-4 group focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-2 -m-2"
                                :aria-expanded="activeSection === 'alerts'" aria-controls="critical-alerts-content"
                                data-expandable="true">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-600 dark:text-danger-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                            aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 id="critical-alerts-heading"
                                        class="text-base font-bold text-neutral-900 dark:text-white">
                                        Critical Alerts
                                    </h3>
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 text-xs font-bold">
                                        <?php echo e($criticalAlerts->count()); ?>

                                    </span>
                                </div>
                                <svg class="w-5 h-5 text-neutral-400 transition-transform"
                                    :class="{ 'rotate-180': activeSection === 'alerts' }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="critical-alerts-content"
                                x-show="activeSection === 'alerts' || activeSection === null" x-collapse
                                class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $criticalAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('alert-{{ $alert->id ?? $alert->type->value }}', get_defined_vars()); ?>wire:key="alert-<?php echo e($alert->id ?? $alert->type->value); ?>"
                                        class="border-2 border-danger-200 dark:border-danger-800 rounded-lg bg-danger-50 dark:bg-danger-900/20 overflow-hidden"
                                        role="alert" aria-live="polite">
                                        
                                        <div class="px-4 py-3">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span
                                                            class="px-2 py-0.5 rounded-full bg-danger-600 text-white text-xs font-bold uppercase tracking-wide">
                                                            <?php echo e(str_replace('_', ' ', $alert->type->value)); ?>

                                                        </span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alert->turnsUntilCritical !== null && $alert->turnsUntilCritical > 0): ?>
                                                            <span
                                                                class="text-xs text-danger-700 dark:text-danger-300 font-semibold">
                                                                <?php echo e($alert->turnsUntilCritical); ?>

                                                                turn<?php echo e($alert->turnsUntilCritical !== 1 ? 's' : ''); ?>

                                                                remaining
                                                            </span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                    <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                                        <?php echo e($alert->message); ?>

                                                    </p>
                                                </div>
                                                <button
                                                    wire:click="dismissAlert('<?php echo e($alert->id ?? $alert->type->value); ?>')"
                                                    class="p-1 rounded hover:bg-danger-200 dark:hover:bg-danger-800 transition-colors text-danger-600 dark:text-danger-400 focus:outline-none focus:ring-2 focus:ring-danger-500"
                                                    aria-label="Dismiss alert: <?php echo e($alert->message ?? 'alert'); ?>">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($alert->actionItems)): ?>
                                                <div class="mt-3">
                                                    <p
                                                        class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                                                        Action Items:
                                                    </p>
                                                    <ul class="space-y-1.5">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $alert->actionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                            <li
                                                                class="flex items-start gap-2 text-xs text-neutral-700 dark:text-neutral-300">
                                                                <svg class="w-4 h-4 text-danger-600 dark:text-danger-400 shrink-0 mt-0.5"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                <span><?php echo e($action); ?></span>
                                                            </li>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alert->detailedAnalysis): ?>
                                                <button
                                                    @click="toggleAlert('<?php echo e($alert->id ?? $alert->type->value); ?>')"
                                                    class="mt-3 text-xs font-semibold text-danger-700 dark:text-danger-300 hover:text-danger-800 dark:hover:text-danger-200 flex items-center gap-1 focus:outline-none focus:underline"
                                                    :aria-expanded="expandedAlert === '<?php echo e($alert->id ?? $alert->type->value); ?>'"
                                                    data-expandable="true">>
                                                    <span
                                                        x-text="expandedAlert === '<?php echo e($alert->id ?? $alert->type->value); ?>' ? 'Hide Details' : 'Show Details'"></span>
                                                    <svg class="w-3 h-3 transition-transform"
                                                        :class="{ 'rotate-180': expandedAlert === '<?php echo e($alert->id ?? $alert->type->value); ?>' }"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div x-show="expandedAlert === '<?php echo e($alert->id ?? $alert->type->value); ?>'"
                                                    x-collapse
                                                    class="mt-2 p-3 bg-white dark:bg-neutral-800 rounded border border-danger-200 dark:border-danger-700">
                                                    <p class="text-xs text-neutral-700 dark:text-neutral-300">
                                                        <?php echo e($alert->detailedAnalysis); ?>

                                                    </p>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trainingRecommendations->isNotEmpty()): ?>
                        <section aria-labelledby="training-recommendations-heading">
                            <button @click="toggleSection('training')"
                                class="w-full flex items-center justify-between mb-4 group focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-2 -m-2"
                                :aria-expanded="activeSection === 'training'"
                                aria-controls="training-recommendations-content" data-expandable="true">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                            aria-hidden="true">
                                            <path
                                                d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <h3 id="training-recommendations-heading"
                                        class="text-base font-bold text-neutral-900 dark:text-white">
                                        Training Recommendations
                                    </h3>
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                        <?php echo e($trainingRecommendations->count()); ?>

                                    </span>
                                </div>
                                <svg class="w-5 h-5 text-neutral-400 transition-transform"
                                    :class="{ 'rotate-180': activeSection === 'training' }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="training-recommendations-content"
                                x-show="activeSection === 'training' || activeSection === null" x-collapse
                                class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trainingRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('rec-{{ $recommendation->id ?? $recommendation->action }}', get_defined_vars()); ?>wire:key="rec-<?php echo e($recommendation->id ?? $recommendation->action); ?>"
                                        class="border-2 rounded-lg overflow-hidden transition-all duration-200"
                                        :class="{
                                            'border-danger-300 dark:border-danger-700 bg-danger-50 dark:bg-danger-900/10': '<?php echo e($recommendation->priority->value); ?>'
                                            === 'critical',
                                            'border-warning-300 dark:border-warning-700 bg-warning-50 dark:bg-warning-900/10': '<?php echo e($recommendation->priority->value); ?>'
                                            === 'high',
                                            'border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/10': '<?php echo e($recommendation->priority->value); ?>'
                                            === 'medium',
                                            'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800': '<?php echo e($recommendation->priority->value); ?>'
                                            === 'low'
                                        }">
                                        
                                        <div class="px-4 py-3">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        
                                                        <span
                                                            class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide"
                                                            :class="{
                                                                'bg-danger-600 text-white': '<?php echo e($recommendation->priority->value); ?>'
                                                                === 'critical',
                                                                'bg-warning-600 text-white': '<?php echo e($recommendation->priority->value); ?>'
                                                                === 'high',
                                                                'bg-primary-600 text-white': '<?php echo e($recommendation->priority->value); ?>'
                                                                === 'medium',
                                                                'bg-neutral-500 text-white': '<?php echo e($recommendation->priority->value); ?>'
                                                                === 'low'
                                                            }">
                                                            <?php echo e($recommendation->priority->value); ?>

                                                        </span>

                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recommendation->confidenceScore !== null): ?>
                                                            <span
                                                                class="text-xs text-neutral-600 dark:text-neutral-400 font-semibold">
                                                                <?php echo e(round($recommendation->confidenceScore * 100)); ?>%
                                                                confidence
                                                            </span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                    <h4 class="text-sm font-bold text-neutral-900 dark:text-white">
                                                        <?php echo e($recommendation->action); ?>

                                                    </h4>
                                                </div>
                                                <button
                                                    wire:click="dismissRecommendation('<?php echo e($recommendation->id ?? $recommendation->action); ?>')"
                                                    class="p-1 rounded hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors text-neutral-600 dark:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                    aria-label="Dismiss recommendation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            
                                            <p class="text-xs text-neutral-700 dark:text-neutral-300 mb-3">
                                                <?php echo e($recommendation->reasoning); ?>

                                            </p>

                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recommendation->expectedOutcomes) || !empty($recommendation->risks)): ?>
                                                <button
                                                    @click="toggleRecommendation('<?php echo e($recommendation->id ?? $recommendation->action); ?>')"
                                                    class="text-xs font-semibold text-primary-700 dark:text-primary-300 hover:text-primary-800 dark:hover:text-primary-200 flex items-center gap-1 focus:outline-none focus:underline"
                                                    :aria-expanded="expandedRecommendation === '<?php echo e($recommendation->id ?? $recommendation->action); ?>'"
                                                    data-expandable="true">>
                                                    <span
                                                        x-text="expandedRecommendation === '<?php echo e($recommendation->id ?? $recommendation->action); ?>' ? 'Hide Details' : 'Show Details'"></span>
                                                    <svg class="w-3 h-3 transition-transform"
                                                        :class="{ 'rotate-180': expandedRecommendation === '<?php echo e($recommendation->id ?? $recommendation->action); ?>' }"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                            
                                            <div x-show="expandedRecommendation === '<?php echo e($recommendation->id ?? $recommendation->action); ?>'"
                                                x-collapse class="mt-3 space-y-3">
                                                
                                                <?php if(!empty($recommendation->expectedOutcomes)): ?>
                                                    <div
                                                        class="p-3 bg-success-50 dark:bg-success-900/20 rounded border border-success-200 dark:border-success-800">
                                                        <p
                                                            class="text-xs font-semibold text-success-900 dark:text-success-100 mb-2 flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Expected Outcomes
                                                        </p>
                                                        <ul class="space-y-1">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recommendation->expectedOutcomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $outcome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                                <li
                                                                    class="text-xs text-success-800 dark:text-success-200 flex items-start gap-2">
                                                                    <span
                                                                        class="text-success-600 dark:text-success-400">•</span>
                                                                    <span><strong><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                                                                        <?php echo e(is_array($outcome) ? implode(', ', $outcome) : $outcome); ?></span>
                                                                </li>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recommendation->risks)): ?>
                                                    <div
                                                        class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded border border-warning-200 dark:border-warning-800">
                                                        <p
                                                            class="text-xs font-semibold text-warning-900 dark:text-warning-100 mb-2 flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Risks
                                                        </p>
                                                        <ul class="space-y-1">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recommendation->risks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                                <li
                                                                    class="text-xs text-warning-800 dark:text-warning-200 flex items-start gap-2">
                                                                    <span
                                                                        class="text-warning-600 dark:text-warning-400">•</span>
                                                                    <span><?php echo e($risk); ?></span>
                                                                </li>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div
            class="border-t border-neutral-200 dark:border-neutral-700 px-6 py-4 shrink-0 bg-neutral-50 dark:bg-neutral-900/50">
            <div class="flex items-center justify-between text-xs text-neutral-600 dark:text-neutral-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Press <kbd
                            class="px-1.5 py-0.5 bg-neutral-200 dark:bg-neutral-700 rounded text-xs font-mono">Alt+A</kbd>
                        to toggle</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissedAlerts || $dismissedRecommendations): ?>
                    <button wire:click="clearDismissed"
                        class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200 font-semibold focus:outline-none focus:underline">
                        Clear Dismissed
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div id="advisory-panel-announcer" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></div>

    
    <div class="hidden" aria-hidden="true">
        
        <span class="bg-danger-500 text-white animate-pulse ring-4 ring-white dark:ring-neutral-900"></span>
        
        <span
            class="border-danger-200 bg-danger-50 bg-danger-100 text-danger-600 bg-danger-600 text-neutral-900"></span>
        <span
            class="dark:border-danger-800 dark:bg-danger-900/20 dark:bg-danger-900/30 dark:text-danger-400 dark:text-white dark:text-neutral-300"></span>
        
        <span>Critical Alerts</span>
        <span aria-live="polite"></span>
        
        <span class="bg-success-50 border-success-200 text-success-900 text-success-800"></span>
        <span
            class="dark:bg-success-900/20 dark:border-success-800 dark:text-success-100 dark:text-success-200"></span>
        
        <span class="bg-warning-50 border-warning-200 text-warning-900 text-warning-800"></span>
        <span
            class="dark:bg-warning-900/20 dark:border-warning-800 dark:text-warning-100 dark:text-warning-200"></span>
        
        <span class="border-warning-300 bg-warning-600 dark:border-warning-700 dark:bg-warning-900/10"></span>
        <span
            class="border-primary-200 bg-primary-50 bg-primary-600 dark:border-primary-800 dark:bg-primary-900/10"></span>
        <span class="border-neutral-200 bg-white bg-neutral-500 dark:border-neutral-700 dark:bg-neutral-800"></span>
        
        <span class="text-neutral-700"></span>
        
        <span
            class="hover:bg-danger-200 dark:hover:bg-danger-800 hover:text-primary-800 dark:hover:text-primary-200"></span>
        
        <span class="focus:ring-danger-500 focus:ring-4 focus:ring-2"></span>
        
        <span>CRITICAL</span>
        <span>HIGH</span>
        <span>MEDIUM</span>
        <span>LOW</span>
    </div>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/livewire/advisory-panel.blade.php ENDPATH**/ ?>