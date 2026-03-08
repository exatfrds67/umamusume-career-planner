@props(['rarity', 'size' => 'sm'])

@php
    $sizeClasses = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $rarityColors = [
        'SSR' =>
            'bg-linear-to-r from-yellow-100 to-amber-100 text-amber-800 dark:from-yellow-900 dark:to-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700',
        'SR' =>
            'bg-linear-to-r from-purple-100 to-pink-100 text-purple-800 dark:from-purple-900 dark:to-pink-900 dark:text-purple-200 border border-purple-300 dark:border-purple-700',
        'R' =>
            'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-600',
    ];

    $colorClass = $rarityColors[$rarity] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span class="inline-flex items-center {{ $sizeClass }} rounded font-bold {{ $colorClass }}">
    {{ $rarity }}
</span>
