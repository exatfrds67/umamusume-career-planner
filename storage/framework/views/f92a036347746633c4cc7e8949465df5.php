

<?php $__env->startSection('title', 'Data Management Hub'); ?>

<?php $__env->startSection('content'); ?>
    
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['items' => [['label' => 'Data Management']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

    <div class="container mx-auto px-4 py-8" x-data="dataManagementHub()">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Management Hub</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Unified interface for importing, exporting, migrating, and backing up your career data.
            </p>
        </div>

        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Characters</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_characters">0
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Careers</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_careers">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💾</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Backups</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_backups">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Success Rate</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400"
                            x-text="statistics.success_rate + '%'">0%</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Data management tabs">
                    <button type="button" @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📋</span> Overview
                    </button>
                    <button type="button" @click="activeTab = 'import'"
                        :class="activeTab === 'import' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📥</span> Import
                    </button>
                    <button type="button" @click="activeTab = 'export'"
                        :class="activeTab === 'export' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📤</span> Export
                    </button>
                    <button type="button" @click="activeTab = 'migration'"
                        :class="activeTab === 'migration' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>🔄</span> Migration
                    </button>
                    <button type="button" @click="activeTab = 'backup'"
                        :class="activeTab === 'backup' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>💾</span> Backup
                    </button>
                    <button type="button" @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📜</span> History
                    </button>
                </nav>
            </div>

            
            <div class="p-6">
                
                <div x-show="activeTab === 'overview'" x-transition>
                    <?php echo $__env->make('data-management.partials.overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div x-show="activeTab === 'import'" x-cloak x-transition>
                    <?php echo $__env->make('data-management.partials.import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div x-show="activeTab === 'export'" x-cloak x-transition>
                    <?php echo $__env->make('data-management.partials.export', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div x-show="activeTab === 'migration'" x-cloak x-transition>
                    <?php echo $__env->make('data-management.partials.migration', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div x-show="activeTab === 'backup'" x-cloak x-transition>
                    <?php echo $__env->make('data-management.partials.backup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div x-show="activeTab === 'history'" x-cloak x-transition>
                    <?php echo $__env->make('data-management.partials.history', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>

        
        <div x-show="ongoingOperations.length > 0" x-transition
            class="fixed bottom-4 right-4 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900 dark:text-white">Ongoing Operations</h3>
                    <span
                        class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full"
                        x-text="ongoingOperations.length"></span>
                </div>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto space-y-3">
                <template x-for="op in ongoingOperations" :key="op.operation_id">
                    <?php if (isset($component)) { $__componentOriginalb9d0986f79238f03d3f70413292615c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9d0986f79238f03d3f70413292615c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-management.progress-tracker','data' => ['xBind:operationId' => 'op.operation_id','xBind:operationType' => 'op.operation_type','xBind:status' => 'op.status','xBind:message' => 'op.message','showDetails' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-management.progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:operation-id' => 'op.operation_id','x-bind:operation-type' => 'op.operation_type','x-bind:status' => 'op.status','x-bind:message' => 'op.message','show-details' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9d0986f79238f03d3f70413292615c3)): ?>
<?php $attributes = $__attributesOriginalb9d0986f79238f03d3f70413292615c3; ?>
<?php unset($__attributesOriginalb9d0986f79238f03d3f70413292615c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9d0986f79238f03d3f70413292615c3)): ?>
<?php $component = $__componentOriginalb9d0986f79238f03d3f70413292615c3; ?>
<?php unset($__componentOriginalb9d0986f79238f03d3f70413292615c3); ?>
<?php endif; ?>
                </template>
            </div>
        </div>
    </div>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/data-management/index.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/data-management/index.blade.php ENDPATH**/ ?>