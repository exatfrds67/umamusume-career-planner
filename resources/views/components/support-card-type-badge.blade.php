@props(['type'])

@php
    $colors = [
        'speed' => 'bg-blue-100 text-blue-800 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400',
        'stamina' => 'bg-green-100 text-green-800 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400',
        'power' => 'bg-red-100 text-red-800 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400',
        'guts' => 'bg-orange-100 text-orange-800 ring-orange-600/20 dark:bg-orange-900/30 dark:text-orange-400',
        'wit' => 'bg-purple-100 text-purple-800 ring-purple-600/20 dark:bg-purple-900/30 dark:text-purple-400',
        'friend' => 'bg-pink-100 text-pink-800 ring-pink-600/20 dark:bg-pink-900/30 dark:text-pink-400',
    ];
    $colorClass = $colors[$type] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {$colorClass}"]) }}>
    {{ ucfirst($type) }}
</span>
