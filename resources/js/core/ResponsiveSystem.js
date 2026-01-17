/**
 * Responsive System for managing breakpoints and viewport changes
 * Provides reactive breakpoint detection and event emission
 */

import eventBus from "./EventBus.js";

class ResponsiveSystem {
    constructor() {
        // Define breakpoints matching Tailwind CSS v4 defaults
        this.breakpoints = {
            mobile: 320,
            tablet: 768,
            desktop: 1024,
            wide: 1280,
        };

        this.currentBreakpoint = this.getBreakpoint();
        this.init();
    }

    /**
     * Initialize the responsive system
     */
    init() {
        // Set initial breakpoint on body
        this.updateBodyAttribute();

        // Listen for resize events with debouncing
        let resizeTimeout;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 150);
        });

        // Listen for orientation changes
        window.addEventListener("orientationchange", () => {
            setTimeout(() => {
                this.handleResize();
            }, 200);
        });
    }

    /**
     * Get current breakpoint based on window width
     * @returns {string} Current breakpoint name
     */
    getBreakpoint() {
        const width = window.innerWidth;

        if (width >= this.breakpoints.wide) {
            return "wide";
        }
        if (width >= this.breakpoints.desktop) {
            return "desktop";
        }
        if (width >= this.breakpoints.tablet) {
            return "tablet";
        }
        return "mobile";
    }

    /**
     * Handle window resize
     */
    handleResize() {
        const newBreakpoint = this.getBreakpoint();

        if (newBreakpoint !== this.currentBreakpoint) {
            const oldBreakpoint = this.currentBreakpoint;
            this.currentBreakpoint = newBreakpoint;

            // Update body attribute
            this.updateBodyAttribute();

            // Emit breakpoint change event
            eventBus.emit("breakpoint:change", {
                from: oldBreakpoint,
                to: newBreakpoint,
                width: window.innerWidth,
                height: window.innerHeight,
            });
        }

        // Emit resize event
        eventBus.emit("viewport:resize", {
            width: window.innerWidth,
            height: window.innerHeight,
            breakpoint: this.currentBreakpoint,
        });
    }

    /**
     * Update body data attribute with current breakpoint
     */
    updateBodyAttribute() {
        document.body.setAttribute("data-breakpoint", this.currentBreakpoint);
    }

    /**
     * Check if current breakpoint matches
     * @param {string} breakpoint - Breakpoint name to check
     * @returns {boolean}
     */
    is(breakpoint) {
        return this.currentBreakpoint === breakpoint;
    }

    /**
     * Check if current breakpoint is at least the specified breakpoint
     * @param {string} breakpoint - Breakpoint name to check
     * @returns {boolean}
     */
    isAtLeast(breakpoint) {
        const breakpointOrder = ["mobile", "tablet", "desktop", "wide"];
        const currentIndex = breakpointOrder.indexOf(this.currentBreakpoint);
        const targetIndex = breakpointOrder.indexOf(breakpoint);
        return currentIndex >= targetIndex;
    }

    /**
     * Check if current breakpoint is at most the specified breakpoint
     * @param {string} breakpoint - Breakpoint name to check
     * @returns {boolean}
     */
    isAtMost(breakpoint) {
        const breakpointOrder = ["mobile", "tablet", "desktop", "wide"];
        const currentIndex = breakpointOrder.indexOf(this.currentBreakpoint);
        const targetIndex = breakpointOrder.indexOf(breakpoint);
        return currentIndex <= targetIndex;
    }

    /**
     * Get current viewport dimensions
     * @returns {Object} Width and height
     */
    getViewport() {
        return {
            width: window.innerWidth,
            height: window.innerHeight,
            breakpoint: this.currentBreakpoint,
        };
    }
}

// Create and export global responsive system instance
const responsiveSystem = new ResponsiveSystem();

// Make it available globally
window.responsiveSystem = responsiveSystem;

export default responsiveSystem;
