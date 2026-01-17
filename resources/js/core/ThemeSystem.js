/**
 * Theme System
 * Handles light/dark theme switching with system preference detection
 * Requirements: 13.1, 13.2, 13.3, 13.4, 13.5
 */

class ThemeSystem {
    constructor() {
        this.theme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }

    init() {
        // Apply initial theme
        this.applyTheme(this.theme);

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

                // Update toggle button state if it exists
                const toggleButton = document.getElementById("theme-toggle");
                if (toggleButton) {
                    toggleButton.setAttribute(
                        "aria-pressed",
                        this.theme === "dark" ? "true" : "false",
                    );
                }

                // Update settings page theme selection
                this.updateSettingsPageTheme();

                // Show toast notification
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

    getStoredTheme() {
        return localStorage.getItem("theme");
    }

    applyTheme(theme) {
        this.theme = theme;

        // Update HTML class
        if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        // Store preference
        localStorage.setItem("theme", theme);

        // Update background if system exists
        if (window.backgroundSystem) {
            window.backgroundSystem.updateBackground();
        }

        // Announce to screen readers
        this.announceThemeChange(theme);
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

                // Update aria-pressed state
                toggleButton.setAttribute(
                    "aria-pressed",
                    this.theme === "dark" ? "true" : "false",
                );

                // Update settings page theme selection if it exists
                this.updateSettingsPageTheme();
            });

            // Set initial aria-pressed state
            toggleButton.setAttribute(
                "aria-pressed",
                this.theme === "dark" ? "true" : "false",
            );
        }
    }

    updateSettingsPageTheme() {
        // Update theme selection buttons in settings page
        const themeContainer = document.querySelector(
            ".grid.grid-cols-3.gap-3",
        );
        if (!themeContainer) return;

        const buttons = themeContainer.querySelectorAll("button");
        const themes = ["light", "dark", "system"];
        const currentThemeIndex = themes.indexOf(this.theme);

        if (currentThemeIndex !== -1 && buttons[currentThemeIndex]) {
            // Remove active state from all buttons
            buttons.forEach((btn) => {
                btn.classList.remove("border-primary-500");
                btn.classList.add("border-gray-300", "dark:border-gray-600");
                const indicator = btn.querySelector(".border-primary-500");
                if (indicator) indicator.remove();
            });

            // Add active state to current theme button
            const activeButton = buttons[currentThemeIndex];
            activeButton.classList.remove(
                "border-gray-300",
                "dark:border-gray-600",
            );
            activeButton.classList.add("border-primary-500");

            // Add visual indicator
            if (!activeButton.querySelector(".border-primary-500")) {
                const indicator = document.createElement("span");
                indicator.className =
                    "pointer-events-none absolute -inset-px rounded-lg border-2 border-primary-500";
                indicator.setAttribute("aria-hidden", "true");
                activeButton.appendChild(indicator);
            }
        }
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
