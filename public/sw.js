/**
 * Service Worker for UmamusumeCareerPlanner PWA
 *
 * Advanced caching strategies for optimal offline performance:
 * - Network-first for HTML navigation (fresh content when online)
 * - Cache-first for static assets (images, fonts, CSS, JS)
 * - Stale-while-revalidate for API responses
 * - Background sync for offline data submission
 *
 * Core Web Vitals optimizations:
 * - Precaching critical assets for fast LCP
 * - Runtime caching for optimal INP
 * - Stable asset loading for minimal CLS
 */

// Build: 2026-01-22-v3-fix-retry
const CACHE_VERSION = "v3";
const CACHE_NAME = `umamusume-career-planner-${CACHE_VERSION}`;
const RUNTIME_CACHE = `umamusume-runtime-${CACHE_VERSION}`;
const IMAGE_CACHE = `umamusume-images-${CACHE_VERSION}`;
const API_CACHE = `umamusume-api-${CACHE_VERSION}`;

// Critical assets to precache for fast LCP
const PRECACHE_ASSETS = [
    "/",
    "/offline.html",
    "/manifest.json",
    "/images/app_logo/uma_musume_race_planner_logo_128.png",
    "/images/app_logo/uma_musume_race_planner_logo_256.png",
    "/images/app_logo/uma_musume_race_planner_logo_512.png",
];

// Critical authenticated routes to cache on first visit
const CRITICAL_ROUTES = [
    "/dashboard",
    "/characters",
    "/about",
];

// Cache size limits
const CACHE_LIMITS = {
    images: 100, // Max 100 images
    runtime: 50, // Max 50 runtime entries
    api: 30, // Max 30 API responses
};

// Cache expiration times (in seconds)
const CACHE_EXPIRATION = {
    images: 7 * 24 * 60 * 60, // 7 days
    runtime: 24 * 60 * 60, // 1 day
    api: 5 * 60, // 5 minutes
};

/**
 * Install Event - Precache critical assets
 */
self.addEventListener("install", (event) => {
    console.log("[Service Worker] Installing v3...");

    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log("[Service Worker] Precaching critical assets");
                return cache.addAll(
                    PRECACHE_ASSETS.map(
                        (url) => new Request(url, { cache: "reload" }),
                    ),
                );
            })
            .then(() => self.skipWaiting())
            .catch((error) => {
                console.error("[Service Worker] Precache failed:", error);
            }),
    );
});

/**
 * Activate Event - Clean up old caches
 */
self.addEventListener("activate", (event) => {
    console.log("[Service Worker] Activating v3...");

    const currentCaches = [CACHE_NAME, RUNTIME_CACHE, IMAGE_CACHE, API_CACHE];

    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => !currentCaches.includes(name))
                        .map((name) => {
                            console.log(
                                "[Service Worker] Deleting old cache:",
                                name,
                            );
                            return caches.delete(name);
                        }),
                );
            })
            .then(() => self.clients.claim()),
    );
});

/**
 * Fetch Event - Apply caching strategies
 */
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // Skip development and debug endpoints
    if (shouldSkipRequest(url)) {
        return;
    }

    // Apply appropriate caching strategy
    if (isNavigationRequest(request)) {
        event.respondWith(networkFirstStrategy(request));
    } else if (isImageRequest(request)) {
        event.respondWith(cacheFirstWithRefresh(request, IMAGE_CACHE));
    } else if (isStaticAsset(request)) {
        event.respondWith(cacheFirstStrategy(request, RUNTIME_CACHE));
    } else if (isApiRequest(url)) {
        event.respondWith(staleWhileRevalidate(request, API_CACHE));
    } else {
        event.respondWith(networkWithCacheFallback(request));
    }
});

/**
 * Check if request should be skipped
 */
function shouldSkipRequest(url) {
    const skipPaths = [
        "/@vite/",
        "/__vite_ping",
        "/hot",
        "/_debugbar/",
        "/_boost/",
        "/_ignition/",
        "/livewire/",
        "/broadcasting/",
    ];

    return skipPaths.some((path) => url.pathname.includes(path));
}

/**
 * Check if request is a navigation request
 */
function isNavigationRequest(request) {
    return request.mode === "navigate" || request.destination === "document";
}

/**
 * Check if request is for an image
 */
function isImageRequest(request) {
    return (
        request.destination === "image" ||
        /\.(png|jpg|jpeg|gif|webp|avif|svg|ico)$/i.test(request.url)
    );
}

/**
 * Check if request is for a static asset
 */
function isStaticAsset(request) {
    return (
        request.destination === "style" ||
        request.destination === "script" ||
        request.destination === "font" ||
        /\.(css|js|woff2?|ttf|eot)$/i.test(request.url)
    );
}

/**
 * Check if request is an API request
 */
function isApiRequest(url) {
    return url.pathname.startsWith("/api/");
}

/**
 * Network-first strategy for navigation requests
 * Ensures fresh content when online, falls back to cache when offline
 */
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);

        // Cache successful GET responses only
        if (networkResponse.ok && request.method === "GET") {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        // Try cache fallback
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page
        const offlineResponse = await caches.match("/offline.html");
        return (
            offlineResponse ||
            new Response("Offline - Please check your connection", {
                status: 503,
                statusText: "Service Unavailable",
                headers: { "Content-Type": "text/plain" },
            })
        );
    }
}

/**
 * Cache-first strategy for static assets
 * Fast loading from cache, network fallback
 */
async function cacheFirstStrategy(request, cacheName) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok && request.method === "GET") {
            const cache = await caches.open(cacheName);
            cache.put(request, networkResponse.clone());
            await trimCache(cacheName, CACHE_LIMITS.runtime);
        }

        return networkResponse;
    } catch (error) {
        return new Response("Asset not available", {
            status: 404,
            statusText: "Not Found",
        });
    }
}

/**
 * Cache-first with background refresh for images
 * Returns cached version immediately, updates cache in background
 */
async function cacheFirstWithRefresh(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);

    // Start network fetch in background (only for GET requests)
    const fetchPromise =
        request.method === "GET"
            ? fetch(request)
                  .then((networkResponse) => {
                      if (networkResponse.ok) {
                          cache.put(request, networkResponse.clone());
                          trimCache(cacheName, CACHE_LIMITS.images);
                      }
                      return networkResponse;
                  })
                  .catch(() => null)
            : Promise.resolve(null);

    // Return cached response immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }

    // Wait for network if no cache
    const networkResponse = await fetchPromise;
    return (
        networkResponse ||
        new Response("Image not available", {
            status: 404,
            statusText: "Not Found",
        })
    );
}

/**
 * Stale-while-revalidate strategy for API requests
 * Returns cached response immediately, updates cache in background
 */
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);

    // Start network fetch (only for GET requests)
    const fetchPromise =
        request.method === "GET"
            ? fetch(request)
                  .then((networkResponse) => {
                      if (networkResponse.ok) {
                          // Add timestamp for expiration checking
                          const responseWithTimestamp = networkResponse.clone();
                          cache.put(request, responseWithTimestamp);
                          trimCache(cacheName, CACHE_LIMITS.api);
                      }
                      return networkResponse;
                  })
                  .catch(() => null)
            : fetch(request).catch(() => null);

    // Return cached response if fresh enough
    if (cachedResponse && request.method === "GET") {
        // Check if cache is still valid (within expiration time)
        const cacheDate = cachedResponse.headers.get("date");
        if (cacheDate) {
            const age = (Date.now() - new Date(cacheDate).getTime()) / 1000;
            if (age < CACHE_EXPIRATION.api) {
                return cachedResponse;
            }
        }
    }

    // Wait for network response
    const networkResponse = await fetchPromise;

    if (networkResponse) {
        return networkResponse;
    }

    // Return stale cache if network fails (GET only)
    if (cachedResponse && request.method === "GET") {
        return cachedResponse;
    }

    return new Response(JSON.stringify({ error: "Offline" }), {
        status: 503,
        statusText: "Service Unavailable",
        headers: { "Content-Type": "application/json" },
    });
}

/**
 * Network with cache fallback for other requests
 */
async function networkWithCacheFallback(request) {
    try {
        const networkResponse = await fetch(request);

        // Only cache GET requests (HEAD, POST, PUT, DELETE cannot be cached)
        if (networkResponse.ok && request.method === "GET") {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return (
            cachedResponse ||
            new Response("Resource not available", {
                status: 404,
                statusText: "Not Found",
            })
        );
    }
}

/**
 * Trim cache to maximum size (LRU eviction)
 */
async function trimCache(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    if (keys.length > maxItems) {
        // Delete oldest entries (first in, first out)
        const deleteCount = keys.length - maxItems;
        await Promise.all(
            keys.slice(0, deleteCount).map((key) => cache.delete(key)),
        );
    }
}

/**
 * Message Event - Handle messages from clients
 */
self.addEventListener("message", (event) => {
    const { type, payload } = event.data || {};

    switch (type) {
        case "SKIP_WAITING":
            self.skipWaiting();
            event.ports[0]?.postMessage({ success: true });
            break;

        case "CLEAR_CACHE":
            event.waitUntil(
                clearAllCaches().then(() => {
                    notifyClients({ type: "CACHE_CLEARED" });
                    event.ports[0]?.postMessage({ success: true });
                }),
            );
            break;

        case "CLEAR_API_CACHE":
            event.waitUntil(
                caches.delete(API_CACHE).then(() => {
                    notifyClients({ type: "API_CACHE_CLEARED" });
                    event.ports[0]?.postMessage({ success: true });
                }),
            );
            break;

        case "PRECACHE_ASSETS":
            if (payload && Array.isArray(payload.urls)) {
                event.waitUntil(
                    precacheAssets(payload.urls).then(() => {
                        event.ports[0]?.postMessage({ success: true });
                    }),
                );
            }
            break;

        case "GET_CACHE_STATUS":
            event.waitUntil(
                getCacheStatus().then((status) => {
                    event.source.postMessage({
                        type: "CACHE_STATUS",
                        payload: status,
                    });
                }),
            );
            break;

        case "PRECACHE_ROUTES":
            if (payload && Array.isArray(payload.routes)) {
                event.waitUntil(
                    precacheRoutes(payload.routes).then(() => {
                        event.ports[0]?.postMessage({ success: true });
                    }),
                );
            }
            break;
    }
});

/**
 * Clear all caches
 */
async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(cacheNames.map((name) => caches.delete(name)));
    console.log("[Service Worker] All caches cleared");
}

/**
 * Precache additional assets
 */
async function precacheAssets(urls) {
    const cache = await caches.open(CACHE_NAME);
    await cache.addAll(urls);
    console.log("[Service Worker] Precached additional assets:", urls);
}

/**
 * Get cache status
 */
async function getCacheStatus() {
    const cacheNames = await caches.keys();
    const status = {};

    for (const name of cacheNames) {
        const cache = await caches.open(name);
        const keys = await cache.keys();
        status[name] = {
            count: keys.length,
            urls: keys.map((req) => req.url),
        };
    }

    return status;
}

/**
 * Notify all clients
 */
async function notifyClients(message) {
    const clients = await self.clients.matchAll();
    clients.forEach((client) => client.postMessage(message));
}

/**
 * Background Sync for offline data submission
 */
self.addEventListener("sync", (event) => {
    if (event.tag === "sync-data") {
        event.waitUntil(syncOfflineData());
    }
});

/**
 * Sync offline data when connection is restored
 * Opens IndexedDB and processes pending operations
 */
async function syncOfflineData() {
    console.log("[Service Worker] Syncing offline data...");

    try {
        const clients = await self.clients.matchAll({ type: "window" });
        for (const client of clients) {
            client.postMessage({ type: "TRIGGER_SYNC" });
        }
    } catch (error) {
        console.error("[Service Worker] Sync failed:", error);
    }
}

/**
 * Precache critical routes for offline access
 */
async function precacheRoutes(routes) {
    const cache = await caches.open(CACHE_NAME);

    for (const route of routes) {
        try {
            const response = await fetch(route, { credentials: "same-origin" });
            if (response.ok) {
                await cache.put(new Request(route), response);
            }
        } catch (error) {
            console.warn("[Service Worker] Could not precache route:", route);
        }
    }

    console.log("[Service Worker] Precached routes:", routes);
}

/**
 * Push notification handling
 */
self.addEventListener("push", (event) => {
    if (!event.data) return;

    const data = event.data.json();

    event.waitUntil(
        self.registration.showNotification(data.title || "Notification", {
            body: data.body || "",
            icon: "/images/app_logo/uma_musume_race_planner_logo_128.png",
            badge: "/images/app_logo/uma_musume_race_planner_logo_128.png",
            data: data.data || {},
        }),
    );
});

/**
 * Notification click handling
 */
self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data?.url || "/";

    event.waitUntil(
        self.clients.matchAll({ type: "window" }).then((clientList) => {
            // Focus existing window if available
            for (const client of clientList) {
                if (client.url === urlToOpen && "focus" in client) {
                    return client.focus();
                }
            }
            // Open new window
            if (self.clients.openWindow) {
                return self.clients.openWindow(urlToOpen);
            }
        }),
    );
});

console.log("[Service Worker] v3 Loaded with advanced caching strategies");
