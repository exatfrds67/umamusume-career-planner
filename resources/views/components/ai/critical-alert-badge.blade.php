@props(['alertCount' => 0, 'size' => 'md'])

@php
    $hasCriticalAlerts = $alertCount > 0;

    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };

    $badgeSizeClasses = match ($size) {
        'sm' => 'h-4 w-4 text-[10px]',
        'md' => 'h-5 w-5 text-xs',
        'lg' => 'h-6 w-6 text-sm',
        default => 'h-5 w-5 text-xs',
    };
@endphp

<button type="button" id="critical-alert-btn"
    {{ $attributes->merge([
        'class' =>
            '-m-2.5 p-2.5 relative transition-colors duration-200 ' .
            ($hasCriticalAlerts
                ? 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300'
                : 'text-neutral-400 hover:text-neutral-500 dark:text-neutral-300 dark:hover:text-neutral-100'),
        'aria-label' => $hasCriticalAlerts
            ? "View {$alertCount} critical " . Str::plural('alert', $alertCount)
            : 'No critical alerts',
        'title' => $hasCriticalAlerts
            ? "{$alertCount} critical " . Str::plural('alert', $alertCount) . ' - Click to view'
            : 'No critical alerts',
    ]) }}
    @click="$dispatch('open-advisory-panel', { section: 'alerts' })">
    <span class="sr-only">
        Critical Alert Badge
    </span>
    <span class="sr-only">
        @if ($hasCriticalAlerts)
            View {{ $alertCount }} critical {{ Str::plural('alert', $alertCount) }}
        @else
            No critical alerts
        @endif
    </span>

    {{-- Alert Icon --}}
    <svg class="{{ $sizeClasses }} {{ $hasCriticalAlerts ? 'critical-alert-icon' : '' }}" fill="none"
        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>

    {{-- Alert Count Badge --}}
    @if ($hasCriticalAlerts)
        <span
            class="absolute -top-1 -right-1 {{ $badgeSizeClasses }} flex items-center justify-center rounded-full bg-red-600 dark:bg-red-500 text-white font-bold shadow-lg critical-alert-badge ring-2 ring-white dark:ring-neutral-800"
            aria-hidden="true">
            {{ $alertCount > 99 ? '99+' : $alertCount }}
        </span>
    @endif
</button>

@once
    @push('styles')
        @vite(['resources/css/components/ai/critical-alert-badge.css'])
    @endpush
@endonce
