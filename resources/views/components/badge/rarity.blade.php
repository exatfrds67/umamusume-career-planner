@props([
    'rarity' => 'R',
    'ariaLabel' => null,
])

@php
    $rarityUpper = strtoupper($rarity);

    $rarityClass = match ($rarityUpper) {
        'SSR' => 'badge-warning',
        'SR' => 'badge-secondary',
        'R' => 'badge-primary',
        'NORMAL' => 'badge-primary',
        'RARE' => 'badge-secondary',
        'UNIQUE' => 'badge-warning',
        default => 'badge-primary',
    };

    $classes = "badge $rarityClass " . ($attributes->get('class') ?? '');

    $ariaLabelText = $ariaLabel ?? "Rarity $rarityUpper";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $ariaLabelText }}">
    {{ $rarityUpper }}
</span>
