export default () => ({
    currentWorkflow: null,
    completedSteps: 0,
    totalDuration: 0,

    initialize() {
        this.fetchWorkflow();
        setInterval(() => this.fetchWorkflow(), 2000);
    },

    async fetchWorkflow() {
        try {
            const response = await fetch("/api/ai/chat/workflow-status");
            const responseData = await response.json();
            
            // Normalize payload to handle `{success: true, data: {...}}` envelope
            const data = responseData.success !== undefined ? responseData.data : responseData;

            // `data` in getWorkflowStatus IS the workflow object (contains workflow_id, status, agents)
            if (data && (data.workflow_id || data.status || data.workflow)) {
                this.currentWorkflow = data.workflow || data;
                this.calculateMetrics();
            } else {
                this.currentWorkflow = null;
            }
        } catch (error) {
            console.error("Failed to fetch workflow status:", error);
        }
    },

    calculateMetrics() {
        if (!this.currentWorkflow) {
            this.completedSteps = 0;
            this.totalDuration = 0;
            return;
        }
        
        const steps = this.currentWorkflow.agents || this.currentWorkflow.steps || [];

        this.completedSteps = steps.filter(
            (s) => s.status === "completed",
        ).length;
        this.totalDuration = steps
            .filter((s) => s.duration !== undefined || s.execution_time !== undefined)
            .reduce((sum, s) => sum + (s.duration === undefined ? (s.execution_time || 0) : s.duration), 0);
    },
});
