/**
 * Training Timeline Component
 * Manages turn-by-turn training progression with swipe navigation
 * 
 * Features:
 * - Navigate training turns with swipe gestures or buttons
 * - Display turn-specific stat gains and events
 * - Smooth 60fps animations
 * - Touch gesture support (swipe left/right)
 */
export function trainingTimeline() {
    return {
        // State
        currentTurn: 1,
        totalTurns: 1,
        turns: [],
        isLoading: false,
        showEventDetails: false,
        selectedEventIndex: null,
        touchStartX: 0,
        touchEndX: 0,
        
        // Computed
        get canGoForward() {
            return this.currentTurn < this.totalTurns;
        },
        
        get canGoBackward() {
            return this.currentTurn > 1;
        },
        
        get progressPercentage() {
            return Math.round((this.currentTurn / this.totalTurns) * 100);
        },
        
        get currentTurnData() {
            return this.turns[this.currentTurn - 1] || null;
        },
        
        get upcomingEventsCount() {
            return this.turns.filter(t => t.turn > this.currentTurn && t.events?.length > 0).length;
        },
        
        // Methods
        init() {
            this.loadTurns();
            this.setupGestureListeners();
        },
        
        loadTurns() {
            // Would be called with Alpine effect or onload
            // Sample data structure for turns
            if (this.turns.length === 0) {
                this.turns = [];
                for (let i = 1; i <= this.totalTurns; i++) {
                    this.turns.push({
                        turn: i,
                        stats: {
                            speed: 0,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wit: 0
                        },
                        energy: 100,
                        condition: 'normal',
                        events: [],
                        completed: i < this.currentTurn
                    });
                }
            }
        },
        
        nextTurn() {
            if (this.canGoForward) {
                this.currentTurn++;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },
        
        prevTurn() {
            if (this.canGoBackward) {
                this.currentTurn--;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },
        
        goToTurn(turnNumber) {
            if (turnNumber >= 1 && turnNumber <= this.totalTurns) {
                this.currentTurn = turnNumber;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },
        
        scrollToCurrentTurn() {
            this.$nextTick(() => {
                const element = this.$el?.querySelector(`[data-turn="${this.currentTurn}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            });
        },
        
        setupGestureListeners() {
            // Swipe detection
            this.$el?.addEventListener('touchstart', (e) => {
                this.touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            this.$el?.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            }, false);
        },
        
        handleSwipe() {
            const threshold = 50; // Minimum swipe distance
            const diff = this.touchStartX - this.touchEndX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    // Swiped left - go forward
                    this.nextTurn();
                } else {
                    // Swiped right - go backward
                    this.prevTurn();
                }
            }
        },
        
        selectEvent(index) {
            this.selectedEventIndex = index;
            this.showEventDetails = true;
        },
        
        closeEventDetails() {
            this.showEventDetails = false;
            this.selectedEventIndex = null;
        },
        
        getTurnStatusColor(turn) {
            if (turn.completed) return 'bg-green-100 dark:bg-green-900/30';
            if (turn.turn === this.currentTurn) return 'bg-blue-100 dark:bg-blue-900/30';
            return 'bg-gray-100 dark:bg-gray-700/30';
        },
        
        getTurnStatusText(turn) {
            if (turn.completed) return 'Completed';
            if (turn.turn === this.currentTurn) return 'Current';
            return 'Upcoming';
        },
        
        getConditionColor(condition) {
            const colors = {
                'great': 'text-green-600 dark:text-green-400',
                'good': 'text-lime-600 dark:text-lime-400',
                'normal': 'text-gray-600 dark:text-gray-400',
                'bad': 'text-red-600 dark:text-red-400'
            };
            return colors[condition] || colors['normal'];
        },
        
        getStatChangeColor(stat) {
            if (stat > 0) return 'text-green-600 dark:text-green-400';
            if (stat < 0) return 'text-red-600 dark:text-red-400';
            return 'text-gray-500 dark:text-gray-400';
        }
    };
}
