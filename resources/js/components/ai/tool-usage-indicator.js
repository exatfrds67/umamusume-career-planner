/**
 * Tool Usage Indicator Component
 * Shows active tool executions in a compact indicator
 */

export default () => ({
    activeTools: [],

    initialize() {
        this.fetchToolUsage();
        setInterval(() => this.fetchToolUsage(), 1000);
    },

    async fetchToolUsage() {
        try {
            const response = await fetch("/api/ai/chat/tool-usage");
            const responseData = await response.json();
            
            // Normalize payload to handle `{success: true, data: {...}}` envelope
            const data = responseData.success !== undefined ? responseData.data : responseData;

            if (data && data.active_tools) {
                this.activeTools = data.active_tools.map((tool, index) => ({
                    id: tool.id || index,
                    name: tool.tool_name || tool.name,
                    status: tool.status || 'unknown',
                    duration: tool.execution_time !== undefined ? parseInt(tool.execution_time, 10) : tool.duration
                }));
            } else if (data && data.tools) {
                this.activeTools = data.tools;
            }
        } catch (error) {
            console.error("Failed to fetch tool usage:", error);
        }
    },
});
