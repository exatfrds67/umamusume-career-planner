@props(['currentProvider' => 'ollama', 'currentModel' => 'llama3.3'])

<div class="provider-selector flex items-center gap-2" x-data="providerSelector()" x-init="initialize()">

    {{-- Provider Badge --}}
    <div class="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium"
        :class="{
            'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300': provider === 'ollama' &&
                status === 'online',
            'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': provider === 'bedrock' &&
                status === 'online',
            'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300': provider === 'agent' &&
                status === 'online',
            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': status === 'offline',
            'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300': status === 'connecting'
        }">

        {{-- Status Indicator --}}
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                :class="{
                    'bg-green-400': status === 'online',
                    'bg-yellow-400': status === 'connecting',
                    'bg-gray-400': status === 'offline'
                }"
                x-show="status !== 'offline'"></span>
            <span class="relative inline-flex rounded-full h-2 w-2"
                :class="{
                    'bg-green-500': status === 'online',
                    'bg-yellow-500': status === 'connecting',
                    'bg-gray-500': status === 'offline'
                }"></span>
        </span>

        {{-- Provider Name --}}
        <span x-text="getProviderLabel()"></span>

        {{-- Model Name --}}
        <span class="opacity-75" x-text="model"></span>
    </div>

    {{-- Provider Selector Dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="p-1 hover:bg-white/50 dark:hover:bg-gray-600 rounded transition-colors"
            aria-label="Change AI provider" aria-expanded="false" :aria-expanded="open.toString()">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
            role="menu">

            <div class="p-2">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase px-3 py-2">
                    AI Provider
                </div>

                {{-- Ollama (Local) --}}
                <button @click="selectProvider('ollama', 'llama3.3'); open = false"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="provider === 'ollama' ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' :
                        'text-gray-700 dark:text-gray-300'"
                    role="menuitem">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <div class="text-left">
                            <div class="font-medium">Ollama (Local)</div>
                            <div class="text-xs opacity-75">Fast, private, free</div>
                        </div>
                    </div>
                    <template x-if="provider === 'ollama'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </template>
                </button>

                {{-- AWS Bedrock --}}
                <button @click="selectProvider('bedrock', 'claude-3.5-sonnet'); open = false"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="provider === 'bedrock' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' :
                        'text-gray-700 dark:text-gray-300'"
                    role="menuitem">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                        <div class="text-left">
                            <div class="font-medium">AWS Bedrock</div>
                            <div class="text-xs opacity-75">Advanced reasoning</div>
                        </div>
                    </div>
                    <template x-if="provider === 'bedrock'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </template>
                </button>

                {{-- MCP Agents --}}
                <button @click="selectProvider('agent', 'training-agent'); open = false"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="provider === 'agent' ?
                        'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300' :
                        'text-gray-700 dark:text-gray-300'"
                    role="menuitem">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div class="text-left">
                            <div class="font-medium">MCP Agents</div>
                            <div class="text-xs opacity-75">Specialized subagents</div>
                        </div>
                    </div>
                    <template x-if="provider === 'agent'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </template>
                </button>

                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                {{-- Auto-Fallback Toggle --}}
                <label
                    class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors">
                    <span>Auto-fallback to Bedrock</span>
                    <input type="checkbox" x-model="autoFallback" @change="updateAutoFallback()"
                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </label>
            </div>
        </div>
    </div>
</div>

<script>
    function providerSelector() {
        return {
            provider: '{{ $currentProvider }}',
            model: '{{ $currentModel }}',
            status: 'connecting',
            autoFallback: true,

            initialize() {
                this.checkStatus();
                // Poll status every 5 seconds
                setInterval(() => this.checkStatus(), 5000);
            },

            async checkStatus() {
                try {
                    const response = await fetch('/api/ai/chat/server-status');
                    const data = await response.json();

                    if (data.servers && data.servers[this.provider]) {
                        this.status = data.servers[this.provider].status;
                    }
                } catch (error) {
                    console.error('Failed to check provider status:', error);
                    this.status = 'offline';
                }
            },

            async selectProvider(provider, model) {
                this.provider = provider;
                this.model = model;

                // Save preference
                try {
                    await fetch('/api/ai/chat/preferences', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            provider: provider,
                            model: model
                        })
                    });
                } catch (error) {
                    console.error('Failed to save provider preference:', error);
                }

                this.checkStatus();
            },

            async updateAutoFallback() {
                try {
                    await fetch('/api/ai/chat/preferences', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            auto_fallback: this.autoFallback
                        })
                    });
                } catch (error) {
                    console.error('Failed to save auto-fallback preference:', error);
                }
            },

            getProviderLabel() {
                const labels = {
                    'ollama': 'Ollama',
                    'bedrock': 'Bedrock',
                    'agent': 'Agent'
                };
                return labels[this.provider] || this.provider;
            }
        };
    }
</script>
