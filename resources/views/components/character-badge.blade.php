@props([
    'type' => 'scenario',
    'value' => '',
])

@php
    $variant = match ($type) {
        'scenario' => 'secondary',
        'status'   => match ($value) {
            'active'    => 'success',
            'completed' => 'primary',
            'retired'   => 'warning',
            'archived'  => 'secondary',
            default     => 'secondary',
        },
        'seeded' => 'secondary',
        default => 'secondary',
    };

    $label = match ($type) {
        'scenario' => str_replace('_', ' ', strtoupper((string) $value)),
        'status'   => ucfirst((string) $value),
        'seeded'   => $value ? 'Seeded' : 'My Run',
        default    => (string) $value,
    };
@endphp

<x-badge :variant="$variant" {{ $attributes->merge(['class' => 'font-semibold']) }}>
    {{ $label }}
</x-badge>
