@props(['rarity', 'size' => 'sm'])

@php
    $variantMap = [
        'SSR' => 'rarity-ssr',
        'SR'  => 'rarity-sr',
        'R'   => 'rarity-r',
    ];
    $variant = $variantMap[$rarity] ?? 'neutral';
    // Use !important overrides so .badge base CSS (unlayered, higher cascade priority) is correctly superseded.
    $sizeOverride = match ($size) {
        'xs'    => '!text-xs !px-1.5 !py-0.5',
        'md'    => '!text-sm !px-2.5 !py-1',
        default => '!text-xs !px-2 !py-0.5',
    };
@endphp

<x-badge :variant="$variant" class="{{ $sizeOverride }} !rounded !font-bold">
    {{ $rarity }}
</x-badge>
