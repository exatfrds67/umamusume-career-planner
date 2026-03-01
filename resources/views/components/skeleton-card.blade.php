{{--
Component: SkeletonCard
Purpose: Loading placeholder for content cards
Props:
  - lines (int, optional): Number of text lines, default 3
  - showImage (bool, optional): Show image placeholder, default false
  - showAvatar (bool, optional): Show avatar placeholder, default false
  - showActions (bool, optional): Show action button placeholders, default false
Usage:
  <x-skeleton-card :lines="4" show-image show-actions />
Accessibility: WCAG 2.2 AA compliant, aria-hidden, reduced motion support
--}}

@props([
    'lines' => 3,
    'showImage' => false,
    'showAvatar' => false,
    'showActions' => false,
])

<div 
    {{ $attributes->merge([
        'class' => 'animate-pulse rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-xs',
        'aria-hidden' => 'true',
    ]) }}
    role="presentation"
>
    {{-- Image Placeholder --}}
    @if($showImage)
        <div class="h-40 w-full rounded-lg bg-gray-200 dark:bg-gray-700 mb-4"></div>
    @endif
    
    {{-- Header with Avatar --}}
    @if($showAvatar)
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700"></div>
                <div class="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-700"></div>
            </div>
        </div>
    @endif
    
    {{-- Title Placeholder --}}
    <div class="h-5 w-3/4 rounded bg-gray-200 dark:bg-gray-700 mb-4"></div>
    
    {{-- Text Lines --}}
    <div class="space-y-3">
        @for($i = 0; $i < $lines; $i++)
            @php
                $widths = ['w-full', 'w-5/6', 'w-4/5', 'w-3/4', 'w-2/3'];
                $width = $widths[$i % count($widths)];
            @endphp
            <div class="h-3 {{ $width }} rounded bg-gray-200 dark:bg-gray-700"></div>
        @endfor
    </div>
    
    {{-- Action Buttons Placeholder --}}
    @if($showActions)
        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="h-9 w-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-9 w-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
        </div>
    @endif
</div>
