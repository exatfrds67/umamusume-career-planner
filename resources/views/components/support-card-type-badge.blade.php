@props(['type', 'size' => 'sm'])

@php
    $variant = 'type-' . strtolower($type);
    // Use !important overrides so .badge base CSS (unlayered, higher cascade priority) is correctly superseded.
    // font-medium omitted — .badge already sets font-weight: 500 (= font-medium), so no override needed.
    $sizeOverride = match ($size) {
        'xs'    => '!text-xs !px-1.5 !py-0.5',
        'md'    => '!text-sm !px-2.5 !py-1',
        default => '!text-xs !px-2 !py-0.5',
    };
@endphp

<x-badge :variant="$variant" class="{{ $sizeOverride }} !rounded">
    <span class="w-2 h-2 rounded-full bg-current opacity-70" aria-hidden="true"></span>
    {{ ucfirst($type) }}
</x-badge>
