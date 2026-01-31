/**
 * Characters List Page Script
 * Handles instant filtering and search for character list
 */

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("charactersList", () => ({
        // State
        allCharacters: [],
        filteredCharacters: [],
        loading: false,

        // Filters
        filters: {
            search: "",
            scenario: "",
            status: "",
            sort: "name", // Default to alphabetical sorting
        },

        // Initialize
        init() {
            // Load characters from window.charactersData (injected by Blade)
            this.allCharacters = window.charactersData || [];
            this.filteredCharacters = [...this.allCharacters];

            // Debug: Log character count
            console.log(
                `Loaded ${this.allCharacters.length} characters from database`,
            );

            // Apply initial filtering
            this.filterCharacters();
        },

        // Filter characters based on current filters
        filterCharacters() {
            let filtered = [...this.allCharacters];

            // Filter by search query
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase();
                filtered = filtered.filter((character) =>
                    character.name.toLowerCase().includes(query),
                );
            }

            // Filter by scenario
            if (this.filters.scenario) {
                filtered = filtered.filter(
                    (character) =>
                        character.scenario_type === this.filters.scenario,
                );
            }

            // Filter by status
            if (this.filters.status) {
                filtered = filtered.filter(
                    (character) => character.status === this.filters.status,
                );
            }

            // Sort characters
            filtered = this.sortCharacters(filtered);

            this.filteredCharacters = filtered;
        },

        // Sort characters based on selected sort option
        sortCharacters(characters) {
            const sorted = [...characters];

            // Always show pinned characters first
            sorted.sort((a, b) => {
                // Pinned characters come first
                if (a.is_pinned && !b.is_pinned) return -1;
                if (!a.is_pinned && b.is_pinned) return 1;

                // Then apply the selected sort
                switch (this.filters.sort) {
                    case "name":
                        return a.name.localeCompare(b.name);

                    case "created_at":
                        return (
                            new Date(b.created_at).getTime() -
                            new Date(a.created_at).getTime()
                        );

                    case "updated_at":
                    default:
                        return (
                            new Date(b.updated_at).getTime() -
                            new Date(a.updated_at).getTime()
                        );
                }
            });

            return sorted;
        },

        // Clear all filters
        clearFilters() {
            this.filters.search = "";
            this.filters.scenario = "";
            this.filters.status = "";
            this.filters.sort = "name";
            this.filterCharacters();
        },

        // Check if any filters are active
        get hasActiveFilters() {
            return (
                this.filters.search !== "" ||
                this.filters.scenario !== "" ||
                this.filters.status !== "" ||
                this.filters.sort !== "name"
            );
        },
    }));
});
