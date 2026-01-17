/**
 * Accessibility Settings Manager
 * Manages user accessibility preferences and applies them to the application
 */

import eventBus from "./EventBus.js";

class AccessibilitySettings {
    constructor() {
        this.settings = {
            textSize: 100, // 80-200%
            highContrast: false,
            reducedMotion: false,
            keyboardNav: false,
        };

        this.init();
    }

    /**
     * Initialize accessibility settings
     */
    init() {
        // Load settings from localStorage
        this.loadSettings();

        // Apply settings to document
        this.applySettings();

        // Listen for settings changes
        eventBus.on("accessibility:update", (newSettings) => {
            this.updateSettings(newSettings);
        });

        // Listen for system preference changes
        this.watchSystemPreferences();
    }

    /**
     * Load settings from localStorage
     */
    loadSettings() {
        const textSize = localStorage.getItem("accessibility_text_size");
        const highContrast = localStorage.getItem(
            "accessibility_high_contrast"
        );
        const reducedMotion = localStorage.getItem(
            "accessibility_reduced_motion"
        );
        const keyboardNav = localStorage.getItem("accessibility_keyboard_nav");

        if (textSize) {
            this.settings.textSize = parseInt(textSize);
        }

        if (highContrast !== null) {
            this.settings.highContrast = highContrast === "true";
        }

        if (reducedMotion !== null) {
            this.settings.reducedMotion = reducedMotion === "true";
        } else {
            // Check system preference
            this.settings.reducedMotion = window.matchMedia(
                "(prefers-reduced-motion: reduce)"
            ).matches;
        }

        if (keyboardNav !== null) {
            this.settings.keyboardNav = keyboardNav === "true";
        }
    }

    /**
     * Save settings to localStorage
     */
    saveSettings() {
        localStorage.setItem(
            "accessibility_text_size",
            this.settings.textSize.toString()
        );
        localStorage.setItem(
            "accessibility_high_contrast",
            this.settings.highContrast.toString()
        );
        localStorage.setItem(
            "accessibility_reduced_motion",
            this.settings.reducedMotion.toString()
        );
        localStorage.setItem(
            "accessibility_keyboard_nav",
            this.settings.keyboardNav.toString()
        );
    }

    /**
     * Update settings
     * @param {Object} newSettings - New settings to apply
     */
    updateSettings(newSettings) {
        // Merge new settings with existing
        this.settings = { ...this.settings, ...newSettings };

        // Save to localStorage
        this.saveSettings();

        // Apply to document
        this.applySettings();

        // Announce change to screen readers
        this.announceChange(newSettings);

        // Emit event
        eventBus.emit("accessibility:changed", this.settings);
    }

    /**
     * Apply settings to document
     */
    applySettings() {
        const body = document.body;

        // Apply text size
        body.style.fontSize = `${this.settings.textSize}%`;

        // Apply high contrast mode
        if (this.settings.highContrast) {
            body.classList.add("high-contrast");
        } else {
            body.classList.remove("high-contrast");
        }

        // Apply reduced motion
        if (this.settings.reducedMotion) {
            document.documentElement.style.setProperty(
                "--animation-duration",
                "0.01ms"
            );
            body.classList.add("reduced-motion");
        } else {
            document.documentElement.style.removeProperty(
                "--animation-duration"
            );
            body.classList.remove("reduced-motion");
        }

        // Apply keyboard navigation enhancement
        if (this.settings.keyboardNav) {
            body.classList.add("keyboard-navigation");
        } else {
            body.classList.remove("keyboard-navigation");
        }

        // Apply large text mode if text size is >= 150%
        if (this.settings.textSize >= 150) {
            body.classList.add("large-text-mode");
        } else {
            body.classList.remove("large-text-mode");
        }
    }

    /**
     * Watch for system preference changes
     */
    watchSystemPreferences() {
        // Watch for reduced motion preference changes
        const reducedMotionQuery = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        );

        reducedMotionQuery.addEventListener("change", (e) => {
            // Only update if user hasn't explicitly set preference
            if (localStorage.getItem("accessibility_reduced_motion") === null) {
                this.updateSettings({ reducedMotion: e.matches });
            }
        });
    }

    /**
     * Announce setting change to screen readers
     * @param {Object} changes - Changed settings
     */
    announceChange(changes) {
        const announcements = [];

        if (changes.textSize !== undefined) {
            announcements.push(`Text size set to ${changes.textSize}%`);
        }

        if (changes.highContrast !== undefined) {
            announcements.push(
                `High contrast mode ${
                    changes.highContrast ? "enabled" : "disabled"
                }`
            );
        }

        if (changes.reducedMotion !== undefined) {
            announcements.push(
                `Reduced motion ${
                    changes.reducedMotion ? "enabled" : "disabled"
                }`
            );
        }

        if (changes.keyboardNav !== undefined) {
            announcements.push(
                `Keyboard navigation enhancement ${
                    changes.keyboardNav ? "enabled" : "disabled"
                }`
            );
        }

        if (announcements.length > 0) {
            this.announce(announcements.join(". "));
        }
    }

    /**
     * Announce message to screen readers
     * @param {string} message - Message to announce
     * @param {string} level - Announcement level (polite or assertive)
     */
    announce(message, level = "polite") {
        const liveRegion = document.getElementById("aria-live-region");
        if (!liveRegion) {
            return;
        }

        // Set aria-live level
        liveRegion.setAttribute("aria-live", level);

        // Clear previous message
        liveRegion.textContent = "";

        // Set new message after a brief delay to ensure screen readers pick it up
        setTimeout(() => {
            liveRegion.textContent = message;
        }, 100);

        // Clear message after 5 seconds
        setTimeout(() => {
            liveRegion.textContent = "";
        }, 5000);
    }

    /**
     * Get current settings
     * @returns {Object} Current settings
     */
    getSettings() {
        return { ...this.settings };
    }

    /**
     * Reset settings to defaults
     */
    resetSettings() {
        this.updateSettings({
            textSize: 100,
            highContrast: false,
            reducedMotion: window.matchMedia("(prefers-reduced-motion: reduce)")
                .matches,
            keyboardNav: false,
        });
    }
}

// Create and export global accessibility settings instance
const accessibilitySettings = new AccessibilitySettings();

// Make it available globally
window.accessibilitySettings = accessibilitySettings;

export default accessibilitySettings;
