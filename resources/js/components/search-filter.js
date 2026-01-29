/**
 * searchFilter - Alpine.js component for advanced plan search and filtering
 * 
 * Purpose: Powerful search and filtering for plan discovery
 * 
 * Features:
 *   - Full-text search across plan attributes
 *   - Multi-faceted filtering (character, scenario, status, tags)
 *   - Search history and saved filters
 *   - Real-time result count
 *   - Debounced search for performance
 * 
 * Usage:
 *   <div x-data="searchFilter()">
 *       <!-- search UI -->
 *   </div>
 */

export function searchFilter() {
    return {
        // State
        plans: [],
        searchQuery: '',
        selectedFilters: {
            character: [],
            scenario: [],
            status: [],
            tags: [],
            dateRange: null,
        },
        searchHistory: [],
        savedFilters: [],
        isSearching: false,
        searchTimeout: null,

        // Initialize
        init() {
            this.loadSearchHistory();
            this.loadSavedFilters();
        },

        // Get unique values for filters
        get availableCharacters() {
            return [...new Set(this.plans.map(p => p.characterName))].sort();
        },

        get availableScenarios() {
            return [...new Set(this.plans.map(p => p.scenario))].filter(Boolean).sort();
        },

        get availableStatuses() {
            return ['active', 'archived', 'favorite'];
        },

        get availableTags() {
            const tags = new Set();
            this.plans.forEach(p => {
                if (p.tags && Array.isArray(p.tags)) {
                    p.tags.forEach(t => tags.add(t));
                }
            });
            return Array.from(tags).sort();
        },

        // Search and filter
        get searchResults() {
            let results = this.plans;

            // Full-text search
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                results = results.filter(plan => {
                    return (
                        plan.characterName.toLowerCase().includes(query) ||
                        (plan.scenario && plan.scenario.toLowerCase().includes(query)) ||
                        (plan.notes && plan.notes.toLowerCase().includes(query)) ||
                        (plan.tags && plan.tags.some(t => t.toLowerCase().includes(query)))
                    );
                });
            }

            // Character filter
            if (this.selectedFilters.character.length > 0) {
                results = results.filter(p =>
                    this.selectedFilters.character.includes(p.characterName)
                );
            }

            // Scenario filter
            if (this.selectedFilters.scenario.length > 0) {
                results = results.filter(p =>
                    this.selectedFilters.scenario.includes(p.scenario)
                );
            }

            // Status filter
            if (this.selectedFilters.status.length > 0) {
                results = results.filter(p => {
                    const hasStatus = this.selectedFilters.status.some(status => {
                        if (status === 'favorite') return p.isFavorite;
                        if (status === 'archived') return p.isArchived;
                        if (status === 'active') return !p.isArchived && !p.isFavorite;
                        return false;
                    });
                    return hasStatus;
                });
            }

            // Tags filter (AND logic)
            if (this.selectedFilters.tags.length > 0) {
                results = results.filter(p =>
                    this.selectedFilters.tags.every(tag =>
                        p.tags && p.tags.includes(tag)
                    )
                );
            }

            return results;
        },

        get resultCount() {
            return this.searchResults.length;
        },

        // Debounced search
        handleSearch(event) {
            clearTimeout(this.searchTimeout);
            this.isSearching = true;

            this.searchTimeout = setTimeout(() => {
                this.searchQuery = event.target.value;
                this.isSearching = false;

                // Add to history
                if (this.searchQuery.trim()) {
                    this.addToSearchHistory(this.searchQuery);
                }

                this.$dispatch('search-results-updated', {
                    results: this.searchResults,
                    count: this.resultCount,
                });
            }, 300);
        },

        // Filter helpers
        toggleFilter(type, value) {
            const filters = this.selectedFilters[type];
            const index = filters.indexOf(value);

            if (index >= 0) {
                filters.splice(index, 1);
            } else {
                filters.push(value);
            }

            this.$dispatch('filters-changed', { filters: this.selectedFilters });
        },

        clearAllFilters() {
            this.selectedFilters = {
                character: [],
                scenario: [],
                status: [],
                tags: [],
                dateRange: null,
            };
            this.searchQuery = '';
            this.$dispatch('filters-cleared');
        },

        // Search history
        addToSearchHistory(query) {
            if (!this.searchHistory.includes(query)) {
                this.searchHistory.unshift(query);
                if (this.searchHistory.length > 10) {
                    this.searchHistory.pop();
                }
                this.saveSearchHistory();
            }
        },

        removeFromSearchHistory(query) {
            const index = this.searchHistory.indexOf(query);
            if (index >= 0) {
                this.searchHistory.splice(index, 1);
                this.saveSearchHistory();
            }
        },

        loadSearchHistory() {
            const saved = localStorage.getItem('searchHistory');
            if (saved) {
                this.searchHistory = JSON.parse(saved);
            }
        },

        saveSearchHistory() {
            localStorage.setItem('searchHistory', JSON.stringify(this.searchHistory));
        },

        // Saved filters
        saveCurrentFilter(name) {
            const filter = {
                id: Date.now(),
                name,
                filters: JSON.parse(JSON.stringify(this.selectedFilters)),
                createdAt: new Date().toISOString(),
            };

            this.savedFilters.push(filter);
            this.saveSavedFilters();
            this.$dispatch('filter-saved', { filter });
        },

        applySavedFilter(id) {
            const filter = this.savedFilters.find(f => f.id === id);
            if (filter) {
                this.selectedFilters = JSON.parse(JSON.stringify(filter.filters));
                this.$dispatch('saved-filter-applied', { filter });
            }
        },

        deleteSavedFilter(id) {
            const index = this.savedFilters.findIndex(f => f.id === id);
            if (index >= 0) {
                this.savedFilters.splice(index, 1);
                this.saveSavedFilters();
            }
        },

        loadSavedFilters() {
            const saved = localStorage.getItem('savedFilters');
            if (saved) {
                this.savedFilters = JSON.parse(saved);
            }
        },

        saveSavedFilters() {
            localStorage.setItem('savedFilters', JSON.stringify(this.savedFilters));
        },

        // Export results
        exportResults() {
            const data = {
                exportedAt: new Date().toISOString(),
                query: this.searchQuery,
                filters: this.selectedFilters,
                count: this.resultCount,
                results: this.searchResults,
            };

            const csv = this.resultsToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `search-results-${Date.now()}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        },

        resultsToCSV(data) {
            let csv = `Search Results Export\n`;
            csv += `Exported: ${data.exportedAt}\n`;
            csv += `Query: ${data.query}\n`;
            csv += `Total Results: ${data.count}\n\n`;

            csv += `Character,Scenario,Created,Updated\n`;
            data.results.forEach(result => {
                csv += `"${result.characterName}","${result.scenario || ''}","${result.createdAt}","${result.updatedAt}"\n`;
            });

            return csv;
        },
    };
}
