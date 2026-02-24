<div>
    @if ($saved)
        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-3" role="status" aria-live="polite">
            <p class="text-sm text-green-700 dark:text-green-400">Settings saved successfully.</p>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Visual Accessibility --}}
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Visual</h4>
            <div class="space-y-4">
                {{-- High Contrast Mode --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label for="high-contrast-toggle" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            High Contrast Mode
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Increase contrast for better visibility
                        </p>
                    </div>
                    <button type="button" id="high-contrast-toggle"
                        wire:click="$toggle('highContrast')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $highContrast ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                        role="switch" aria-checked="{{ $highContrast ? 'true' : 'false' }}">
                        <span class="sr-only">Enable high contrast mode</span>
                        <span aria-hidden="true"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $highContrast ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                {{-- Reduced Motion --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label for="reduced-motion-toggle" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reduced Motion
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Minimize animations and transitions
                        </p>
                    </div>
                    <button type="button" id="reduced-motion-toggle"
                        wire:click="$toggle('reducedMotion')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $reducedMotion ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                        role="switch" aria-checked="{{ $reducedMotion ? 'true' : 'false' }}">
                        <span class="sr-only">Enable reduced motion</span>
                        <span aria-hidden="true"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $reducedMotion ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                {{-- Font Size --}}
                <div>
                    <label for="font-size-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Font Size
                    </label>
                    <select id="font-size-select" wire:model.live="fontSize"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="small">Small</option>
                        <option value="medium">Medium (Default)</option>
                        <option value="large">Large</option>
                        <option value="x-large">Extra Large</option>
                    </select>
                </div>

                {{-- Color Blind Mode --}}
                <div>
                    <label for="colorblind-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Color Blind Mode
                    </label>
                    <select id="colorblind-select" wire:model.live="colorBlindMode"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="none">None</option>
                        <option value="deuteranopia">Deuteranopia (Red-Green)</option>
                        <option value="protanopia">Protanopia (Red-Green)</option>
                        <option value="tritanopia">Tritanopia (Blue-Yellow)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Focus & Navigation --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Focus & Navigation</h4>
            <div class="space-y-4">
                {{-- Enhanced Focus Indicators --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label for="focus-indicators-toggle" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Enhanced Focus Indicators
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Show prominent focus outlines for keyboard navigation
                        </p>
                    </div>
                    <button type="button" id="focus-indicators-toggle"
                        wire:click="$toggle('focusIndicators')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $focusIndicators ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                        role="switch" aria-checked="{{ $focusIndicators ? 'true' : 'false' }}">
                        <span class="sr-only">Enable enhanced focus indicators</span>
                        <span aria-hidden="true"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $focusIndicators ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Screen Reader --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Screen Reader</h4>
            <div class="space-y-4">
                {{-- Screen Reader Optimization --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label for="screen-reader-toggle" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Screen Reader Optimization
                        </label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Enhanced descriptions for assistive technology
                        </p>
                    </div>
                    <button type="button" id="screen-reader-toggle"
                        wire:click="$toggle('screenReaderOptimization')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $screenReaderOptimization ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                        role="switch" aria-checked="{{ $screenReaderOptimization ? 'true' : 'false' }}">
                        <span class="sr-only">Enable screen reader optimization</span>
                        <span aria-hidden="true"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $screenReaderOptimization ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
                @if (Route::has('keyboard.shortcuts'))
                    <div>
                        <a href="{{ route('keyboard.shortcuts') }}"
                            class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                            View Keyboard Shortcuts →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <button type="button" wire:click="resetToDefaults"
                class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                Reset to Defaults
            </button>
        </div>
    </div>
</div>
