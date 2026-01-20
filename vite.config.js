import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        target: "esnext",
        // Enable minification for production
        minify: "esbuild",
        // Enable source maps for debugging (disable in production if needed)
        sourcemap: false,
        // Code splitting configuration
        rollupOptions: {
            output: {
                // Manual chunk splitting for optimal bundle sizes
                manualChunks: {
                    // Vendor chunk for Alpine.js and plugins
                    "vendor-alpine": ["alpinejs", "@alpinejs/persist"],
                    // Core utilities chunk
                    "core-utils": [
                        "./resources/js/core/EventBus.js",
                        "./resources/js/core/ResponsiveSystem.js",
                        "./resources/js/core/ThemeSystem.js",
                    ],
                    // Accessibility chunk
                    accessibility: [
                        "./resources/js/core/AccessibilitySettings.js",
                        "./resources/js/core/AccessibilitySystem.js",
                    ],
                },
                // Asset file naming with content hash for cache busting
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split(".");
                    const ext = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp|avif/i.test(ext)) {
                        return `assets/images/[name]-[hash][extname]`;
                    }
                    if (/woff2?|eot|ttf|otf/i.test(ext)) {
                        return `assets/fonts/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
                // Chunk file naming
                chunkFileNames: "assets/js/[name]-[hash].js",
                // Entry file naming
                entryFileNames: "assets/js/[name]-[hash].js",
            },
        },
        // Chunk size warning limit (in KB)
        chunkSizeWarningLimit: 500,
        // CSS code splitting
        cssCodeSplit: true,
        // Asset inlining threshold (4KB)
        assetsInlineLimit: 4096,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ["alpinejs", "@alpinejs/persist"],
        // Exclude large dependencies that should be loaded on demand
        exclude: [],
    },
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
    // Enable CSS preprocessing optimizations
    css: {
        devSourcemap: true,
    },
});
