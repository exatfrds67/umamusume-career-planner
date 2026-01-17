@props([
    'value' => 0,
    'max' => 1200,
    'type' => 'speed', // speed, stamina, power, guts, wisdom
    'label' => null,
    'showValue' => true,
])

@php
    $percentage = min(100, ($value / $max) * 100);
    $colors = [
        'speed' => 'bg-blue-500',
        'stamina' => 'bg-orange-500',
        'power' => 'bg-pink-500',
        'guts' => 'bg-yellow-500',
        'wisdom' => 'bg-green-500',
    ];
    $bgColor = $colors[$type] ?? 'bg-gray-500';
@endphp

<div {{ $attributes->merge(['class' => 'stat-bar']) }}>
    @if($label)
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $label }}</span>
            @if($showValue)
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($value) }}</span>
            @endif
        </div>
    @endif
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div class="{{ $bgColor }} h-2.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
    </div>
</div>
