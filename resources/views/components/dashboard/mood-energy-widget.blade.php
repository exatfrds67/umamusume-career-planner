@props([
    'mood' => 'good', // great, good, normal, bad, awful
    'energy' => 72,
    'maxEnergy' => 100,
])

@php
    $moodConfig = [
        'great' => ['emoji' => '😄', 'label' => 'Great', 'color' => 'text-success-600 dark:text-success-400'],
        'good' => ['emoji' => '🙂', 'label' => 'Good', 'color' => 'text-primary-600 dark:text-primary-400'],
        'normal' => ['emoji' => '😐', 'label' => 'Normal', 'color' => 'text-warning-600 dark:text-warning-400'],
        'bad' => ['emoji' => '😔', 'label' => 'Bad', 'color' => 'text-error-600 dark:text-error-400'],
        'awful' => ['emoji' => '😢', 'label' => 'Awful', 'color' => 'text-error-700 dark:text-error-500'],
    ];

    $currentMood = $moodConfig[$mood] ?? $moodConfig['normal'];
    $energyPercentage = $maxEnergy > 0 ? ($energy / $maxEnergy) * 100 : 0;

    $energyColor = match (true) {
        $energyPercentage >= 70 => 'success',
        $energyPercentage >= 40 => 'warning',
        default => 'error',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow']) }}>
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Mood & Energy
        </h3>

        <div class="space-y-4">
            {{-- Mood Status --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl" role="img" aria-label="{{ $currentMood['label'] }} mood">
                        {{ $currentMood['emoji'] }}
                    </span>
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Mood</span>
                        <p class="text-base font-medium {{ $currentMood['color'] }}">
                            {{ $currentMood['label'] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Energy Bar --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Energy</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $energy }}/{{ $maxEnergy }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="h-3 rounded-full transition-all duration-300 {{ $energyColor === 'success' ? 'bg-success-500' : ($energyColor === 'warning' ? 'bg-warning-500' : 'bg-error-500') }}"
                        style="width: {{ $energyPercentage }}%" role="progressbar" aria-valuenow="{{ $energy }}"
                        aria-valuemin="0" aria-valuemax="{{ $maxEnergy }}" aria-label="Energy level"></div>
                </div>
            </div>

            {{-- Recovery Options --}}
            <button type="button" class="w-full btn btn-outline btn-sm mt-2">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                Recovery Options
            </button>
        </div>
    </div>
</div>
