/**
 * Character Creation Wizard
 * Handles multi-step character creation with database/API integration
 */

// Register Alpine component on initialization
// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
document.addEventListener("alpine:init", () => {
    window.Alpine.data("characterWizard", () => ({
        currentStep: 1,
        showGallery: false,
        showDatabase: false,
        showExternalApi: false,
        showExternalPrefillNotice: false,

        // Validation state (WCAG 2.2 AA compliance)
        validationErrors: {},
        isValidating: false,

        // External API state (not yet implemented)
        // externalCharacters: [],
        // selectedExternalCharacter: null,
        // externalLoading: false,
        // externalDataLoading: false,
        // externalError: null,
        // externalSearched: false,
        // externalFilters: {
        //     query: "",
        //     category: "",
        // },

        formData: {
            title: "",
            name: "",
            avatar_url: "",
            avatar_preview: "",
            imageZoom: 1,
            imageRotation: 0,
            imageFlipH: false,
            imageX: 0,
            imageY: 0,
            scenario_type: "",
            external_source: null,
            external_id: null,
            trainee: null,
            stats: {
                speed: 0,
                stamina: 0,
                power: 0,
                guts: 0,
                wit: 0,
            },
            aptitudes: {
                distance: {
                    sprint: "",
                    mile: "",
                    medium: "",
                    long: "",
                },
                surface: {
                    turf: "",
                    dirt: "",
                },
                style: {
                    front_runner: "",
                    pace_chaser: "",
                    late_surger: "",
                    end_closer: "",
                },
            },
        },

        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,

        // Search Filters
        filters: {
            query: "",
            rarity: "",
            distance: "",
            surface: "",
            strategy: "",
        },

        // Trainee Database (from window.pageData)
        trainees: [],

        // Filtered trainees based on search filters
        filteredTrainees() {
            if (!this.trainees || this.trainees.length === 0) {
                return [];
            }

            return this.trainees.filter((trainee) => {
                // Search query filter
                if (this.filters.query) {
                    const query = this.filters.query.toLowerCase();
                    const matchesName = trainee.name
                        ?.toLowerCase()
                        .includes(query);
                    const matchesTitle = trainee.title
                        ?.toLowerCase()
                        .includes(query);
                    if (!matchesName && !matchesTitle) {
                        return false;
                    }
                }

                // Rarity filter
                if (
                    this.filters.rarity &&
                    trainee.rarity !== this.filters.rarity
                ) {
                    return false;
                }

                // Distance filter
                if (
                    this.filters.distance &&
                    trainee.distance !== this.filters.distance
                ) {
                    return false;
                }

                // Surface filter
                if (
                    this.filters.surface &&
                    trainee.surface !== this.filters.surface
                ) {
                    return false;
                }

                // Strategy filter
                if (
                    this.filters.strategy &&
                    trainee.strategy !== this.filters.strategy
                ) {
                    return false;
                }

                return true;
            });
        },

        // Select a trainee from the database
        selectTrainee(trainee) {
            this.formData.trainee = trainee;
            this.formData.name = trainee.name || "";
            this.formData.title = trainee.title || "";
            this.formData.avatar_url = trainee.image || "";
            this.formData.avatar_preview = trainee.image || "";

            // Prefill stats if available
            if (trainee.baseStats) {
                this.formData.stats = { ...trainee.baseStats };
            }

            // Prefill aptitudes if available
            if (trainee.aptitudes) {
                this.formData.aptitudes = { ...trainee.aptitudes };
            }

            // Close database modal
            this.showDatabase = false;
        },

        init() {
            // Load trainee data from window.pageData
            if (window.pageData && window.pageData.trainees) {
                this.trainees = window.pageData.trainees;
            }

            // Load draft from localStorage
            this.loadDraft();

            // Auto-save draft every 30 seconds
            setInterval(() => this.saveDraft(), 30000);

            // Check for external prefill
            if (window.pageData && window.pageData.externalPrefill) {
                this.showExternalPrefillNotice = true;
                this.formData.name = window.pageData.externalPrefill.name || "";
                this.formData.avatar_url =
                    window.pageData.externalPrefill.image || "";
                this.formData.avatar_preview =
                    window.pageData.externalPrefill.image || "";
            }
        },

        // Step names for mobile progress bar
        getStepName(step) {
            const names = {
                1: "Basic Info",
                2: "Stats",
                3: "Aptitudes",
                4: "Review",
            };
            return names[step] || "";
        },

        // Navigation
        goToStep(step) {
            if (step < 1 || step > 4) return;
            if (
                step > this.currentStep &&
                !this.validateStep(this.currentStep)
            ) {
                return;
            }
            this.currentStep = step;
            this.clearValidationErrors();
        },

        nextStep() {
            if (this.validateStep(this.currentStep)) {
                this.currentStep++;
                this.clearValidationErrors();
                this.saveDraft();
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.clearValidationErrors();
            }
        },

        // Validation
        validateStep(step) {
            this.validationErrors = {};

            switch (step) {
                case 1:
                    if (!this.formData.name || !this.formData.name.trim()) {
                        this.validationErrors.name =
                            "Character name is required";
                    }
                    if (!this.formData.scenario_type) {
                        this.validationErrors.scenario_type =
                            "Scenario type is required";
                    }
                    break;
                case 2:
                    // Stats validation (optional - could add range checks)
                    break;
                case 3:
                    // Aptitudes validation - check if at least some are selected
                    break;
            }

            return Object.keys(this.validationErrors).length === 0;
        },

        clearValidationErrors() {
            this.validationErrors = {};
        },

        validateStat(stat) {
            const value = this.formData.stats[stat];
            if (value > 1200) {
                this.formData.stats[stat] = 1200;
            }
            if (value < 0) {
                this.formData.stats[stat] = 0;
            }
        },

        isStep3Valid() {
            // Check if at least one aptitude is selected in each category
            const hasDistance = Object.values(
                this.formData.aptitudes.distance,
            ).some((v) => v);
            const hasSurface = Object.values(
                this.formData.aptitudes.surface,
            ).some((v) => v);
            const hasStyle = Object.values(this.formData.aptitudes.style).some(
                (v) => v,
            );
            return hasDistance && hasSurface && hasStyle;
        },

        // Stat grading
        getGrade(value) {
            if (value >= 1200) return "SS";
            if (value >= 1000) return "S";
            if (value >= 800) return "A";
            if (value >= 600) return "B";
            if (value >= 400) return "C";
            if (value >= 200) return "D";
            if (value >= 100) return "E";
            if (value >= 50) return "F";
            return "G";
        },

        getGradeColor(value) {
            const grade = this.getGrade(value);
            const colors = {
                SS: "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
                S: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
                A: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
                B: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
                C: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200",
                D: "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400",
                E: "bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-500",
                F: "bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-600",
                G: "bg-gray-100 text-gray-300 dark:bg-gray-700 dark:text-gray-700",
            };
            return colors[grade] || colors["G"];
        },

        // External API Integration (not yet implemented)
        // async searchExternalCharacters() { ... }
        // selectExternalCharacter(character) { ... }
        // async loadExternalCharacterData() { ... }

        // Draft Management
        saveDraft() {
            try {
                localStorage.setItem(
                    "character_create_draft",
                    JSON.stringify({
                        formData: this.formData,
                        currentStep: this.currentStep,
                        timestamp: Date.now(),
                    }),
                );
            } catch (error) {
                console.error("Failed to save draft:", error);
            }
        },

        loadDraft() {
            try {
                const draft = localStorage.getItem("character_create_draft");
                if (draft) {
                    const parsed = JSON.parse(draft);
                    // Only load if less than 24 hours old
                    if (Date.now() - parsed.timestamp < 24 * 60 * 60 * 1000) {
                        this.formData = parsed.formData;
                        this.currentStep = parsed.currentStep || 1;
                    }
                }
            } catch (error) {
                console.error("Failed to load draft:", error);
            }
        },

        clearStorage() {
            try {
                localStorage.removeItem("character_create_draft");
            } catch (error) {
                console.error("Failed to clear draft:", error);
            }
        },
    }));
});
