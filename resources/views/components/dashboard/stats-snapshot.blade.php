@props([
    'stats' => [
        'speed' => 0,
        'stamina' => 0,
        'power' => 0,
        'guts' => 0,
        'wit' => 0,
    ],
    'character' => null,
    'raceRequirements' => [],
    'nextRaceName' => null,
])

@php
    $statConfig = [
        'speed' => ['label' => 'Speed', 'type' => 'speed'],
        'stamina' => ['label' => 'Stamina', 'type' => 'stamina'],
        'power' => ['label' => 'Power', 'type' => 'power'],
        'guts' => ['label' => 'Guts', 'type' => 'guts'],
        'wit' => ['label' => 'Wit', 'type' => 'wisdom'],
    ];

    // Helper function to get grade from stat value
    $getGrade = function ($value) {
        return match (true) {
            $value >= 1200 => 'SS',
            $value >= 1100 => 'S',
            $value >= 1000 => 'A+',
            $value >= 900 => 'A',
            $value >= 800 => 'B+',
            $value >= 700 => 'B',
            $value >= 600 => 'C+',
            $value >= 500 => 'C',
            $value >= 400 => 'D+',
            $value >= 300 => 'D',
            $value >= 200 => 'E+',
            $value >= 100 => 'E',
            $value >= 50 => 'F',
            default => 'G+',
        };
    };
@endphp

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden']) }}>
    <div class="px-5 py-5 sm:px-6 sm:py-6">
        <div class="mb-5 flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold leading-6 text-neutral-900 dark:text-white">Stat Snapshot</h3>
            @if ($character)
                <a href="{{ route('characters.show', $character) }}"
                    class="inline-flex items-center rounded-md px-2 py-1 text-sm font-semibold text-primary-600 hover:bg-primary-50 hover:text-primary-500 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-primary-400 dark:hover:bg-primary-900/25 dark:hover:text-primary-300 dark:focus-visible:ring-offset-neutral-900"
                    aria-label="View detailed stats for {{ $character->name }}">
                    View Details
                </a>
            @endif
        </div>
        <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-300">
            {{ !empty($raceRequirements) ? 'Why this matters now: these values are compared against your next race requirements.' : 'Why this matters now: these values determine training effectiveness and future race options.' }}
        </p>

        <ul class="space-y-4" role="list">
            @foreach ($statConfig as $key => $config)
                @php
                    $value = $stats[$key] ?? 0;
                    $requirement = null;

                    if (! empty($raceRequirements)) {
                        $requirement = $raceRequirements[$key] ?? ($key === 'wit' ? ($raceRequirements['wisdom'] ?? null) : null);
                    }

                    $tooltipContent = null;

                    if (is_numeric($requirement) && (int) $requirement > 0) {
                        $requirementInt = (int) $requirement;
                        $requirementPercent = (int) min(999, round(($value / $requirementInt) * 100));
                        $tooltipContent = sprintf(
                            '%s: %d/%d (%d%% of %s requirement)',
                            $config['label'],
                            $value,
                            $requirementInt,
                            $requirementPercent,
                            $nextRaceName ?? 'next race'
                        );
                    } else {
                        $maxPercent = (int) min(100, round(($value / 1200) * 100));
                        $tooltipContent = sprintf('%s: %d/1200 (%d%% of max)', $config['label'], $value, $maxPercent);
                    }
                @endphp
                <li class="w-full">
                    <x-tooltip class="block! w-full" :content="$tooltipContent" position="top" maxWidth="sm">
                        <x-stat-bar class="w-full" :stat="$key" :current="$value" :max="1200" show-icon show-percentage
                            show-soft-cap />
                    </x-tooltip>
                </li>
            @endforeach
        </ul>

        @if ($character && $character->current_turn > 0)
            <div class="mt-5 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-neutral-600 dark:text-neutral-300">Total Stats</span>
                    <span class="font-semibold tabular-nums text-neutral-900 dark:text-white">
                        {{ array_sum($stats) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>
