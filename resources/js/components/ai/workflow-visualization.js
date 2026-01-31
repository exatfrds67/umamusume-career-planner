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
            const data = await response.json();

            if (data.workflow) {
                this.currentWorkflow = data.workflow;
                this.calculateMetrics();
            } else {
                this.currentWorkflow = null;
            }
        } catch (error) {
            console.error("Failed to fetch workflow status:", error);
        }
    },

    calculateMetrics() {
        if (!this.currentWorkflow || !this.currentWorkflow.steps) {
            this.completedSteps = 0;
            this.totalDuration = 0;
            return;
        }

        this.completedSteps = this.currentWorkflow.steps.filter(
            (s) => s.status === "completed",
        ).length;
        this.totalDuration = this.currentWorkflow.steps
            .filter((s) => s.duration)
            .reduce((sum, s) => sum + s.duration, 0);
    },
});
