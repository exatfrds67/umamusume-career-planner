{{-- Empty State Component for Dashboard when no characters exist --}}
<div class="text-center py-12">
    <div class="mx-auto max-w-md">
        {{-- Illustration --}}
        <div
            class="mx-auto h-24 w-24 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-6">
            <svg class="h-12 w-12 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </div>

        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            No Characters Yet
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-8">
            Create your first Uma Musume character to start tracking your training progress, races, and skills.
        </p>

        <a href="{{ route('characters.create') }}"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-xs text-white bg-primary-600 hover:bg-primary-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Your First Character
        </a>
    </div>

    {{-- Feature Cards --}}
    <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-left">
            <div
                class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Track Stats</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Monitor Speed, Stamina, Power, Guts, and Wit
                progression in real-time.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-left">
            <div
                class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/30 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Plan Races</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Schedule races, track readiness, and optimize your race
                strategy.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-left">
            <div
                class="w-10 h-10 rounded-lg bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-secondary-600 dark:text-secondary-400" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Manage Skills</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Acquire and optimize skills to maximize your character's
                potential.</p>
        </div>
    </div>
</div>
