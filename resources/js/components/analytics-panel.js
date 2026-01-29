/**
 * analyticsPanel - Alpine.js component for plan analytics & statistics
 * 
 * Purpose: Display comprehensive plan statistics and analytics
 * 
 * Features:
 *   - Key metrics calculation (plans created, wins, favorites)
 *   - Time-based analytics (creation trends, win rates)
 *   - Character/scenario statistics
 *   - Export analytics data
 *   - Filterable by date range
 * 
 * Usage:
 *   <div x-data="analyticsPanel()">
 *       <!-- dashboard content -->
 *   </div>
 */

export function analyticsPanel() {
    return {
        // State
        plans: [],
        dateRange: 'all', // all, week, month, year
        startDate: null,
        endDate: null,

        // Computed metrics
        get totalPlans() {
            return this.filteredPlans.length;
        },

        get totalWins() {
            return this.filteredPlans.filter(p => p.raceResults?.some(r => r.placement === 1)).length;
        },

        get totalFavorites() {
            return this.filteredPlans.filter(p => p.isFavorite).length;
        },

        get winRate() {
            if (this.totalPlans === 0) return 0;
            return Math.round((this.totalWins / this.totalPlans) * 100);
        },

        get averageCreatedAt() {
            if (this.totalPlans === 0) return 0;
            const dates = this.filteredPlans.map(p => new Date(p.createdAt).getTime());
            return new Date(dates.reduce((a, b) => a + b, 0) / dates.length);
        },

        get characterStats() {
            const stats = {};
            this.filteredPlans.forEach(plan => {
                const char = plan.characterName;
                if (!stats[char]) {
                    stats[char] = { count: 0, wins: 0 };
                }
                stats[char].count++;
                if (plan.raceResults?.some(r => r.placement === 1)) {
                    stats[char].wins++;
                }
            });
            return Object.entries(stats).map(([name, data]) => ({
                name,
                ...data,
                winRate: data.count > 0 ? Math.round((data.wins / data.count) * 100) : 0,
            })).sort((a, b) => b.count - a.count);
        },

        get scenarioStats() {
            const stats = {};
            this.filteredPlans.forEach(plan => {
                const scenario = plan.scenario || 'Unknown';
                if (!stats[scenario]) {
                    stats[scenario] = { count: 0, wins: 0 };
                }
                stats[scenario].count++;
                if (plan.raceResults?.some(r => r.placement === 1)) {
                    stats[scenario].wins++;
                }
            });
            return Object.entries(stats).map(([name, data]) => ({
                name,
                ...data,
                winRate: data.count > 0 ? Math.round((data.wins / data.count) * 100) : 0,
            })).sort((a, b) => b.count - a.count);
        },

        get filteredPlans() {
            let filtered = this.plans;

            // Apply date range filter
            if (this.startDate || this.endDate) {
                filtered = filtered.filter(p => {
                    const date = new Date(p.createdAt);
                    if (this.startDate && date < this.startDate) return false;
                    if (this.endDate && date > this.endDate) return false;
                    return true;
                });
            }

            return filtered;
        },

        // Set date range
        setDateRange(range) {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            switch (range) {
                case 'week':
                    this.startDate = new Date(today);
                    this.startDate.setDate(this.startDate.getDate() - 7);
                    this.endDate = now;
                    break;
                case 'month':
                    this.startDate = new Date(today);
                    this.startDate.setMonth(this.startDate.getMonth() - 1);
                    this.endDate = now;
                    break;
                case 'year':
                    this.startDate = new Date(today);
                    this.startDate.setFullYear(this.startDate.getFullYear() - 1);
                    this.endDate = now;
                    break;
                default:
                    this.startDate = null;
                    this.endDate = null;
            }

            this.dateRange = range;
        },

        // Export analytics
        exportAnalytics() {
            const data = {
                exportedAt: new Date().toISOString(),
                dateRange: this.dateRange,
                metrics: {
                    totalPlans: this.totalPlans,
                    totalWins: this.totalWins,
                    winRate: this.winRate,
                    totalFavorites: this.totalFavorites,
                },
                characterStats: this.characterStats,
                scenarioStats: this.scenarioStats,
            };

            const csv = this.convertToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `analytics-${Date.now()}.csv`;
            link.click();
            URL.revokeObjectURL(url);

            this.$dispatch('analytics-exported', { data });
        },

        // Convert data to CSV
        convertToCSV(data) {
            let csv = `Analytics Report\n`;
            csv += `Exported: ${data.exportedAt}\n`;
            csv += `Date Range: ${data.dateRange}\n\n`;

            csv += `Metrics\n`;
            csv += `Total Plans,${data.metrics.totalPlans}\n`;
            csv += `Total Wins,${data.metrics.totalWins}\n`;
            csv += `Win Rate,${data.metrics.winRate}%\n`;
            csv += `Total Favorites,${data.metrics.totalFavorites}\n\n`;

            csv += `Character Statistics\n`;
            csv += `Character,Count,Wins,Win Rate\n`;
            data.characterStats.forEach(stat => {
                csv += `${stat.name},${stat.count},${stat.wins},${stat.winRate}%\n`;
            });
            csv += '\n';

            csv += `Scenario Statistics\n`;
            csv += `Scenario,Count,Wins,Win Rate\n`;
            data.scenarioStats.forEach(stat => {
                csv += `${stat.name},${stat.count},${stat.wins},${stat.winRate}%\n`;
            });

            return csv;
        },

        // Initialize
        init() {
            // Load plans from localStorage or API
            const savedPlans = localStorage.getItem('analyticsPlans');
            if (savedPlans) {
                this.plans = JSON.parse(savedPlans);
            }
        },
    };
}
