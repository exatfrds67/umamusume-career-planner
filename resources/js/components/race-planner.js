/**
 * racePlanner Alpine.js Component
 *
 * Provides a race selection interface for the plan wizard Step 4.
 * Displays a filterable list of common races that users can toggle on/off.
 * Syncs selected races with the parent planWizard component's plan.races array.
 */
export default function racePlanner() {
    return {
        gradeFilter: "",
        distanceFilter: "",
        surfaceFilter: "",
        phaseFilter: "",

        races: [
            {
                id: "hop-stakes",
                name: "Hop Stakes",
                grade: "G3",
                distance: 1400,
                surface: "turf",
                phase: "junior",
                distanceCategory: "mile",
            },
            {
                id: "junior-maiden",
                name: "Junior Maiden",
                grade: "G3",
                distance: 1200,
                surface: "turf",
                phase: "junior",
                distanceCategory: "short",
            },
            {
                id: "satsuki-sho",
                name: "Satsuki Sho",
                grade: "G1",
                distance: 2000,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "tokyo-yushun",
                name: "Tokyo Yushun (Japanese Derby)",
                grade: "G1",
                distance: 2400,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "kikuka-sho",
                name: "Kikuka Sho",
                grade: "G1",
                distance: 3000,
                surface: "turf",
                phase: "classic",
                distanceCategory: "long",
            },
            {
                id: "oka-sho",
                name: "Oka Sho",
                grade: "G1",
                distance: 1600,
                surface: "turf",
                phase: "classic",
                distanceCategory: "mile",
            },
            {
                id: "yushun-himba",
                name: "Yushun Himba (Oaks)",
                grade: "G1",
                distance: 2400,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "shuka-sho",
                name: "Shuka Sho",
                grade: "G1",
                distance: 2000,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "nhk-mile-cup",
                name: "NHK Mile Cup",
                grade: "G1",
                distance: 1600,
                surface: "turf",
                phase: "classic",
                distanceCategory: "mile",
            },
            {
                id: "japan-cup",
                name: "Japan Cup",
                grade: "G1",
                distance: 2400,
                surface: "turf",
                phase: "senior",
                distanceCategory: "intermediate",
            },
            {
                id: "arima-kinen",
                name: "Arima Kinen",
                grade: "G1",
                distance: 2500,
                surface: "turf",
                phase: "senior",
                distanceCategory: "long",
            },
            {
                id: "tenno-sho-spring",
                name: "Tenno Sho (Spring)",
                grade: "G1",
                distance: 3200,
                surface: "turf",
                phase: "senior",
                distanceCategory: "long",
            },
            {
                id: "tenno-sho-autumn",
                name: "Tenno Sho (Autumn)",
                grade: "G1",
                distance: 2000,
                surface: "turf",
                phase: "senior",
                distanceCategory: "intermediate",
            },
            {
                id: "takarazuka-kinen",
                name: "Takarazuka Kinen",
                grade: "G1",
                distance: 2200,
                surface: "turf",
                phase: "senior",
                distanceCategory: "intermediate",
            },
            {
                id: "victoria-mile",
                name: "Victoria Mile",
                grade: "G1",
                distance: 1600,
                surface: "turf",
                phase: "senior",
                distanceCategory: "mile",
            },
            {
                id: "yasuda-kinen",
                name: "Yasuda Kinen",
                grade: "G1",
                distance: 1600,
                surface: "turf",
                phase: "senior",
                distanceCategory: "mile",
            },
            {
                id: "mile-championship",
                name: "Mile Championship",
                grade: "G1",
                distance: 1600,
                surface: "turf",
                phase: "senior",
                distanceCategory: "mile",
            },
            {
                id: "sprinters-stakes",
                name: "Sprinters Stakes",
                grade: "G1",
                distance: 1200,
                surface: "turf",
                phase: "senior",
                distanceCategory: "short",
            },
            {
                id: "february-stakes",
                name: "February Stakes",
                grade: "G1",
                distance: 1600,
                surface: "dirt",
                phase: "senior",
                distanceCategory: "mile",
            },
            {
                id: "champions-cup",
                name: "Champions Cup",
                grade: "G1",
                distance: 1800,
                surface: "dirt",
                phase: "senior",
                distanceCategory: "intermediate",
            },
            {
                id: "osaka-hai",
                name: "Osaka Hai",
                grade: "G1",
                distance: 2000,
                surface: "turf",
                phase: "senior",
                distanceCategory: "intermediate",
            },
            {
                id: "takamatsunomiya-kinen",
                name: "Takamatsunomiya Kinen",
                grade: "G1",
                distance: 1200,
                surface: "turf",
                phase: "senior",
                distanceCategory: "short",
            },
            {
                id: "spring-stakes",
                name: "Spring Stakes",
                grade: "G2",
                distance: 1800,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "tulip-sho",
                name: "Tulip Sho",
                grade: "G2",
                distance: 1600,
                surface: "turf",
                phase: "classic",
                distanceCategory: "mile",
            },
            {
                id: "rose-stakes",
                name: "Rose Stakes",
                grade: "G2",
                distance: 1800,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "kobe-shimbun-hai",
                name: "Kobe Shimbun Hai",
                grade: "G2",
                distance: 2400,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "mainichi-hai",
                name: "Mainichi Hai",
                grade: "G3",
                distance: 1800,
                surface: "turf",
                phase: "classic",
                distanceCategory: "intermediate",
            },
            {
                id: "fairy-stakes",
                name: "Fairy Stakes",
                grade: "G3",
                distance: 1600,
                surface: "turf",
                phase: "junior",
                distanceCategory: "mile",
            },
        ],

        get filteredRaces() {
            return this.races.filter((race) => {
                if (this.gradeFilter && race.grade !== this.gradeFilter) {
                    return false;
                }
                if (
                    this.distanceFilter &&
                    race.distanceCategory !== this.distanceFilter
                ) {
                    return false;
                }
                if (this.surfaceFilter && race.surface !== this.surfaceFilter) {
                    return false;
                }
                if (this.phaseFilter && race.phase !== this.phaseFilter) {
                    return false;
                }
                return true;
            });
        },

        isSelected(raceId) {
            return this.$parent.plan.races.some((r) => r.id === raceId);
        },

        toggleRace(race) {
            if (this.isSelected(race.id)) {
                this.$parent.plan.races = this.$parent.plan.races.filter(
                    (r) => r.id !== race.id,
                );
            } else {
                this.$parent.plan.races.push({
                    id: race.id,
                    name: race.name,
                    grade: race.grade,
                    distance: race.distance,
                    surface: race.surface,
                    phase: race.phase,
                });
            }
            window.dispatchEvent(
                new CustomEvent("races-selected", {
                    detail: { races: this.$parent.plan.races },
                }),
            );
        },

        clearAll() {
            this.$parent.plan.races = [];
            window.dispatchEvent(
                new CustomEvent("races-selected", {
                    detail: { races: [] },
                }),
            );
        },
    };
}
