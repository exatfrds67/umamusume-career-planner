/**
 * Performance Monitor Module
 *
 * Monitors Core Web Vitals (LCP, INP, CLS) and other performance metrics
 * to ensure optimal user experience. Targets:
 * - LCP (Largest Contentful Paint): < 2.5s
 * - INP (Interaction to Next Paint): < 200ms
 * - CLS (Cumulative Layout Shift): < 0.1
 *
 * @module PerformanceMonitor
 * @requires EventBus
 */

import eventBus from "./EventBus.js";

/**
 * Performance thresholds based on Core Web Vitals
 */
const THRESHOLDS = {
    LCP: {
        good: 2500, // 2.5 seconds
        needsImprovement: 4000, // 4 seconds
    },
    INP: {
        good: 200, // 200ms
        needsImprovement: 500, // 500ms
    },
    CLS: {
        good: 0.1,
        needsImprovement: 0.25,
    },
    FCP: {
        good: 1800, // 1.8 seconds
        needsImprovement: 3000, // 3 seconds
    },
    TTFB: {
        good: 800, // 800ms
        needsImprovement: 1800, // 1.8 seconds
    },
};

/**
 * Performance Monitor class
 */
class PerformanceMonitor {
    constructor() {
        this.metrics = {
            LCP: null,
            INP: null,
            CLS: null,
            FCP: null,
            TTFB: null,
            FID: null,
        };
        this.observers = {};
        this.interactions = [];
        this.layoutShifts = [];
        this.initialized = false;
        this.reportCallback = null;
    }

    /**
     * Initialize performance monitoring
     * @param {Object} options - Configuration options
     * @param {Function} options.onReport - Callback for metric reports
     */
    init(options = {}) {
        if (this.initialized) {
            return;
        }

        this.reportCallback = options.onReport || this.defaultReportHandler;

        // Initialize Core Web Vitals observers
        this.observeLCP();
        this.observeINP();
        this.observeCLS();
        this.observeFCP();
        this.observeTTFB();

        // Initialize resource timing
        this.observeResourceTiming();

        // Initialize long task observer
        this.observeLongTasks();

        this.initialized = true;

        eventBus.emit("performanceMonitor:initialized");

        console.log("[PerformanceMonitor] Initialized");
    }

    /**
     * Observe Largest Contentful Paint (LCP)
     */
    observeLCP() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];

                this.metrics.LCP = lastEntry.startTime;
                this.reportMetric("LCP", lastEntry.startTime);
            });

            observer.observe({
                type: "largest-contentful-paint",
                buffered: true,
            });
            this.observers.LCP = observer;
        } catch (e) {
            console.warn("[PerformanceMonitor] LCP observation not supported");
        }
    }

    /**
     * Observe Interaction to Next Paint (INP)
     * INP measures responsiveness by tracking all interactions
     */
    observeINP() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();

                entries.forEach((entry) => {
                    // Calculate interaction duration
                    const duration = entry.duration;

                    this.interactions.push({
                        duration,
                        timestamp: entry.startTime,
                        type: entry.name,
                    });

                    // Keep only the worst interactions (for INP calculation)
                    this.interactions.sort((a, b) => b.duration - a.duration);
                    if (this.interactions.length > 10) {
                        this.interactions = this.interactions.slice(0, 10);
                    }

                    // INP is the 98th percentile of interactions
                    // For simplicity, we use the worst interaction
                    const inp = this.calculateINP();
                    if (inp !== this.metrics.INP) {
                        this.metrics.INP = inp;
                        this.reportMetric("INP", inp);
                    }
                });
            });

            observer.observe({
                type: "event",
                buffered: true,
                durationThreshold: 16,
            });
            this.observers.INP = observer;
        } catch (e) {
            // Fallback to First Input Delay (FID) for older browsers
            this.observeFID();
        }
    }

    /**
     * Calculate INP from collected interactions
     * @returns {number} - INP value in milliseconds
     */
    calculateINP() {
        if (this.interactions.length === 0) {
            return 0;
        }

        // Sort by duration descending
        const sorted = [...this.interactions].sort(
            (a, b) => b.duration - a.duration,
        );

        // Get 98th percentile (or worst if fewer than 50 interactions)
        const index = Math.min(
            Math.floor(sorted.length * 0.02),
            sorted.length - 1,
        );
        return sorted[index].duration;
    }

    /**
     * Observe First Input Delay (FID) - fallback for INP
     */
    observeFID() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const firstEntry = entries[0];

                this.metrics.FID =
                    firstEntry.processingStart - firstEntry.startTime;
                this.reportMetric("FID", this.metrics.FID);
            });

            observer.observe({ type: "first-input", buffered: true });
            this.observers.FID = observer;
        } catch (e) {
            console.warn("[PerformanceMonitor] FID observation not supported");
        }
    }

    /**
     * Observe Cumulative Layout Shift (CLS)
     */
    observeCLS() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            let clsValue = 0;
            let sessionValue = 0;
            let sessionEntries = [];

            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();

                entries.forEach((entry) => {
                    // Only count layout shifts without recent user input
                    if (!entry.hadRecentInput) {
                        const firstSessionEntry = sessionEntries[0];
                        const lastSessionEntry =
                            sessionEntries[sessionEntries.length - 1];

                        // If the entry occurred less than 1 second after the previous entry
                        // and less than 5 seconds after the first entry in the session,
                        // include the entry in the current session
                        if (
                            sessionValue &&
                            entry.startTime - lastSessionEntry.startTime <
                                1000 &&
                            entry.startTime - firstSessionEntry.startTime < 5000
                        ) {
                            sessionValue += entry.value;
                            sessionEntries.push(entry);
                        } else {
                            // Start a new session
                            sessionValue = entry.value;
                            sessionEntries = [entry];
                        }

                        // Update CLS if this session is larger
                        if (sessionValue > clsValue) {
                            clsValue = sessionValue;
                            this.metrics.CLS = clsValue;
                            this.reportMetric("CLS", clsValue);
                        }

                        this.layoutShifts.push({
                            value: entry.value,
                            timestamp: entry.startTime,
                            sources: entry.sources,
                        });
                    }
                });
            });

            observer.observe({ type: "layout-shift", buffered: true });
            this.observers.CLS = observer;
        } catch (e) {
            console.warn("[PerformanceMonitor] CLS observation not supported");
        }
    }

    /**
     * Observe First Contentful Paint (FCP)
     */
    observeFCP() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const fcpEntry = entries.find(
                    (entry) => entry.name === "first-contentful-paint",
                );

                if (fcpEntry) {
                    this.metrics.FCP = fcpEntry.startTime;
                    this.reportMetric("FCP", fcpEntry.startTime);
                }
            });

            observer.observe({ type: "paint", buffered: true });
            this.observers.FCP = observer;
        } catch (e) {
            console.warn("[PerformanceMonitor] FCP observation not supported");
        }
    }

    /**
     * Observe Time to First Byte (TTFB)
     */
    observeTTFB() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const navigationEntry = entries.find(
                    (entry) => entry.entryType === "navigation",
                );

                if (navigationEntry) {
                    this.metrics.TTFB = navigationEntry.responseStart;
                    this.reportMetric("TTFB", navigationEntry.responseStart);
                }
            });

            observer.observe({ type: "navigation", buffered: true });
            this.observers.TTFB = observer;
        } catch (e) {
            console.warn("[PerformanceMonitor] TTFB observation not supported");
        }
    }

    /**
     * Observe resource timing for asset loading performance
     */
    observeResourceTiming() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();

                entries.forEach((entry) => {
                    // Track slow resources (> 1 second)
                    if (entry.duration > 1000) {
                        eventBus.emit("performanceMonitor:slowResource", {
                            name: entry.name,
                            duration: entry.duration,
                            type: entry.initiatorType,
                        });
                    }
                });
            });

            observer.observe({ type: "resource", buffered: true });
            this.observers.resource = observer;
        } catch (e) {
            console.warn(
                "[PerformanceMonitor] Resource timing observation not supported",
            );
        }
    }

    /**
     * Observe long tasks that block the main thread
     */
    observeLongTasks() {
        if (!("PerformanceObserver" in window)) {
            return;
        }

        try {
            const observer = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();

                entries.forEach((entry) => {
                    eventBus.emit("performanceMonitor:longTask", {
                        duration: entry.duration,
                        startTime: entry.startTime,
                    });
                });
            });

            observer.observe({ type: "longtask", buffered: true });
            this.observers.longTask = observer;
        } catch (e) {
            // Long task observation not supported
        }
    }

    /**
     * Report a metric
     * @param {string} name - Metric name
     * @param {number} value - Metric value
     */
    reportMetric(name, value) {
        const rating = this.getRating(name, value);

        const report = {
            name,
            value,
            rating,
            threshold: THRESHOLDS[name],
            timestamp: Date.now(),
        };

        if (this.reportCallback) {
            this.reportCallback(report);
        }

        eventBus.emit("performanceMonitor:metric", report);

        // Log warnings for poor metrics
        if (rating === "poor") {
            console.warn(
                `[PerformanceMonitor] ${name} is poor: ${value.toFixed(2)}`,
                report,
            );
        }
    }

    /**
     * Get rating for a metric value
     * @param {string} name - Metric name
     * @param {number} value - Metric value
     * @returns {string} - 'good', 'needs-improvement', or 'poor'
     */
    getRating(name, value) {
        const threshold = THRESHOLDS[name];

        if (!threshold) {
            return "unknown";
        }

        if (value <= threshold.good) {
            return "good";
        }

        if (value <= threshold.needsImprovement) {
            return "needs-improvement";
        }

        return "poor";
    }

    /**
     * Default report handler
     * @param {Object} report - Metric report
     */
    defaultReportHandler(report) {
        // Log to console in development
        if (process.env.NODE_ENV === "development") {
            const color =
                report.rating === "good"
                    ? "green"
                    : report.rating === "needs-improvement"
                      ? "orange"
                      : "red";

            console.log(
                `%c[${report.name}] ${report.value.toFixed(2)} (${report.rating})`,
                `color: ${color}`,
            );
        }
    }

    /**
     * Get all current metrics
     * @returns {Object} - Current metrics with ratings
     */
    getMetrics() {
        const result = {};

        Object.entries(this.metrics).forEach(([name, value]) => {
            if (value !== null) {
                result[name] = {
                    value,
                    rating: this.getRating(name, value),
                };
            }
        });

        return result;
    }

    /**
     * Get Core Web Vitals summary
     * @returns {Object} - Summary of LCP, INP, and CLS
     */
    getCoreWebVitals() {
        return {
            LCP: this.metrics.LCP
                ? {
                      value: this.metrics.LCP,
                      rating: this.getRating("LCP", this.metrics.LCP),
                      target: THRESHOLDS.LCP.good,
                  }
                : null,
            INP: this.metrics.INP
                ? {
                      value: this.metrics.INP,
                      rating: this.getRating("INP", this.metrics.INP),
                      target: THRESHOLDS.INP.good,
                  }
                : null,
            CLS: this.metrics.CLS
                ? {
                      value: this.metrics.CLS,
                      rating: this.getRating("CLS", this.metrics.CLS),
                      target: THRESHOLDS.CLS.good,
                  }
                : null,
        };
    }

    /**
     * Check if all Core Web Vitals pass
     * @returns {boolean}
     */
    passesWebVitals() {
        const vitals = this.getCoreWebVitals();

        return (
            (!vitals.LCP || vitals.LCP.rating === "good") &&
            (!vitals.INP || vitals.INP.rating === "good") &&
            (!vitals.CLS || vitals.CLS.rating === "good")
        );
    }

    /**
     * Send metrics to analytics endpoint
     * @param {string} endpoint - Analytics endpoint URL
     */
    async sendToAnalytics(endpoint) {
        const metrics = this.getMetrics();

        try {
            // Use sendBeacon for reliability
            if (navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify(metrics)], {
                    type: "application/json",
                });
                navigator.sendBeacon(endpoint, blob);
            } else {
                await fetch(endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(metrics),
                    keepalive: true,
                });
            }
        } catch (e) {
            console.error("[PerformanceMonitor] Failed to send metrics:", e);
        }
    }

    /**
     * Destroy all observers
     */
    destroy() {
        Object.values(this.observers).forEach((observer) => {
            if (observer) {
                observer.disconnect();
            }
        });

        this.observers = {};
        this.metrics = {
            LCP: null,
            INP: null,
            CLS: null,
            FCP: null,
            TTFB: null,
            FID: null,
        };
        this.interactions = [];
        this.layoutShifts = [];
        this.initialized = false;
    }
}

// Create singleton instance
const performanceMonitor = new PerformanceMonitor();

// Export thresholds for testing
export { performanceMonitor, THRESHOLDS };

// Auto-initialize when DOM is ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () =>
        performanceMonitor.init(),
    );
} else {
    performanceMonitor.init();
}

export default performanceMonitor;
