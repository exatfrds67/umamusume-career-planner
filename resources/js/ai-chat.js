/**
 * AI Chat Interface Alpine.js Component
 */

window.aiChatInterface = function (config) {
    return {
        // State
        messages: [],
        currentMessage: "",
        isProcessing: false,
        isTyping: false,
        showServerStatus: false,
        showWorkflow: false,
        showSettings: false,
        activeTools: [],

        // Configuration
        characterId: config.characterId || null,
        careerId: config.careerId || null,
        conversationId: null,

        // Provider state
        provider: "ollama",
        model: "llama3.3",
        status: "connecting",
        selectedAgent: null,

        /**
         * Initialize the chat interface
         */
        initialize() {
            this.conversationId = this.generateConversationId();
            this.loadPreferences();
            this.loadConversationHistory();
            this.setupEventListeners();

            // Check for initial message in URL
            const urlParams = new URLSearchParams(window.location.search);
            const initialMessage = urlParams.get("initial_message");

            if (initialMessage) {
                this.currentMessage = initialMessage;
                // Clean URL
                const url = new URL(window.location);
                url.searchParams.delete("initial_message");
                window.history.replaceState({}, "", url);

                // Auto-send after short delay to ensure init
                setTimeout(() => {
                    this.sendMessage();
                }, 500);
            }

            // Auto-scroll to bottom on new messages
            this.$watch("messages", () => {
                this.$nextTick(() => this.scrollToBottom());
            });
        },

        /**
         * Send a message to the AI
         */
        async sendMessage() {
            if (!this.currentMessage.trim() || this.isProcessing) {
                return;
            }

            const userMessage = this.currentMessage.trim();
            this.currentMessage = "";

            // Add user message to chat
            this.addMessage({
                id: Date.now(),
                sender: "user",
                content: userMessage,
                timestamp: new Date().toISOString(),
            });

            this.isProcessing = true;
            this.isTyping = true;

            const aiMessageId = Date.now() + 1;
            this.addMessage({
                id: aiMessageId,
                sender: "ai",
                content: "",
                timestamp: new Date().toISOString(),
                metadata: {},
                isStreaming: true,
            });

            try {
                const streamed = await this.sendMessageStreaming(
                    userMessage,
                    aiMessageId,
                );

                if (!streamed) {
                    await this.sendMessageStandard(userMessage, aiMessageId);
                }
            } catch (error) {
                console.error("Failed to send message:", error);
                this.showError(
                    "Network error. Please check your connection and try again.",
                );
            } finally {
                this.isProcessing = false;
                this.isTyping = false;
            }
        },

        /**
         * Send a message using streaming SSE endpoint
         */
        async sendMessageStreaming(message, aiMessageId) {
            try {
                const response = await fetch("/api/ai/chat/message/stream", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "text/event-stream",
                    },
                    body: JSON.stringify({
                        message: message,
                        character_id: this.characterId,
                        career_id: this.careerId,
                        provider: this.provider,
                        conversation_id: this.conversationId,
                        model: this.model,
                        agent_type: this.selectedAgent,
                    }),
                });

                if (!response.ok || !response.body) {
                    return false;
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder("utf-8");
                let buffer = "";

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const parts = buffer.split("\n\n");
                    buffer = parts.pop() || "";

                    for (const part of parts) {
                        const lines = part
                            .split("\n")
                            .map((line) => line.trim())
                            .filter(Boolean);

                        for (const line of lines) {
                            if (!line.startsWith("data:")) continue;
                            const payload = line.replace(/^data:\s*/, "");
                            if (!payload) continue;

                            let data;
                            try {
                                data = JSON.parse(payload);
                            } catch (e) {
                                continue;
                            }

                            if (data.error) {
                                this.showError(data.error);
                                return true;
                            }

                            if (data.chunk !== undefined) {
                                this.appendToMessage(aiMessageId, data.chunk);
                            }

                            if (data.metadata) {
                                this.updateMessageMetadata(
                                    aiMessageId,
                                    data.metadata,
                                );
                            }

                            if (data.conversation_id) {
                                this.conversationId = data.conversation_id;
                            }

                            if (data.done) {
                                this.markMessageStreamingComplete(aiMessageId);
                            }
                        }
                    }
                }

                return true;
            } catch (error) {
                console.error("Streaming failed:", error);
                return false;
            }
        },

        /**
         * Send a message using the standard JSON endpoint
         */
        async sendMessageStandard(message, aiMessageId) {
            const response = await fetch("/api/ai/chat/message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    message: message,
                    character_id: this.characterId,
                    career_id: this.careerId,
                    provider: this.provider,
                    conversation_id: this.conversationId,
                    model: this.model,
                    agent_type: this.selectedAgent,
                }),
            });

            const data = await response.json();

            if (data.success) {
                this.replaceMessageContent(aiMessageId, data.message);
                this.updateMessageMetadata(aiMessageId, data.metadata);

                if (data.conversation_id) {
                    this.conversationId = data.conversation_id;
                }
            } else {
                this.showError(
                    data.error || data.message || "Failed to get AI response",
                );
            }

            this.markMessageStreamingComplete(aiMessageId);
        },

        appendToMessage(messageId, chunk) {
            const message = this.messages.find((m) => m.id === messageId);
            if (!message) return;
            message.content += chunk;
            this.messages = [...this.messages];
        },

        replaceMessageContent(messageId, content) {
            const message = this.messages.find((m) => m.id === messageId);
            if (!message) return;
            message.content = content;
            this.messages = [...this.messages];
        },

        updateMessageMetadata(messageId, metadata) {
            const message = this.messages.find((m) => m.id === messageId);
            if (!message) return;
            message.metadata = { ...message.metadata, ...metadata };
            this.messages = [...this.messages];
        },

        markMessageStreamingComplete(messageId) {
            const message = this.messages.find((m) => m.id === messageId);
            if (!message) return;
            message.isStreaming = false;
            this.messages = [...this.messages];
        },

        /**
         * Handle Enter key press
         */
        handleEnterKey(event) {
            if (event.shiftKey) {
                // Shift+Enter: new line (default behavior)
                return;
            }

            // Enter: send message
            event.preventDefault();
            this.sendMessage();
        },

        /**
         * Add a message to the chat
         */
        addMessage(message) {
            this.messages.push(message);
            this.saveToLocalStorage();
        },

        /**
         * Format message content (markdown-like)
         */
        formatMessage(content) {
            if (!content) return "";

            // Basic markdown formatting
            let formatted = content
                // Headers (## Header)
                .replace(
                    /^## (.*$)/gm,
                    '<h3 class="text-lg font-bold mt-3 mb-1 text-gray-800 dark:text-gray-100">$1</h3>',
                )
                .replace(
                    /^### (.*$)/gm,
                    '<h4 class="text-md font-bold mt-2 mb-1 text-gray-800 dark:text-gray-100">$1</h4>',
                )
                // Bold (**text**)
                .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
                // Italic (*text*)
                .replace(/\*(.*?)\*/g, "<em>$1</em>")
                // Code blocks (```code```)
                .replace(
                    /```([\s\S]*?)```/g,
                    '<pre class="bg-gray-100 dark:bg-gray-800 p-2 rounded my-2 overflow-x-auto text-sm font-mono"><code>$1</code></pre>',
                )
                // Inline code (`code`)
                .replace(
                    /`([^`]+)`/g,
                    '<code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-sm font-mono text-primary-600 dark:text-primary-400">$1</code>',
                )
                // Unordered Lists (- item)
                .replace(
                    /^\s*-\s+(.*$)/gm,
                    '<li class="ml-4 list-disc">$1</li>',
                )
                // Numbered Lists (1. item)
                .replace(
                    /^\s*\d+\.\s+(.*$)/gm,
                    '<li class="ml-4 list-decimal">$1</li>',
                )
                // Wrap lists in ul/ol (simplified approach: just treating them as lines for now, but CSS classes help)
                // Line breaks
                .replace(/\n/g, "<br>");

            return formatted;
        },

        /**
         * Format timestamp
         */
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return "Just now";
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;

            return date.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        /**
         * Copy message to clipboard
         */
        async copyMessage(content) {
            try {
                // Strip HTML tags for plain text copy
                const plainText = content.replace(/<[^>]*>/g, "");
                await navigator.clipboard.writeText(plainText);

                // Show success feedback (could be a toast notification)
                console.log("Message copied to clipboard");
            } catch (error) {
                console.error("Failed to copy message:", error);
            }
        },

        /**
         * Rate a message
         */
        async rateMessage(messageId, rating) {
            const message = this.messages.find((m) => m.id === messageId);
            if (!message) return;

            message.rating = rating;

            try {
                await fetch("/api/ai/chat/rate", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                    },
                    body: JSON.stringify({
                        message_id: messageId,
                        rating: rating,
                    }),
                });
            } catch (error) {
                console.error("Failed to rate message:", error);
            }
        },

        /**
         * Scroll to bottom of messages
         */
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        /**
         * Show error message
         */
        showError(message) {
            this.addMessage({
                id: Date.now(),
                sender: "system",
                content: `⚠️ ${message}`,
                timestamp: new Date().toISOString(),
                isError: true,
            });
        },

        /**
         * Generate conversation ID
         */
        generateConversationId() {
            return `conv_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        },

        /**
         * Load user preferences
         */
        async loadPreferences() {
            try {
                const response = await fetch("/api/ai/chat/preferences");
                const data = await response.json();

                if (data.success && data.preferences) {
                    this.provider = data.preferences.provider || "ollama";
                    this.model = data.preferences.model || "llama3.3";
                    this.selectedAgent = data.preferences.selected_agent || null;
                }
            } catch (error) {
                console.error("Failed to load preferences:", error);
            }
        },

        /**
         * Load conversation history from localStorage
         */
        loadConversationHistory() {
            const key = `chat_history_${this.characterId || "general"}`;
            const stored = localStorage.getItem(key);

            if (stored) {
                try {
                    this.messages = JSON.parse(stored);
                } catch (error) {
                    console.error("Failed to parse stored messages:", error);
                }
            }
        },

        /**
         * Save conversation to localStorage
         */
        saveToLocalStorage() {
            const key = `chat_history_${this.characterId || "general"}`;
            try {
                localStorage.setItem(key, JSON.stringify(this.messages));
            } catch (error) {
                console.error("Failed to save messages:", error);
            }
        },

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Listen for quick message events
            window.addEventListener("send-quick-message", (event) => {
                this.currentMessage = event.detail.message;
                this.sendMessage();
            });

            // Listen for agent change events
            window.addEventListener("agent-changed", (event) => {
                console.log("Agent changed to:", event.detail.agent);
                this.selectedAgent = event.detail.agent;
            });

            // Listen for provider/model change events from provider-selector
            window.addEventListener("ai-provider-changed", (event) => {
                console.log("Provider/Model changed:", event.detail);
                if (event.detail.provider)
                    this.provider = event.detail.provider;
                if (event.detail.model) this.model = event.detail.model;
            });
        },
    };
};
