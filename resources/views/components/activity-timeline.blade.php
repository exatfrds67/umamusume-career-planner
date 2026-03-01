{{--
Component: ActivityTimeline
Purpose: Vertical timeline visualization of historical events and milestones

Props:
  - title (string): Component title
  - events (array): Timeline events array
  - variant (string): Display variant - 'timeline'/'feed'/'compact' (default: timeline)
  - maxEvents (int): Maximum events to show (default: 10)

Events structure:
  [
      {
          id: unique_id,
          title: 'Event Title',
          description: 'Event details',
          type: 'race'|'skill'|'milestone'|'achievement',
          timestamp: 'ISO 8601 date',
          icon: 'emoji or icon class',
          color: 'color-code',
          metadata: { ... } // Additional data
      }
  ]

Usage:
  <x-activity-timeline 
      title="Recent Activity" 
      :events="$recentEvents"
      variant="timeline"
  />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'title' => 'Activity Timeline',
    'events' => [],
    'variant' => 'timeline',
    'maxEvents' => 10,
])

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    {{-- Header --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Recent milestones and achievements</p>
    </div>

    {{-- Timeline Variant (Default) --}}
    @if ($variant === 'timeline')
        <div class="space-y-0" x-data="activityTimeline({{ json_encode($events) }})">
            {{-- Timeline Container --}}
            <div class="relative">
                {{-- Timeline Line --}}
                <div
                    class="absolute left-4 top-0 bottom-0 w-1 bg-linear-to-b from-blue-400 to-purple-400 dark:from-blue-500 dark:to-purple-500">
                </div>

                {{-- Events --}}
                <template x-for="(event, index) in displayedEvents" :key="event.id">
                    <div class="relative pl-16 pb-8 last:pb-0">
                        {{-- Timeline Dot --}}
                        <div class="absolute left-0 w-9 h-9 rounded-full flex items-center justify-center text-lg"
                            :class="getEventColor(event.type) + ' ring-4 ring-white dark:ring-gray-800'">
                            <span x-text="getEventIcon(event.type)"></span>
                        </div>

                        {{-- Event Card --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500 transition-colors"
                            role="article" :aria-label="`${event.title} on ${formatDate(event.timestamp)}`">

                            {{-- Event Header --}}
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="event.title">
                                </h4>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
                                    x-text="getTimeAgo(event.timestamp)">
                                </span>
                            </div>

                            {{-- Event Type Badge --}}
                            <div class="inline-block mb-2">
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                    :class="getEventBadgeStyle(event.type)" x-text="formatEventType(event.type)">
                                </span>
                            </div>

                            {{-- Event Description --}}
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3" x-text="event.description">
                            </p>

                            {{-- Event Metadata (if available) --}}
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

            {{-- Load More Button --}}
            <template x-if="events.length > displayedEvents.length">
                <div class="text-center pt-4">
                    <button @click="loadMore()"
                        class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                        Show More Events
                    </button>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="events.length === 0">
                <div class="text-center py-12">
                    <div class="text-4xl mb-4 opacity-20">📋</div>
                    <p class="text-gray-600 dark:text-gray-400">No events yet</p>
                </div>
            </template>
        </div>

        {{-- Feed Variant --}}
    @elseif ($variant === 'feed')
        <div class="space-y-3" x-data="activityTimeline({{ json_encode($events) }})">
            <template x-for="(event, index) in displayedEvents" :key="event.id">
                <div class="flex gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    role="article">

                    {{-- Icon --}}
                    <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg"
                        :class="getEventColor(event.type)">
                        <span x-text="getEventIcon(event.type)"></span>
                    </div>

                    {{-- Content --}}
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

        {{-- Compact Variant --}}
    @elseif ($variant === 'compact')
        <div class="space-y-2" x-data="activityTimeline({{ json_encode($events) }})">
            <template x-for="(event, index) in displayedEvents.slice(0, 5)" :key="event.id">
                <div
                    class="flex items-center justify-between text-xs p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded transition-colors">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <span class="text-lg shrink-0" x-text="getEventIcon(event.type)"></span>
                        <span class="text-gray-900 dark:text-white font-medium truncate" x-text="event.title">
                        </span>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 shrink-0" x-text="getTimeAgo(event.timestamp)">
                    </span>
                </div>
            </template>
        </div>
    @endif

    {{-- Event Type Legend --}}
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

@once
    @vite(['resources/js/components/activity-timeline.js'])
@endonce
