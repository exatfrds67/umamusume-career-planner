@props(['stat', 'current', 'max' => 1200, 'gain' => 0, 'showGain' => true])

@php
    $percentage = min(100, ($current / $max) * 100);
    $statClasses = [
        'speed'   => ['bar' => 'bg-stat-speed-500 dark:bg-stat-speed-400',     'gain' => 'bg-stat-speed-300 dark:bg-stat-speed-600'],
        'stamina' => ['bar' => 'bg-stat-stamina-500 dark:bg-stat-stamina-400', 'gain' => 'bg-stat-stamina-300 dark:bg-stat-stamina-600'],
        'power'   => ['bar' => 'bg-stat-power-500 dark:bg-stat-power-400',     'gain' => 'bg-stat-power-300 dark:bg-stat-power-600'],
        'guts'    => ['bar' => 'bg-stat-guts-500 dark:bg-stat-guts-400',       'gain' => 'bg-stat-guts-300 dark:bg-stat-guts-600'],
        'wit'     => ['bar' => 'bg-stat-wit-500 dark:bg-stat-wit-400',         'gain' => 'bg-stat-wit-300 dark:bg-stat-wit-600'],
    ];
    $classes = $statClasses[$stat] ?? ['bar' => 'bg-neutral-500 dark:bg-neutral-400', 'gain' => 'bg-neutral-300 dark:bg-neutral-600'];
@endphp

<div class="space-y-2">
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 capitalize transition-colors duration-300">
            {{ $stat }}
        </span>
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-neutral-900 dark:text-white transition-colors duration-300">
                {{ $current }}
            </span>
            @if ($showGain && $gain != 0)
                <span
                    class="text-xs font-semibold {{ $gain > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} transition-colors duration-300">
                    {{ $gain > 0 ? '+' : '' }}{{ $gain }}
                </span>
            @endif
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="relative h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden transition-colors duration-300">
        <div class="absolute inset-0 {{ $classes['bar'] }} rounded-full transition-all duration-500"
            style="width: {{ $percentage }}%"></div>

        @if ($showGain && $gain > 0)
            <!-- Gain Preview -->
            @php
                $newPercentage = min(100, (($current + $gain) / $max) * 100);
            @endphp
            <div class="absolute inset-0 {{ $classes['gain'] }} rounded-full transition-all duration-500 opacity-50"
                style="width: {{ $newPercentage }}%"></div>
        @endif
    </div>

    <!-- Max Indicator -->
    <div class="flex justify-end">
        <span class="text-xs text-neutral-500 dark:text-neutral-400 transition-colors duration-300">
            / {{ $max }}
        </span>
    </div>
</div>
