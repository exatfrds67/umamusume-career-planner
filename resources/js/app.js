import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import connectivityMonitor from "./core/connectivity-monitor.js";

import deckBuilder from "./deck-builder.js";
import planWizard from "./components/plan-wizard.js";

// Register Alpine plugins early
Alpine.plugin(persist);

// Register connectivity monitor before Alpine starts
Alpine.data("connectivityMonitor", connectivityMonitor);
Alpine.data("deckBuilder", deckBuilder);
Alpine.data("planWizard", planWizard);

// Initialize Alpine.js immediately for faster interactivity
window.Alpine = Alpine;

// Defer non-critical module loading
const loadNonCriticalModules = () => {
    // Import character validation module
    import("./character-validation.js");

    // Import training predictions module
    import("./training-predictions.js");

    // Import settings module
    import("./settings.js");

    // Import AI Chat module
    import("./ai-chat.js");

    // Import core modules
    import("./core/EventBus.js");
    import("./core/ResponsiveSystem.js");
    import("./core/AccessibilitySettings.js");
    import("./core/AccessibilitySystem.js");
    import("./core/ThemeSystem.js");

    // Import performance optimization modules
    import("./core/ImageOptimization.js");
    import("./core/PerformanceMonitor.js");
};

// Start Alpine immediately for better INP
Alpine.start();

// Load non-critical modules after Alpine starts
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", loadNonCriticalModules);
} else {
    // Use requestIdleCallback for better performance
    if ("requestIdleCallback" in window) {
        requestIdleCallback(loadNonCriticalModules, { timeout: 2000 });
    } else {
        setTimeout(loadNonCriticalModules, 1);
    }
}

// Service Worker Management with advanced caching
if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            // First, unregister all existing service workers to clear bad cache
            const registrations =
                await navigator.serviceWorker.getRegistrations();

            // If there are old registrations, unregister them
            if (registrations.length > 0) {
                console.log("[SW] Found old service workers, unregistering...");
                await Promise.all(
                    registrations.map((registration) =>
                        registration.unregister(),
                    ),
                );
                console.log("[SW] Old service workers unregistered");

                // Clear all caches
                if ("caches" in window) {
                    const cacheKeys = await caches.keys();
                    await Promise.all(
                        cacheKeys.map((key) => caches.delete(key)),
                    );
                    console.log("[SW] All caches cleared");
                }
            }

            // Register the new service worker
            const registration = await navigator.serviceWorker.register(
                "/sw.js",
                {
                    updateViaCache: "none", // Don't cache the service worker file itself
                },
            );

            console.log("[SW] Service Worker registered:", registration.scope);

            // Check for updates immediately
            registration.update();

            // Handle updates
            registration.addEventListener("updatefound", () => {
                const newWorker = registration.installing;

                newWorker.addEventListener("statechange", () => {
                    if (
                        newWorker.state === "installed" &&
                        navigator.serviceWorker.controller
                    ) {
                        // New service worker available, reload to activate
                        console.log("[SW] New version available, reloading...");
                        window.location.reload();
                    }
                });
            });

            // Listen for controller change (new service worker activated)
            navigator.serviceWorker.addEventListener("controllerchange", () => {
                console.log("[SW] New service worker activated");
            });

            // Listen for messages from service worker
            navigator.serviceWorker.addEventListener("message", (event) => {
                const { type, payload } = event.data || {};

                switch (type) {
                    case "CACHE_CLEARED":
                        console.log("[SW] Cache cleared successfully");
                        break;
                    case "API_CACHE_CLEARED":
                        console.log("[SW] API cache cleared successfully");
                        break;
                    case "CACHE_STATUS":
                        console.log("[SW] Cache status:", payload);
                        break;
                }
            });
        } catch (error) {
            console.error("[SW] Service Worker registration failed:", error);
        }
    });
}

// Utility functions for service worker communication
window.ServiceWorkerUtils = {
    /**
     * Clear all service worker caches
     */
    clearCache() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "CLEAR_CACHE",
            });
        }
    },

    /**
     * Clear API cache only
     */
    clearApiCache() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "CLEAR_API_CACHE",
            });
        }
    },

    /**
     * Precache additional assets
     * @param {string[]} urls - URLs to precache
     */
    precacheAssets(urls) {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "PRECACHE_ASSETS",
                payload: { urls },
            });
        }
    },

    /**
     * Get cache status
     */
    getCacheStatus() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "GET_CACHE_STATUS",
            });
        }
    },

    /**
     * Force service worker update
     */
    async forceUpdate() {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
            await registration.update();
        }
    },
};
