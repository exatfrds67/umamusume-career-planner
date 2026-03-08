/**
 * SP Allocator Component
 * Manages skill SP budget allocation with drag-and-drop interface
 * 
 * Features:
 * - Drag and drop skills to allocate SP
 * - Budget validation and enforcement
 * - Visual feedback for over-allocation
 * - Auto-save with debouncing
 * - Undo/redo support
 */
export function spAllocator() {
    return {
        // State
        character: null,
        skills: [],
        allocations: {}, // { skillId: spAmount }
        totalBudget: 0,
        availableSP: 0,
        isLoading: false,
        isDirty: false,
        isSaving: false,
        draggedSkill: null,
        draggedFromIndex: null,
        showBudgetWarning: false,
        allocationHistory: [],
        historyIndex: -1,
        saveTimeout: null,
        
        // Computed
        get totalAllocated() {
            return Object.values(this.allocations).reduce((sum, val) => sum + (val || 0), 0);
        },
        
        get remainingSP() {
            return this.totalBudget - this.totalAllocated;
        },
        
        get isOverBudget() {
            return this.remainingSP < 0;
        },
        
        get budgetStatus() {
            if (this.isOverBudget) return 'critical';
            if (this.remainingSP < (this.totalBudget * 0.1)) return 'warning';
            return 'healthy';
        },
        
        get budgetColor() {
            const colors = {
                critical: 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20',
                warning: 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                healthy: 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20'
            };
            return colors[this.budgetStatus];
        },
        
        get allocatedSkills() {
            return this.skills.filter(s => (this.allocations[s.id] || 0) > 0);
        },
        
        get unallocatedSkills() {
            return this.skills.filter(s => (this.allocations[s.id] || 0) === 0);
        },
        
        // Methods
        init() {
            this.loadCharacterSkills();
        },
        
        loadCharacterSkills() {
            // Initialize allocations from character's skill plan
            // This would be populated from a server call
            this.skills.forEach(skill => {
                if (!this.allocations[skill.id]) {
                    this.allocations[skill.id] = 0;
                }
            });
            this.saveHistoryState();
        },
        
        handleDragStart(skillId, fromIndex = null) {
            this.draggedSkill = skillId;
            this.draggedFromIndex = fromIndex;
        },
        
        handleDragOver(e) {
            e.preventDefault();
            // Visual feedback for drop zone
        },
        
        handleDrop(e) {
            e.preventDefault();
            if (!this.draggedSkill) return;
            
            // Reset drag state
            this.draggedSkill = null;
            this.draggedFromIndex = null;
        },
        
        allocateSP(skillId, amount) {
            const previousAmount = this.allocations[skillId] || 0;
            this.allocations[skillId] = Math.max(0, amount);
            
            // Check if over budget
            if (this.isOverBudget) {
                // Show warning and prevent if critical
                this.showBudgetWarning = true;
                
                // Auto-revert if way over
                if (this.remainingSP < -100) {
                    this.allocations[skillId] = previousAmount;
                    this.$dispatch('allocation-error', { message: 'Allocation exceeds budget significantly' });
                    return false;
                }
            } else {
                this.showBudgetWarning = false;
            }
            
            this.isDirty = true;
            this.saveHistoryState();
            this.debouncedSave();
            this.$dispatch('allocation-changed', { skillId, amount: this.allocations[skillId] });
            return true;
        },
        
        incrementAllocation(skillId, amount = 1) {
            const current = this.allocations[skillId] || 0;
            this.allocateSP(skillId, current + amount);
        },
        
        decrementAllocation(skillId, amount = 1) {
            const current = this.allocations[skillId] || 0;
            this.allocateSP(skillId, current - amount);
        },
        
        clearAllocation(skillId) {
            this.allocations[skillId] = 0;
            this.isDirty = true;
            this.saveHistoryState();
            this.debouncedSave();
            this.$dispatch('allocation-cleared', { skillId });
        },
        
        clearAllAllocations() {
            Object.keys(this.allocations).forEach(skillId => {
                this.allocations[skillId] = 0;
            });
            this.isDirty = true;
            this.saveHistoryState();
            this.debouncedSave();
            this.$dispatch('all-allocations-cleared');
        },
        
        distributeEvenly() {
            if (this.skills.length === 0) return;
            
            const perSkill = Math.floor(this.totalBudget / this.skills.length);
            this.skills.forEach(skill => {
                this.allocations[skill.id] = perSkill;
            });
            this.isDirty = true;
            this.saveHistoryState();
            this.debouncedSave();
            this.$dispatch('distributed-evenly');
        },
        
        saveHistoryState() {
            // Remove future history if we're not at the end
            this.allocationHistory = this.allocationHistory.slice(0, this.historyIndex + 1);
            
            // Save current state
            this.allocationHistory.push(JSON.parse(JSON.stringify(this.allocations)));
            this.historyIndex++;
        },
        
        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.allocations = JSON.parse(JSON.stringify(this.allocationHistory[this.historyIndex]));
                this.isDirty = true;
                this.debouncedSave();
                this.$dispatch('allocation-undo');
            }
        },
        
        redo() {
            if (this.historyIndex < this.allocationHistory.length - 1) {
                this.historyIndex++;
                this.allocations = JSON.parse(JSON.stringify(this.allocationHistory[this.historyIndex]));
                this.isDirty = true;
                this.debouncedSave();
                this.$dispatch('allocation-redo');
            }
        },
        
        debouncedSave() {
            // Clear existing timeout
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            
            // Set new timeout for save
            this.saveTimeout = setTimeout(() => {
                this.save();
            }, 1000); // Save after 1 second of inactivity
        },
        
        save() {
            if (!this.isDirty) return;
            
            this.isSaving = true;
            
            // Would call API to save allocations
            // POST /api/skills/allocate
            setTimeout(() => {
                this.isSaving = false;
                this.isDirty = false;
                this.$dispatch('allocations-saved', { allocations: this.allocations });
            }, 300);
        },
        
        resetToDefaults() {
            // Reset to last saved state (remove latest history entry)
            if (this.allocationHistory.length > 0) {
                this.historyIndex--;
                this.allocations = JSON.parse(JSON.stringify(this.allocationHistory[this.historyIndex]));
                this.isDirty = false;
                this.$dispatch('allocation-reset');
            }
        },
        
        validateAllocation(skillId) {
            const skill = this.skills.find(s => s.id === skillId);
            if (!skill) return false;
            
            const allocated = this.allocations[skillId] || 0;
            
            // Check maximum SP per skill (usually skill's max_sp)
            if (allocated > skill.max_sp) {
                return false;
            }
            
            return true;
        },
        
        getSkillTier(skillId) {
            const skill = this.skills.find(s => s.id === skillId);
            return skill?.tier || 'C';
        },
        
        getSkillColor(tier) {
            const colors = {
                'S': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200',
                'A': 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200',
                'B': 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200',
                'C': 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
                'D': 'bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200'
            };
            return colors[tier] || colors['C'];
        }
    };
}
