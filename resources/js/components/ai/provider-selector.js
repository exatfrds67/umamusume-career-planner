export default () => ({
    provider: "",
    model: "",
    status: "connecting",
    autoFallback: true,
    models: { ollama: {}, bedrock: {} },
    open: false,

    async initialize() {
        // Load saved preferences first so the selector reflects the current choice
        await this.loadSavedPreferences();
        this.checkStatus();
        this.fetchModels();
        setInterval(() => this.checkStatus(), 5000);
    },

    async loadSavedPreferences() {
        try {
            const response = await fetch("/api/ai/chat/preferences");
            const data = await response.json();
            if (data.success && data.preferences) {
                this.provider = data.preferences.provider || "ollama";
                this.model = data.preferences.model || "llama3";
            }
        } catch (e) {
            // Silently fall back to defaults
        }
    },

    async fetchModels() {
        try {
            const response = await fetch("/api/ai/chat/models");
            const data = await response.json();
            if (data.success) {
                this.models = data.models;
                const ollamaKeys = Object.keys(this.models.ollama ?? {});
                if (
                    ollamaKeys.length > 0 &&
                    !ollamaKeys.includes(this.model) &&
                    this.provider === "ollama"
                ) {
                    this.model = data.defaults.ollama;
                }
            }
        } catch (e) {
            console.error("Failed to fetch models", e);
        }
    },

    async checkStatus() {
        try {
            const response = await fetch("/api/ai/chat/server-status");
            const data = await response.json();

            const servers = data.data?.servers ?? data.servers;
            if (servers && servers[this.provider]) {
                this.status = servers[this.provider].status;
            }
        } catch (error) {
            console.error("Failed to check provider status:", error);
            this.status = "offline";
        }
    },

    async selectProvider(provider, model) {
        this.provider = provider;
        this.model = model;
        this.open = false;

        window.dispatchEvent(
            new CustomEvent("ai-provider-changed", {
                detail: { provider: this.provider, model: this.model },
            }),
        );

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
                    provider: provider,
                    model: model,
                }),
            });
        } catch (error) {
            console.error("Failed to save provider preference:", error);
        }

        this.checkStatus();
    },

    async updateAutoFallback() {
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
                    auto_fallback: this.autoFallback,
                }),
            });
        } catch (error) {
            console.error("Failed to save auto-fallback preference:", error);
        }
    },

    getProviderLabel() {
        const labels = {
            ollama: "Ollama",
            bedrock: "Bedrock",
            agent: "Agent",
        };
        return labels[this.provider] || this.provider;
    },
});
