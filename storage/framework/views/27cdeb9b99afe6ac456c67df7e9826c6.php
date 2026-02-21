
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Fan Distribution',
    'grades' => [],
    'variant' => 'pyramid',
    'height' => 'h-96',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Fan Distribution',
    'grades' => [],
    'variant' => 'pyramid',
    'height' => 'h-96',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($title); ?></h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Race tier distribution and fanbase growth</p>
    </div>

    
    <div
        class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700/50">
        <div class="flex items-center justify-between">
            <div>
                <span class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Total Fanbase</span>
                <span class="block text-2xl font-bold text-gray-900 dark:text-white" x-text="totalFans.toLocaleString()">
                </span>
            </div>
            <div class="text-3xl opacity-20">👥</div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant === 'pyramid'): ?>
        <div class="<?php echo e($height); ?> flex flex-col justify-center items-center space-y-2" x-data="classPyramid(<?php echo e(json_encode($grades)); ?>)">

            
            <template x-for="(layer, index) in sortedGrades" :key="index">
                <div class="w-full">
                    
                    <div class="flex items-center justify-center gap-2 mb-2">
                        
                        <div :style="`width: ${(index) * 15}px`"></div>

                        
                        <div class="flex-1 relative">
                            <div :class="`${layer.color} rounded-lg transition-all duration-300 hover:shadow-lg hover:scale-105 cursor-pointer p-3`"
                                :style="`opacity: 1 - (index * 0.1)`" @mouseover="hoveredLayer = index"
                                @mouseout="hoveredLayer = null" role="button"
                                :aria-label="`${layer.grade} tier with ${layer.fans.toLocaleString()} fans`"
                                tabindex="0">

                                
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold text-sm" x-text="layer.grade"></span>
                                    <span class="text-xs opacity-90"
                                        x-text="`${layer.fans.toLocaleString()} fans`"></span>
                                    <span class="text-xs font-semibold"
                                        x-text="`${Math.round((layer.fans / totalFans) * 100)}%`">
                                    </span>
                                </div>
                            </div>
                        </div>

                        
                        <div :style="`width: ${(index) * 15}px`"></div>
                    </div>

                    
                    <div x-show="hoveredLayer === index"
                        class="text-center text-xs text-gray-600 dark:text-gray-400 mb-2" x-transition>
                        <span x-text="`Growth potential: +${(layer.fans * 0.3).toLocaleString()} fans`"></span>
                    </div>
                </div>
            </template>
        </div>

        
    <?php elseif($variant === 'bars'): ?>
        <div class="space-y-3" x-data="classPyramid(<?php echo e(json_encode($grades)); ?>)">
            <template x-for="(grade, index) in sortedGrades" :key="index">
                <div class="space-y-1">
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="grade.grade">
                        </label>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400"
                            x-text="`${grade.fans.toLocaleString()} (${Math.round((grade.fans / totalFans) * 100)}%)`">
                        </span>
                    </div>

                    
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div :class="`${grade.color} h-full rounded-full transition-all duration-500`"
                            :style="`width: ${(grade.fans / maxFans) * 100}%`">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        
    <?php elseif($variant === 'cards'): ?>
        <div class="grid grid-cols-2 gap-3 @lg:grid-cols-3" x-data="classPyramid(<?php echo e(json_encode($grades)); ?>)">
            <template x-for="(grade, index) in sortedGrades" :key="index">
                <div :class="`${grade.color} rounded-lg p-4 text-white space-y-2 hover:shadow-lg transition-shadow`"
                    role="article" :aria-label="`${grade.grade} tier`">

                    
                    <h4 class="font-bold text-lg" x-text="grade.grade"></h4>

                    
                    <div>
                        <span class="text-xs opacity-90 block">Fanbase</span>
                        <span class="text-xl font-bold" x-text="grade.fans.toLocaleString()"></span>
                    </div>

                    
                    <div>
                        <span class="text-xs opacity-90 block">Share</span>
                        <span class="text-sm font-semibold" x-text="`${Math.round((grade.fans / totalFans) * 100)}%`">
                        </span>
                    </div>
                </div>
            </template>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    {{-- Legend {{--
    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-3">Grade Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="font-medium text-red-600 dark:text-red-400">G1</span>
                <span class="text-gray-600 dark:text-gray-400"> - Highest tier races</span>
            </div>
            <div>
                <span class="font-medium text-blue-600 dark:text-blue-400">Open</span>
                <span class="text-gray-600 dark:text-gray-400"> - General races</span>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('630a45e3-bfdd-4d38-94a5-7f490aa351cf')): $__env->markAsRenderedOnce('630a45e3-bfdd-4d38-94a5-7f490aa351cf'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/class-pyramid.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/class-pyramid.blade.php ENDPATH**/ ?>