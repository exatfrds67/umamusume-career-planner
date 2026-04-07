@props([
    'characterId' => null,
    'careerId' => null,
])

<div {{ $attributes->merge(['class' => 'ai-chat-interface flex flex-col h-full bg-white dark:bg-neutral-900 rounded-lg shadow-lg']) }} x-data="aiChatInterface({
    characterId: {{ $characterId ?? 'null' }},
    careerId: {{ $careerId ?? 'null' }}
})"
    x-init="initialize()" role="region" aria-labelledby="chat-title">

    {{-- Chat Header --}}
    <div
        class="chat-header flex items-center justify-between p-4 border-b border-neutral-200 dark:border-neutral-700 bg-linear-to-r from-primary-50 to-primary-100 dark:from-neutral-800 dark:to-neutral-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div>
                <h2 id="chat-title" class="text-lg font-semibold text-neutral-900 dark:text-white">
                    AI Career Assistant
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <x-ai.provider-selector />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- New Conversation --}}
            <button @click="newConversation()"
                class="p-2 hover:bg-white/50 dark:hover:bg-neutral-600 rounded-lg transition-colors"
                aria-label="New conversation" title="New conversation"
                :disabled="isProcessing">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
            </button>

            {{-- Export Conversation --}}
            <button @click="exportConversation('markdown')"
                class="p-2 hover:bg-white/50 dark:hover:bg-neutral-600 rounded-lg transition-colors"
                aria-label="Export conversation" title="Export conversation"
                :disabled="messages.length === 0">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </button>

            {{-- MCP Server Status --}}
            <button @click="showServerStatus = !showServerStatus"
                class="p-2 hover:bg-white/50 dark:hover:bg-neutral-600 rounded-lg transition-colors"
                aria-label="Toggle server status">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
            </button>

            {{-- Workflow Visualization Toggle --}}
            <button @click="showWorkflow = !showWorkflow"
                class="p-2 hover:bg-white/50 dark:hover:bg-neutral-600 rounded-lg transition-colors"
                aria-label="Toggle workflow visualization">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </button>

            {{-- Settings --}}
            <button @click="showSettings = !showSettings"
                class="p-2 hover:bg-white/50 dark:hover:bg-neutral-600 rounded-lg transition-colors"
                aria-label="Chat settings">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor"
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
                aria-live="polite" aria-label="Chat messages" :aria-busy="isProcessing">

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
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                            Welcome to AI Career Assistant
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-400 max-w-md mb-6">
                            Ask me anything about training strategies, character optimization, race preparation, or
                            skill builds. I'm here to help you reach S-rank aptitudes!
                        </p>

                        {{-- Conversation Starters --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-lg w-full">
                            <button @click="currentMessage = 'What training should I focus on for Speed?'; sendMessage()"
                                class="text-left p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="font-medium text-primary-600 dark:text-primary-400">🏃 Training</span>
                                <span class="block mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">What training should I focus on for Speed?</span>
                            </button>
                            <button @click="currentMessage = 'Analyze my character stats and suggest improvements'; sendMessage()"
                                class="text-left p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="font-medium text-primary-600 dark:text-primary-400">📊 Analysis</span>
                                <span class="block mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Analyze my character stats and suggest improvements</span>
                            </button>
                            <button @click="currentMessage = 'What skills should I prioritize for my build?'; sendMessage()"
                                class="text-left p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="font-medium text-primary-600 dark:text-primary-400">⭐ Skills</span>
                                <span class="block mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">What skills should I prioritize for my build?</span>
                            </button>
                            <button @click="currentMessage = 'Help me plan my upcoming race strategy'; sendMessage()"
                                class="text-left p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="font-medium text-primary-600 dark:text-primary-400">🏁 Race Strategy</span>
                                <span class="block mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Help me plan my upcoming race strategy</span>
                            </button>
                        </div>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex items-start gap-3 animate-fade-in-up"
                        :class="msg.sender === 'user' ? 'flex-row-reverse' : 'flex-row'" role="article"
                        :aria-label="`Message from ${msg.sender === 'user' ? 'you' : 'AI assistant'}`">

                        {{-- Avatar --}}
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 shadow-xs border border-white dark:border-neutral-800"
                            :class="{
                                'bg-neutral-300 dark:bg-neutral-600': msg.sender === 'user',
                                'bg-red-400': msg.sender === 'system' && msg.isError,
                                'bg-neutral-400': msg.sender === 'system' && !msg.isError,
                                'bg-primary-500': msg.sender === 'ai'
                            }">
                            <template x-if="msg.sender === 'user'">
                                <svg class="w-5 h-5 text-neutral-700 dark:text-neutral-200" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </template>
                            <template x-if="msg.sender === 'ai'">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </template>
                            <template x-if="msg.sender === 'system'">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.924-.833-2.694 0L4.07 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </template>
                        </div>

                        {{-- Message Content --}}
                        <div class="flex-1 max-w-4xl">
                            <div class="rounded-2xl px-4 py-3 shadow-xs"
                                :class="{
                                    'bg-primary-600 text-white rounded-tr-sm': msg.sender === 'user',
                                    'bg-red-50 dark:bg-red-900/20 text-red-900 dark:text-red-200 border border-red-200 dark:border-red-800': msg.sender === 'system' && msg.isError,
                                    'bg-neutral-50 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700': msg.sender === 'system' && !msg.isError,
                                    'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white border border-neutral-200 dark:border-neutral-700 rounded-tl-sm': msg.sender === 'ai'
                                }">

                                {{-- Message Header --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm"
                                            x-text="msg.sender === 'user' ? 'You' : (msg.sender === 'system' ? 'System' : 'AI Assistant')"></span>
                                        <template x-if="msg.sender === 'ai' && msg.metadata?.model">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300"
                                                x-text="msg.metadata.model"></span>
                                        </template>
                                        <template x-if="msg.sender === 'ai' && msg.metadata?.provider">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300"
                                                x-text="msg.metadata.provider"></span>
                                        </template>
                                        <template x-if="msg.sender === 'ai' && msg.metadata?.agent">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"
                                                x-text="msg.metadata.agent"></span>
                                        </template>
                                        <template x-if="msg.sender === 'ai' && msg.metadata?.rag_enhanced">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 flex items-center gap-1"
                                                title="Enhanced with game knowledge">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                Knowledge
                                            </span>
                                        </template>
                                    </div>
                                    <time class="text-xs opacity-75" :datetime="msg.timestamp"
                                        x-text="formatTime(msg.timestamp)"></time>
                                </div>

                                {{-- Message Text --}}
                                <div class="prose prose-sm max-w-none"
                                    :class="msg.sender === 'user' ? 'prose-invert' : 'dark:prose-invert'"
                                    x-html="formatMessage(msg.content)"></div>

                                {{-- AI Message Metadata --}}
                                <template x-if="msg.sender === 'ai' && msg.metadata">
                                    <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                        <div class="flex flex-wrap items-center gap-3 text-xs opacity-75">
                                            <template x-if="msg.metadata.processing_time">
                                                <span>
                                                    <svg class="w-3 h-3 inline mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span x-text="`${msg.metadata.processing_time}s`"></span>
                                                </span>
                                            </template>
                                            <template x-if="msg.metadata.confidence !== null && msg.metadata.confidence !== undefined">
                                                <span>
                                                    <svg class="w-3 h-3 inline mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    <span x-text="`Confidence ${Math.round(msg.metadata.confidence * 100)}%`"></span>
                                                </span>
                                            </template>
                                            <template x-if="msg.metadata.cost !== null && msg.metadata.cost !== undefined">
                                                <span>
                                                    <svg class="w-3 h-3 inline mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span x-text="`$${Number(msg.metadata.cost).toFixed(4)}`"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Knowledge Sources (RAG Attribution) --}}
                                <template x-if="msg.sender === 'ai' && msg.metadata?.knowledge_sources && msg.metadata.knowledge_sources.length > 0">
                                    <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                        <div class="text-xs text-neutral-600 dark:text-neutral-400 flex items-start gap-2">
                                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <div class="flex-1">
                                                <div class="font-medium mb-1">Knowledge sources used:</div>
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="source in msg.metadata.knowledge_sources" :key="source">
                                                        <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 text-xs" x-text="source"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Message Error/Recovery Actions --}}
                                <template x-if="msg.actions && msg.actions.length > 0">
                                    <div class="mt-3 pt-3 border-t border-red-200 dark:border-red-900/50 flex flex-wrap gap-2">
                                        <template x-for="action in msg.actions" :key="action.method">
                                            <button @click="$event.preventDefault(); typeof $data[action.method] === 'function' ? $data[action.method]() : null"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                                                    :class="action.primary ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-neutral-600 hover:bg-neutral-700 focus:ring-neutral-500'"
                                                    x-text="action.label">
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                {{-- Message Actions --}}
                                <div class="mt-3 flex items-center gap-3 text-xs">
                                    <button @click="copyMessage(msg.id, msg.content)"
                                        class="flex items-center gap-1 opacity-75 hover:opacity-100 transition-all"
                                        :class="[
                                            msg.sender === 'user' ? 'text-white' : 'text-neutral-600 dark:text-neutral-400',
                                            isCopiedMessage(msg.id) ? 'opacity-100 text-green-600 dark:text-green-400' : ''
                                        ]"
                                        aria-label="Copy message">
                                        {{-- Checkmark icon when copied --}}
                                        <template x-if="isCopiedMessage(msg.id)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                        {{-- Copy icon when not copied --}}
                                        <template x-if="! isCopiedMessage(msg.id)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                        <span x-text="isCopiedMessage(msg.id) ? 'Copied!' : 'Copy'"></span>
                                    </button>

                                    {{-- Regenerate button (AI messages only) --}}
                                    <template x-if="msg.sender === 'ai' && messages.indexOf(msg) === messages.map(m => m.sender).lastIndexOf('ai')">
                                        <button @click="regenerateResponse()"
                                            class="flex items-center gap-1 opacity-75 hover:opacity-100 transition-opacity text-neutral-600 dark:text-neutral-400"
                                            :disabled="isProcessing"
                                            aria-label="Regenerate response">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>Regenerate</span>
                                        </button>
                                    </template>

                                    {{-- Delete message --}}
                                    <button @click="deleteMessage(msg.id)"
                                        class="flex items-center gap-1 opacity-50 hover:opacity-100 transition-opacity text-red-500 dark:text-red-400"
                                        aria-label="Delete message">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Typing Indicator --}}
                <div x-show="isTyping" class="flex items-start gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 bg-primary-500 rounded-full shadow-xs border border-white dark:border-neutral-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 max-w-4xl bg-neutral-100 dark:bg-neutral-800 rounded-2xl rounded-tl-sm px-4 py-3 shadow-xs">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-medium text-neutral-600 dark:text-neutral-400"
                                x-text="`AI is thinking... (${model})`"></span>
                            <button @click="cancelStreaming()"
                                class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors"
                                aria-label="Cancel response">
                                Cancel
                            </button>
                        </div>
                        <div class="flex gap-1 py-1">
                            <span class="w-2 h-2 bg-neutral-400 rounded-full animate-bounce"
                                style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-neutral-400 rounded-full animate-bounce"
                                style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-neutral-400 rounded-full animate-bounce"
                                style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>

                {{-- Screen reader live region for new messages --}}
                <div role="status" aria-live="polite" class="sr-only"
                    x-text="isTyping ? 'AI is generating a response...' : ''"></div>
            </div>

            {{-- Tool Usage Indicators --}}
            <div x-show="activeTools.length > 0"
                class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-800">
                <x-ai.tool-usage-indicator />
            </div>

            {{-- Input Area --}}
            <div class="p-3 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                <form @submit.prevent="sendMessage()" class="flex items-end gap-3 max-w-5xl mx-auto">
                    <div class="flex-1 relative">
                        <label for="message-input" class="sr-only">Type your message</label>
                        <textarea id="message-input" x-model="currentMessage" @keydown.enter.prevent="handleEnterKey($event)"
                            placeholder="Ask about training strategies, character optimization, or any career planning questions..."
                            class="w-full resize-none border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 shadow-sm py-3 px-4 pb-8"
                            rows="2" maxlength="2000" :disabled="isProcessing"
                            :aria-disabled="isProcessing"
                            aria-describedby="input-help"
                            aria-label="Type your message to AI assistant"></textarea>
                        <div id="input-help"
                            class="absolute bottom-2 inset-x-4 flex items-center justify-between text-xs text-neutral-400 dark:text-neutral-500">
                            <span>Enter to send, Shift+Enter for new line — <button type="button" @click="openKeyboardShortcuts()" class="hover:text-primary-500 transition-colors" aria-label="Show keyboard shortcuts">Shortcuts (Ctrl+/)</button></span>
                            <span x-text="`${currentMessage.length}/2000`"></span>
                        </div>
                    </div>

                    <button type="submit" :disabled="!currentMessage.trim() || isProcessing"
                        class="p-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm self-stretch flex items-center justify-center min-w-14"
                        aria-label="Send message">
                        <svg x-show="!isProcessing" class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <svg x-show="isProcessing" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
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
        <div class="fixed inset-y-0 right-0 w-80 bg-neutral-50 dark:bg-neutral-800 border-l border-neutral-200 dark:border-neutral-700 shadow-xl transform transition-transform duration-300 md:relative md:shadow-none z-50 md:z-auto"
            x-show="showServerStatus || showWorkflow || showSettings"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0">

            {{-- Server Status Panel --}}
            <div x-show="showServerStatus" class="p-4">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">MCP Server Status</h3>
                <x-ai.server-status-indicator />
            </div>

            {{-- Workflow Visualization Panel --}}
            <div x-show="showWorkflow" class="p-4">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Agent Workflow</h3>
                <x-ai.workflow-visualization />
            </div>

            {{-- Settings Panel --}}
            <div x-show="showSettings" class="p-4">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Chat Settings</h3>
                <x-ai.agent-selector />
            </div>
        </div>
    </div>

    {{-- Keyboard Shortcuts Modal --}}
    <div x-show="showKeyboardShortcuts"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="closeKeyboardShortcuts()"
        @keydown.escape.window="closeKeyboardShortcuts()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="shortcuts-title">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 id="shortcuts-title" class="text-lg font-semibold text-neutral-900 dark:text-white">Keyboard Shortcuts</h3>
                <button type="button" @click="closeKeyboardShortcuts()" class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-lg transition-colors" aria-label="Close">
                    <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-neutral-700 dark:text-neutral-300">Send message</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 rounded text-xs font-mono text-neutral-600 dark:text-neutral-300">Enter</kbd>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-700 dark:text-neutral-300">New line</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 rounded text-xs font-mono text-neutral-600 dark:text-neutral-300">Shift+Enter</kbd>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-700 dark:text-neutral-300">Focus input</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 rounded text-xs font-mono text-neutral-600 dark:text-neutral-300">Ctrl+K</kbd>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-700 dark:text-neutral-300">Cancel response</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 rounded text-xs font-mono text-neutral-600 dark:text-neutral-300">Esc</kbd>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-700 dark:text-neutral-300">Show shortcuts</span>
                    <kbd class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 rounded text-xs font-mono text-neutral-600 dark:text-neutral-300">Ctrl+/</kbd>
                </div>
            </div>
        </div>
    </div>
</div>
