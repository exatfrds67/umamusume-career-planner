/**
 * Race Targets Page
 * Handles race targeting and planning interface.
 *
 * Data contract (per SPEC-003 §14.2):
 *   race.grade       — G1 | G2 | G3 | OP | Pre-OP | Debut
 *   race.phase       — junior | classic | senior | all
 *   race.fanCount    — fans awarded on win
 *   race.fanRequirement — minimum fans required to enter
 *   race.spReward    — SP awarded on win
 *   race.isUraFinale — boolean
 */

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
// Register Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("raceTargets", () => ({
        races: window.pageData?.races || [],
        selectedRaces: [],
        selectedRaceDetails: {},
        raceTurns: {},
        filterGrade: null,
        filterPhase: null,

        /** Available grade options — game-accurate order (PRD-003 §4.1). */
        grades: ["G1", "G2", "G3", "OP", "Pre-OP", "Debut"],

        /** Available phase options. */
        phases: ["junior", "classic", "senior", "all"],

        get filteredRaces() {
            let result = this.races;
            if (this.filterGrade) {
                result = result.filter((r) => r.grade === this.filterGrade);
            }
            if (this.filterPhase) {
                result = result.filter((r) => r.phase === this.filterPhase);
            }
            return result;
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

        setGradeFilter(grade) {
            this.filterGrade = this.filterGrade === grade ? null : grade;
        },

        setPhaseFilter(phase) {
            this.filterPhase = this.filterPhase === phase ? null : phase;
        },

        getGradeCount(grade) {
            return this.selectedRaces.filter((raceId) => {
                const race = this.selectedRaceDetails[raceId];
                return race && race.grade === grade;
            }).length;
        },

        savePlan() {
            if (this.selectedRaces.length === 0) { return; }

            const plan = {
                savedAt: new Date().toISOString(),
                selectedRaces: this.selectedRaces,
                raceTurns: this.raceTurns,
                details: this.selectedRaces.map((id) => ({
                    ...this.selectedRaceDetails[id],
                    plannedTurn: this.raceTurns[id] || null,
                })),
            };

            try {
                localStorage.setItem("umamusume_race_plan", JSON.stringify(plan));
                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "success",
                            message: `Race plan saved! ${this.selectedRaces.length} races selected.`,
                        },
                    }),
                );
            } catch {
                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "error",
                            message: "Failed to save plan. Storage may be full.",
                        },
                    }),
                );
            }
        },
    }));
});
