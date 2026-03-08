@props(['type', 'size' => 'sm'])

@php
    $sizeClasses = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $typeColors = [
        'speed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'stamina' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'power' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'guts' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'wit' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'friend' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    ];

    $colorClass = $typeColors[strtolower($type)] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span class="inline-flex items-center {{ $sizeClass }} rounded font-medium {{ $colorClass }}">
    {{ ucfirst($type) }}
</span>
