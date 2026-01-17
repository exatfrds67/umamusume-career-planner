@props([
    'stats' => [
        'speed' => 0,
        'stamina' => 0,
        'power' => 0,
        'guts' => 0,
        'wit' => 0,
    ],
    'character' => null,
])

@php
    $statConfig = [
        'speed' => ['label' => 'Speed', 'type' => 'speed'],
        'stamina' => ['label' => 'Stamina', 'type' => 'stamina'],
        'power' => ['label' => 'Power', 'type' => 'power'],
        'guts' => ['label' => 'Guts', 'type' => 'guts'],
        'wit' => ['label' => 'Wisdom', 'type' => 'wisdom'],
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

<div {{ $attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow']) }}>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Stat Snapshot</h3>
            @if ($character)
                <a href="{{ route('characters.show', $character) }}"
                    class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                    View Details
                </a>
            @endif
        </div>

        <div class="space-y-4">
            @foreach ($statConfig as $key => $config)
                @php
                    $value = $stats[$key] ?? 0;
                    $grade = $getGrade($value);
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="text-sm text-gray-500 dark:text-gray-400 w-16">{{ $config['label'] }}</span>
                            <x-ui.grade-badge :grade="$grade" />
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">({{ $value }})</span>
                    </div>
                    <x-ui.stat-bar :type="$config['type']" :value="$value" :max="1200" :showValue="false" />
                </div>
            @endforeach
        </div>

        @if ($character && $character->current_turn > 0)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Stats</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ array_sum($stats) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>
