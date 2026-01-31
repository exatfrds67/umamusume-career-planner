@props(['tools' => []])

<div class="tool-usage-indicator" x-data="toolUsageIndicator()" x-init="initialize()">

    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Active Tools:</span>

        <template x-for="tool in activeTools" :key="tool.id">
            <div
                class="flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-gray-700 rounded-full border border-blue-200 dark:border-blue-800 shadow-sm">
                {{-- Tool Icon --}}
                <svg class="w-3 h-3 text-blue-600 dark:text-blue-400 animate-pulse" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>

                {{-- Tool Name --}}
                <span class="text-xs font-medium text-gray-900 dark:text-white" x-text="tool.name"></span>

                {{-- Progress Spinner --}}
                <template x-if="tool.status === 'running'">
                    <svg class="w-3 h-3 text-blue-600 dark:text-blue-400 animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </template>

                {{-- Duration --}}
                <template x-if="tool.duration">
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="`${tool.duration}s`"></span>
                </template>
            </div>
        </template>

        {{-- No Active Tools --}}
        <template x-if="activeTools.length === 0">
            <span class="text-xs text-gray-500 dark:text-gray-400">None</span>
        </template>
    </div>
</div>

@once
    @vite(['resources/js/components/ai/tool-usage-indicator.js'])
@endonce
