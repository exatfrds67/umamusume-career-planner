@props(['tier', 'size' => 'sm'])

@php
    $variant = match ($tier) {
        'S+' => 'tier-s-plus',
        'S'  => 'tier-s',
        'A'  => 'tier-a',
        'B'  => 'tier-b',
        'C'  => 'tier-c',
        default => 'neutral',
    };
    // Use !important overrides so .badge base CSS (unlayered, higher cascade priority) is correctly superseded.
    $sizeOverride = match ($size) {
        'xs'    => '!text-xs !px-1.5 !py-0.5',
        'md'    => '!text-sm !px-2.5 !py-1',
        default => '!text-xs !px-2 !py-0.5',
    };
    $tierIcon = match ($tier) {
        'S+' => '👑',
        'S'  => '★',
        'A'  => '◆',
        'B'  => '▲',
        default => null,
    };
@endphp

<x-badge :variant="$variant" class="{{ $sizeOverride }} !rounded !font-bold">
    @if ($tierIcon)
        <span aria-hidden="true">{{ $tierIcon }}</span>
    @endif
    {{ $tier }}
</x-badge>
