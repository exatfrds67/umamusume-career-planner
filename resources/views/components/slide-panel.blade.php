{{--
Component: SlidePanel
Purpose: Side drawer/modal panel that slides in from edge

Features:
  - Slides from left or right edge
  - Overlay backdrop with close on click
  - Keyboard support (Escape to close)
  - Smooth animations
  - Responsive sizing
  - Full accessibility (ARIA labels, focus management)
  - Dark mode support

Props:
  - title (string): Panel title
  - position (string): 'left' or 'right' (default: 'right')
  - size (string): 'sm' (300px), 'md' (400px), 'lg' (600px) - default: 'md'
  - closeOnBackdrop (bool): Close when clicking backdrop (default: true)
  - showHeader (bool): Show title header (default: true)

Usage:
  <x-slide-panel title="Settings" position="right" size="md" @panel-close="handleClose()">
      Panel content goes here
  </x-slide-panel>

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'title' => 'Panel',
    'position' => 'right',
    'size' => 'md',
    'closeOnBackdrop' => true,
    'showHeader' => true,
])

@php
    $sizeMap = [
        'sm' => 'w-[300px]',
        'md' => 'w-[400px]',
        'lg' => 'w-[600px]',
    ];
    $panelWidth = $sizeMap[$size] ?? $sizeMap['md'];
    $positionClasses = $position === 'left' ? 'left-0 slide-in-left' : 'right-0 slide-in-right';
@endphp

<div x-data="slidePanel()" @keydown.escape="close()" class="fixed inset-0 z-50 overflow-hidden" style="display: none;"
    x-show="isOpen" @click.self="handleBackdropClick()">

    {{-- Backdrop Overlay --}}
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70 transition-opacity duration-300"
        @click="handleBackdropClick()" x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" aria-hidden="true">
    </div>

    {{-- Panel Container --}}
    <div class="fixed inset-y-0 {{ $positionClasses }} {{ $panelWidth }} bg-white dark:bg-neutral-800 shadow-2xl flex flex-col z-50"
        x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        :x-transition:enter-start="position === 'left' ? '-translate-x-full' : 'translate-x-full'"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
        :x-transition:leave-start="'translate-x-0'"
        :x-transition:leave-end="position === 'left' ? '-translate-x-full' : 'translate-x-full'" role="dialog"
        aria-modal="true" :aria-label="title" @click.stop>

        {{-- Header --}}
        @if ($showHeader)
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 shrink-0">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                    {{ $title }}
                </h2>
                <button @click="close()"
                    class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                    aria-label="Close panel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- Content Area --}}
        <div class="flex-1 overflow-y-auto px-6 py-4">
            {{ $slot }}
        </div>

        {{-- Footer Slot (optional) --}}
        @if ($slot->has('footer'))
            <div
                class="border-t border-neutral-200 dark:border-neutral-700 px-6 py-4 shrink-0 bg-neutral-50 dark:bg-neutral-900/50">
                {{ $slot->get('footer') }}
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/animations.css'])
    @endpush
    @vite(['resources/js/components/slide-panel.js'])
@endonce
