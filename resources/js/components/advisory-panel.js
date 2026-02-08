/**
 * Advisory Panel Alpine.js Component
 *
 * Provides client-side interactivity for the AI Advisory Panel including:
 * - Expand/collapse functionality
 * - Dismissal functionality
 * - Keyboard navigation (Tab, Arrow keys, Enter, Escape)
 * - Accessibility compliance (WCAG 2.2 AA)
 *
 * Keyboard Shortcuts:
 * - Alt+A: Toggle panel open/closed
 * - Escape: Close panel
 * - Arrow Up/Down: Navigate between recommendations/alerts
 * - Enter: Expand/collapse focused item
 * - Tab: Standard focus navigation
 *
 * @returns {Object} Alpine.js component data and methods
 */
export default function advisoryPanel() {
    return {
        // State
        isOpen: false,
        activeSection: null,
        expandedRecommendation: null,
        expandedAlert: null,
        focusedItemIndex: -1,
        focusableItems: [],

        /**
         * Initialize the component
         */
        init() {
            // Set up keyboard shortcuts
            this.setupKeyboardShortcuts();

            // Debounced update function for better performance
            this.debouncedUpdateFocusable = this.debounce(() => {
                this.updateFocusableItems();
            }, 150);

            // Update focusable items when panel opens
            this.$watch("isOpen", (value) => {
                if (value) {
                    // Use requestAnimationFrame for smoother rendering
                    requestAnimationFrame(() => {
                        this.$nextTick(() => {
                            this.updateFocusableItems();
                            // Focus first item when panel opens
                            if (this.focusableItems.length > 0) {
                                this.focusedItemIndex = 0;
                                this.focusItem(0);
                            }
                        });
                    });
                } else {
                    // Reset focus when panel closes
                    this.focusedItemIndex = -1;
                    // Clear cached items to free memory
                    this.focusableItems = [];
                }
            });

            // Update focusable items when sections expand/collapse (debounced)
            this.$watch("activeSection", () => {
                this.debouncedUpdateFocusable();
            });

            // Listen for Livewire events
            this.setupLivewireListeners();
        },

        /**
         * Set up keyboard shortcuts
         */
        setupKeyboardShortcuts() {
            // Alt+A to toggle panel
            window.addEventListener("keydown", (e) => {
                // Alt+A: Toggle panel
                if (e.altKey && e.key === "a") {
                    e.preventDefault();
                    this.togglePanel();
                    return;
                }

                // Only handle other shortcuts when panel is open
                if (!this.isOpen) {
                    return;
                }

                // Escape: Close panel
                if (e.key === "Escape") {
                    e.preventDefault();
                    this.closePanel();
                    return;
                }

                // Arrow keys: Navigate between items
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    this.navigateDown();
                    return;
                }

                if (e.key === "ArrowUp") {
                    e.preventDefault();
                    this.navigateUp();
                    return;
                }

                // Enter: Expand/collapse focused item
                if (e.key === "Enter") {
                    const focusedElement = document.activeElement;

                    // Check if focused element is an expandable item
                    if (
                        focusedElement &&
                        focusedElement.hasAttribute("data-expandable")
                    ) {
                        e.preventDefault();
                        focusedElement.click();
                    }
                }
            });
        },

        /**
         * Set up Livewire event listeners
         */
        setupLivewireListeners() {
            // Throttled update function to prevent excessive updates
            const throttledUpdate = this.throttle(() => {
                this.updateFocusableItems();
            }, 200);

            // Listen for panel toggle events
            window.addEventListener("advisory-panel-toggled", (e) => {
                this.isOpen = e.detail.isOpen;
            });

            // Listen for panel open event
            window.addEventListener("advisory-panel-opened", () => {
                this.isOpen = true;
            });

            // Listen for panel close event
            window.addEventListener("advisory-panel-closed", () => {
                this.isOpen = false;
            });

            // Listen for alert dismissed event
            window.addEventListener("alert-dismissed", (e) => {
                this.showToast("Alert dismissed", "success");
                throttledUpdate();
            });

            // Listen for recommendation dismissed event
            window.addEventListener("recommendation-dismissed", (e) => {
                this.showToast("Recommendation dismissed", "success");
                throttledUpdate();
            });

            // Listen for advisory refresh event
            window.addEventListener("advisory-refreshed", () => {
                this.showToast("Advisory refreshed", "info");
                throttledUpdate();
            });

            // Listen for dismissed cleared event
            window.addEventListener("dismissed-cleared", () => {
                this.showToast("Dismissed items cleared", "success");
                throttledUpdate();
            });
        },

        /**
         * Toggle panel open/closed
         */
        togglePanel() {
            this.isOpen = !this.isOpen;

            // Announce to screen readers
            this.announceToScreenReader(
                this.isOpen ? "Advisory panel opened" : "Advisory panel closed",
            );
        },

        /**
         * Open the panel
         */
        openPanel() {
            this.isOpen = true;
            this.announceToScreenReader("Advisory panel opened");
        },

        /**
         * Close the panel
         */
        closePanel() {
            this.isOpen = false;
            this.announceToScreenReader("Advisory panel closed");

            // Return focus to toggle button
            this.$nextTick(() => {
                const toggleButton = document.querySelector(
                    '[aria-label*="Open AI Advisory Panel"]',
                );
                if (toggleButton) {
                    toggleButton.focus();
                }
            });
        },

        /**
         * Toggle section visibility
         * @param {string} section - Section name
         */
        toggleSection(section) {
            if (this.activeSection === section) {
                this.activeSection = null;
                this.announceToScreenReader(`${section} section collapsed`);
            } else {
                this.activeSection = section;
                this.announceToScreenReader(`${section} section expanded`);
            }
        },

        /**
         * Expand/collapse a recommendation
         * @param {string} id - Recommendation ID
         */
        toggleRecommendation(id) {
            if (this.expandedRecommendation === id) {
                this.expandedRecommendation = null;
                this.announceToScreenReader("Recommendation details collapsed");
            } else {
                this.expandedRecommendation = id;
                this.announceToScreenReader("Recommendation details expanded");
            }
        },

        /**
         * Expand/collapse an alert
         * @param {string} id - Alert ID
         */
        toggleAlert(id) {
            if (this.expandedAlert === id) {
                this.expandedAlert = null;
                this.announceToScreenReader("Alert details collapsed");
            } else {
                this.expandedAlert = id;
                this.announceToScreenReader("Alert details expanded");
            }
        },

        /**
         * Update list of focusable items
         */
        updateFocusableItems() {
            if (!this.isOpen) {
                this.focusableItems = [];
                return;
            }

            // Get all focusable items in the panel
            const panel = this.$el.querySelector('[role="dialog"]');
            if (!panel) {
                this.focusableItems = [];
                return;
            }

            // Optimized selector - more specific to reduce query scope
            const selector = [
                "button:not([disabled]):not([aria-hidden='true'])",
                "[href]:not([aria-hidden='true'])",
                "input:not([disabled]):not([aria-hidden='true'])",
                "select:not([disabled]):not([aria-hidden='true'])",
                "textarea:not([disabled]):not([aria-hidden='true'])",
                '[tabindex]:not([tabindex="-1"]):not([aria-hidden="true"])',
            ].join(", ");

            // Use querySelectorAll once and filter efficiently
            const elements = panel.querySelectorAll(selector);
            this.focusableItems = Array.from(elements).filter((item) => {
                // Optimized visibility check - check offsetParent first (fastest)
                if (item.offsetParent === null) return false;

                // Only compute styles if offsetParent check passes
                const style = window.getComputedStyle(item);
                return (
                    style.visibility !== "hidden" && style.display !== "none"
                );
            });

            // If no items found, reset index
            if (this.focusableItems.length === 0) {
                this.focusedItemIndex = -1;
            }
        },

        /**
         * Navigate to next item
         */
        navigateDown() {
            if (this.focusableItems.length === 0) {
                this.updateFocusableItems();
            }

            if (this.focusableItems.length === 0) {
                return;
            }

            // Move to next item
            this.focusedItemIndex =
                (this.focusedItemIndex + 1) % this.focusableItems.length;
            this.focusItem(this.focusedItemIndex);

            // Announce navigation to screen readers
            const currentItem = this.focusableItems[this.focusedItemIndex];
            const itemLabel = this.getItemLabel(currentItem);
            if (itemLabel) {
                this.announceToScreenReader(`Focused: ${itemLabel}`);
            }
        },

        /**
         * Navigate to previous item
         */
        navigateUp() {
            if (this.focusableItems.length === 0) {
                this.updateFocusableItems();
            }

            if (this.focusableItems.length === 0) {
                return;
            }

            // Move to previous item
            this.focusedItemIndex =
                this.focusedItemIndex <= 0
                    ? this.focusableItems.length - 1
                    : this.focusedItemIndex - 1;
            this.focusItem(this.focusedItemIndex);

            // Announce navigation to screen readers
            const currentItem = this.focusableItems[this.focusedItemIndex];
            const itemLabel = this.getItemLabel(currentItem);
            if (itemLabel) {
                this.announceToScreenReader(`Focused: ${itemLabel}`);
            }
        },

        /**
         * Focus an item by index
         * @param {number} index - Item index
         */
        focusItem(index) {
            if (index >= 0 && index < this.focusableItems.length) {
                const item = this.focusableItems[index];

                // Use requestAnimationFrame for smoother focus transitions
                requestAnimationFrame(() => {
                    item.focus();

                    // Optimized scroll - only scroll if not in viewport
                    const rect = item.getBoundingClientRect();
                    const isInViewport =
                        rect.top >= 0 &&
                        rect.left >= 0 &&
                        rect.bottom <=
                            (window.innerHeight ||
                                document.documentElement.clientHeight) &&
                        rect.right <=
                            (window.innerWidth ||
                                document.documentElement.clientWidth);

                    if (!isInViewport) {
                        item.scrollIntoView({
                            behavior: "smooth",
                            block: "nearest",
                            inline: "nearest",
                        });
                    }
                });
            }
        },

        /**
         * Get label for an item (for screen reader announcements)
         * @param {HTMLElement} item - Item element
         * @returns {string|null} Item label
         */
        getItemLabel(item) {
            // Try aria-label first
            if (item.hasAttribute("aria-label")) {
                return item.getAttribute("aria-label");
            }

            // Try text content
            if (item.textContent && item.textContent.trim()) {
                return item.textContent.trim().substring(0, 100);
            }

            // Try title attribute
            if (item.hasAttribute("title")) {
                return item.getAttribute("title");
            }

            return null;
        },

        /**
         * Announce message to screen readers
         * @param {string} message - Message to announce
         */
        announceToScreenReader(message) {
            // Create or get announcement element
            let announcer = document.getElementById("advisory-panel-announcer");

            if (!announcer) {
                announcer = document.createElement("div");
                announcer.id = "advisory-panel-announcer";
                announcer.setAttribute("role", "status");
                announcer.setAttribute("aria-live", "polite");
                announcer.setAttribute("aria-atomic", "true");
                announcer.className = "sr-only";
                document.body.appendChild(announcer);
            }

            // Clear and set new message
            announcer.textContent = "";
            setTimeout(() => {
                announcer.textContent = message;
            }, 100);
        },

        /**
         * Show toast notification
         * @param {string} message - Toast message
         * @param {string} type - Toast type (success, error, info, warning)
         */
        showToast(message, type = "info") {
            // Dispatch custom event for toast notification
            window.dispatchEvent(
                new CustomEvent("show-toast", {
                    detail: { message, type },
                }),
            );
        },

        /**
         * Handle click outside panel
         * @param {Event} event - Click event
         */
        handleClickOutside(event) {
            // Only close if clicking outside the panel content
            const panel = this.$el.querySelector('[role="dialog"]');
            if (panel && !panel.contains(event.target)) {
                this.closePanel();
            }
        },

        /**
         * Get priority color classes
         * @param {string} priority - Priority level
         * @returns {string} Tailwind classes
         */
        getPriorityClasses(priority) {
            const classes = {
                critical:
                    "border-danger-300 dark:border-danger-700 bg-danger-50 dark:bg-danger-900/10",
                high: "border-warning-300 dark:border-warning-700 bg-warning-50 dark:bg-warning-900/10",
                medium: "border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/10",
                low: "border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800",
            };

            return classes[priority] || classes.low;
        },

        /**
         * Get priority badge classes
         * @param {string} priority - Priority level
         * @returns {string} Tailwind classes
         */
        getPriorityBadgeClasses(priority) {
            const classes = {
                critical: "bg-danger-600 text-white",
                high: "bg-warning-600 text-white",
                medium: "bg-primary-600 text-white",
                low: "bg-neutral-500 text-white",
            };

            return classes[priority] || classes.low;
        },

        /**
         * Debounce utility function
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @returns {Function} Debounced function
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Throttle utility function
         * @param {Function} func - Function to throttle
         * @param {number} limit - Time limit in milliseconds
         * @returns {Function} Throttled function
         */
        throttle(func, limit) {
            let inThrottle;
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func(...args);
                    inThrottle = true;
                    setTimeout(() => (inThrottle = false), limit);
                }
            };
        },
    };
}

// Auto-register with Alpine if available
if (typeof window.Alpine !== "undefined") {
    window.Alpine.data("advisoryPanel", advisoryPanel);
}
