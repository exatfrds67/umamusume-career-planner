@props([
    'variant' => 'info',
    'dismissible' => false,
    'ariaLabel' => null,
])

@php
    $baseClasses = 'alert';

    $variantClasses = match ($variant) {
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error' => 'alert-error',
        'info' => 'alert-info',
        default => 'alert-info',
    };

    $classes = trim("$baseClasses $variantClasses " . ($attributes->get('class') ?? ''));

    $roleValue = match ($variant) {
        'error' => 'alert',
        'warning' => 'alert',
        default => 'status',
    };

    $ariaLabelText = $ariaLabel ?? ucfirst($variant) . ' message';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="{{ $roleValue }}" aria-label="{{ $ariaLabelText }}"
    @if ($dismissible) x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90" @endif>
    <div class="flex items-start gap-3">
        <div class="flex-1">
            {{ $slot }}
        </div>

        @if ($dismissible)
            <button type="button" @click="show = false"
                class="shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
                aria-label="Dismiss alert">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
