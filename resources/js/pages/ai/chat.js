/**
 * AI Chat Page Script
 * Handles quick message sending functionality
 */

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
