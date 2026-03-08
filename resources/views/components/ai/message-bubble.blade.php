@props(['message'])

<div class="flex items-start gap-3 animate-fade-in-up" :class="message.sender === 'user' ? 'flex-row-reverse' : 'flex-row'" role="article"
    :aria-label="`Message from ${message.sender === 'user' ? 'you' : 'AI assistant'}`">

    {{-- Avatar --}}
    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
        :class="message.sender === 'user' ? 'bg-neutral-300 dark:bg-neutral-600' : 'bg-primary-500'">
        <template x-if="message.sender === 'user'">
            <svg class="w-5 h-5 text-neutral-700 dark:text-neutral-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </template>
        <template x-if="message.sender === 'ai'">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </template>
    </div>

    {{-- Message Content --}}
    <div class="flex-1 max-w-3xl">
        <div class="rounded-lg p-4 shadow-xs"
            :class="message.sender === 'user' ?
                'bg-primary-600 text-white' :
                'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white border border-neutral-200 dark:border-neutral-600'">

            {{-- Message Header --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm"
                        x-text="message.sender === 'user' ? 'You' : 'AI Assistant'"></span>
                    <template x-if="message.sender === 'ai' && message.metadata?.model">
                        <span class="text-xs px-2 py-0.5 rounded-full"
                            :class="message.sender === 'user' ? 'bg-primary-500' :
                                'bg-neutral-100 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300'"
                            x-text="message.metadata.model"></span>
                    </template>
                    <template x-if="message.sender === 'ai' && message.metadata?.agent">
                        <span
                            class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                            x-text="message.metadata.agent"></span>
                    </template>
                </div>
                <time class="text-xs opacity-75" :datetime="message.timestamp"
                    x-text="formatTime(message.timestamp)"></time>
            </div>

            {{-- Message Text --}}
            <div class="prose prose-sm max-w-none"
                :class="message.sender === 'user' ? 'prose-invert' : 'dark:prose-invert'"
                x-html="formatMessage(message.content)"></div>

            {{-- AI Message Metadata --}}
            <template x-if="message.sender === 'ai' && message.metadata">
                <div class="mt-3 pt-3 border-t"
                    :class="message.sender === 'user' ? 'border-primary-500' : 'border-neutral-200 dark:border-neutral-600'">

                    {{-- Confidence Score --}}
                    <template x-if="message.metadata.confidence">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs opacity-75">Confidence:</span>
                            <div class="flex-1 h-2 bg-neutral-200 dark:bg-neutral-600 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 transition-all duration-300"
                                    :style="`width: ${message.metadata.confidence}%`"></div>
                            </div>
                            <span class="text-xs font-medium" x-text="`${message.metadata.confidence}%`"></span>
                        </div>
                    </template>

                    {{-- Processing Time & Cost --}}
                    <div class="flex items-center justify-between text-xs opacity-75">
                        <div class="flex items-center gap-3">
                            <template x-if="message.metadata.processing_time">
                                <span>
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="`${message.metadata.processing_time}s`"></span>
                                </span>
                            </template>
                            <template x-if="message.metadata.tokens">
                                <span>
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                    <span x-text="`${message.metadata.tokens} tokens`"></span>
                                </span>
                            </template>
                        </div>
                        <template x-if="message.metadata.cost">
                            <span class="font-medium">
                                $<span x-text="message.metadata.cost.toFixed(4)"></span>
                            </span>
                        </template>
                    </div>

                    {{-- Tools Used --}}
                    <template x-if="message.metadata.tools_used && message.metadata.tools_used.length > 0">
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span class="text-xs opacity-75">Tools:</span>
                            <template x-for="tool in message.metadata.tools_used" :key="tool">
                                <span
                                    class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full"
                                    x-text="tool"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Message Actions --}}
            <div class="mt-3 flex items-center gap-3 text-xs">
                <button @click="copyMessage(message.id, message.content)"
                    class="flex items-center gap-1 opacity-75 hover:opacity-100 transition-all"
                    :class="[
                        message.sender === 'user' ? 'text-white' : 'text-neutral-600 dark:text-neutral-400',
                        copiedMessageId === message.id ? 'opacity-100 text-green-600 dark:text-green-400' : ''
                    ]"
                    aria-label="Copy message">
                    <template x-if="copiedMessageId === message.id">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="copiedMessageId !== message.id">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </template>
                    <span x-text="copiedMessageId === message.id ? 'Copied!' : 'Copy'"></span>
                </button>

                <template x-if="message.sender === 'ai'">
                    <button @click="rateMessage(message.id, 'helpful')"
                        class="flex items-center gap-1 opacity-75 hover:opacity-100 transition-opacity"
                        :class="[
                            message.sender === 'user' ? 'text-white' : 'text-neutral-600 dark:text-neutral-400',
                            message.rating === 'helpful' ? 'opacity-100 text-green-600 dark:text-green-400' : ''
                        ]"
                        aria-label="Mark as helpful">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                        </svg>
                        <span>Helpful</span>
                    </button>
                </template>

                <template x-if="message.sender === 'ai'">
                    <button @click="rateMessage(message.id, 'not_helpful')"
                        class="flex items-center gap-1 opacity-75 hover:opacity-100 transition-opacity"
                        :class="[
                            message.sender === 'user' ? 'text-white' : 'text-neutral-600 dark:text-neutral-400',
                            message.rating === 'not_helpful' ? 'opacity-100 text-red-600 dark:text-red-400' : ''
                        ]"
                        aria-label="Mark as not helpful">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" />
                        </svg>
                        <span>Not Helpful</span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
