@props([
    'mood' => 'good', // great, good, normal, bad, very_bad
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

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl w-full']) }}>
    <div class="px-6 pt-6 pb-8">
        <h3 class="text-lg font-medium leading-6 text-neutral-900 dark:text-white mb-4">
            Mood & Energy
        </h3>

        <div class="w-full space-y-4">
            {{-- Mood Status using ConditionBadge --}}
            <div class="flex items-center justify-between w-full">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Mood</span>
                <x-condition-badge :condition="strtoupper($mood)" trend="flat" :turns-active="0" :show-trend="false" />
            </div>

            <hr class="border-neutral-200 dark:border-neutral-700/60">

            {{-- Energy using EnergyGauge --}}
            <div class="w-full pt-0.5">
                <x-energy-gauge :value="$energyPercentage" :show-icon="false" :trend="$energyPercentage >= 70 ? 'up' : ($energyPercentage >= 40 ? 'flat' : 'down')" />
            </div>


        </div>
    </div>
</div>
