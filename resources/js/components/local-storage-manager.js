/**
 * localStorageManager - Alpine.js component for plan persistence in local mode
 * 
 * Purpose: Handle draft plans, autosave, and local storage management
 * 
 * Features:
 *   - Draft plan autosave with debounce
 *   - Plan retrieval by UUID
 *   - Draft clearing after successful save
 *   - Conflict detection and resolution
 *   - Quota management
 *   - Data versioning
 * 
 * Usage:
 *   <div x-data="localStorageManager()">
 *       <button @click="autosavePlan()">Save Draft</button>
 *   </div>
 */

export function localStorageManager() {
    return {
        // State
        draftPlan: null,
        lastSaveTime: null,
        saveTimeout: null,
        storageQuota: 5242880, // 5MB
        schemaVersion: 1,
        
        // Initialization
        init() {
            // Load draft plan from localStorage on init
            this.loadDraft();
            
            // Setup periodic quota check
            setInterval(() => this.checkQuota(), 60000); // Every 60 seconds
        },
        
        // Save plan with auto-debouncing
        autosavePlan(planData, debounceMs = 2000) {
            // Clear existing timeout
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            
            // Set new debounced save
            this.saveTimeout = setTimeout(() => {
                this.savePlan(planData);
            }, debounceMs);
        },
        
        // Save plan to localStorage
        savePlan(planData) {
            try {
                const planKey = `draft_plan_${planData.uuid}`;
                const wrappedData = {
                    uuid: planData.uuid,
                    data: planData,
                    savedAt: new Date().toISOString(),
                    schemaVersion: this.schemaVersion,
                    checksum: this.calculateChecksum(planData),
                };
                
                localStorage.setItem(planKey, JSON.stringify(wrappedData));
                this.lastSaveTime = new Date().toISOString();
                
                this.$dispatch('plan-saved', { uuid: planData.uuid, time: this.lastSaveTime });
                console.log(`Plan ${planData.uuid} saved to localStorage`);
                
                return true;
            } catch (error) {
                if (error.name === 'QuotaExceededError') {
                    console.error('localStorage quota exceeded');
                    this.$dispatch('storage-quota-exceeded');
                    this.handleQuotaExceeded();
                } else {
                    console.error('Failed to save plan:', error);
                }
                return false;
            }
        },
        
        // Load draft plan by UUID
        loadDraft(uuid) {
            try {
                const planKey = `draft_plan_${uuid}`;
                const stored = localStorage.getItem(planKey);
                
                if (stored) {
                    const wrapped = JSON.parse(stored);
                    
                    // Verify checksum
                    if (this.calculateChecksum(wrapped.data) !== wrapped.checksum) {
                        console.warn('Plan data checksum mismatch - data may be corrupted');
                        this.$dispatch('plan-corrupted', { uuid });
                        return null;
                    }
                    
                    // Check schema version
                    if (wrapped.schemaVersion < this.schemaVersion) {
                        console.log('Migrating plan to new schema version');
                        wrapped.data = this.migratePlan(wrapped.data, wrapped.schemaVersion);
                    }
                    
                    this.draftPlan = wrapped.data;
                    return wrapped.data;
                }
                
                return null;
            } catch (error) {
                console.error('Failed to load plan:', error);
                this.$dispatch('plan-load-error', { uuid });
                return null;
            }
        },
        
        // Clear draft after successful save
        clearDraft(uuid) {
            try {
                const planKey = `draft_plan_${uuid}`;
                localStorage.removeItem(planKey);
                this.draftPlan = null;
                this.$dispatch('draft-cleared', { uuid });
                return true;
            } catch (error) {
                console.error('Failed to clear draft:', error);
                return false;
            }
        },
        
        // List all draft plans
        listDrafts() {
            try {
                const drafts = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key.startsWith('draft_plan_')) {
                        const stored = localStorage.getItem(key);
                        const wrapped = JSON.parse(stored);
                        drafts.push({
                            uuid: wrapped.uuid,
                            characterName: wrapped.data.characterName,
                            savedAt: wrapped.savedAt,
                            turns: wrapped.data.turns?.length || 0,
                        });
                    }
                }
                return drafts.sort((a, b) => 
                    new Date(b.savedAt) - new Date(a.savedAt)
                );
            } catch (error) {
                console.error('Failed to list drafts:', error);
                return [];
            }
        },
        
        // Check for conflicting drafts
        detectConflicts(uuid) {
            try {
                const planKey = `draft_plan_${uuid}`;
                const conflict_key = `conflict_${uuid}`;
                const serverVersion = sessionStorage.getItem(conflict_key);
                
                if (!serverVersion) return null;
                
                const localVersion = localStorage.getItem(planKey);
                if (!localVersion) return null;
                
                const local = JSON.parse(localVersion);
                const server = JSON.parse(serverVersion);
                
                // Compare timestamps
                if (new Date(local.savedAt) > new Date(server.savedAt)) {
                    return {
                        type: 'LOCAL_NEWER',
                        localTime: local.savedAt,
                        serverTime: server.savedAt,
                        message: 'You have unsaved local changes newer than server version',
                    };
                }
                
                return null;
            } catch (error) {
                console.error('Failed to detect conflicts:', error);
                return null;
            }
        },
        
        // Resolve conflicts
        resolveConflict(uuid, strategy = 'KEEP_LOCAL') {
            try {
                const conflict = this.detectConflicts(uuid);
                if (!conflict) return true;
                
                if (strategy === 'KEEP_LOCAL') {
                    // Keep local version, sync to server later
                    sessionStorage.removeItem(`conflict_${uuid}`);
                    this.$dispatch('conflict-resolved', { uuid, strategy });
                    return true;
                } else if (strategy === 'KEEP_SERVER') {
                    // Discard local changes
                    this.clearDraft(uuid);
                    sessionStorage.removeItem(`conflict_${uuid}`);
                    this.$dispatch('conflict-resolved', { uuid, strategy });
                    return true;
                }
                
                return false;
            } catch (error) {
                console.error('Failed to resolve conflict:', error);
                return false;
            }
        },
        
        // Check quota usage
        checkQuota() {
            try {
                const totalSize = this.calculateTotalSize();
                const usage = (totalSize / this.storageQuota) * 100;
                
                this.$dispatch('quota-checked', { usage, bytes: totalSize });
                
                if (usage > 90) {
                    console.warn(`localStorage quota ${usage.toFixed(1)}% full`);
                    this.$dispatch('quota-warning', { usage });
                }
                
                return usage;
            } catch (error) {
                console.error('Failed to check quota:', error);
                return 0;
            }
        },
        
        // Handle quota exceeded
        handleQuotaExceeded() {
            try {
                // Delete oldest draft plans
                const drafts = this.listDrafts();
                if (drafts.length > 1) {
                    const oldest = drafts[drafts.length - 1];
                    this.clearDraft(oldest.uuid);
                    console.log(`Cleared oldest draft (${oldest.uuid}) to free space`);
                    this.$dispatch('draft-cleared-for-quota', { uuid: oldest.uuid });
                }
            } catch (error) {
                console.error('Failed to handle quota exceeded:', error);
            }
        },
        
        // Calculate total storage size
        calculateTotalSize() {
            let total = 0;
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                total += key.length + value.length;
            }
            return total * 2; // Approximate bytes
        },
        
        // Calculate data checksum
        calculateChecksum(data) {
            const str = JSON.stringify(data);
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash).toString(16);
        },
        
        // Migrate plan to new schema version
        migratePlan(data, fromVersion) {
            if (fromVersion < 1) {
                // Migration from v0 to v1
                if (!data.schemaVersion) {
                    data.schemaVersion = 1;
                }
                if (!data.turns) {
                    data.turns = [];
                }
            }
            return data;
        },
        
        // Export plan to JSON file
        exportPlanAsJSON(uuid, filename = null) {
            try {
                const plan = this.loadDraft(uuid);
                if (!plan) {
                    console.error('Plan not found');
                    return false;
                }
                
                const filename_actual = filename || `${plan.characterName}_${uuid}.json`;
                const dataStr = JSON.stringify(plan, null, 2);
                const dataBlob = new Blob([dataStr], { type: 'application/json' });
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename_actual;
                link.click();
                
                this.$dispatch('plan-exported', { uuid, filename: filename_actual });
                return true;
            } catch (error) {
                console.error('Failed to export plan:', error);
                return false;
            }
        },
    };
}
