@props([
    'variant' => 'info',
    'autoDismiss' => true,
    'duration' => 5000,
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

    $classes = trim("$baseClasses $variantClasses shadow-lg " . ($attributes->get('class') ?? ''));

    $roleValue = match ($variant) {
        'error' => 'alert',
        'warning' => 'alert',
        default => 'status',
    };

    $ariaLabelText = $ariaLabel ?? ucfirst($variant) . ' notification';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="{{ $roleValue }}" aria-label="{{ $ariaLabelText }}"
    aria-live="{{ $variant === 'error' ? 'assertive' : 'polite' }}" x-data="{
        show: true,
        init() {
            @if ($autoDismiss) setTimeout(() => { this.show = false }, {{ $duration }}) @endif
        }
    }" x-show="show"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            @if ($variant === 'success')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif ($variant === 'warning')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @elseif ($variant === 'error')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>

        <div class="flex-1">
            {{ $slot }}
        </div>

        <button type="button" @click="show = false"
            class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
            aria-label="Dismiss notification">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
