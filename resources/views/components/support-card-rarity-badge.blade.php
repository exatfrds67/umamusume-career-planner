@props(['rarity'])

@php
    $colors = [
        'SSR' => 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white',
        'SR' => 'bg-gradient-to-r from-purple-400 to-pink-500 text-white',
        'R' => 'bg-gradient-to-r from-blue-400 to-cyan-500 text-white',
    ];
    $colorClass = $colors[$rarity] ?? 'bg-gray-500 text-white';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center rounded px-2 py-1 text-xs font-bold shadow-sm {$colorClass}"]) }}>
    {{ $rarity }}
</span>
