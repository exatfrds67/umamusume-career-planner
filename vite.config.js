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
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ["chart.js", "alpinejs"],
                    training: ["resources/js/components/TrainingSystem.js"],
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
