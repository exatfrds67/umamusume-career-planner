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
        _boundOnDrag: null,
        _boundStopDrag: null,

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
            const avatarUrl = this.resolveCharacterImageUrl(trainee);

            this.clearAvatarUploadInput();
            this.formData.trainee = trainee;
            this.formData.name = trainee.name || "";
            this.formData.title = trainee.title || "";
            this.formData.avatar_url = avatarUrl;
            this.formData.avatar_preview = avatarUrl;

            // Prefill stats if available
            if (trainee.baseStats) {
                this.formData.stats = { ...trainee.baseStats };
            }

            // Prefill aptitudes if available (deep merge to preserve structure)
            if (trainee.aptitudes && typeof trainee.aptitudes === 'object' && !Array.isArray(trainee.aptitudes)) {
                this.formData.aptitudes = this.deepMerge(this.formData.aptitudes, trainee.aptitudes);
            }

            // Close database modal
            this.showDatabase = false;
        },

        init() {
            const dataElement = document.getElementById("page-data");
            const pageData = dataElement
                ? JSON.parse(dataElement.textContent)
                : {};

            // Load trainee data from pageData
            if (pageData && pageData.trainees) {
                this.trainees = pageData.trainees;
            }

            // Load draft from localStorage
            this.loadDraft();

            // Auto-save draft every 30 seconds
            setInterval(() => this.saveDraft(), 30000);

            // Bind document-level drag events for image positioning
            this._boundOnDrag = (e) => this.onDrag(e);
            this._boundStopDrag = () => this.stopDrag();
            document.addEventListener('mousemove', this._boundOnDrag);
            document.addEventListener('mouseup', this._boundStopDrag);
            document.addEventListener('touchmove', this._boundOnDrag, { passive: false });
            document.addEventListener('touchend', this._boundStopDrag);

            // Check for external prefill
            if (pageData && pageData.externalPrefill) {
                const avatarUrl = this.resolveCharacterImageUrl(
                    pageData.externalPrefill,
                );

                this.showExternalPrefillNotice = true;
                this.formData.name = pageData.externalPrefill.name || "";
                this.formData.avatar_url = avatarUrl;
                this.formData.avatar_preview = avatarUrl;
            }
        },

        resolveCharacterImageUrl(character) {
            const candidates = [
                character?.avatar_url,
                character?.image,
                character?.image_url,
                character?.thumb_img,
                character?.image_path,
            ];

            for (const candidate of candidates) {
                if (typeof candidate === "string" && candidate.trim() !== "") {
                    return candidate;
                }
            }

            return "";
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
            const distance = this.formData.aptitudes?.distance;
            const surface = this.formData.aptitudes?.surface;
            const style = this.formData.aptitudes?.style;

            if (!distance || !surface || !style) {
                return false;
            }

            const hasDistance = Object.values(distance).some((v) => v);
            const hasSurface = Object.values(surface).some((v) => v);
            const hasStyle = Object.values(style).some((v) => v);
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
                C: "bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200",
                D: "bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400",
                E: "bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-500",
                F: "bg-neutral-100 text-neutral-400 dark:bg-neutral-700 dark:text-neutral-600",
                G: "bg-neutral-100 text-neutral-300 dark:bg-neutral-700 dark:text-neutral-700",
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
                        const defaults = JSON.parse(JSON.stringify(this.formData));
                        this.formData = this.deepMerge(defaults, parsed.formData || {});
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

        deepMerge(target, source) {
            const result = { ...target };
            for (const key of Object.keys(source)) {
                if (
                    source[key] &&
                    typeof source[key] === "object" &&
                    !Array.isArray(source[key]) &&
                    target[key] &&
                    typeof target[key] === "object" &&
                    !Array.isArray(target[key])
                ) {
                    result[key] = this.deepMerge(target[key], source[key]);
                } else {
                    result[key] = source[key];
                }
            }
            return result;
        },

        destroy() {
            if (this._boundOnDrag) {
                document.removeEventListener('mousemove', this._boundOnDrag);
                document.removeEventListener('touchmove', this._boundOnDrag);
            }
            if (this._boundStopDrag) {
                document.removeEventListener('mouseup', this._boundStopDrag);
                document.removeEventListener('touchend', this._boundStopDrag);
            }
        },

        // --- Image Editor Methods ---

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPG, PNG, or GIF)');
                return;
            }

            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File size must be less than 2MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.formData.avatar_preview = e.target.result;
                this.formData.avatar_url = 'custom_upload';
                this.showGallery = false;
                this.resetImageEdits();
            };
            reader.readAsDataURL(file);
        },

        clearAvatarUploadInput() {
            const avatarUploadInput = document.querySelector('input[name="avatar_upload"]');

            if (avatarUploadInput) {
                avatarUploadInput.value = '';
            }
        },

        selectGalleryImage(imagePath) {
            this.clearAvatarUploadInput();
            this.formData.avatar_url = imagePath;
            this.formData.avatar_preview = imagePath;
            this.showGallery = false;
            this.resetImageEdits();
        },

        useDefaultAvatar() {
            this.clearAvatarUploadInput();
            this.formData.avatar_url = '';
            this.formData.avatar_preview = '';
            this.showGallery = false;
            this.resetImageEdits();
        },

        adjustZoom(delta) {
            const newZoom = this.formData.imageZoom + delta;
            if (newZoom >= 0.5 && newZoom <= 2) {
                this.formData.imageZoom = Math.round(newZoom * 10) / 10;
            }
        },

        rotateImage(degrees) {
            this.formData.imageRotation = (this.formData.imageRotation + degrees) % 360;
            if (this.formData.imageRotation < 0) {
                this.formData.imageRotation += 360;
            }
        },

        flipImageHorizontal() {
            this.formData.imageFlipH = !this.formData.imageFlipH;
        },

        resetImageEdits() {
            this.formData.imageZoom = 1;
            this.formData.imageRotation = 0;
            this.formData.imageFlipH = false;
            this.formData.imageX = 0;
            this.formData.imageY = 0;
        },

        moveImage(deltaX, deltaY) {
            this.formData.imageX += deltaX;
            this.formData.imageY += deltaY;
        },

        startDrag(event) {
            this.isDragging = true;
            const clientX = event.touches ? event.touches[0].clientX : event.clientX;
            const clientY = event.touches ? event.touches[0].clientY : event.clientY;
            this.dragStartX = clientX - this.formData.imageX;
            this.dragStartY = clientY - this.formData.imageY;
        },

        onDrag(event) {
            if (!this.isDragging) {
                return;
            }
            event.preventDefault();
            const clientX = event.touches ? event.touches[0].clientX : event.clientX;
            const clientY = event.touches ? event.touches[0].clientY : event.clientY;
            this.formData.imageX = clientX - this.dragStartX;
            this.formData.imageY = clientY - this.dragStartY;
        },

        stopDrag() {
            this.isDragging = false;
        },
    }));
});
