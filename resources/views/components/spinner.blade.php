{{--
Component: Spinner
Purpose: Loading indicator with accessibility
Props:
  - size (string, optional): Spinner size (xs|sm|md|lg|xl), default md
  - color (string, optional): Spinner color (primary|gray|white), default primary
  - label (string, optional): Screen reader label, default "Loading"
Usage:
  <x-spinner size="lg" />
  <x-spinner size="sm" color="white" label="Saving changes" />
Accessibility: WCAG 2.2 AA compliant, aria-label for screen readers, reduced motion support
--}}

@props([
    'size' => 'md',
    'color' => 'primary',
    'label' => 'Loading',
])

@php
    $sizes = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
        'xl' => 'h-12 w-12',
    ];
    
    $colors = [
        'primary' => 'text-primary-600 dark:text-primary-400',
        'gray' => 'text-neutral-500 dark:text-neutral-400',
        'white' => 'text-white',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<svg
    {{ $attributes->merge([
        'class' => "{$sizeClass} {$colorClass} animate-spin motion-reduce:animate-[spin_1.5s_linear_infinite]",
        'role' => 'status',
        'aria-label' => $label,
    ]) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    <span class="sr-only">{{ $label }}</span>
</svg>
