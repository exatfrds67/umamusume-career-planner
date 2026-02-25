/**
 * Local Storage Convert
 * Handles validation and conversion of local storage data to account storage.
 *
 * No server-side data injection needed — reads directly from localStorage.
 */

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
document.addEventListener("alpine:init", () => {
    Alpine.data("localStorageConvert", () => ({
        stats: { characters: 0, careers: 0, builds: 0 },
        validating: false,
        validationResult: null,
        converting: false,
        conversionComplete: false,
        conversionMessage: "",
        options: {
            keepLocal: true,
            mergeDuplicates: false,
        },

        init() {
            this.loadStats();
        },

        loadStats() {
            try {
                const characters = JSON.parse(
                    localStorage.getItem("ucp_characters") || "[]",
                );
                const careers = JSON.parse(
                    localStorage.getItem("ucp_careers") || "[]",
                );
                const builds = JSON.parse(
                    localStorage.getItem("ucp_skill_builds") || "[]",
                );
                this.stats.characters = characters.length;
                this.stats.careers = careers.length;
                this.stats.builds = builds.length;
            } catch (e) {
                console.warn("Failed to load local stats:", e);
            }
        },

        async validateData() {
            this.validating = true;
            this.validationResult = null;

            try {
                const localData = {
                    characters: JSON.parse(
                        localStorage.getItem("ucp_characters") || "[]",
                    ),
                    careers: JSON.parse(
                        localStorage.getItem("ucp_careers") || "[]",
                    ),
                    builds: JSON.parse(
                        localStorage.getItem("ucp_skill_builds") ||
                            "[]",
                    ),
                };

                const response = await fetch(
                    "/api/storage/validate",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ data: localData }),
                    },
                );

                this.validationResult = await response.json();
            } catch (e) {
                this.validationResult = {
                    valid: false,
                    errors: ["Failed to validate: " + e.message],
                };
            } finally {
                this.validating = false;
            }
        },

        async convertToAccount() {
            if (!this.validationResult?.valid) {
                return;
            }
            this.converting = true;

            try {
                const localData = {
                    characters: JSON.parse(
                        localStorage.getItem("ucp_characters") || "[]",
                    ),
                    careers: JSON.parse(
                        localStorage.getItem("ucp_careers") || "[]",
                    ),
                    builds: JSON.parse(
                        localStorage.getItem("ucp_skill_builds") ||
                            "[]",
                    ),
                };

                const response = await fetch("/api/storage/convert", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector(
                                'meta[name="csrf-token"]',
                            )?.content || "",
                    },
                    body: JSON.stringify({
                        data: localData,
                        options: this.options,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    this.conversionComplete = true;
                    this.conversionMessage =
                        result.message ||
                        "All data has been migrated to your account.";

                    if (!this.options.keepLocal) {
                        localStorage.removeItem("ucp_characters");
                        localStorage.removeItem("ucp_careers");
                        localStorage.removeItem("ucp_skill_builds");
                    }
                } else {
                    this.validationResult = {
                        valid: false,
                        errors: [
                            result.message || "Conversion failed.",
                        ],
                    };
                }
            } catch (e) {
                this.validationResult = {
                    valid: false,
                    errors: ["Conversion error: " + e.message],
                };
            } finally {
                this.converting = false;
            }
        },
    }));
});
