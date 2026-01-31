/**
 * Build Planner Component
 * Handles skill build templates, AI optimization, and saved builds
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("buildPlanner", () => ({
        // State
        buildTemplates: [],
        selectedTemplate: null,
        aiOptimization: null,
        savedBuilds: [],
        loading: false,

        // Initialize
        init() {
            this.loadBuildTemplates();
            this.loadSavedBuilds();
        },

        // Load build templates
        async loadBuildTemplates() {
            try {
                const response = await fetch("/api/skills/build-templates", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                if (!response.ok)
                    throw new Error("Failed to load build templates");

                const data = await response.json();
                this.buildTemplates = data.data || [];
            } catch (error) {
                console.error("Error loading build templates:", error);
                this.buildTemplates = this.getMockTemplates();
            }
        },

        // Load saved builds
        async loadSavedBuilds() {
            try {
                const response = await fetch("/api/skills/saved-builds", {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                if (!response.ok)
                    throw new Error("Failed to load saved builds");

                const data = await response.json();
                this.savedBuilds = data.data || [];
            } catch (error) {
                console.error("Error loading saved builds:", error);
                this.savedBuilds = [];
            }
        },

        // Select template
        selectTemplate(template) {
            this.selectedTemplate = template;
            this.aiOptimization = null;
        },

        // Get AI optimization
        async getAIOptimization() {
            if (!this.selectedTemplate) return;

            this.loading = true;
            try {
                const response = await fetch("/api/skills/build-optimization", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        template_id: this.selectedTemplate.id,
                    }),
                });

                if (!response.ok)
                    throw new Error("Failed to get AI optimization");

                const data = await response.json();
                this.aiOptimization = data.data;

                this.showSuccess("AI optimization completed successfully");
            } catch (error) {
                console.error("Error getting AI optimization:", error);
                this.showError("Failed to get AI optimization");
            } finally {
                this.loading = false;
            }
        },

        // Apply build
        async applyBuild() {
            if (!this.selectedTemplate) return;

            if (
                !confirm(
                    "This will apply the selected build to your character. Continue?",
                )
            ) {
                return;
            }

            this.loading = true;
            try {
                const response = await fetch("/api/skills/apply-build", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        template_id: this.selectedTemplate.id,
                    }),
                });

                if (!response.ok) throw new Error("Failed to apply build");

                this.showSuccess("Build applied successfully");

                // Reload parent component data
                if (window.Alpine && window.Alpine.store) {
                    window.dispatchEvent(
                        new CustomEvent("reload-character-data"),
                    );
                }
            } catch (error) {
                console.error("Error applying build:", error);
                this.showError("Failed to apply build");
            } finally {
                this.loading = false;
            }
        },

        // Save build
        async saveBuild() {
            if (!this.selectedTemplate) return;

            const buildName = prompt("Enter a name for this build:");
            if (!buildName) return;

            this.loading = true;
            try {
                const response = await fetch("/api/skills/save-build", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        name: buildName,
                        template_id: this.selectedTemplate.id,
                    }),
                });

                if (!response.ok) throw new Error("Failed to save build");

                await this.loadSavedBuilds();
                this.showSuccess("Build saved successfully");
            } catch (error) {
                console.error("Error saving build:", error);
                this.showError("Failed to save build");
            } finally {
                this.loading = false;
            }
        },

        // Export build
        exportBuild(build = null) {
            const buildData = build || this.selectedTemplate;
            if (!buildData) return;

            const dataStr = JSON.stringify(buildData, null, 2);
            const dataBlob = new Blob([dataStr], { type: "application/json" });
            const url = URL.createObjectURL(dataBlob);

            const link = document.createElement("a");
            link.href = url;
            link.download = `skill-build-${buildData.name || "export"}.json`;
            link.click();

            URL.revokeObjectURL(url);
            this.showSuccess("Build exported successfully");
        },

        // Load build
        loadBuild(build) {
            this.selectedTemplate = build;
            this.aiOptimization = null;
            this.showSuccess("Build loaded successfully");
        },

        // Delete build
        async deleteBuild(buildId) {
            if (!confirm("Are you sure you want to delete this build?")) {
                return;
            }

            try {
                const response = await fetch(`/api/skills/builds/${buildId}`, {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                if (!response.ok) throw new Error("Failed to delete build");

                await this.loadSavedBuilds();
                this.showSuccess("Build deleted successfully");
            } catch (error) {
                console.error("Error deleting build:", error);
                this.showError("Failed to delete build");
            }
        },

        // Create custom build
        createCustomBuild() {
            this.showError("Custom build creation coming soon!");
        },

        // Mock templates for development
        getMockTemplates() {
            return [
                {
                    id: 1,
                    name: "Speed Specialist",
                    category: "Speed",
                    meta_tier: "S",
                    description:
                        "Optimized for short-distance speed races with acceleration focus",
                    skill_count: 8,
                    total_sp_cost: 800,
                    optimized_cost: 640,
                    potential_savings: 160,
                    tags: ["Speed", "Acceleration", "Short Distance"],
                    skills: [],
                },
                {
                    id: 2,
                    name: "Stamina Endurance",
                    category: "Stamina",
                    meta_tier: "A",
                    description:
                        "Built for long-distance races with stamina recovery",
                    skill_count: 10,
                    total_sp_cost: 1000,
                    optimized_cost: 800,
                    potential_savings: 200,
                    tags: ["Stamina", "Recovery", "Long Distance"],
                    skills: [],
                },
                {
                    id: 3,
                    name: "Balanced All-Rounder",
                    category: "Balanced",
                    meta_tier: "A",
                    description:
                        "Versatile build suitable for various race types",
                    skill_count: 12,
                    total_sp_cost: 1200,
                    optimized_cost: 960,
                    potential_savings: 240,
                    tags: ["Balanced", "Versatile", "All Distance"],
                    skills: [],
                },
            ];
        },

        // Utility functions
        showSuccess(message) {
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: { type: "success", message },
                }),
            );
        },

        showError(message) {
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: { type: "error", message },
                }),
            );
        },
    }));
});
