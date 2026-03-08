@props([
    'title' => 'Something went wrong',
    'message' => null,
    'retryable' => false,
    'icon' => 'warning',
])

@php
    $iconColor = match ($icon) {
        'warning' => 'text-amber-500',
        'error' => 'text-red-500',
        'offline' => 'text-neutral-400',
        default => 'text-amber-500',
    };

    $bgColor = match ($icon) {
        'warning' => 'bg-amber-100 dark:bg-amber-900/30',
        'error' => 'bg-red-100 dark:bg-red-900/30',
        'offline' => 'bg-neutral-100 dark:bg-neutral-800',
        default => 'bg-amber-100 dark:bg-amber-900/30',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-6']) }} role="alert">
    <div class="w-12 h-12 {{ $bgColor }} rounded-full flex items-center justify-center mb-3">
        @if ($icon === 'warning')
            <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.924-.833-2.694 0L4.07 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        @elseif ($icon === 'error')
            <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($icon === 'offline')
            <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414" />
            </svg>
        @endif
    </div>

    <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">{{ $title }}</h4>

    @if ($message)
        <p class="text-xs text-neutral-600 dark:text-neutral-400 max-w-xs mb-3">{{ $message }}</p>
    @endif

    @if ($retryable)
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
