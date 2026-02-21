
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Activity Timeline',
    'events' => [],
    'variant' => 'timeline',
    'maxEvents' => 10,
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
    'title' => 'Activity Timeline',
    'events' => [],
    'variant' => 'timeline',
    'maxEvents' => 10,
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
        <p class="text-sm text-gray-500 dark:text-gray-400">Recent milestones and achievements</p>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant === 'timeline'): ?>
        <div class="space-y-0" x-data="activityTimeline(<?php echo e(json_encode($events)); ?>)">
            
            <div class="relative">
                
                <div
                    class="absolute left-4 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-400 to-purple-400 dark:from-blue-500 dark:to-purple-500">
                </div>

                
                <template x-for="(event, index) in displayedEvents" :key="event.id">
                    <div class="relative pl-16 pb-8 last:pb-0">
                        
                        <div class="absolute left-0 w-9 h-9 rounded-full flex items-center justify-center text-lg"
                            :class="getEventColor(event.type) + ' ring-4 ring-white dark:ring-gray-800'">
                            <span x-text="getEventIcon(event.type)"></span>
                        </div>

                        
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-colors"
                            role="article" :aria-label="`${event.title} on ${formatDate(event.timestamp)}`">

                            
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="event.title">
                                </h4>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
                                    x-text="getTimeAgo(event.timestamp)">
                                </span>
                            </div>

                            
                            <div class="inline-block mb-2">
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                    :class="getEventBadgeStyle(event.type)" x-text="formatEventType(event.type)">
                                </span>
                            </div>

                            
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3" x-text="event.description">
                            </p>

                            
                            <template x-if="event.metadata && Object.keys(event.metadata).length">
                                <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                    <template x-for="(value, key) in event.metadata" :key="key">
                                        <div>
                                            <span class="font-medium" x-text="key + ':'"></span>
                                            <span x-text="formatMetadata(key, value)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            
            <template x-if="events.length > displayedEvents.length">
                <div class="text-center pt-4">
                    <button @click="loadMore()"
                        class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                        Show More Events
                    </button>
                </div>
            </template>

            
            <template x-if="events.length === 0">
                <div class="text-center py-12">
                    <div class="text-4xl mb-4 opacity-20">📋</div>
                    <p class="text-gray-600 dark:text-gray-400">No events yet</p>
                </div>
            </template>
        </div>

        
    <?php elseif($variant === 'feed'): ?>
        <div class="space-y-3" x-data="activityTimeline(<?php echo e(json_encode($events)); ?>)">
            <template x-for="(event, index) in displayedEvents" :key="event.id">
                <div class="flex gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    role="article">

                    
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg"
                        :class="getEventColor(event.type)">
                        <span x-text="getEventIcon(event.type)"></span>
                    </div>

                    
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="event.title">
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="event.description">
                        </p>
                        <span class="text-xs text-gray-500 dark:text-gray-500 mt-2 block"
                            x-text="getTimeAgo(event.timestamp)">
                        </span>
                    </div>
                </div>
            </template>
        </div>

        
    <?php elseif($variant === 'compact'): ?>
        <div class="space-y-2" x-data="activityTimeline(<?php echo e(json_encode($events)); ?>)">
            <template x-for="(event, index) in displayedEvents.slice(0, 5)" :key="event.id">
                <div
                    class="flex items-center justify-between text-xs p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded transition-colors">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <span class="text-lg flex-shrink-0" x-text="getEventIcon(event.type)"></span>
                        <span class="text-gray-900 dark:text-white font-medium truncate" x-text="event.title">
                        </span>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 flex-shrink-0" x-text="getTimeAgo(event.timestamp)">
                    </span>
                </div>
            </template>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-3">Event Types</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Race</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Skill</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Milestone</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Achievement</span>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('611a95c3-03de-46ee-87b7-8f776860db5a')): $__env->markAsRenderedOnce('611a95c3-03de-46ee-87b7-8f776860db5a'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/components/activity-timeline.js']); ?>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/activity-timeline.blade.php ENDPATH**/ ?>