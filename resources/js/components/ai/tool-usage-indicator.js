/**
 * Tool Usage Indicator Component
 * Shows active tool executions in a compact indicator
 */

import Alpine from "alpinejs";

// Register Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("toolUsageIndicator", () => ({
        activeTools: [],

        initialize() {
            this.fetchToolUsage();
            setInterval(() => this.fetchToolUsage(), 1000);
        },

        async fetchToolUsage() {
            try {
                const response = await fetch("/api/ai/chat/tool-usage");
                const data = await response.json();

                if (data.tools) {
                    this.activeTools = data.tools;
                }
            } catch (error) {
                console.error("Failed to fetch tool usage:", error);
            }
        },
    }));
});
