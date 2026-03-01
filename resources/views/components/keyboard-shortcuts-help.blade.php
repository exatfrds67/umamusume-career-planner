<div x-data="{ open: false }" @keydown.window.shift.slash="open = true" @keydown.window.escape="open = false">
    <!-- Keyboard Shortcuts Help Modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" role="dialog"
            aria-labelledby="keyboard-shortcuts-title" aria-modal="true">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-neutral-200 dark:border-neutral-700">
                <h2 id="keyboard-shortcuts-title" class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                    Keyboard Shortcuts
                </h2>
                <button @click="open = false" class="btn btn-secondary btn-sm" aria-label="Close keyboard shortcuts">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6">
                <!-- Navigation Shortcuts -->
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Navigation</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Skip to main content</span>
                            <kbd class="kbd">Alt + 1</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Toggle sidebar</span>
                            <kbd class="kbd">Alt + S</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Focus search</span>
                            <kbd class="kbd">Alt + /</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Navigate forward</span>
                            <kbd class="kbd">Tab</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Navigate backward</span>
                            <kbd class="kbd">Shift + Tab</kbd>
                        </div>
                    </div>
                </div>

                <!-- Accessibility Shortcuts -->
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Accessibility</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Open accessibility settings</span>
                            <kbd class="kbd">Alt + A</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Show keyboard shortcuts</span>
                            <kbd class="kbd">Shift + ?</kbd>
                        </div>
                    </div>
                </div>

                <!-- General Shortcuts -->
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">General</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Close modal/dialog</span>
                            <kbd class="kbd">Escape</kbd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-neutral-700 dark:text-neutral-300">Activate button/link</span>
                            <kbd class="kbd">Enter</kbd> or <kbd class="kbd">Space</kbd>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div
                    class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-primary-900 dark:text-primary-100 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Accessibility Tips
                    </h4>
                    <ul class="text-sm text-primary-800 dark:text-primary-200 space-y-1 list-disc list-inside">
                        <li>Use Tab to navigate between interactive elements</li>
                        <li>Press Enter or Space to activate buttons and links</li>
                        <li>Use arrow keys to navigate within menus and lists</li>
                        <li>Screen reader users: ARIA labels provide context for all interactive elements</li>
                        <li>All keyboard shortcuts work with screen readers</li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex justify-end gap-3 p-6 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-850">
                <button @click="open = false" class="btn btn-primary">
                    Got it
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Help Button -->
    <button @click="open = true"
        class="fixed bottom-4 right-4 btn btn-primary rounded-full w-12 h-12 shadow-lg hover:shadow-xl transition-shadow z-40"
        aria-label="Show keyboard shortcuts (Shift + ?)" title="Keyboard Shortcuts (Shift + ?)">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/keyboard-shortcuts.css'])
    @endpush
@endonce
