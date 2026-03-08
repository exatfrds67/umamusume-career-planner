@props([
    'show' => false,            // Controls visibility (use x-data binding)
    'title' => 'Confirm Action', // Dialog title
    'message' => '',            // Confirmation message
    'confirmText' => 'Confirm', // Confirm button text
    'cancelText' => 'Cancel',   // Cancel button text
    'variant' => 'warning',     // 'warning' | 'danger' | 'info' | 'success'
    'confirmAction' => '',      // Alpine.js method to call on confirm
    'cancelAction' => '',       // Alpine.js method to call on cancel
])

@php
    $iconClasses = match($variant) {
        'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        'warning' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
        'success' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        'info' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        default => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
    };
    
    $confirmButtonClasses = match($variant) {
        'danger' => 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-500 dark:hover:bg-yellow-600 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white',
        'info' => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white',
        default => 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-500 dark:hover:bg-yellow-600 text-white',
    };
    
    $iconSvg = match($variant) {
        'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
    };
@endphp

{{-- Backdrop --}}
<div 
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    @keydown.escape.window="show = false; {{ $cancelAction ? $cancelAction . '()' : '' }}"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-dialog-title"
    {{ $attributes }}
>
    {{-- Dialog Container --}}
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click.away="show = false; {{ $cancelAction ? $cancelAction . '()' : '' }}"
        class="relative w-full max-w-lg mx-4 bg-white dark:bg-neutral-800 rounded-lg shadow-xl overflow-hidden"
    >
        <div class="p-6">
            <div class="flex items-start gap-4">
                {{-- Icon --}}
                <div class="shrink-0 w-12 h-12 rounded-full {{ $iconClasses }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $iconSvg !!}
                    </svg>
                </div>
                
                {{-- Content --}}
                <div class="flex-1">
                    <h3 id="confirm-dialog-title" class="text-lg font-semibold text-neutral-900 dark:text-white">
                        {{ $title }}
                    </h3>
                    
                    @if($message)
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            {{ $message }}
                        </p>
                    @endif
                    
                    {{-- Custom Slot Content --}}
                    @if($slot->isNotEmpty())
                        <div class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
                            {{ $slot }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50">
            <button 
                type="button"
                @click="show = false; {{ $cancelAction ? $cancelAction . '()' : '' }}"
                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition-colors duration-200"
            >
                {{ $cancelText }}
            </button>
            
            <button 
                type="button"
                @click="show = false; {{ $confirmAction ? $confirmAction . '()' : '' }}"
                class="px-4 py-2 text-sm font-medium rounded-md focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition-all duration-200 {{ $confirmButtonClasses }}"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
