@props(['stat', 'current', 'max' => 1200, 'gain' => 0, 'showGain' => true])

@php
    $percentage = min(100, ($current / $max) * 100);
    $statColors = [
        'speed' => 'blue',
        'stamina' => 'green',
        'power' => 'red',
        'guts' => 'purple',
        'wit' => 'yellow',
    ];
    $color = $statColors[$stat] ?? 'gray';
@endphp

<div class="space-y-2">
    <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize transition-colors duration-300">
            {{ $stat }}
        </span>
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-gray-900 dark:text-white transition-colors duration-300">
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
    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden transition-colors duration-300">
        <div class="absolute inset-0 bg-{{ $color }}-500 dark:bg-{{ $color }}-400 rounded-full transition-all duration-500"
            style="width: {{ $percentage }}%"></div>

        @if ($showGain && $gain > 0)
            <!-- Gain Preview -->
            @php
                $newPercentage = min(100, (($current + $gain) / $max) * 100);
            @endphp
            <div class="absolute inset-0 bg-{{ $color }}-300 dark:bg-{{ $color }}-600 rounded-full transition-all duration-500 opacity-50"
                style="width: {{ $newPercentage }}%"></div>
        @endif
    </div>

    <!-- Max Indicator -->
    <div class="flex justify-end">
        <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors duration-300">
            / {{ $max }}
        </span>
    </div>
</div>
