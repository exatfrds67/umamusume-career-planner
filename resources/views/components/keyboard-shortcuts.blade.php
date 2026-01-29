{{-- Phase 6: Keyboard Shortcuts Manager Component --}}
<div
    x-data="keyboardShortcuts()"
    @init="init()"
    @shortcut-executed="$dispatch('notification-show', {message: 'Shortcut executed: ' + lastAction, type: 'info', duration: 1500})"
>
    <!-- Hidden Help Dialog Trigger -->
    <button
        @click="showHelpDialog = true"
        type="button"
        aria-label="Show keyboard shortcuts help"
        class="sr-only"
    >
        Show Help
    </button>

    <!-- Help Dialog -->
    <template x-if="showHelpDialog">
        <div
            @click.self="showHelpDialog = false"
            @keydown.escape="showHelpDialog = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="shortcuts-title"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto"
            >
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
                    <h2 id="shortcuts-title" class="text-xl font-bold text-gray-900 dark:text-white">
                        ⌨ Keyboard Shortcuts
                    </h2>
                    <button
                        @click="showHelpDialog = false"
                        type="button"
                        aria-label="Close dialog"
                        class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Shortcuts List -->
                    <div class="space-y-4">
                        <template x-for="shortcut in shortcuts" :key="shortcut.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="shortcut.label"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span x-text="shortcut.id"></span>
                                    </p>
                                </div>
                                <kbd
                                    class="px-2 py-1 text-sm font-semibold text-gray-900 dark:text-white bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded shadow-sm"
                                    x-text="formatShortcut(shortcut)"
                                ></kbd>
                            </div>
                        </template>
                    </div>

                    <!-- Platform Note -->
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <p class="text-xs text-blue-800 dark:text-blue-200">
                            <template x-if="isMac">
                                <span>💡 On Mac, use <kbd class="font-semibold">⌘ Cmd</kbd> instead of Ctrl</span>
                            </template>
                            <template x-if="!isMac">
                                <span>💡 Use <kbd class="font-semibold">Ctrl</kbd> for shortcuts, or <kbd class="font-semibold">Alt</kbd> when specified</span>
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
                    <button
                        @click="resetShortcuts()"
                        type="button"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition"
                    >
                        Reset to Defaults
                    </button>
                    <button
                        @click="showHelpDialog = false"
                        type="button"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition"
                    >
                        Close (ESC)
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
