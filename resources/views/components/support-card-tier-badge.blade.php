@props(['tier', 'size' => 'sm'])

@php
    $sizeClasses = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $tierColors = [
        'S+' =>
            'bg-linear-to-r from-red-100 to-pink-100 text-red-800 dark:from-red-900 dark:to-pink-900 dark:text-red-200 border border-red-300 dark:border-red-700',
        'S' =>
            'bg-linear-to-r from-orange-100 to-yellow-100 text-orange-800 dark:from-orange-900 dark:to-yellow-900 dark:text-orange-200 border border-orange-300 dark:border-orange-700',
        'A' =>
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-300 dark:border-green-700',
        'B' =>
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700',
        'C' =>
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600',
    ];

    $colorClass = $tierColors[$tier] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span class="inline-flex items-center {{ $sizeClass }} rounded font-bold {{ $colorClass }}">
    {{ $tier }}
</span>
