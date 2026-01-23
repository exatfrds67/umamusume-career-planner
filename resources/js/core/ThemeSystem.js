/**
 * Theme System
 * Handles light/dark theme switching with system preference detection
 * Requirements: 13.1, 13.2, 13.3, 13.4, 13.5
 */

class ThemeSystem {
    constructor() {
        // Get theme from localStorage or system preference
        const stored = localStorage.getItem("theme");
        this.theme = stored || this.getSystemTheme();
        
        // Always store in localStorage for consistency
        localStorage.setItem("theme", this.theme);
        this.init();
    }

    init() {
        // Apply theme immediately (DOM already has dark class from inline script)
        this.applyTheme(this.theme, true);

        // Listen for system theme changes
        this.watchSystemTheme();

        // Set up theme toggle button
        this.setupThemeToggle();

        // Set up keyboard shortcut (Alt+T)
        this.setupKeyboardShortcut();

        // Set up background system
        this.setupBackgroundSystem();
    }

    setupKeyboardShortcut() {
        document.addEventListener("keydown", (e) => {
            // Alt+T to toggle theme
            if (e.altKey && e.key.toLowerCase() === "t") {
                e.preventDefault();
                this.toggleTheme();
                this.showThemeChangeToast();
            }
        });
    }

    showThemeChangeToast() {
        // Create toast notification
        const toast = document.createElement("div");
        toast.className =
            "fixed bottom-4 right-4 bg-primary-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in";
        toast.textContent = `Theme: ${
            this.theme === "dark" ? "Dark" : "Light"
        } mode`;
        document.body.appendChild(toast);

        // Remove after 2 seconds
        setTimeout(() => {
            toast.classList.add("animate-fade-out");
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    getSystemTheme() {
        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    }

    applyTheme(theme, isInitial = false) {
        this.theme = theme;

        // Update HTML class (only if different from current state)
        const htmlHasDark = document.documentElement.classList.contains("dark");
        const shouldBeDark = theme === "dark";
        
        if (shouldBeDark && !htmlHasDark) {
            document.documentElement.classList.add("dark");
        } else if (!shouldBeDark && htmlHasDark) {
            document.documentElement.classList.remove("dark");
        }

        // Store preference
        localStorage.setItem("theme", theme);

        // Update background if system exists
        if (window.backgroundSystem) {
            window.backgroundSystem.updateBackground();
        }

        // Update toggle button state
        this.updateToggleButtonState();

        // Announce to screen readers (unless this is initial load)
        if (!isInitial) {
            this.announceThemeChange(theme);
        }
    }

    toggleTheme() {
        const newTheme = this.theme === "dark" ? "light" : "dark";
        this.applyTheme(newTheme);
    }

    watchSystemTheme() {
        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

        mediaQuery.addEventListener("change", (e) => {
            // Only auto-switch if user hasn't set a preference
            if (!localStorage.getItem("theme")) {
                this.applyTheme(e.matches ? "dark" : "light");
            }
        });
    }

    setupThemeToggle() {
        const toggleButton = document.getElementById("theme-toggle");

        if (toggleButton) {
            toggleButton.addEventListener("click", () => {
                this.toggleTheme();
            });

            // Set initial aria-pressed state
            this.updateToggleButtonState();
        }
    }

    updateToggleButtonState() {
        const toggleButton = document.getElementById("theme-toggle");
        if (toggleButton) {
            toggleButton.setAttribute(
                "aria-pressed",
                this.theme === "dark" ? "true" : "false",
            );
        }
    }

    updateSettingsPageTheme() {
        // This will be called by settings.js to sync the theme buttons
        // when running on the settings page
        const themeContainer = document.querySelector(
            ".grid.grid-cols-3.gap-3",
        );
        if (!themeContainer) return;

        const buttons = themeContainer.querySelectorAll("button");
        const themes = ["light", "dark", "system"];
        
        // Find the button for current theme
        let activeIndex = themes.indexOf(this.theme);
        if (activeIndex === -1) activeIndex = 2; // Default to system

        buttons.forEach((btn, idx) => {
            if (idx === activeIndex) {
                btn.classList.remove("border-gray-300", "dark:border-gray-600");
                btn.classList.add("border-primary-500");
                
                // Add visual indicator if not present
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

    setupBackgroundSystem() {
        // Support both hero-background (welcome page) and app-background (app layout)
        const backgroundElements = [
            document.getElementById("hero-background"),
            document.getElementById("app-background"),
        ].filter(Boolean);

        if (backgroundElements.length === 0) return;

        window.backgroundSystem = {
            elements: backgroundElements,
            updateBackground: () => {
                const theme = document.documentElement.classList.contains(
                    "dark",
                )
                    ? "dark"
                    : "light";
                const isMobile = window.innerWidth < 768;
                const device = isMobile ? "mobile" : "desktop";

                backgroundElements.forEach((element) => {
                    const bgUrl = element.getAttribute(
                        `data-bg-${theme}-${device}`,
                    );

                    if (bgUrl) {
                        element.style.backgroundImage = `url('${bgUrl}')`;
                    }
                });
            },
        };

        // Initial background
        window.backgroundSystem.updateBackground();

        // Update on resize
        let resizeTimeout;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                window.backgroundSystem.updateBackground();
            }, 250);
        });
    }

    announceThemeChange(theme) {
        // Create or update live region for screen reader announcements
        let liveRegion = document.getElementById("theme-announcement");

        if (!liveRegion) {
            liveRegion = document.createElement("div");
            liveRegion.id = "theme-announcement";
            liveRegion.className = "sr-only";
            liveRegion.setAttribute("role", "status");
            liveRegion.setAttribute("aria-live", "polite");
            document.body.appendChild(liveRegion);
        }

        liveRegion.textContent = `Theme changed to ${theme} mode`;
    }
}

// Initialize theme system when DOM is ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        window.themeSystem = new ThemeSystem();
    });
} else {
    window.themeSystem = new ThemeSystem();
}

export default ThemeSystem;
