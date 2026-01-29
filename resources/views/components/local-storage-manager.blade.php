{{-- Phase 6: Local Storage Manager Component --}}
<div
    x-data="localStorageManager()"
    x-init="init()"
    @plan-saved="$dispatch('toast-show', {message: 'Plan saved to draft', type: 'success'})"
    @draft-cleared="$dispatch('toast-show', {message: 'Draft cleared', type: 'info'})"
    @storage-quota-exceeded="$dispatch('toast-show', {message: 'Storage limit exceeded - cleaning up old drafts', type: 'warning'})"
    @quota-warning="$dispatch('toast-show', {message: 'Storage usage at ' + checkQuota() + '%', type: 'warning'})"
    @conflict-resolved="$dispatch('toast-show', {message: 'Conflict resolved using selected strategy', type: 'success'})"
>
    <!-- 
        Hidden component - handles plan persistence automatically.
        Use via Alpine events:
        
        // Auto-save a plan
        Alpine.store('planManager').autosavePlan(planData);
        
        // Load a draft
        const draft = Alpine.store('planManager').loadDraft(uuid);
        
        // Check storage usage
        const usage = Alpine.store('planManager').checkQuota();
        
        // Clear a draft
        Alpine.store('planManager').clearDraft(uuid);
        
        // Export for backup
        Alpine.store('planManager').exportPlanAsJSON(uuid, filename);
    -->

    <!-- Storage Status Display (Optional Debug) -->
    <template x-if="false">
        <div class="text-xs text-gray-500 dark:text-gray-400 p-2 bg-gray-100 dark:bg-gray-900 rounded">
            <div>Last Save: <span x-text="lastSaveTime ? new Date(lastSaveTime).toLocaleTimeString() : 'Never'"></span></div>
            <div>Storage: <span x-text="checkQuota() + '%'"></span></div>
            <div>Drafts: <span x-text="listDrafts().length"></span></div>
        </div>
    </template>
</div>
