@props([
    'variant' => 'text',
    'lines' => 3,
    'showAvatar' => false,
])

@php
    $baseClasses = 'animate-pulse';
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }} role="status" aria-label="Loading content">
    <span class="sr-only">Loading...</span>

    @if ($variant === 'message')
        {{-- Chat message skeleton --}}
        <div class="flex items-start gap-3">
            @if ($showAvatar)
                <div class="w-8 h-8 bg-neutral-200 dark:bg-neutral-700 rounded-full shrink-0"></div>
            @endif
            <div class="flex-1 space-y-2 max-w-md">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/2"></div>
            </div>
        </div>
    @elseif ($variant === 'card')
        {{-- Card skeleton --}}
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4 space-y-3">
            <div class="flex items-center gap-3">
                @if ($showAvatar)
                    <div class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full shrink-0"></div>
                @endif
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3"></div>
                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-1/4"></div>
                </div>
            </div>
            @for ($i = 0; $i < $lines; $i++)
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded" style="width: {{ rand(60, 100) }}%"></div>
            @endfor
        </div>
    @else
        {{-- Text lines skeleton --}}
        <div class="space-y-2">
            @for ($i = 0; $i < $lines; $i++)
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded" style="width: {{ $i === $lines - 1 ? rand(40, 70) : rand(80, 100) }}%"></div>
            @endfor
        </div>
    @endif
</div>
