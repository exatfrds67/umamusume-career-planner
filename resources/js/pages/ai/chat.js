/**
 * AI Chat Page Script
 * Handles quick message sending functionality
 */

// Import the aiChatInterface Alpine component definition
import "../../ai-chat.js";

// Import AI sub-components needed on the chat page
import "../../components/ai/tool-usage-indicator.js";

/**
 * Send a quick message to the chat interface
 * @param {string} message - The message to send
 */
function sendQuickMessage(message) {
    // Dispatch event to chat interface component
    window.dispatchEvent(
        new CustomEvent("send-quick-message", {
            detail: {
                message: message,
            },
        }),
    );
}

// Expose function globally for button onclick handlers
window.sendQuickMessage = sendQuickMessage;
