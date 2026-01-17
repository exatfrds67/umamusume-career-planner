@props([
    'status' => 'default',
    'ariaLabel' => null,
])

@php
    $statusLower = strtolower($status);

    $statusClass = match ($statusLower) {
        'success', 'completed', 'active', 'online', 'on_track' => 'badge-success',
        'warning', 'pending', 'at_risk' => 'badge-warning',
        'error', 'failed', 'offline', 'off_track' => 'badge-error',
        'info', 'processing' => 'badge-primary',
        default => 'badge-secondary',
    };

    $classes = "badge $statusClass " . ($attributes->get('class') ?? '');

    $displayText = $slot->isEmpty() ? ucfirst(str_replace('_', ' ', $status)) : $slot;
    $ariaLabelText = $ariaLabel ?? "Status: $displayText";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $ariaLabelText }}">
    {{ $displayText }}
</span>
