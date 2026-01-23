@props([
    'lastTip' => 'Based on your current stats, focusing on Speed training would be optimal. Your character is showing good potential for distance races.',
    'tipTimestamp' => null,
])

@php
    $timestamp = $tipTimestamp ?? now()->subMinutes(15);
@endphp

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden h-full flex flex-col']) }}>
    {{-- Header --}}
    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="relative shrink-0">
                <div class="w-8 h-8 rounded-full bg-linear-to-br from-primary-400 to-secondary-500 flex items-center justify-center shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </div>
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-none">AI Advisor</h3>
                <span class="text-[10px] text-primary-600 dark:text-primary-400 font-medium">Online</span>
            </div>
        </div>
        <span class="text-xs text-gray-400 font-medium">{{ $timestamp->shortAbsoluteDiffForHumans() }}</span>
    </div>

    {{-- Chat Body --}}
    <div class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/50 dark:bg-gray-900/20">
        {{-- Incoming Message --}}
        <div class="flex gap-3">
            <div class="shrink-0 mt-1">
                 <div class="w-6 h-6 rounded-full bg-linear-to-br from-primary-400 to-secondary-500 flex items-center justify-center opacity-75">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
            </div>
            <div class="glass-card-inner rounded-2xl rounded-tl-none p-3.5 shadow-sm max-w-[90%]">
                <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                    {{ $lastTip }}
                </p>
            </div>
        </div>
        
        {{-- Contextual Suggestions (Simulating chat logic) --}}
        <div class="pl-9 space-y-2">
             <div class="flex flex-wrap gap-2">
                <a href="{{ route('ai.chat', ['topic' => 'training']) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:text-primary-600 dark:hover:text-primary-400 transition-colors text-xs font-medium text-gray-600 dark:text-gray-300 shadow-sm">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Analyze Training
                </a>
                <a href="{{ route('ai.chat', ['topic' => 'race']) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-secondary-300 dark:hover:border-secondary-700 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors text-xs font-medium text-gray-600 dark:text-gray-300 shadow-sm">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8M3 21h18M5 21v-8a2 2 0 012-2h14a2 2 0 012 2v8m-2 0h.01M12 17h.01M12 11H8m8 0h-2" />
                    </svg>
                    Race Strategy
                </a>
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="p-3 bg-white dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700/50 shrink-0">
        <form action="{{ route('ai.chat') }}" method="GET" class="relative">
            <input type="text" 
                   name="initial_message" 
                   placeholder="Ask for advice..." 
                   class="w-full pl-3 pr-10 py-2 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                   autocomplete="off">
            <button type="submit" 
                    class="absolute right-1.5 top-1.5 p-1 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-md transition-colors"
                    aria-label="Send">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>
</div>
