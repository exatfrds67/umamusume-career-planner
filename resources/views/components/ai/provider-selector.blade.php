@props(['currentProvider' => 'ollama', 'currentModel' => 'llama3.3'])

<div class="provider-selector flex items-center gap-2" x-data="providerSelector()" x-init="initialize()">

    <div class="relative" @click.away="open = false">
        <button @click="open = !open"
            class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <div class="w-2 h-2 rounded-full"
                :class="{
                    'bg-green-500': provider === 'ollama',
                    'bg-blue-500': provider === 'bedrock',
                    'bg-purple-500': provider === 'agent'
                }">
            </div>
            <span x-text="model || provider || 'Select Model'"></span>
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
            style="display: none;">
            <!-- Provider Tabs -->
            <div class="flex border-b border-slate-200 dark:border-slate-700">
                <button @click="provider = 'ollama'; model = models.ollama[0] || ''"
                    class="flex-1 px-4 py-2 text-xs font-medium text-center transition-colors"
                    :class="provider === 'ollama' ?
                        'bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white border-b-2 border-green-500' :
                        'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'">
                    Ollama
                </button>
                <button @click="provider = 'bedrock'; model = Object.keys(models.bedrock)[0] || ''"
                    class="flex-1 px-4 py-2 text-xs font-medium text-center transition-colors"
                    :class="provider === 'bedrock' ?
                        'bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white border-b-2 border-blue-500' :
                        'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'">
                    Bedrock
                </button>
            </div>

            <!-- Model List -->
            <div class="max-h-64 overflow-y-auto p-1">
                <template x-if="provider === 'ollama'">
                    <div class="space-y-0.5">
                        <template x-for="(details, id) in models.ollama" :key="id">
                            <button @click="selectProvider('ollama', id)"
                                class="w-full text-left px-3 py-2 text-sm rounded-md transition-colors flex items-center justify-between group"
                                :class="model === id ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' :
                                    'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                <span x-text="details.name || id"></span>
                                <svg x-show="model === id" class="w-4 h-4 text-green-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                        <div x-show="Object.keys(models.ollama).length === 0"
                            class="px-3 py-4 text-center text-sm text-slate-500">
                            No Ollama models found
                        </div>
                    </div>
                </template>

                <template x-if="provider === 'bedrock'">
                    <div class="space-y-0.5">
                        <template x-for="(price, m) in models.bedrock" :key="m">
                            <button @click="selectProvider('bedrock', m)"
                                class="w-full text-left px-3 py-2 text-sm rounded-md transition-colors flex items-center justify-between group"
                                :class="model === m ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' :
                                    'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'">
                                <span x-text="m.split('.')[1] || m"></span>
                                <svg x-show="model === m" class="w-4 h-4 text-blue-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                        <div x-show="Object.keys(models.bedrock).length === 0"
                            class="px-3 py-4 text-center text-sm text-slate-500">
                            No Bedrock models found
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Alpine component logic moved to resources/js/components/ai/provider-selector.js --}}
@once
    @vite(['resources/js/components/ai/provider-selector.js'])
@endonce
