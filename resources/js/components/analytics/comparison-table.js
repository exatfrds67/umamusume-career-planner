export default (config) => ({
    careers: config.careers || [],
    columns: config.columns || [],
    sortable: config.sortable,
    filterable: config.filterable,
    highlightBest: config.highlightBest,

    searchQuery: "",
    scenarioFilter: "",
    statusFilter: "",
    sortColumn: "",
    sortDirection: "asc",

    get filteredCareers() {
        let result = [...this.careers];

        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            result = result.filter(
                (c) =>
                    (c.career_name || "").toLowerCase().includes(query) ||
                    (c.character_name || "").toLowerCase().includes(query),
            );
        }

        if (this.scenarioFilter) {
            result = result.filter(
                (c) => c.scenario_type === this.scenarioFilter,
            );
        }

        if (this.statusFilter) {
            result = result.filter((c) => c.status === this.statusFilter);
        }

        if (this.sortColumn) {
            result.sort((a, b) => {
                let aVal = a[this.sortColumn];
                let bVal = b[this.sortColumn];

                if (aVal == null) aVal = 0;
                if (bVal == null) bVal = 0;

                if (typeof aVal === "number" && typeof bVal === "number") {
                    return this.sortDirection === "asc"
                        ? aVal - bVal
                        : bVal - aVal;
                }

                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();

                if (this.sortDirection === "asc") {
                    return aVal.localeCompare(bVal);
                }
                return bVal.localeCompare(aVal);
            });
        }

        return result;
    },

    sortBy(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
        } else {
            this.sortColumn = column;
            this.sortDirection = "asc";
        }
    },

    isHighlighted(career, key, type) {
        if (!this.highlightBest || this.filteredCareers.length < 2)
            return false;

        const values = this.filteredCareers.map((c) => c[key] || 0);
        const careerValue = career[key] || 0;

        if (type === "max") {
            return careerValue === Math.max(...values);
        } else if (type === "min") {
            return careerValue === Math.min(...values);
        }
        return false;
    },

    isBestPerformer(career) {
        if (!this.highlightBest || this.filteredCareers.length < 2)
            return false;

        const scores = this.filteredCareers.map((c) => ({
            id: c.career_id,
            score: (c.efficiency_rating || 0) + (c.race_win_rate || 0),
        }));

        const maxScore = Math.max(...scores.map((s) => s.score));
        return (
            (career.efficiency_rating || 0) + (career.race_win_rate || 0) ===
            maxScore
        );
    },

    formatNumber(value) {
        if (value == null) return "0";
        return new Intl.NumberFormat().format(Math.round(value));
    },

    formatPercentage(value) {
        if (value == null) return "0%";
        return Math.round(value) + "%";
    },

    formatScenario(value) {
        if (!value) return "Unknown";
        return value.replace("_", " ");
    },

    formatStatus(value) {
        if (!value) return "Unknown";
        return value.replace("_", " ");
    },

    getPercentageColor(value) {
        if (value >= 70) return "bg-green-500";
        if (value >= 50) return "bg-yellow-500";
        return "bg-red-500";
    },

    getScenarioBadgeClass(scenario) {
        const classes = {
            ura_finale:
                "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",
            unity_cup:
                "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300",
        };
        return (
            classes[scenario] ||
            "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
        );
    },

    getStatusBadgeClass(status) {
        const classes = {
            completed:
                "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",
            in_progress:
                "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",
            abandoned:
                "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300",
        };
        return (
            classes[status] ||
            "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
        );
    },

    calculateAverage(key) {
        if (this.filteredCareers.length === 0) return 0;
        const sum = this.filteredCareers.reduce(
            (acc, c) => acc + (c[key] || 0),
            0,
        );
        return Math.round((sum / this.filteredCareers.length) * 10) / 10;
    },

    calculateSum(key) {
        return this.filteredCareers.reduce((acc, c) => acc + (c[key] || 0), 0);
    },
});
