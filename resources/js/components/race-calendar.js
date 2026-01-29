/**
 * Race Calendar Component
 * Manages race carousel navigation and race selection with gesture support
 * 
 * Features:
 * - Race carousel with swipe navigation
 * - Race filtering by type (turf/dirt/short/mile/medium/long)
 * - Auto-scroll to current/upcoming race
 * - Smooth 60fps transitions
 * - Race data with distance, grade, fan count
 */
export function raceCalendar() {
    return {
        // State
        races: [],
        currentRaceIndex: 0,
        selectedRaceId: null,
        filterType: null,
        isLoading: false,
        touchStartX: 0,
        touchEndX: 0,
        showFilters: false,
        selectedMonth: null,
        
        // Computed
        get currentRace() {
            return this.filteredRaces[this.currentRaceIndex] || null;
        },
        
        get filteredRaces() {
            if (!this.filterType) {
                return this.races;
            }
            return this.races.filter(r => r.type === this.filterType);
        },
        
        get totalRaces() {
            return this.filteredRaces.length;
        },
        
        get canGoForward() {
            return this.currentRaceIndex < this.totalRaces - 1;
        },
        
        get canGoBackward() {
            return this.currentRaceIndex > 0;
        },
        
        get raceProgress() {
            return this.totalRaces > 0 
                ? Math.round(((this.currentRaceIndex + 1) / this.totalRaces) * 100)
                : 0;
        },
        
        get upcomingRaces() {
            return this.filteredRaces.slice(this.currentRaceIndex, this.currentRaceIndex + 3);
        },
        
        get monthsWithRaces() {
            const months = new Map();
            this.races.forEach(race => {
                const month = race.month || 1;
                if (!months.has(month)) {
                    months.set(month, []);
                }
                months.get(month).push(race);
            });
            return Array.from(months.entries()).sort((a, b) => a[0] - b[0]);
        },
        
        // Methods
        init() {
            this.loadRaces();
            this.setupGestureListeners();
        },
        
        loadRaces() {
            // Would be populated from server
            // Sample race data structure
            if (this.races.length === 0) {
                this.races = [];
            }
        },
        
        nextRace() {
            if (this.canGoForward) {
                this.currentRaceIndex++;
                this.$dispatch('race-changed', { race: this.currentRace });
                this.scrollToCurrentRace();
            }
        },
        
        prevRace() {
            if (this.canGoBackward) {
                this.currentRaceIndex--;
                this.$dispatch('race-changed', { race: this.currentRace });
                this.scrollToCurrentRace();
            }
        },
        
        goToRace(index) {
            if (index >= 0 && index < this.totalRaces) {
                this.currentRaceIndex = index;
                this.selectedRaceId = this.currentRace.id;
                this.$dispatch('race-changed', { race: this.currentRace });
                this.scrollToCurrentRace();
            }
        },
        
        selectRaceById(raceId) {
            const index = this.filteredRaces.findIndex(r => r.id === raceId);
            if (index !== -1) {
                this.goToRace(index);
                this.selectedRaceId = raceId;
            }
        },
        
        filterByType(type) {
            this.filterType = type === this.filterType ? null : type;
            this.currentRaceIndex = 0;
            this.$dispatch('filter-changed', { type: this.filterType });
        },
        
        filterByMonth(month) {
            this.selectedMonth = month === this.selectedMonth ? null : month;
            this.currentRaceIndex = 0;
            this.$dispatch('month-filter-changed', { month: this.selectedMonth });
        },
        
        scrollToCurrentRace() {
            this.$nextTick(() => {
                const element = this.$el?.querySelector(`[data-race-index="${this.currentRaceIndex}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            });
        },
        
        setupGestureListeners() {
            this.$el?.addEventListener('touchstart', (e) => {
                this.touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            this.$el?.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            }, false);
        },
        
        handleSwipe() {
            const threshold = 50;
            const diff = this.touchStartX - this.touchEndX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.nextRace();
                } else {
                    this.prevRace();
                }
            }
        },
        
        getRaceStatusColor(race) {
            if (race.status === 'completed') return 'bg-green-100 dark:bg-green-900/30';
            if (race.status === 'upcoming') return 'bg-blue-100 dark:bg-blue-900/30';
            return 'bg-gray-100 dark:bg-gray-700/30';
        },
        
        getGradeColor(grade) {
            const colors = {
                'G1': 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20',
                'G2': 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20',
                'G3': 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                'Listed': 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20',
                'Open': 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20'
            };
            return colors[grade] || colors['Open'];
        },
        
        getDistanceLabel(distance) {
            if (distance < 1200) return 'Short (1000m-1100m)';
            if (distance < 1600) return 'Mile (1400m-1500m)';
            if (distance < 2400) return 'Medium (2000m-2300m)';
            return 'Long (2400m+)';
        },
        
        getRaceTypeIcon(type) {
            const icons = {
                'turf': '🌱',
                'dirt': '🏜️',
                'short': '⚡',
                'mile': '🎯',
                'medium': '📏',
                'long': '🏁'
            };
            return icons[type] || '🏁';
        }
    };
}
