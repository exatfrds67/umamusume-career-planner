<div x-data="{
    open: false,
    textSize: $persist(100).as('accessibility_text_size'),
    highContrast: $persist(false).as('accessibility_high_contrast'),
    reducedMotion: $persist(false).as('accessibility_reduced_motion'),
    keyboardNav: $persist(false).as('accessibility_keyboard_nav'),

    init() {
        // Listen for open event
        this.$watch('open', value => {
            if (value) {
                // Trap focus in panel
                this.$nextTick(() => {
                    window.accessibilitySystem.trapFocus(this.$refs.panel);
                });
            } else {
                // Release focus trap
                window.accessibilitySystem.releaseFocus();
            }
        });

        // Listen for global open event
        window.addEventListener('open-accessibility-settings', () => {
            this.open = true;
        });

        // Listen for custom event from Alpine
        this.$el.addEventListener('open-accessibility-settings', () => {
            this.open = true;
        });
    },

    updateTextSize(delta) {
        this.textSize = Math.max(80, Math.min(200, this.textSize + delta));
        this.applySettings();
    },

    toggleHighContrast() {
        this.highContrast = !this.highContrast;
        this.applySettings();
    },

    toggleReducedMotion() {
        this.reducedMotion = !this.reducedMotion;
        this.applySettings();
    },

    toggleKeyboardNav() {
        this.keyboardNav = !this.keyboardNav;
        this.applySettings();
    },

    applySettings() {
        window.accessibilitySettings.updateSettings({
            textSize: this.textSize,
            highContrast: this.highContrast,
            reducedMotion: this.reducedMotion,
            keyboardNav: this.keyboardNav
        });
    },

    resetSettings() {
        this.textSize = 100;
        this.highContrast = false;
        this.reducedMotion = false;
        this.keyboardNav = false;
        this.applySettings();
        window.accessibilitySettings.announce('Accessibility settings reset to defaults', 'polite');
    },

    close() {
        this.open = false;
    }
}" @keydown.escape.window="open = false">
    <!-- Accessibility Settings Panel -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="accessibility-settings-panel"
        style="display: none;" role="dialog" aria-modal="true" aria-labelledby="accessibility-settings-title">
        <!-- Backdrop -->
        <div @click="close()" class="accessibility-settings-backdrop"></div>

        <!-- Panel Content -->
        <div x-ref="panel" class="accessibility-settings-content" @click.stop>
            <!-- Header -->
            <div class="accessibility-settings-header">
                <h2 id="accessibility-settings-title" class="accessibility-settings-title">
                    Accessibility Settings
                </h2>
                <button @click="close()" class="accessibility-settings-close" aria-label="Close accessibility settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="accessibility-settings-body">
                <!-- Text Size Control -->
                <div class="accessibility-setting-group">
                    <span id="text-size-label" class="accessibility-setting-label">
                        Text Size
                    </span>
                    <div class="accessibility-setting-controls" role="group" aria-labelledby="text-size-label">
                        <button @click="updateTextSize(-10)" class="btn btn-secondary btn-sm"
                            aria-label="Decrease text size" :disabled="textSize <= 80">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <span class="accessibility-setting-value" aria-live="polite" aria-atomic="true">
                            <span x-text="textSize"></span>%
                        </span>
                        <button @click="updateTextSize(10)" class="btn btn-secondary btn-sm"
                            aria-label="Increase text size" :disabled="textSize >= 200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                    <p class="accessibility-setting-help">
                        Adjust text size from 80% to 200%. Current size: <span x-text="textSize"></span>%
                    </p>
                </div>

                <!-- High Contrast Mode -->
                <div class="accessibility-setting-group">
                    <span id="high-contrast-label" class="accessibility-setting-label">
                        High Contrast Mode
                    </span>
                    <label class="accessibility-toggle">
                        <input type="checkbox" id="high-contrast-toggle" name="high_contrast" x-model="highContrast"
                            @change="toggleHighContrast()" class="accessibility-toggle-input"
                            aria-labelledby="high-contrast-label" aria-describedby="high-contrast-help">
                        <span class="accessibility-toggle-slider"></span>
                        <span class="accessibility-toggle-label">
                            <span x-text="highContrast ? 'Enabled' : 'Disabled'"></span>
                        </span>
                    </label>
                    <p id="high-contrast-help" class="accessibility-setting-help">
                        Increases contrast and border visibility for better readability.
                    </p>
                </div>

                <!-- Reduced Motion -->
                <div class="accessibility-setting-group">
                    <span id="reduced-motion-label" class="accessibility-setting-label">
                        Reduced Motion
                    </span>
                    <label class="accessibility-toggle">
                        <input type="checkbox" id="reduced-motion-toggle" name="reduced_motion" x-model="reducedMotion"
                            @change="toggleReducedMotion()" class="accessibility-toggle-input"
                            aria-labelledby="reduced-motion-label" aria-describedby="reduced-motion-help">
                        <span class="accessibility-toggle-slider"></span>
                        <span class="accessibility-toggle-label">
                            <span x-text="reducedMotion ? 'Enabled' : 'Disabled'"></span>
                        </span>
                    </label>
                    <p id="reduced-motion-help" class="accessibility-setting-help">
                        Minimizes animations and transitions for users sensitive to motion.
                    </p>
                </div>

                <!-- Keyboard Navigation Enhancement -->
                <div class="accessibility-setting-group">
                    <span id="keyboard-nav-label" class="accessibility-setting-label">
                        Enhanced Keyboard Navigation
                    </span>
                    <label class="accessibility-toggle">
                        <input type="checkbox" id="keyboard-nav-toggle" name="keyboard_nav" x-model="keyboardNav"
                            @change="toggleKeyboardNav()" class="accessibility-toggle-input"
                            aria-labelledby="keyboard-nav-label" aria-describedby="keyboard-nav-help">
                        <span class="accessibility-toggle-slider"></span>
                        <span class="accessibility-toggle-label">
                            <span x-text="keyboardNav ? 'Enabled' : 'Disabled'"></span>
                        </span>
                    </label>
                    <p id="keyboard-nav-help" class="accessibility-setting-help">
                        Enhances focus indicators and keyboard navigation support.
                    </p>
                </div>

                <!-- Keyboard Shortcuts Info -->
                <div class="accessibility-setting-group">
                    <span class="accessibility-setting-label">
                        Keyboard Shortcuts
                    </span>
                    <div class="accessibility-setting-help">
                        <p>Available keyboard shortcuts:</p>
                        <ul>
                            <li><strong>Alt + A</strong> - Open accessibility settings</li>
                            <li><strong>Alt + S</strong> - Toggle sidebar</li>
                            <li><strong>Alt + /</strong> - Focus search</li>
                            <li><strong>Alt + 1</strong> - Skip to main content</li>
                            <li><strong>Escape</strong> - Close modals</li>
                        </ul>
                    </div>
                </div>

                <!-- Current Settings Status -->
                <div class="accessibility-status">
                    <div class="accessibility-status-item">
                        <span class="accessibility-status-label">Text Size:</span>
                        <span class="accessibility-status-value" x-text="textSize + '%'"></span>
                    </div>
                    <div class="accessibility-status-item">
                        <span class="accessibility-status-label">High Contrast:</span>
                        <span class="accessibility-status-value" x-text="highContrast ? 'On' : 'Off'"></span>
                    </div>
                    <div class="accessibility-status-item">
                        <span class="accessibility-status-label">Reduced Motion:</span>
                        <span class="accessibility-status-value" x-text="reducedMotion ? 'On' : 'Off'"></span>
                    </div>
                    <div class="accessibility-status-item">
                        <span class="accessibility-status-label">Keyboard Navigation:</span>
                        <span class="accessibility-status-value" x-text="keyboardNav ? 'On' : 'Off'"></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="accessibility-settings-footer">
                <button @click="resetSettings()" class="btn btn-outline btn-md flex-1">
                    Reset to Defaults
                </button>
                <button @click="close()" class="btn btn-primary btn-md flex-1">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
