@props(['character'])

@php
    $id           = $character['id'] ?? '';
    $name         = $character['name'] ?? 'Unknown';
    $status       = $character['status'] ?? 'active';
    $scenarioType = $character['scenario_type'] ?? '';
    $progress     = $character['progress'] ?? 0;
    $isSeeded     = $character['is_seeded'] ?? false;
    $variantCount = $character['variant_count'] ?? 1;
    $stats        = $character['current_stats'] ?? [];
    $nextGoal     = $character['next_goal'] ?? null;
    $remainingGoals = $character['remaining_goal_count'] ?? 0;

    $avatarSrc = $character['avatar_processed']
        ?: ($character['avatar_url']
        ?: ($character['avatar_fallback_url'] ?? ''));

    if ($avatarSrc && ! str_starts_with($avatarSrc, 'http://') && ! str_starts_with($avatarSrc, 'https://') && ! str_starts_with($avatarSrc, '/')) {
        $avatarSrc = '/'.$avatarSrc;
    }

    $scenarioLabel = $scenarioType
        ? ucfirst(str_replace('_', ' ', $scenarioType))
        : 'No Scenario';

    $statusVariant = match ($status) {
        'active'    => 'success',
        'completed' => 'primary',
        'retired'   => 'error',
        'archived'  => 'neutral',
        default     => 'neutral',
    };

    $secondaryInfoAvailable = $variantCount > 1 || $nextGoal || $remainingGoals > 0;
@endphp

<article class="border-b border-neutral-200 last:border-b-0 dark:border-neutral-700">
    <a href="{{ url('/characters/'.$id) }}"
        aria-label="View {{ $name }} character details"
        class="group grid min-h-11 grid-cols-[minmax(0,1fr)_auto] items-center gap-2 px-3 py-2.5 transition hover:bg-neutral-50 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-inset dark:hover:bg-neutral-800/70 dark:focus-visible:ring-primary-400">

        <div class="grid min-w-0 grid-cols-1 items-center gap-2 md:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)] md:gap-3">
            <div class="flex min-w-0 items-center gap-2">
                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                    @if ($avatarSrc)
                        <img src="{{ $avatarSrc }}" alt="" class="h-full w-full object-cover" loading="lazy">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-neutral-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="truncate text-sm font-bold text-neutral-900 transition-colors group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">{{ $name }}</span>
                        @if ($isSeeded)
                            <span class="h-2 w-2 shrink-0 rounded-full bg-primary-500" aria-hidden="true"></span>
                            <span class="sr-only">Seeded character</span>
                        @endif
                    </div>
                    <div class="mt-0.5 flex min-w-0 flex-wrap items-center gap-1.5">
                        <span class="inline-flex min-h-6 items-center rounded border border-neutral-200 px-1.5 py-0.5 text-[11px] font-medium text-neutral-600 dark:border-neutral-600 dark:text-neutral-300">{{ $scenarioLabel }}</span>
                        <x-badge :variant="$statusVariant" class="rounded! text-[11px]! px-1.5! py-0.5! capitalize">{{ ucfirst($status) }}</x-badge>
                        <span class="inline-flex min-h-6 items-center rounded border border-neutral-200 px-1.5 py-0.5 text-[11px] font-semibold tabular-nums text-neutral-600 dark:border-neutral-600 dark:text-neutral-300 md:hidden">{{ $progress }}%</span>
                    </div>
                </div>
            </div>

            <div class="hidden grid-cols-5 gap-1.5 md:grid" aria-label="Core stats">
                @foreach (['speed' => 'SPD', 'stamina' => 'STM', 'power' => 'POW', 'guts' => 'GUT', 'wit' => 'WIT'] as $statKey => $statLabel)
                    @php $statVal = $stats[$statKey] ?? null; @endphp
                    <div class="rounded border border-neutral-200 px-1.5 py-1 text-center dark:border-neutral-700">
                        <div class="text-[11px] font-bold leading-none tabular-nums text-neutral-800 dark:text-neutral-100">{{ is_numeric($statVal) ? $statVal : '—' }}</div>
                        <div class="mt-0.5 text-[9px] font-semibold leading-none tracking-wide text-neutral-500 dark:text-neutral-400">{{ $statLabel }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-2 md:min-w-22 md:justify-end">
            <div class="hidden md:block">
                <div class="mb-1 flex items-center justify-end gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                    <span>Career</span>
                    <span class="font-semibold tabular-nums">{{ $progress }}%</span>
                </div>
                <div class="h-1.5 w-16 overflow-hidden rounded bg-neutral-200 dark:bg-neutral-700"
                    role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"
                    aria-label="Career progress {{ $progress }}%">
                    <div class="h-full rounded bg-primary-500" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <svg class="h-4 w-4 shrink-0 text-neutral-400 transition-colors group-hover:text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    @if ($secondaryInfoAvailable)
        <details class="group/details px-3 pb-2">
            <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-1 rounded px-1 py-1 text-xs font-semibold text-neutral-600 underline decoration-dotted underline-offset-4 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:text-neutral-300 dark:hover:text-primary-300">
                <span class="group-open/details:hidden">Show secondary details</span>
                <span class="hidden group-open/details:inline">Hide secondary details</span>
            </summary>

            <div class="rounded border border-neutral-200 bg-neutral-50 p-2 text-xs text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($variantCount > 1)
                        <span class="inline-flex min-h-6 items-center rounded border border-neutral-300 px-2 py-0.5 font-semibold dark:border-neutral-600">
                            {{ $variantCount }} variants
                        </span>
                    @endif

                    @if ($nextGoal)
                        <span class="inline-flex min-h-6 items-center rounded border border-neutral-300 px-2 py-0.5 dark:border-neutral-600">
                            Next goal: {{ $nextGoal['name'] ?? 'Unknown' }}
                        </span>
                    @endif

                    @if ($remainingGoals > 0)
                        <span class="inline-flex min-h-6 items-center rounded border border-neutral-300 px-2 py-0.5 font-semibold dark:border-neutral-600">
                            +{{ $remainingGoals }} more goals
                        </span>
                    @endif
                </div>
            </div>
        </details>
    @endif
</article>
