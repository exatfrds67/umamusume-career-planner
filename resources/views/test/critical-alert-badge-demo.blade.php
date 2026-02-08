<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Critical Alert Badge Demo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-6">Critical Alert Badge Component</h3>

                    <div class="space-y-8">
                        {{-- No Alerts State --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">No Alerts (Default State)</h4>
                            <div class="flex items-center gap-4">
                                <x-ai.critical-alert-badge :alert-count="0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    No critical alerts - icon is gray and not pulsing
                                </span>
                            </div>
                        </div>

                        {{-- Single Alert --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Single Alert</h4>
                            <div class="flex items-center gap-4">
                                <x-ai.critical-alert-badge :alert-count="1" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    1 critical alert - red pulsing icon with badge showing "1"
                                </span>
                            </div>
                        </div>

                        {{-- Multiple Alerts --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Multiple Alerts</h4>
                            <div class="flex items-center gap-4">
                                <x-ai.critical-alert-badge :alert-count="5" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    5 critical alerts - red pulsing icon with badge showing "5"
                                </span>
                            </div>
                        </div>

                        {{-- Many Alerts (99+) --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Many Alerts (99+)</h4>
                            <div class="flex items-center gap-4">
                                <x-ai.critical-alert-badge :alert-count="150" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    150 critical alerts - badge shows "99+" when count exceeds 99
                                </span>
                            </div>
                        </div>

                        {{-- Size Variations --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Size Variations</h4>
                            <div class="flex items-center gap-6">
                                <div class="flex flex-col items-center gap-2">
                                    <x-ai.critical-alert-badge :alert-count="3" size="sm" />
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Small</span>
                                </div>
                                <div class="flex flex-col items-center gap-2">
                                    <x-ai.critical-alert-badge :alert-count="3" size="md" />
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Medium (Default)</span>
                                </div>
                                <div class="flex flex-col items-center gap-2">
                                    <x-ai.critical-alert-badge :alert-count="3" size="lg" />
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Large</span>
                                </div>
                            </div>
                        </div>

                        {{-- Accessibility Features --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Accessibility Features</h4>
                            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <li>Screen reader announcements for alert count</li>
                                <li>Keyboard accessible (Tab to focus, Enter/Space to activate)</li>
                                <li>ARIA labels describing current state</li>
                                <li>Reduced motion support (animations disabled when user prefers reduced motion)</li>
                                <li>High contrast mode support</li>
                                <li>Focus visible indicators</li>
                                <li>Proper color contrast ratios (WCAG 2.2 AA compliant)</li>
                            </ul>
                        </div>

                        {{-- Integration Example --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Integration Example (Header)</h4>
                            <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Navigation Header</span>
                                    <div class="flex items-center gap-4">
                                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                        </button>
                                        <x-ai.critical-alert-badge :alert-count="3" />
                                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.75a6 6 0 00-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Event Handling --}}
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <h4 class="text-md font-semibold mb-4">Event Handling</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Clicking the badge dispatches an Alpine.js event: <code
                                    class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">open-advisory-panel</code>
                                with section set to <code
                                    class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">alerts</code>
                            </p>
                            <div x-data="{ message: '' }"
                                @open-advisory-panel.window="message = 'Event received! Section: ' + $event.detail.section">
                                <x-ai.critical-alert-badge :alert-count="2" />
                                <p x-show="message" x-text="message"
                                    class="mt-4 text-sm font-medium text-green-600 dark:text-green-400"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
