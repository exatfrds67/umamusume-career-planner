export default () => ({
    selectedAgent: null,

    initialize() {
        this.loadPreference();
    },

    async loadPreference() {
        try {
            const response = await fetch("/api/ai/chat/preferences");
            const responseData = await response.json();
            
            // Normalize payload to handle `{success: true, data: {...}}` envelope
            const data = responseData.success !== undefined ? responseData.data : responseData;
            
            if (data && data.selected_agent) {
                this.selectedAgent = data.selected_agent;
            }
        } catch (error) {
            console.error("Failed to load agent preference:", error);
        }
    },

    async selectAgent(agent) {
        this.selectedAgent = agent;

        try {
            await fetch("/api/ai/chat/preferences", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: JSON.stringify({
                    selected_agent: agent,
                }),
            });

            window.dispatchEvent(
                new CustomEvent("agent-changed", {
                    detail: {
                        agent: agent,
                    },
                }),
            );
        } catch (error) {
            console.error("Failed to save agent preference:", error);
        }
    },
});
