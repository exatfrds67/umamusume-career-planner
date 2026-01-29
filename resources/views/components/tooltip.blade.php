@props([
    'content' => '',        // Tooltip text content
    'position' => 'top',    // 'top' | 'bottom' | 'left' | 'right'
    'delay' => 300,         // Hover delay in milliseconds
    'maxWidth' => 'xs',     // 'xs' | 'sm' | 'md' | 'lg' | 'none'
])

@php
    $maxWidthClasses = match($maxWidth) {
        'xs' => 'max-w-xs',
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'none' => '',
        default => 'max-w-xs',
    };
    
    // Arrow positioning based on tooltip position
    $arrowClasses = match($position) {
        'top' => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 rotate-45',
        'bottom' => 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 rotate-45',
        'left' => 'right-0 top-1/2 -translate-y-1/2 translate-x-1/2 rotate-45',
        'right' => 'left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 rotate-45',
        default => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 rotate-45',
    };
    
    // Tooltip positioning relative to trigger
    $tooltipPositionClasses = match($position) {
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    };
@endphp

<div 
    x-data="{
        show: false,
        timeout: null,
        
        showTooltip() {
            this.timeout = setTimeout(() => {
                this.show = true;
            }, {{ $delay }});
        },
        
        hideTooltip() {
            clearTimeout(this.timeout);
            this.show = false;
        }
    }"
    @mouseenter="showTooltip()"
    @mouseleave="hideTooltip()"
    @focus="showTooltip()"
    @blur="hideTooltip()"
    class="relative inline-block"
    {{ $attributes }}
>
    {{-- Trigger Element (slot content) --}}
    <div>
        {{ $slot }}
    </div>
    
    {{-- Tooltip Popup --}}
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        role="tooltip"
        class="absolute z-50 {{ $tooltipPositionClasses }} {{ $maxWidthClasses }}"
        style="pointer-events: none;"
    >
        <div class="relative px-3 py-2 text-sm text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg">
            {{-- Tooltip Content --}}
            <div class="relative z-10">
                {{ $content }}
            </div>
            
            {{-- Arrow --}}
            <div class="absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 {{ $arrowClasses }}" aria-hidden="true"></div>
        </div>
    </div>
</div>
