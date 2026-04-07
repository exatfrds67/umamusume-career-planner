@props([
    'races' => [],
])

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden']) }}>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-neutral-900 dark:text-white">
                Upcoming Races
            </h3>
            <a href="{{ route('races.index') }}"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                aria-label="View all upcoming races">
                View All
            </a>
        </div>

        <div class="space-y-3">
            @forelse($races as $race)
                @php
                    $turnsAway = (int) ($race['turnsAway'] ?? 0);
                    $turnValue = $race['turn'] ?? null;
                    $turnsProgress = $turnsAway > 0 ? max(8, min(100, (int) round((14 - min($turnsAway, 14)) / 14 * 100))) : 0;
                @endphp
                <div
                    class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors {{ isset($race['isGoalRace']) && $race['isGoalRace'] ? 'border-l-4 border-primary-500' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ ($race['grade'] ?? 'G3') === 'G1' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                {{ $race['grade'] ?? 'G3' }}
                            </span>
                            <x-tooltip :content="$race['name'] ?? 'Unknown Race'" position="top">
                                <span class="text-sm font-medium text-neutral-900 dark:text-white truncate max-w-45">
                                    {{ $race['name'] ?? 'Unknown Race' }}
                                </span>
                            </x-tooltip>
                            @if (isset($race['isGoalRace']) && $race['isGoalRace'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Goal
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                            @if ($turnValue !== null)
                                <span class="mr-2 font-medium">Turn {{ $turnValue }}</span>
                            @endif
                            @if (isset($race['turnsAway']))
                                @if ($turnsAway === 0)
                                    <span class="font-semibold text-primary-600 dark:text-primary-400">Race Day!</span>
                                @else
                                    <span class="font-medium">T-{{ $turnsAway }}</span>
                                    <span class="ml-1">({{ $turnsAway }} turn{{ $turnsAway !== 1 ? 's' : '' }} away)</span>
                                @endif
                            @elseif (isset($race['date']))
                                {{ \Carbon\Carbon::parse($race['date'])->format('M d, Y') }}
                            @endif
                        </div>
                        @if ($turnsAway > 0)
                            <div class="mt-1.5 h-1.5 w-full max-w-32 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $turnsProgress }}%"></div>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 ml-4">
                        <x-ui.readiness-badge :percentage="$race['readiness'] ?? 0" />
                        <a href="{{ route('races.index') }}"
                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400"
                            title="View race details">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        No upcoming races scheduled.
                    </p>
                    <a href="{{ route('races.index') }}"
                        class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        Schedule a race
                        <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
