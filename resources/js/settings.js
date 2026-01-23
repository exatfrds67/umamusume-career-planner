/**
 * Settings Page Interactive Functionality
 * Handles all toggle switches, sliders, and real-time updates
 */

document.addEventListener("DOMContentLoaded", function () {
    // Initialize all toggle switches
    initializeToggles();

    // Initialize font size slider
    initializeFontSizeSlider();

    // Initialize theme selection
    initializeThemeSelection();

    // Initialize settings search
    initializeSettingsSearch();

    // Initialize save functionality
    initializeSaveButtons();

    // Load saved settings from localStorage
    loadSavedSettings();
});

/**
 * Initialize all toggle switches with click handlers
 */
function initializeToggles() {
    const toggles = document.querySelectorAll('[role="switch"]');

    toggles.forEach((toggle) => {
        toggle.addEventListener("click", function () {
            const isChecked = this.getAttribute("aria-checked") === "true";
            const newState = !isChecked;

            // Update aria-checked attribute
            this.setAttribute("aria-checked", newState);

            // Update visual state
            const slider = this.querySelector('span[aria-hidden="true"]');
            if (slider) {
                if (newState) {
                    slider.classList.remove("translate-x-0");
                    slider.classList.add("translate-x-5");
                    this.classList.remove("bg-gray-200", "dark:bg-gray-600");
                    this.classList.add("bg-primary-600");
                } else {
                    slider.classList.remove("translate-x-5");
                    slider.classList.add("translate-x-0");
                    this.classList.remove("bg-primary-600");
                    this.classList.add("bg-gray-200", "dark:bg-gray-600");
                }
            }

            // Save to localStorage
            const settingId = this.id;
            if (settingId) {
                saveSettingToStorage(settingId, newState);

                // Apply immediate effects for certain settings
                applySettingEffect(settingId, newState);
            }

            // Show save indicator
            showSaveIndicator();
        });
    });
}

/**
 * Initialize font size slider
 */
function initializeFontSizeSlider() {
    const slider = document.getElementById("font-size");
    const valueDisplay = document.getElementById("font-size-value");

    if (slider && valueDisplay) {
        slider.addEventListener("input", function () {
            const value = this.value;
            valueDisplay.textContent = value + "%";

            // Apply font size change in real-time
            document.documentElement.style.fontSize = value / 100 + "rem";

            // Save to localStorage
            saveSettingToStorage("font-size", value);
            showSaveIndicator();
        });
    }
}

/**
 * Initialize theme selection buttons
 */
function initializeThemeSelection() {
    const themeContainer = document.querySelector(".grid.grid-cols-3.gap-3");
    if (!themeContainer) return;

    const buttons = themeContainer.querySelectorAll("button");

    buttons.forEach((button, index) => {
        button.addEventListener("click", function () {
            // Determine theme based on button index
            const themes = ["light", "dark", "system"];
            const selectedTheme = themes[index];

            // Delegate to ThemeSystem if available
            if (window.themeSystem) {
                window.themeSystem.applyTheme(selectedTheme);
                // Sync the button UI
                updateThemeButtonUI(buttons, index);
            } else {
                // Fallback if ThemeSystem isn't loaded
                applyTheme(selectedTheme);
                updateThemeButtonUI(buttons, index);
            }

            // Save to localStorage
            saveSettingToStorage("theme", selectedTheme);
            showSaveIndicator();
        });
    });

    // Initial sync with current theme
    if (window.themeSystem) {
        setTimeout(() => {
            const themes = ["light", "dark", "system"];
            const currentIndex = themes.indexOf(window.themeSystem.theme);
            if (currentIndex !== -1) {
                updateThemeButtonUI(buttons, currentIndex);
            }
        }, 100);
    }
}

/**
 * Update theme button UI (extracted for reusability)
 */
function updateThemeButtonUI(buttons, activeIndex) {
    buttons.forEach((btn, idx) => {
        if (idx === activeIndex) {
            btn.classList.remove("border-gray-300", "dark:border-gray-600");
            btn.classList.add("border-primary-500");
            
            if (!btn.querySelector(".border-primary-500")) {
                const indicator = document.createElement("span");
                indicator.className =
                    "pointer-events-none absolute -inset-px rounded-lg border-2 border-primary-500";
                indicator.setAttribute("aria-hidden", "true");
                btn.appendChild(indicator);
            }
        } else {
            btn.classList.remove("border-primary-500");
            btn.classList.add("border-gray-300", "dark:border-gray-600");
            const indicator = btn.querySelector(".border-primary-500");
            if (indicator) indicator.remove();
        }
    });
}

/**
 * Apply theme to the document (fallback if ThemeSystem not available)
 */
function applyTheme(theme) {
    const html = document.documentElement;

    if (theme === "dark") {
        html.classList.add("dark");
    } else if (theme === "light") {
        html.classList.remove("dark");
    } else if (theme === "system") {
        // Use system preference
        if (
            window.matchMedia &&
            window.matchMedia("(prefers-color-scheme: dark)").matches
        ) {
            html.classList.add("dark");
        } else {
            html.classList.remove("dark");
        }
    }

    // Update header theme toggle button state
    const themeToggle = document.getElementById("theme-toggle");
    if (themeToggle) {
        const isDark = html.classList.contains("dark");
        themeToggle.setAttribute("aria-pressed", isDark ? "true" : "false");
    }

    // Announce theme change to screen readers
    announceThemeChange(theme);
    
    // Store preference
    localStorage.setItem("theme", theme);
}

/**
 * Announce theme change to screen readers
 */
function announceThemeChange(theme) {
    let liveRegion = document.getElementById("theme-announcement");

    if (!liveRegion) {
        liveRegion = document.createElement("div");
        liveRegion.id = "theme-announcement";
        liveRegion.className = "sr-only";
        liveRegion.setAttribute("role", "status");
        liveRegion.setAttribute("aria-live", "polite");
        document.body.appendChild(liveRegion);
    }

    const isDark = document.documentElement.classList.contains("dark");
    liveRegion.textContent = `Theme changed to ${
        isDark ? "dark" : "light"
    } mode`;
}

/**
 * Initialize settings search functionality
 */
function initializeSettingsSearch() {
    const searchInput = document.getElementById("settings-search");
    if (!searchInput) return;

    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();
        const settingSections = document.querySelectorAll(
            '[id^="account"], [id^="privacy"], [id^="ai"], [id^="appearance"], [id^="accessibility"], [id^="notifications"], [id^="gameplay"], [id^="advanced"]'
        );

        settingSections.forEach((section) => {
            const text = section.textContent.toLowerCase();
            if (text.includes(query) || query === "") {
                section.style.display = "";
            } else {
                section.style.display = "none";
            }
        });
    });
}

/**
 * Initialize save buttons
 */
function initializeSaveButtons() {
    // Save All Changes button
    const saveButton = Array.from(document.querySelectorAll("button")).find(
        (btn) => btn.textContent.includes("Save All Changes")
    );
    if (saveButton) {
        saveButton.addEventListener("click", function () {
            saveAllSettings();
            showSuccessMessage("Settings saved successfully!");
        });
    }

    // Reset to Defaults button
    const resetButton = Array.from(document.querySelectorAll("button")).find(
        (btn) => btn.textContent.includes("Reset to Defaults")
    );
    if (resetButton) {
        resetButton.addEventListener("click", function () {
            if (
                confirm(
                    "Are you sure you want to reset all settings to defaults? This cannot be undone."
                )
            ) {
                resetToDefaults();
                showSuccessMessage("Settings reset to defaults");
            }
        });
    }

    // Cancel button
    const cancelButton = Array.from(document.querySelectorAll("button")).find(
        (btn) => btn.textContent.includes("Cancel")
    );
    if (cancelButton) {
        cancelButton.addEventListener("click", function () {
            loadSavedSettings();
            showSuccessMessage("Changes discarded");
        });
    }
}

/**
 * Save setting to localStorage
 */
function saveSettingToStorage(key, value) {
    try {
        const settings = JSON.parse(
            localStorage.getItem("app_settings") || "{}"
        );
        settings[key] = value;
        localStorage.setItem("app_settings", JSON.stringify(settings));
    } catch (error) {
        console.error("Error saving setting:", error);
    }
}

/**
 * Load saved settings from localStorage
 */
function loadSavedSettings() {
    try {
        const settings = JSON.parse(
            localStorage.getItem("app_settings") || "{}"
        );

        // Apply each saved setting
        Object.keys(settings).forEach((key) => {
            const value = settings[key];
            const element = document.getElementById(key);

            if (element) {
                if (element.getAttribute("role") === "switch") {
                    // Toggle switch
                    element.setAttribute("aria-checked", value);
                    const slider = element.querySelector(
                        'span[aria-hidden="true"]'
                    );
                    if (slider) {
                        if (value) {
                            slider.classList.remove("translate-x-0");
                            slider.classList.add("translate-x-5");
                            element.classList.remove(
                                "bg-gray-200",
                                "dark:bg-gray-600"
                            );
                            element.classList.add("bg-primary-600");
                        } else {
                            slider.classList.remove("translate-x-5");
                            slider.classList.add("translate-x-0");
                            element.classList.remove("bg-primary-600");
                            element.classList.add(
                                "bg-gray-200",
                                "dark:bg-gray-600"
                            );
                        }
                    }
                } else if (element.type === "range") {
                    // Slider
                    element.value = value;
                    const valueDisplay = document.getElementById(
                        key + "-value"
                    );
                    if (valueDisplay) {
                        valueDisplay.textContent = value + "%";
                    }
                } else if (element.tagName === "SELECT") {
                    // Dropdown
                    element.value = value;
                } else if (
                    element.type === "text" ||
                    element.type === "email" ||
                    element.type === "number" ||
                    element.type === "time"
                ) {
                    // Input fields
                    element.value = value;
                }

                // Apply the effect
                applySettingEffect(key, value);
            }
        });

        // Apply theme
        if (settings.theme) {
            applyTheme(settings.theme);
        }
    } catch (error) {
        console.error("Error loading settings:", error);
    }
}

/**
 * Apply immediate effects for certain settings
 */
function applySettingEffect(settingId, value) {
    switch (settingId) {
        case "animations":
            if (!value) {
                document.documentElement.style.setProperty(
                    "--animation-duration",
                    "0s"
                );
            } else {
                document.documentElement.style.removeProperty(
                    "--animation-duration"
                );
            }
            break;

        case "reduced-motion":
            if (value) {
                document.documentElement.classList.add("reduce-motion");
            } else {
                document.documentElement.classList.remove("reduce-motion");
            }
            break;

        case "high-contrast":
            if (value) {
                document.documentElement.classList.add("high-contrast");
            } else {
                document.documentElement.classList.remove("high-contrast");
            }
            break;

        case "compact-mode":
            if (value) {
                document.documentElement.classList.add("compact-mode");
            } else {
                document.documentElement.classList.remove("compact-mode");
            }
            break;

        case "font-size":
            document.documentElement.style.fontSize = value / 100 + "rem";
            break;
    }
}

/**
 * Save all settings
 */
function saveAllSettings() {
    const settings = {};

    // Collect all toggle switches
    document.querySelectorAll('[role="switch"]').forEach((toggle) => {
        if (toggle.id) {
            settings[toggle.id] =
                toggle.getAttribute("aria-checked") === "true";
        }
    });

    // Collect all select dropdowns
    document.querySelectorAll("select").forEach((select) => {
        if (select.id) {
            settings[select.id] = select.value;
        }
    });

    // Collect all input fields
    document
        .querySelectorAll(
            'input[type="text"], input[type="email"], input[type="number"], input[type="time"], input[type="range"]'
        )
        .forEach((input) => {
            if (input.id) {
                settings[input.id] = input.value;
            }
        });

    // Save to localStorage
    try {
        localStorage.setItem("app_settings", JSON.stringify(settings));

        // Optionally send to server
        // fetch('/api/settings', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(settings)
        // });
    } catch (error) {
        console.error("Error saving settings:", error);
        showErrorMessage("Failed to save settings");
    }
}

/**
 * Reset all settings to defaults
 */
function resetToDefaults() {
    localStorage.removeItem("app_settings");
    location.reload();
}

/**
 * Show save indicator
 */
function showSaveIndicator() {
    // Add a subtle indicator that changes need to be saved
    const saveButton = Array.from(document.querySelectorAll("button")).find(
        (btn) => btn.textContent.includes("Save All Changes")
    );
    if (saveButton && !saveButton.classList.contains("ring-2")) {
        saveButton.classList.add("ring-2", "ring-primary-500", "ring-offset-2");

        // Remove after 3 seconds
        setTimeout(() => {
            saveButton.classList.remove(
                "ring-2",
                "ring-primary-500",
                "ring-offset-2"
            );
        }, 3000);
    }
}

/**
 * Show success message
 */
function showSuccessMessage(message) {
    // Create toast notification
    const toast = document.createElement("div");
    toast.className =
        "fixed bottom-4 right-4 bg-success-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in";
    toast.textContent = message;
    document.body.appendChild(toast);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add("animate-fade-out");
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    const toast = document.createElement("div");
    toast.className =
        "fixed bottom-4 right-4 bg-error-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in";
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("animate-fade-out");
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Export functions for use in other modules if needed
if (typeof module !== "undefined" && module.exports) {
    module.exports = {
        saveSettingToStorage,
        loadSavedSettings,
        applyTheme,
        resetToDefaults,
    };
}
