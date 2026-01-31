/**
 * Race Targets Page
 * Handles race targeting and planning interface
 */

import Alpine from "alpinejs";

// Register Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("raceTargets", () => ({
        races: window.pageData?.races || [],
        selectedRaces: [],
        selectedRaceDetails: {},
        raceTurns: {},
        filterGrade: null,

        get filteredRaces() {
            if (!this.filterGrade) return this.races;
            return this.races.filter((r) => r.grade === this.filterGrade);
        },

        get totalProjectedFans() {
            return this.selectedRaces.reduce((sum, raceId) => {
                return sum + (this.selectedRaceDetails[raceId]?.fanCount || 0);
            }, 0);
        },

        toggleTargetRace(raceId) {
            const idx = this.selectedRaces.indexOf(raceId);
            if (idx > -1) {
                this.selectedRaces.splice(idx, 1);
                delete this.selectedRaceDetails[raceId];
            } else {
                this.selectedRaces.push(raceId);
                this.selectedRaceDetails[raceId] = this.races.find(
                    (r) => r.id === raceId,
                );
            }
        },

        removeTargetRace(raceId) {
            this.toggleTargetRace(raceId);
        },

        updateRaceTurn(raceId, turn) {
            this.raceTurns[raceId] = turn;
        },

        getGradeCount(grade) {
            return this.selectedRaces.filter((raceId) => {
                const race = this.selectedRaceDetails[raceId];
                return race && race.grade === grade;
            }).length;
        },

        savePlan() {
            if (this.selectedRaces.length === 0) return;
            alert(
                "Race plan saved! Selected " +
                    this.selectedRaces.length +
                    " races",
            );
            // TODO: Send to server
        },
    }));
});
