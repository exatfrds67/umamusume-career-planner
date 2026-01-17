@props(['energy', 'mood', 'conditions' => [], 'showDetails' => true])

@php
    $moodColors = [
        'great' => 'green',
        'good' => 'blue',
        'normal' => 'gray',
        'bad' => 'orange',
        'awful' => 'red',
    ];
    $moodColor = $moodColors[$mood] ?? 'gray';

    $moodEmojis = [
        'great' => '😄',
        'good' => '🙂',
        'normal' => '😐',
        'bad' => '😟',
        'awful' => '😢',
    ];
    $moodEmoji = $moodEmojis[$mood] ?? '😐';

    $energyColor = $energy >= 70 ? 'green' : ($energy >= 40 ? 'yellow' : 'red');
@endphp

<div class="glass-card-inner rounded-lg p-4 space-y-4">
    <!-- Energy Bar -->
    <div>
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-300">
                Energy
            </span>
            <span class="text-sm font-bold text-gray-900 dark:text-white transition-colors duration-300">
                {{ $energy }}%
            </span>
        </div>
        <div
            class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden transition-colors duration-300">
            <div class="absolute inset-0 bg-{{ $energyColor }}-500 dark:bg-{{ $energyColor }}-400 rounded-full transition-all duration-500"
                style="width: {{ $energy }}%">
                <div class="absolute inset-0 bg-linear-to-r from-transparent to-white/20"></div>
            </div>
        </div>
        @if ($showDetails)
            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 transition-colors duration-300">
                @if ($energy >= 70)
                    Excellent condition for training
                @elseif($energy >= 40)
                    Moderate energy - consider rest soon
                @else
                    Low energy - high failure risk
                @endif
            </div>
        @endif
    </div>

    <!-- Mood Indicator -->
    <div>
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-300">
                Mood
            </span>
            <div class="flex items-center gap-2">
                <span class="text-lg" aria-hidden="true">{{ $moodEmoji }}</span>
                <span
                    class="text-sm font-bold text-{{ $moodColor }}-600 dark:text-{{ $moodColor }}-400 capitalize transition-colors duration-300">
                    {{ $mood }}
                </span>
            </div>
        </div>
        @if ($showDetails)
            <div class="flex gap-1">
                @foreach (['awful', 'bad', 'normal', 'good', 'great'] as $level)
                    <div
                        class="flex-1 h-2 rounded-full {{ $mood === $level ? 'bg-' . $moodColor . '-500 dark:bg-' . $moodColor . '-400' : 'bg-gray-200 dark:bg-gray-700' }} transition-all duration-300">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Conditions -->
    @if (count($conditions) > 0)
        <div>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">
                Active Conditions
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($conditions as $condition)
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium glass-card-inner transition-colors duration-300">
                        @if (isset($condition['icon']))
                            <span class="mr-1" aria-hidden="true">{{ $condition['icon'] }}</span>
                        @endif
                        {{ $condition['name'] ?? $condition }}
                        @if (isset($condition['turns_remaining']))
                            <span class="ml-1 text-gray-500 dark:text-gray-400">({{ $condition['turns_remaining'] }}
                                turns)</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Warning Messages -->
    @if ($energy < 30)
        <div class="p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-xs text-red-700 dark:text-red-300 transition-colors duration-300">
                    Critical energy level! Rest is strongly recommended.
                </p>
            </div>
        </div>
    @elseif($energy < 50)
        <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 shrink-0 mt-0.5" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-yellow-700 dark:text-yellow-300 transition-colors duration-300">
                    Energy is getting low. Consider resting after this training.
                </p>
            </div>
        </div>
    @endif
</div>
