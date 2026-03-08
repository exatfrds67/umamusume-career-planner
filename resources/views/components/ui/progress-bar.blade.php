@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showPercentage' => true,
    'size' => 'md', // sm, md, lg
    'color' => 'primary', // primary, success, warning, error
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;
    
    $sizes = [
        'sm' => 'h-1.5',
        'md' => 'h-2.5',
        'lg' => 'h-4',
    ];
    
    $colors = [
        'primary' => 'bg-primary-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'progress-bar-wrapper']) }}>
    @if($label)
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
            @if($showPercentage)
                <span class="text-sm font-semibold text-neutral-900 dark:text-white">{{ number_format($percentage, 0) }}%</span>
            @endif
        </div>
    @endif
    <div 
        class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full {{ $sizeClass }} overflow-hidden"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
        aria-label="{{ $label ?? 'Progress' }}"
    >
        <div 
            class="{{ $colorClass }} {{ $sizeClass }} rounded-full transition-all duration-300 ease-out" 
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
