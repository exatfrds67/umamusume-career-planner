@props([
    'variant' => 'default',
])

@php
    $baseClasses = 'card';

    $variantClasses = match ($variant) {
        'elevated' => 'shadow-lg',
        'outlined' => 'shadow-none border-2',
        default => '',
    };

    $classes = trim("$baseClasses $variantClasses " . ($attributes->get('class') ?? ''));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
