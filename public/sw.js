/**
 * Service Worker for UmamusumeCareerPlanner PWA
 *
 * This service worker provides offline functionality with a network-first strategy
 * for navigation requests to ensure fresh content is always served when online.
 */

const CACHE_VERSION = "v2";
const CACHE_NAME = `umamusume-career-planner-${CACHE_VERSION}`;

// Assets to cache on install
const STATIC_ASSETS = [
    "/",
    "/offline.html",
    "/manifest.json",
    "/images/app_logo/uma_musume_race_planner_logo_128.png",
    "/images/app_logo/uma_musume_race_planner_logo_256.png",
    "/images/app_logo/uma_musume_race_planner_logo_512.png",
];

/**
 * Install Event - Cache static assets
 */
self.addEventListener("install", (event) => {
    console.log("[Service Worker] Installing...");

    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log("[Service Worker] Caching static assets");
                return cache.addAll(
                    STATIC_ASSETS.map(
                        (url) => new Request(url, { cache: "reload" }),
                    ),
                );
            })
            .then(() => self.skipWaiting())
            .catch((error) => {
                console.error(
                    "[Service Worker] Cache installation failed:",
                    error,
                );
            }),
    );
});

/**
 * Activate Event - Clean up old caches
 */
self.addEventListener("activate", (event) => {
    console.log("[Service Worker] Activating...");

    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter(
                            (name) =>
                                name.startsWith("umamusume-career-planner-") &&
                                name !== CACHE_NAME,
                        )
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
 * Fetch Event - Network-first strategy for navigation, cache-first for assets
 */
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // Skip Vite HMR and dev server requests
    if (
        url.pathname.includes("/@vite/") ||
        url.pathname.includes("/__vite_ping") ||
        url.pathname.includes("/hot")
    ) {
        return;
    }

    // Skip API requests and dynamic endpoints
    if (
        url.pathname.startsWith("/api/") ||
        url.pathname.startsWith("/_debugbar/") ||
        url.pathname.startsWith("/_boost/") ||
        url.pathname.startsWith("/_ignition/")
    ) {
        return;
    }

    // Network-first strategy for HTML navigation requests
    if (request.mode === "navigate" || request.destination === "document") {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone the response before caching
                    const responseToCache = response.clone();

                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });

                    return response;
                })
                .catch(() => {
                    // If network fails, try cache
                    return caches.match(request).then((response) => {
                        if (response) {
                            return response;
                        }

                        // Return offline page if available
                        return caches
                            .match("/offline.html")
                            .then((offlineResponse) => {
                                return (
                                    offlineResponse ||
                                    new Response(
                                        "Offline - Please check your connection",
                                        {
                                            status: 503,
                                            statusText: "Service Unavailable",
                                            headers: new Headers({
                                                "Content-Type": "text/plain",
                                            }),
                                        },
                                    )
                                );
                            });
                    });
                }),
        );
        return;
    }

    // Cache-first strategy for static assets (images, fonts, CSS, JS)
    if (
        request.destination === "image" ||
        request.destination === "font" ||
        request.destination === "style" ||
        request.destination === "script"
    ) {
        event.respondWith(
            caches.match(request).then((response) => {
                if (response) {
                    return response;
                }

                return fetch(request).then((response) => {
                    // Don't cache if not a valid response
                    if (
                        !response ||
                        response.status !== 200 ||
                        response.type === "error"
                    ) {
                        return response;
                    }

                    const responseToCache = response.clone();

                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });

                    return response;
                });
            }),
        );
        return;
    }

    // For everything else, use network with cache fallback
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});

/**
 * Message Event - Handle messages from clients
 */
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }

    if (event.data && event.data.type === "CLEAR_CACHE") {
        event.waitUntil(
            caches
                .keys()
                .then((cacheNames) => {
                    return Promise.all(
                        cacheNames.map((name) => caches.delete(name)),
                    );
                })
                .then(() => {
                    return self.clients.matchAll();
                })
                .then((clients) => {
                    clients.forEach((client) => {
                        client.postMessage({
                            type: "CACHE_CLEARED",
                            message: "All caches have been cleared",
                        });
                    });
                }),
        );
    }
});

console.log("[Service Worker] Loaded");
