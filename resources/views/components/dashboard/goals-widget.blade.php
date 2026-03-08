@props([
    'shortTermGoal' => 'No short-term goal set',
    'shortTermProgress' => 0,
    'longTermGoal' => 'No long-term goal set',
    'longTermProgress' => 0,
    'characterId' => null,
])

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden']) }}>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-neutral-900 dark:text-white">
                Current Goals
            </h3>
            <a href="{{ $characterId ? route('characters.edit', $characterId) : route('characters.index') }}"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                aria-label="Edit goals for this character">
                Edit Goals
            </a>
        </div>

        <div class="space-y-5">
            {{-- Short-term Goal --}}
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/50">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Short-term</span>
                </div>
                <p class="text-sm text-neutral-900 dark:text-white mb-2">{{ $shortTermGoal }}</p>
                <x-ui.progress-bar :value="$shortTermProgress" :max="100" color="primary" size="md" :aria-label="'Short-term goal: ' . $shortTermProgress . '% complete'" />
                <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-1.5">{{ $shortTermProgress }}% complete</p>
            </div>

            {{-- Long-term Goal --}}
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-secondary-100 dark:bg-secondary-900/50">
                        <svg class="w-4 h-4 text-secondary-600 dark:text-secondary-400" fill="none"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Long-term</span>
                </div>
                <p class="text-sm text-neutral-900 dark:text-white mb-2">{{ $longTermGoal }}</p>
                <x-ui.progress-bar :value="$longTermProgress" :max="100" color="success" size="md" :aria-label="'Long-term goal: ' . $longTermProgress . '% complete'" />
                <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-1.5">{{ $longTermProgress }}% complete</p>
            </div>
        </div>
    </div>
</div>
