@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'ariaLabel' => null,
])

@php
    $baseClasses = 'btn';

    $variantClasses = match ($variant) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'outline' => 'btn-outline',
        default => 'btn-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'btn-sm',
        'md' => 'btn-md',
        'lg' => 'btn-lg',
        default => 'btn-md',
    };

    $classes = trim("$baseClasses $variantClasses $sizeClasses " . ($attributes->get('class') ?? ''));

    $isDisabled = $disabled || $loading;

    $ariaAttributes = [
        'aria-label' => $ariaLabel,
        'aria-disabled' => $isDisabled ? 'true' : null,
        'aria-busy' => $loading ? 'true' : null,
    ];
@endphp

@if ($href && !$isDisabled)
    <a href="{{ $href }}" class="{{ $classes }}" role="button"
        @foreach ($ariaAttributes as $key => $value)
            @if ($value)
                {{ $key }}="{{ $value }}"
            @endif @endforeach
        {{ $attributes->except(['class', 'variant', 'size', 'loading', 'disabled', 'href', 'ariaLabel']) }}>
        @if ($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="sr-only">Loading...</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" @if ($isDisabled) disabled @endif
        @foreach ($ariaAttributes as $key => $value)
            @if ($value)
                {{ $key }}="{{ $value }}"
            @endif @endforeach
        {{ $attributes->except(['class', 'variant', 'size', 'type', 'loading', 'disabled', 'href', 'ariaLabel']) }}>
        @if ($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="sr-only">Loading...</span>
        @endif
        {{ $slot }}
    </button>
@endif
