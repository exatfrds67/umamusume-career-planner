{{-- WF-004: AI Advisor Banner --}}
<section id="ai-advisor-banner"
    class="card rounded-xl p-4 mb-6 bg-linear-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 border-primary-200 dark:border-primary-800 animate-fade-in-delay-2"
    aria-labelledby="ai-advisor-heading">
    <div class="flex items-center gap-4">
        <div
            class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 id="ai-advisor-heading" class="text-sm font-semibold text-primary-900 dark:text-primary-100">AI Advisor
            </h3>
            <p id="ai-advisor-message" class="text-sm text-primary-700 dark:text-primary-300 truncate">
                Analyzing training options for optimal recommendations...
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span id="ai-confidence-badge"
                class="hidden px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                85% confidence
            </span>
            <button onclick="showAIDetails()"
                class="px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-100 dark:bg-primary-900/50 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                Details
            </button>
        </div>
    </div>
</section>
