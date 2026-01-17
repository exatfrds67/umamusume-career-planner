/**
 * Accessibility System
 * Manages focus indicators, skip links, keyboard shortcuts, and ARIA live regions
 */

import eventBus from "./EventBus.js";
import accessibilitySettings from "./AccessibilitySettings.js";

class AccessibilitySystem {
    constructor() {
        this.focusableElements = [
            "a[href]",
            "button:not([disabled])",
            "input:not([disabled])",
            "select:not([disabled])",
            "textarea:not([disabled])",
            '[tabindex]:not([tabindex="-1"])',
        ];

        this.keyboardShortcuts = new Map();
        this.init();
    }

    /**
     * Initialize accessibility system
     */
    init() {
        this.setupFocusManagement();
        this.setupSkipLinks();
        this.setupKeyboardShortcuts();
        this.setupAriaLiveRegion();
        this.registerDefaultShortcuts();
    }

    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Track focus for keyboard navigation
        let isUsingKeyboard = false;

        // Detect keyboard usage
        document.addEventListener("keydown", (e) => {
            if (e.key === "Tab") {
                isUsingKeyboard = true;
                document.body.classList.add("using-keyboard");
            }
        });

        // Detect mouse usage
        document.addEventListener("mousedown", () => {
            isUsingKeyboard = false;
            document.body.classList.remove("using-keyboard");
        });

        // Ensure focus is visible for keyboard users
        document.addEventListener("focusin", (e) => {
            if (isUsingKeyboard && e.target) {
                this.ensureFocusVisible(e.target);
            }
        });

        // Listen for focus trap requests
        eventBus.on("focus:trap", (container) => {
            this.trapFocus(container);
        });

        // Listen for focus release requests
        eventBus.on("focus:release", () => {
            this.releaseFocus();
        });
    }

    /**
     * Ensure focus is visible
     * @param {HTMLElement} element - Element to ensure focus visibility
     */
    ensureFocusVisible(element) {
        // Scroll element into view if needed
        if (element.scrollIntoViewIfNeeded) {
            element.scrollIntoViewIfNeeded();
        } else {
            element.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
                inline: "nearest",
            });
        }

        // Emit focus event
        eventBus.emit("focus:changed", element);
    }

    /**
     * Setup skip links
     */
    setupSkipLinks() {
        // Skip links are already in the HTML
        // Add keyboard shortcut to activate first skip link
        this.registerShortcut("Alt+1", () => {
            const skipLink = document.querySelector(".skip-link");
            if (skipLink) {
                skipLink.focus();
            }
        });
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener("keydown", (e) => {
            const shortcut = this.getShortcutKey(e);
            const handler = this.keyboardShortcuts.get(shortcut);

            if (handler) {
                e.preventDefault();
                handler(e);
            }
        });
    }

    /**
     * Get shortcut key from keyboard event
     * @param {KeyboardEvent} e - Keyboard event
     * @returns {string} Shortcut key string
     */
    getShortcutKey(e) {
        const parts = [];

        if (e.ctrlKey) parts.push("Ctrl");
        if (e.altKey) parts.push("Alt");
        if (e.shiftKey) parts.push("Shift");
        if (e.metaKey) parts.push("Meta");

        // Add the key itself
        if (
            e.key &&
            e.key !== "Control" &&
            e.key !== "Alt" &&
            e.key !== "Shift" &&
            e.key !== "Meta"
        ) {
            parts.push(e.key);
        }

        return parts.join("+");
    }

    /**
     * Register keyboard shortcut
     * @param {string} shortcut - Shortcut key combination (e.g., "Ctrl+K")
     * @param {Function} handler - Handler function
     */
    registerShortcut(shortcut, handler) {
        this.keyboardShortcuts.set(shortcut, handler);
    }

    /**
     * Unregister keyboard shortcut
     * @param {string} shortcut - Shortcut key combination
     */
    unregisterShortcut(shortcut) {
        this.keyboardShortcuts.delete(shortcut);
    }

    /**
     * Register default keyboard shortcuts
     */
    registerDefaultShortcuts() {
        // Open accessibility settings (Alt+A)
        this.registerShortcut("Alt+a", () => {
            eventBus.emit("open-accessibility-settings");
        });

        // Toggle sidebar (Alt+S)
        this.registerShortcut("Alt+s", () => {
            eventBus.emit("toggle-sidebar");
        });

        // Focus search (Alt+/)
        this.registerShortcut("Alt+/", () => {
            const searchInput = document.querySelector('[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        });

        // Show keyboard shortcuts help (Shift+?)
        this.registerShortcut("Shift+?", () => {
            eventBus.emit("show-keyboard-shortcuts");
        });

        // Escape key to close modals
        this.registerShortcut("Escape", () => {
            eventBus.emit("close-modal");
        });

        // Home key to scroll to top
        this.registerShortcut("Home", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

        // End key to scroll to bottom
        this.registerShortcut("End", () => {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: "smooth",
            });
        });
    }

    /**
     * Setup ARIA live region
     */
    setupAriaLiveRegion() {
        // ARIA live region is already in the HTML
        // Provide helper method to announce messages
        eventBus.on("announce", ({ message, level = "polite" }) => {
            this.announce(message, level);
        });
    }

    /**
     * Announce message to screen readers
     * @param {string} message - Message to announce
     * @param {string} level - Announcement level (polite or assertive)
     */
    announce(message, level = "polite") {
        accessibilitySettings.announce(message, level);
    }

    /**
     * Trap focus within a container
     * @param {HTMLElement|string} container - Container element or selector
     */
    trapFocus(container) {
        const containerEl =
            typeof container === "string"
                ? document.querySelector(container)
                : container;

        if (!containerEl) {
            return;
        }

        // Get all focusable elements within container
        const focusableElements = containerEl.querySelectorAll(
            this.focusableElements.join(", ")
        );

        if (focusableElements.length === 0) {
            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Store previous focus
        this.previousFocus = document.activeElement;

        // Focus first element
        firstElement.focus();

        // Handle tab key
        const handleTab = (e) => {
            if (e.key !== "Tab") {
                return;
            }

            if (e.shiftKey) {
                // Shift+Tab
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        // Store handler for cleanup
        this.focusTrapHandler = handleTab;

        // Add event listener
        document.addEventListener("keydown", handleTab);

        // Mark container as focus trapped
        containerEl.setAttribute("data-focus-trapped", "true");
    }

    /**
     * Release focus trap
     */
    releaseFocus() {
        // Remove event listener
        if (this.focusTrapHandler) {
            document.removeEventListener("keydown", this.focusTrapHandler);
            this.focusTrapHandler = null;
        }

        // Remove focus trap markers
        const trappedContainers = document.querySelectorAll(
            "[data-focus-trapped]"
        );
        trappedContainers.forEach((container) => {
            container.removeAttribute("data-focus-trapped");
        });

        // Restore previous focus
        if (this.previousFocus) {
            this.previousFocus.focus();
            this.previousFocus = null;
        }
    }

    /**
     * Focus first error in form
     * @param {HTMLElement|string} form - Form element or selector
     */
    focusFirstError(form) {
        const formEl =
            typeof form === "string" ? document.querySelector(form) : form;

        if (!formEl) {
            return;
        }

        // Find first element with aria-invalid="true"
        const firstError = formEl.querySelector('[aria-invalid="true"]');

        if (firstError) {
            firstError.focus();
            this.ensureFocusVisible(firstError);

            // Announce error to screen readers
            const errorMessage = formEl.querySelector(
                `[id="${firstError.getAttribute("aria-describedby")}"]`
            );

            if (errorMessage) {
                this.announce(
                    `Error: ${errorMessage.textContent}`,
                    "assertive"
                );
            }
        }
    }

    /**
     * Get all focusable elements in container
     * @param {HTMLElement|string} container - Container element or selector
     * @returns {NodeList} Focusable elements
     */
    getFocusableElements(container) {
        const containerEl =
            typeof container === "string"
                ? document.querySelector(container)
                : container;

        if (!containerEl) {
            return [];
        }

        return containerEl.querySelectorAll(this.focusableElements.join(", "));
    }

    /**
     * Check if element is focusable
     * @param {HTMLElement} element - Element to check
     * @returns {boolean} True if element is focusable
     */
    isFocusable(element) {
        if (!element) {
            return false;
        }

        // Check if element matches focusable selector
        return element.matches(this.focusableElements.join(", "));
    }
}

// Create and export global accessibility system instance
const accessibilitySystem = new AccessibilitySystem();

// Make it available globally
window.accessibilitySystem = accessibilitySystem;

export default accessibilitySystem;
