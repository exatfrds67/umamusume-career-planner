@props([
    'percentage' => 0,
    'showValue' => true,
])

@php
    // Determine color based on readiness percentage
    $colorClass = match(true) {
        $percentage >= 90 => 'bg-success-100 text-success-700 dark:bg-success-700/30 dark:text-success-200',
        $percentage >= 70 => 'bg-primary-100 text-primary-700 dark:bg-primary-700/30 dark:text-primary-200',
        $percentage >= 50 => 'bg-warning-100 text-warning-700 dark:bg-warning-700/30 dark:text-warning-200',
        default => 'bg-error-100 text-error-700 dark:bg-error-700/30 dark:text-error-200',
    };
    
    $label = match(true) {
        $percentage >= 90 => 'Ready',
        $percentage >= 70 => 'Good',
        $percentage >= 50 => 'Moderate',
        default => 'Low',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium $colorClass"]) }}>
    @if($showValue)
        <span>{{ number_format($percentage, 0) }}%</span>
    @endif
    <span>{{ $label }}</span>
</span>
