@props(['tier'])

@php
    $colors = [
        'S+' => 'bg-red-100 text-red-800 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400',
        'S' => 'bg-orange-100 text-orange-800 ring-orange-600/20 dark:bg-orange-900/30 dark:text-orange-400',
        'A' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-400',
        'B' => 'bg-green-100 text-green-800 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400',
        'C' => 'bg-gray-100 text-gray-800 ring-gray-600/20 dark:bg-gray-700 dark:text-gray-400',
    ];
    $colorClass = $colors[$tier] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {$colorClass}"]) }}>
    {{ $tier }}
</span>
