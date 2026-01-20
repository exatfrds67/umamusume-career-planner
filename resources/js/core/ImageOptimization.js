/**
 * Image Optimization Module
 *
 * Provides utilities for optimizing image loading with modern formats (AVIF, WebP),
 * lazy loading, responsive images, and progressive loading techniques.
 *
 * @module ImageOptimization
 * @requires EventBus
 */

import eventBus from "./EventBus.js";

/**
 * Image format support detection
 */
const formatSupport = {
    avif: null,
    webp: null,
};

/**
 * Check if browser supports AVIF format
 * @returns {Promise<boolean>}
 */
async function checkAvifSupport() {
    if (formatSupport.avif !== null) {
        return formatSupport.avif;
    }

    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            formatSupport.avif = img.width > 0 && img.height > 0;
            resolve(formatSupport.avif);
        };
        img.onerror = () => {
            formatSupport.avif = false;
            resolve(false);
        };
        // Minimal AVIF image (1x1 pixel) - suppress console error
        img.onerror = (e) => {
            e.preventDefault();
            formatSupport.avif = false;
            resolve(false);
        };
        img.src =
            "data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKBzgABpAQ0AIAyAABAABAAIAMg=";
    });
}

/**
 * Check if browser supports WebP format
 * @returns {Promise<boolean>}
 */
async function checkWebpSupport() {
    if (formatSupport.webp !== null) {
        return formatSupport.webp;
    }

    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            formatSupport.webp = img.width > 0 && img.height > 0;
            resolve(formatSupport.webp);
        };
        img.onerror = () => {
            formatSupport.webp = false;
            resolve(false);
        };
        // Minimal WebP image (1x1 pixel)
        img.src =
            "data:image/webp;base64,UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==";
    });
}

/**
 * Get the best supported image format
 * @returns {Promise<string>} - 'avif', 'webp', or 'original'
 */
async function getBestFormat() {
    if (await checkAvifSupport()) {
        return "avif";
    }
    if (await checkWebpSupport()) {
        return "webp";
    }
    return "original";
}

/**
 * ImageOptimization class for managing optimized image loading
 */
class ImageOptimization {
    constructor() {
        this.observer = null;
        this.loadedImages = new Set();
        this.pendingImages = new Map();
        this.formatSupport = formatSupport;
        this.initialized = false;
    }

    /**
     * Initialize the image optimization system
     */
    async init() {
        if (this.initialized) {
            return;
        }

        // Detect format support
        await Promise.all([checkAvifSupport(), checkWebpSupport()]);

        // Add format support classes to document
        document.documentElement.classList.add(
            formatSupport.avif ? "avif" : "no-avif",
        );
        document.documentElement.classList.add(
            formatSupport.webp ? "webp" : "no-webp",
        );

        // Initialize Intersection Observer for lazy loading
        this.initLazyLoading();

        // Initialize responsive image handling
        this.initResponsiveImages();

        this.initialized = true;

        eventBus.emit("imageOptimization:initialized", {
            avifSupport: formatSupport.avif,
            webpSupport: formatSupport.webp,
        });

        console.log("[ImageOptimization] Initialized", {
            avif: formatSupport.avif,
            webp: formatSupport.webp,
        });
    }

    /**
     * Initialize lazy loading with Intersection Observer
     */
    initLazyLoading() {
        // Use native lazy loading if available
        if ("loading" in HTMLImageElement.prototype) {
            document.querySelectorAll('img[loading="lazy"]').forEach((img) => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
            });
        }

        // Intersection Observer for custom lazy loading
        const options = {
            root: null,
            rootMargin: "50px 0px", // Start loading 50px before entering viewport
            threshold: 0.01,
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, options);

        // Observe all lazy images
        this.observeLazyImages();
    }

    /**
     * Observe lazy images in the DOM
     */
    observeLazyImages() {
        document
            .querySelectorAll(
                ".lazy-image:not(.image-loaded), .lazy-background:not(.image-loaded)",
            )
            .forEach((element) => {
                if (!this.loadedImages.has(element)) {
                    this.observer.observe(element);
                }
            });
    }

    /**
     * Load an image element
     * @param {HTMLElement} element - The image element to load
     */
    async loadImage(element) {
        const isBackground = element.classList.contains("lazy-background");

        if (isBackground) {
            await this.loadBackgroundImage(element);
        } else {
            await this.loadImgElement(element);
        }

        element.classList.add("image-loaded");
        this.loadedImages.add(element);

        eventBus.emit("imageOptimization:imageLoaded", { element });
    }

    /**
     * Load an img element with optimized format
     * @param {HTMLImageElement} img - The image element
     */
    async loadImgElement(img) {
        const src = img.dataset.src || img.src;
        const srcset = img.dataset.srcset;

        if (!src) return;

        // Get optimized source
        const optimizedSrc = await this.getOptimizedSource(src);

        return new Promise((resolve, reject) => {
            const tempImg = new Image();

            tempImg.onload = () => {
                img.src = optimizedSrc;
                if (srcset) {
                    img.srcset = srcset;
                }
                resolve();
            };

            tempImg.onerror = () => {
                // Fallback to original source
                img.src = src;
                resolve();
            };

            tempImg.src = optimizedSrc;
        });
    }

    /**
     * Load a background image with optimized format
     * @param {HTMLElement} element - The element with background image
     */
    async loadBackgroundImage(element) {
        const src = element.dataset.bgSrc;

        if (!src) return;

        const optimizedSrc = await this.getOptimizedSource(src);

        return new Promise((resolve) => {
            const img = new Image();

            img.onload = () => {
                element.style.backgroundImage = `url('${optimizedSrc}')`;
                resolve();
            };

            img.onerror = () => {
                element.style.backgroundImage = `url('${src}')`;
                resolve();
            };

            img.src = optimizedSrc;
        });
    }

    /**
     * Get optimized image source based on format support
     * @param {string} src - Original image source
     * @returns {Promise<string>} - Optimized image source
     */
    async getOptimizedSource(src) {
        const bestFormat = await getBestFormat();

        if (bestFormat === "original") {
            return src;
        }

        // Check if optimized version exists
        const ext = src.split(".").pop().toLowerCase();
        const basePath = src.substring(0, src.lastIndexOf("."));

        // Try AVIF first, then WebP
        if (bestFormat === "avif") {
            const avifSrc = `${basePath}.avif`;
            if (await this.imageExists(avifSrc)) {
                return avifSrc;
            }
        }

        if (bestFormat === "webp" || bestFormat === "avif") {
            const webpSrc = `${basePath}.webp`;
            if (await this.imageExists(webpSrc)) {
                return webpSrc;
            }
        }

        return src;
    }

    /**
     * Check if an image exists at the given URL
     * @param {string} url - Image URL to check
     * @returns {Promise<boolean>}
     */
    async imageExists(url) {
        try {
            const response = await fetch(url, { method: "HEAD" });
            return response.ok;
        } catch {
            return false;
        }
    }

    /**
     * Initialize responsive image handling
     */
    initResponsiveImages() {
        // Handle picture elements with source sets
        document.querySelectorAll("picture source").forEach((source) => {
            if (source.dataset.srcset) {
                source.srcset = source.dataset.srcset;
            }
        });

        // Handle responsive images with srcset
        document.querySelectorAll("img[data-srcset]").forEach((img) => {
            if (!img.classList.contains("lazy-image")) {
                img.srcset = img.dataset.srcset;
            }
        });
    }

    /**
     * Generate responsive srcset for an image
     * @param {string} src - Base image source
     * @param {number[]} widths - Array of widths to generate
     * @returns {string} - srcset string
     */
    generateSrcset(src, widths = [320, 640, 768, 1024, 1280, 1536]) {
        const ext = src.split(".").pop();
        const basePath = src.substring(0, src.lastIndexOf("."));

        return widths
            .map((width) => `${basePath}-${width}w.${ext} ${width}w`)
            .join(", ");
    }

    /**
     * Preload critical images
     * @param {string[]} urls - Array of image URLs to preload
     */
    preloadImages(urls) {
        urls.forEach((url) => {
            const link = document.createElement("link");
            link.rel = "preload";
            link.as = "image";
            link.href = url;
            document.head.appendChild(link);
        });
    }

    /**
     * Create a blur-up placeholder effect
     * @param {HTMLImageElement} img - The image element
     * @param {string} placeholderSrc - Low-quality placeholder source
     */
    createBlurUpEffect(img, placeholderSrc) {
        const container = document.createElement("div");
        container.className = "blur-up-container";

        const placeholder = document.createElement("img");
        placeholder.className = "blur-up-placeholder";
        placeholder.src = placeholderSrc;
        placeholder.alt = "";
        placeholder.setAttribute("aria-hidden", "true");

        img.className = "blur-up-image";

        container.appendChild(placeholder);
        container.appendChild(img);

        img.onload = () => {
            img.style.opacity = "1";
            placeholder.style.opacity = "0";
        };

        return container;
    }

    /**
     * Refresh lazy loading for dynamically added content
     */
    refresh() {
        this.observeLazyImages();
    }

    /**
     * Destroy the image optimization instance
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        this.loadedImages.clear();
        this.pendingImages.clear();
        this.initialized = false;
    }
}

// Create singleton instance
const imageOptimization = new ImageOptimization();

// Export utilities
export {
    imageOptimization,
    checkAvifSupport,
    checkWebpSupport,
    getBestFormat,
    formatSupport,
};

// Auto-initialize when DOM is ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () =>
        imageOptimization.init(),
    );
} else {
    imageOptimization.init();
}

export default imageOptimization;
