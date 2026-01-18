@props([
    'characterId' => null,
    'careerId' => null,
])

<div class="ai-chat-interface flex flex-col h-full bg-white dark:bg-gray-900 rounded-lg shadow-lg" x-data="aiChatInterface({
    characterId: {{ $characterId ?? 'null' }},
    careerId: {{ $careerId ?? 'null' }}
})"
    x-init="initialize()" role="region" aria-labelledby="chat-title">

    {{-- Chat Header --}}
    <div
        class="chat-header flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-linear-to-r from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div>
                <h2 id="chat-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                    AI Career Assistant
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <x-ai.provider-selector />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- MCP Server Status --}}
            <button @click="showServerStatus = !showServerStatus"
                class="p-2 hover:bg-white/50 dark:hover:bg-gray-600 rounded-lg transition-colors"
                aria-label="Toggle server status">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
            </button>

            {{-- Workflow Visualization Toggle --}}
            <button @click="showWorkflow = !showWorkflow"
                class="p-2 hover:bg-white/50 dark:hover:bg-gray-600 rounded-lg transition-colors"
                aria-label="Toggle workflow visualization">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </button>

            {{-- Settings --}}
            <button @click="showSettings = !showSettings"
                class="p-2 hover:bg-white/50 dark:hover:bg-gray-600 rounded-lg transition-colors"
                aria-label="Chat settings">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Messages Area --}}
        <div class="flex-1 flex flex-col">
            {{-- Messages Container --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4" x-ref="messagesContainer" role="log"
                aria-live="polite" aria-label="Chat messages">

                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center p-8">
                        <div
                            class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Welcome to AI Career Assistant
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 max-w-md">
                            Ask me anything about training strategies, character optimization, race preparation, or
                            skill builds. I'm here to help you achieve A-grade rankings!
                        </p>
                    </div>
                </template>

                <template x-for="message in messages" :key="message.id">
                    <x-ai.message-bubble :message="message" />
                </template>

                {{-- Typing Indicator --}}
                <div x-show="isTyping" class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-3">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tool Usage Indicators --}}
            <div x-show="activeTools.length > 0"
                class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-800">
                <x-ai.tool-usage-indicator />
            </div>

            {{-- Input Area --}}
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                    <div class="flex-1">
                        <label for="message-input" class="sr-only">Type your message</label>
                        <textarea id="message-input" x-model="currentMessage" @keydown.enter.prevent="handleEnterKey($event)"
                            placeholder="Ask about training strategies, character optimization, or any career planning questions..."
                            class="w-full resize-none border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            rows="3" maxlength="2000" :disabled="isProcessing" aria-describedby="input-help"></textarea>
                        <div id="input-help"
                            class="mt-1 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Press Enter to send, Shift+Enter for new line</span>
                            <span x-text="`${currentMessage.length}/2000`"></span>
                        </div>
                    </div>

                    <button type="submit" :disabled="!currentMessage.trim() || isProcessing"
                        class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        aria-label="Send message">
                        <svg x-show="!isProcessing" class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <svg x-show="isProcessing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Sidebar Panels --}}
        <div class="w-80 border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-y-auto"
            x-show="showServerStatus || showWorkflow || showSettings"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0">

            {{-- Server Status Panel --}}
            <div x-show="showServerStatus" class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">MCP Server Status</h3>
                <x-ai.server-status-indicator />
            </div>

            {{-- Workflow Visualization Panel --}}
            <div x-show="showWorkflow" class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Agent Workflow</h3>
                <x-ai.workflow-visualization />
            </div>

            {{-- Settings Panel --}}
            <div x-show="showSettings" class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Chat Settings</h3>
                <x-ai.agent-selector />
            </div>
        </div>
    </div>
</div>
