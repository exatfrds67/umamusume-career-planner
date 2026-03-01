/**
 * Race Calendar Page Script
 * Handles race carousel navigation, filtering, and selection.
 *
 * Data contract (per SPEC-003 §14.1):
 *   race.surface          — "turf" | "dirt"
 *   race.distanceCategory — "sprint" | "mile" | "medium" | "long" | "super_long"
 *   race.phase            — "junior" | "classic" | "senior" | "all"
 *   race.month            — in-game month label string (e.g. "April")
 *   race.year             — year_in_scenario integer (1 = Junior, 2 = Classic, 3 = Senior)
 *   race.fansReward       — fans awarded on win
 *   race.spReward         — SP awarded on win
 *   race.fanRequirement   — minimum fans required to enter
 *   race.isUraFinale      — boolean
 */

// Access data from data island (injected by Blade)
const dataElement = document.getElementById("race-calendar-data");
const calendarRaces = dataElement ? JSON.parse(dataElement.textContent) : [];

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("raceCarouselView", () => ({
        // State
        races: calendarRaces || [],
        currentRaceIndex: 0,
        activeSurfaceFilter: null,
        activeDistanceFilter: null,
        activePhaseFilter: null,
        activeMonthFilter: null,
        touchStartX: 0,
        touchEndX: 0,

        // Filter option constants (game-accurate values)
        surfaces: ["turf", "dirt"],
        distanceCategories: ["sprint", "mile", "medium", "long", "super_long"],
        phases: ["junior", "classic", "senior", "all"],

        /** Unique in-game month labels derived from actual race data, in scenario order. */
        get months() {
            const seen = new Set();
            const result = [];
            const sorted = [...this.races].sort((a, b) => {
                if (a.year !== b.year) {
                    return (a.year ?? 99) - (b.year ?? 99);
                }
                return 0;
            });
            for (const r of sorted) {
                if (r.month && !seen.has(r.month)) {
                    seen.add(r.month);
                    result.push(r.month);
                }
            }
            return result;
        },

        // Computed properties
        get currentRace() {
            return this.filteredRaces[this.currentRaceIndex] || null;
        },

        get filteredRaces() {
            let filtered = this.races;

            if (this.activeSurfaceFilter) {
                filtered = filtered.filter(
                    (r) => r.surface === this.activeSurfaceFilter,
                );
            }

            if (this.activeDistanceFilter) {
                filtered = filtered.filter(
                    (r) => r.distanceCategory === this.activeDistanceFilter,
                );
            }

            if (this.activePhaseFilter) {
                filtered = filtered.filter(
                    (r) => r.phase === this.activePhaseFilter,
                );
            }

            if (this.activeMonthFilter) {
                filtered = filtered.filter(
                    (r) => r.month === this.activeMonthFilter,
                );
            }

            return filtered;
        },

        get canGoForward() {
            return this.currentRaceIndex < this.filteredRaces.length - 1;
        },

        get canGoBackward() {
            return this.currentRaceIndex > 0;
        },

        get raceProgress() {
            return this.filteredRaces.length > 0
                ? ((this.currentRaceIndex + 1) / this.filteredRaces.length) *
                      100
                : 0;
        },

        get upcomingRaces() {
            return this.filteredRaces.slice(
                this.currentRaceIndex,
                this.currentRaceIndex + 4,
            );
        },

        // Methods
        initRaces() {
            // Races already loaded from window.raceCalendarData
            if (this.races.length > 0) {
                this.$dispatch("races-loaded", { count: this.races.length });
            }
        },

        nextRace() {
            if (this.canGoForward) {
                this.currentRaceIndex++;
                this.$dispatch("race-changed", { race: this.currentRace });
            }
        },

        prevRace() {
            if (this.canGoBackward) {
                this.currentRaceIndex--;
                this.$dispatch("race-changed", { race: this.currentRace });
            }
        },

        goToRace(index) {
            if (index >= 0 && index < this.filteredRaces.length) {
                this.currentRaceIndex = index;
                this.$dispatch("race-changed", { race: this.currentRace });
            }
        },

        selectRace(raceId) {
            this.$dispatch("race-selected", { raceId });

            // Show success toast
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: {
                        type: "success",
                        message: "Race selected successfully!",
                    },
                }),
            );

            // Could navigate to race details or planning page
            // window.location.href = `/races/${raceId}`;
        },

        saveRaceNote(raceId) {
            // Save race selection or note
            this.$dispatch("race-note-saved", { raceId });

            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: {
                        type: "info",
                        message: "Race note saved!",
                    },
                }),
            );
        },

        filterBySurface(surface) {
            this.activeSurfaceFilter =
                this.activeSurfaceFilter === surface ? null : surface;
            this.currentRaceIndex = 0;
            this.$dispatch("filter-changed", { surface: this.activeSurfaceFilter });
        },

        filterByDistance(distanceCategory) {
            this.activeDistanceFilter =
                this.activeDistanceFilter === distanceCategory ? null : distanceCategory;
            this.currentRaceIndex = 0;
            this.$dispatch("filter-changed", { distance: this.activeDistanceFilter });
        },

        filterByPhase(phase) {
            this.activePhaseFilter =
                this.activePhaseFilter === phase ? null : phase;
            this.currentRaceIndex = 0;
            this.$dispatch("filter-changed", { phase: this.activePhaseFilter });
        },

        filterByMonth(month) {
            // month is already an in-game label string (e.g. "April")
            this.activeMonthFilter = this.activeMonthFilter === month ? null : month;
            this.currentRaceIndex = 0;
            this.$dispatch("month-filter-changed", {
                month: this.activeMonthFilter,
            });
        },

        /** Month is already a label string in race data — return it directly. */
        getMonthName(month) {
            return month || "";
        },

        getRaceTypeLabel(type) {
            const labels = {
                // Surface labels
                turf: "Turf",
                dirt: "Dirt",
                // Distance category labels
                sprint: "Sprint",
                mile: "Mile",
                medium: "Medium",
                long: "Long",
                super_long: "Super Long",
            };
            return labels[type] || type;
        },

        getRaceStatusIcon(status) {
            const icons = {
                completed: "✅",
                upcoming: "🔜",
                current: "🏃",
            };
            return icons[status] || "❓";
        },

        getRaceStatusLabel(status) {
            const labels = {
                completed: "Completed",
                upcoming: "Upcoming",
                current: "Available",
            };
            return labels[status] || "Unknown";
        },

        getRaceStatusMessage(status) {
            const messages = {
                completed: "Already completed in career",
                upcoming: "Not yet encountered",
                current: "Available in current turn",
            };
            return messages[status] || "";
        },

        getRaceDescription(race) {
            if (!race) { return ""; }
            const fans = (race.fansReward ?? 0).toLocaleString();
            const sp = race.spReward ?? 0;
            return `${race.grade} race on ${race.surface} surface. Win: ${fans} fans, ${sp} SP.`;
        },

        // Touch gesture handlers
        handleTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
        },

        handleTouchEnd(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            const diff = this.touchStartX - this.touchEndX;

            // Swipe threshold: 50px
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    // Swipe left - next race
                    this.nextRace();
                } else {
                    // Swipe right - previous race
                    this.prevRace();
                }
            }
        },
    }));
});
