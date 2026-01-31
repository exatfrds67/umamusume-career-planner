/**
 * Race Calendar Page Script
 * Handles race carousel navigation, filtering, and selection
 */

// Access data from window.raceCalendarData (injected by Blade)
const { races } = window.raceCalendarData || {};

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("raceCarouselView", () => ({
        // State
        races: races || [],
        currentRaceIndex: 0,
        activeTypeFilter: null,
        activeMonthFilter: null,
        touchStartX: 0,
        touchEndX: 0,

        // Constants
        raceTypes: ["turf", "dirt", "short", "mile", "medium", "long"],
        months: [
            { num: 1, name: "January" },
            { num: 2, name: "February" },
            { num: 3, name: "March" },
            { num: 4, name: "April" },
            { num: 5, name: "May" },
            { num: 6, name: "June" },
            { num: 7, name: "July" },
            { num: 8, name: "August" },
            { num: 9, name: "September" },
            { num: 10, name: "October" },
            { num: 11, name: "November" },
            { num: 12, name: "December" },
        ],

        // Computed properties
        get currentRace() {
            return this.filteredRaces[this.currentRaceIndex] || null;
        },

        get filteredRaces() {
            let filtered = this.races;

            if (this.activeTypeFilter) {
                filtered = filtered.filter(
                    (r) => r.type === this.activeTypeFilter,
                );
            }

            if (this.activeMonthFilter) {
                filtered = filtered.filter(
                    (r) => r.month === parseInt(this.activeMonthFilter),
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

        filterByType(type) {
            this.activeTypeFilter =
                this.activeTypeFilter === type ? null : type;
            this.currentRaceIndex = 0;
            this.$dispatch("filter-changed", { type: this.activeTypeFilter });
        },

        filterByMonth(month) {
            this.activeMonthFilter = month;
            this.currentRaceIndex = 0;
            this.$dispatch("month-filter-changed", {
                month: this.activeMonthFilter,
            });
        },

        getMonthName(monthNum) {
            return (
                this.months.find((m) => m.num === parseInt(monthNum))?.name ||
                ""
            );
        },

        getRaceTypeLabel(type) {
            const labels = {
                turf: "Turf Race",
                dirt: "Dirt Race",
                short: "Short Distance",
                mile: "Mile Race",
                medium: "Medium Distance",
                long: "Long Distance",
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

        getRaceStatusMessage(status) {
            const messages = {
                completed: "Already completed in career",
                upcoming: "Not yet encountered",
                current: "Available in current turn",
            };
            return messages[status] || "";
        },

        getRaceDescription(race) {
            if (!race) return "";
            return `${race.grade} race on ${race.type} surface. Prize pool attracts ${race.fanCount.toLocaleString()} fans.`;
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
