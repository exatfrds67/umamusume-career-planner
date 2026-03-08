import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                // Component styles
                "resources/css/components/animations.css",
                "resources/css/components/character-card.css",
                "resources/css/components/gauges.css",
                "resources/css/components/grade-badge.css",
                "resources/css/components/keyboard-shortcuts.css",
                "resources/css/components/skill-card.css",
                "resources/css/components/stat-bar.css",
                "resources/css/components/support-card.css",
                "resources/css/components/turn-counter.css",
                "resources/css/components/ai/critical-alert-badge.css",
                "resources/css/components/ai/recommendation-card.css",
                "resources/css/components/condition-badge.css",
                // Page-specific scripts
                "resources/js/pages/test-api.js",
                "resources/js/pages/support-cards/deck-builder.js",
                "resources/js/pages/training/predictions.js",
                "resources/js/pages/characters/factors-manage.js",
                "resources/js/pages/ai/chat.js",
                "resources/js/pages/races/calendar.js",
                "resources/js/pages/performance/dashboard.js",
                "resources/js/pages/skills/index.js",
                // Phase 3: High-priority complex views
                "resources/js/pages/characters/create.js",
                "resources/js/pages/characters/edit.js",
                "resources/js/pages/profile/show.js",
                "resources/js/pages/mcp/dashboard.js",
                // Phase 4: OCR and remaining views
                "resources/js/pages/ocr/upload.js",
                "resources/js/pages/ocr/results.js",
                "resources/js/pages/ocr/partials/skill-list-form.js",
                "resources/js/pages/data-management/index.js",
                // Phase 5: Batch 1 - Analytics & AI Components
                "resources/js/components/analytics/stat-progression-chart.js",
                "resources/js/components/analytics/trend-analysis-chart.js",
                "resources/js/components/analytics/comparison-table.js",
                "resources/js/components/ai/tool-execution-monitor.js",
                "resources/js/components/ai/tool-usage-indicator.js",
                "resources/js/components/ai/workflow-visualization.js",
                "resources/js/components/ai/server-status-indicator.js",
                "resources/js/components/ai/provider-selector.js",
                "resources/js/components/ai/performance-metrics.js",
                "resources/js/components/ai/agent-selector.js",
                "resources/js/components/ai/agent-progress-tracker.js",
                // Phase 5: Batch 2 - Interactive UI Components
                "resources/js/components/line-chart.js",
                "resources/js/components/class-pyramid.js",
                "resources/js/components/spirit-burst-gauge.js",
                "resources/js/components/team-member-selector.js",
                "resources/js/components/slide-panel.js",
                "resources/js/components/quick-actions.js",
                "resources/js/components/password-input.js",
                // Phase 5: Batch 3 - Page Views & Partials
                "resources/js/pages/import/index.js",
                "resources/js/pages/export/index.js",
                "resources/js/pages/migration/index.js",
                "resources/js/pages/backup/index.js",
                "resources/js/pages/historical/index.js",
                "resources/js/pages/external-data/browse.js",
                "resources/js/pages/skills/partials/planner.js",
                "resources/js/pages/support-cards/deck-management.js",
                "resources/js/pages/races/targets.js",
                "resources/js/pages/profile/partials/security-tab.js",
                "resources/js/pages/test/remember-me-demo.js",
                "resources/js/components/activity-timeline.js",
                "resources/js/pages/training/show.js",
                "resources/js/components/facility-management.js",
                // Phase 6: Inline JS extraction
                "resources/js/pages/characters/index.js",
                "resources/js/pages/settings/index.js",
                "resources/js/pages/local/convert.js",
                "resources/js/pages/ai/dashboard.js",
                "resources/js/pages/training/partials/predictions-grid.js",
            ],
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
