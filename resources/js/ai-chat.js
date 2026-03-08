/**
 * AI Chat Interface Alpine.js Component
 */

import DOMPurify from "dompurify";

const aiChatInterface = function (config) {
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
        copiedMessageId: null,
        streamAbortController: null,
        showKeyboardShortcuts: false,

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
            this.conversationId = this.restoreOrCreateConversationId();
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
                this.streamAbortController = new AbortController();

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
                    signal: this.streamAbortController.signal,
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
                                const storageKey = `ai_conversation_id_${this.characterId || "general"}`;
                                sessionStorage.setItem(storageKey, data.conversation_id);
                            }

                            if (data.done) {
                                this.markMessageStreamingComplete(aiMessageId);
                            }
                        }
                    }
                }

                this.streamAbortController = null;
                return true;
            } catch (error) {
                this.streamAbortController = null;
                if (error.name === "AbortError") {
                    return true;
                }
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
                    const storageKey = `ai_conversation_id_${this.characterId || "general"}`;
                    sessionStorage.setItem(storageKey, data.conversation_id);
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
                    '<h3 class="text-lg font-bold mt-3 mb-1 text-neutral-800 dark:text-neutral-100">$1</h3>',
                )
                .replace(
                    /^### (.*$)/gm,
                    '<h4 class="text-md font-bold mt-2 mb-1 text-neutral-800 dark:text-neutral-100">$1</h4>',
                )
                // Bold (**text**)
                .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
                // Italic (*text*)
                .replace(/\*(.*?)\*/g, "<em>$1</em>")
                // Code blocks (```code```)
                .replace(
                    /```([\s\S]*?)```/g,
                    '<pre class="bg-neutral-100 dark:bg-neutral-800 p-2 rounded my-2 overflow-x-auto text-sm font-mono"><code>$1</code></pre>',
                )
                // Inline code (`code`)
                .replace(
                    /`([^`]+)`/g,
                    '<code class="bg-neutral-100 dark:bg-neutral-800 px-1 py-0.5 rounded text-sm font-mono text-primary-600 dark:text-primary-400">$1</code>',
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
                // Line breaks
                .replace(/\n/g, "<br>");

            return DOMPurify.sanitize(formatted, {
                ALLOWED_TAGS: [
                    "h3",
                    "h4",
                    "strong",
                    "em",
                    "pre",
                    "code",
                    "li",
                    "ul",
                    "ol",
                    "br",
                    "p",
                    "span",
                    "div",
                ],
                ALLOWED_ATTR: ["class"],
            });
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

        isCopiedMessage(messageId) {
            return this.copiedMessageId === messageId;
        },

        openKeyboardShortcuts() {
            this.showKeyboardShortcuts = true;
        },

        closeKeyboardShortcuts() {
            this.showKeyboardShortcuts = false;
        },

        async copyPlainText(content) {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(content);

                    return true;
                }
            } catch (error) {
                console.warn("Clipboard API copy failed, falling back", error);
            }

            const textarea = document.createElement("textarea");
            textarea.value = content;
            textarea.setAttribute("readonly", "readonly");
            textarea.setAttribute("aria-hidden", "true");
            textarea.style.position = "fixed";
            textarea.style.opacity = "0";
            textarea.style.pointerEvents = "none";

            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);

            const copied = document.execCommand("copy");

            document.body.removeChild(textarea);

            return copied;
        },

        /**
         * Copy message to clipboard with visual feedback
         */
        async copyMessage(messageId, content) {
            try {
                const plainText = content.replace(/<[^>]*>/g, "");
                const copied = await this.copyPlainText(plainText);

                if (!copied) {
                    return;
                }

                this.copiedMessageId = messageId;
                setTimeout(() => {
                    this.copiedMessageId = null;
                }, 2000);
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
         * Show error message with contextual details
         */
        showError(message) {
            let errorTitle = "Error";
            let errorIcon = "⚠️";

            if (message.toLowerCase().includes("network") || message.toLowerCase().includes("connection")) {
                errorTitle = "Connection Error";
                errorIcon = "🔌";
            } else if (message.toLowerCase().includes("rate limit") || message.toLowerCase().includes("too many")) {
                errorTitle = "Rate Limited";
                errorIcon = "⏱️";
            } else if (message.toLowerCase().includes("model") || message.toLowerCase().includes("unavailable")) {
                errorTitle = "Model Unavailable";
                errorIcon = "🤖";
            } else if (message.toLowerCase().includes("unauthorized") || message.toLowerCase().includes("401")) {
                errorTitle = "Authentication Error";
                errorIcon = "🔒";
            }

            this.addMessage({
                id: Date.now(),
                sender: "system",
                content: `${errorIcon} **${errorTitle}**: ${message}`,
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
         * Restore conversation ID from sessionStorage or create a new one
         */
        restoreOrCreateConversationId() {
            const storageKey = `ai_conversation_id_${this.characterId || "general"}`;
            const existing = sessionStorage.getItem(storageKey);
            if (existing) {
                return existing;
            }
            const newId = this.generateConversationId();
            sessionStorage.setItem(storageKey, newId);
            return newId;
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

            // Keyboard shortcuts
            document.addEventListener("keydown", (event) => {
                // Ctrl+K: Focus message input
                if ((event.ctrlKey || event.metaKey) && event.key === "k") {
                    event.preventDefault();
                    const input = document.getElementById("message-input");
                    if (input) {
                        input.focus();
                    }
                }

                // Ctrl+/: Toggle keyboard shortcuts modal
                if ((event.ctrlKey || event.metaKey) && event.key === "/") {
                    event.preventDefault();
                    this.showKeyboardShortcuts = !this.showKeyboardShortcuts;
                }

                // Escape: Cancel streaming or close panels
                if (event.key === "Escape") {
                    if (this.isProcessing) {
                        this.cancelStreaming();
                    } else if (this.showKeyboardShortcuts) {
                        this.showKeyboardShortcuts = false;
                    } else if (this.showServerStatus || this.showWorkflow || this.showSettings) {
                        this.showServerStatus = false;
                        this.showWorkflow = false;
                        this.showSettings = false;
                    }
                }
            });
        },

        /**
         * Cancel an in-progress streaming response
         */
        cancelStreaming() {
            if (this.streamAbortController) {
                this.streamAbortController.abort();
                this.streamAbortController = null;
            }
            this.isProcessing = false;
            this.isTyping = false;

            // Mark last AI message as cancelled
            const lastAiMsg = [...this.messages].reverse().find(m => m.sender === "ai" && m.isStreaming);
            if (lastAiMsg) {
                lastAiMsg.isStreaming = false;
                lastAiMsg.content += "\n\n*[Response cancelled]*";
                this.messages = [...this.messages];
                this.saveToLocalStorage();
            }
        },

        /**
         * Regenerate the last AI response
         */
        async regenerateResponse() {
            // Find the last user message
            const lastUserMsg = [...this.messages].reverse().find(m => m.sender === "user");
            if (!lastUserMsg || this.isProcessing) {
                return;
            }

            // Remove the last AI message
            const lastAiIndex = this.messages.map(m => m.sender).lastIndexOf("ai");
            if (lastAiIndex !== -1) {
                this.messages.splice(lastAiIndex, 1);
                this.messages = [...this.messages];
                this.saveToLocalStorage();
            }

            // Re-send the last user message
            this.currentMessage = lastUserMsg.content;
            // Remove the last user message too (sendMessage will re-add it)
            const lastUserIndex = this.messages.map(m => m.sender).lastIndexOf("user");
            if (lastUserIndex !== -1) {
                this.messages.splice(lastUserIndex, 1);
                this.messages = [...this.messages];
            }

            await this.sendMessage();
        },

        /**
         * Start a new conversation (clear history) with confirmation
         */
        newConversation() {
            if (this.isProcessing) {
                return;
            }

            if (
                this.messages.length > 0 &&
                !window.confirm(
                    "Start a new conversation? Current messages will be cleared.",
                )
            ) {
                return;
            }

            this.messages = [];
            this.conversationId = this.generateConversationId();
            const storageKey = `ai_conversation_id_${this.characterId || "general"}`;
            sessionStorage.setItem(storageKey, this.conversationId);
            this.saveToLocalStorage();
        },

        /**
         * Delete a specific message by ID
         */
        deleteMessage(messageId) {
            const index = this.messages.findIndex((m) => m.id === messageId);
            if (index === -1) {
                return;
            }
            this.messages.splice(index, 1);
            this.messages = [...this.messages];
            this.saveToLocalStorage();
        },

        /**
         * Export conversation as Markdown text and copy to clipboard
         */
        exportConversation(format = "markdown") {
            if (this.messages.length === 0) {
                return;
            }

            let output = "";
            const timestamp = new Date().toISOString().split("T")[0];

            if (format === "markdown") {
                output = `# AI Chat Export (${timestamp})\n\n`;
                output += `**Model:** ${this.model} | **Provider:** ${this.provider}\n\n---\n\n`;

                for (const msg of this.messages) {
                    const sender =
                        msg.sender === "user"
                            ? "**You**"
                            : msg.sender === "ai"
                              ? "**AI Assistant**"
                              : "**System**";
                    const time = msg.timestamp
                        ? new Date(msg.timestamp).toLocaleString()
                        : "";
                    output += `${sender} ${time ? `_(${time})_` : ""}\n\n${msg.content}\n\n---\n\n`;
                }
            } else {
                output = JSON.stringify(
                    {
                        exported_at: new Date().toISOString(),
                        provider: this.provider,
                        model: this.model,
                        conversation_id: this.conversationId,
                        messages: this.messages,
                    },
                    null,
                    2,
                );
            }

            const blob = new Blob([output], {
                type:
                    format === "markdown"
                        ? "text/markdown"
                        : "application/json",
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `chat-export-${timestamp}.${format === "markdown" ? "md" : "json"}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
    };
};

// Keep window reference for any inline onclick handlers
window.aiChatInterface = aiChatInterface;

export default aiChatInterface;
