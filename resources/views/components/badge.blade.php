@props([
    'variant' => 'primary',
])

@php
    $baseClasses = 'badge';

    $variantClasses = match ($variant) {
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        default => 'badge-primary',
    };

    $classes = trim("$baseClasses $variantClasses " . ($attributes->get('class') ?? ''));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
