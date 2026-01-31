{{--
Component: QuickActions
Purpose: Floating action button with menu for quick access to frequent actions

Features:
  - Floating button positioned at bottom-right
  - Expandable menu with action items
  - Smooth animations on expand/collapse
  - Keyboard navigation (Tab, Enter, Escape)
  - Mobile-optimized touch targets
  - Game-aligned colors
  - Full accessibility

Props:
  - items (array): Array of action objects
    [{ icon: '🎯', label: 'Action Name', action: 'method-name' }, ...]
  - position (string): 'bottom-right', 'bottom-left', 'top-right', 'top-left' (default: bottom-right)
  - primaryIcon (string): Icon for primary button (default: '➕')
  - primaryLabel (string): Label for primary button
  - color (string): Color class (default: 'bg-blue-600')

Item Properties:
  - icon: Emoji or icon class
  - label: Action description
  - action: Method name to call or route
  - color: Optional override color class

Usage:
  <x-quick-actions 
      :items="[
          ['icon' => '🎯', 'label' => 'Start Training', 'action' => 'startTraining'],
          ['icon' => '🏁', 'label' => 'Race', 'action' => 'viewRaces'],
      ]"
      position="bottom-right"
      primaryIcon="➕"
      @action-selected="handleAction($event)"
  />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'items' => [],
    'position' => 'bottom-right',
    'primaryIcon' => '➕',
    'primaryLabel' => 'Actions',
    'color' => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600',
])

@php
    $positionMap = [
        'bottom-right' => 'bottom-8 right-8',
        'bottom-left' => 'bottom-8 left-8',
        'top-right' => 'top-8 right-8',
        'top-left' => 'top-8 left-8',
    ];
    $positionClass = $positionMap[$position] ?? $positionMap['bottom-right'];
@endphp

<div class="fixed {{ $positionClass }} z-40" x-data="quickActions({{ json_encode($items) }})" @click.outside="close()">

    {{-- Floating Action Menu Container --}}
    <div class="flex flex-col items-end gap-3" x-show="isOpen" x-transition>
        {{-- Menu Items --}}
        <template x-for="(item, index) in items" :key="index">
            <div class="flex items-center gap-2 animate-fade-in-up" :style="`animation-delay: ${index * 50}ms`">
                {{-- Label --}}
                <div
                    class="bg-gray-800 dark:bg-gray-700 text-white text-xs font-medium px-3 py-2 rounded-lg whitespace-nowrap shadow-lg">
                    <span x-text="item.label"></span>
                </div>

                {{-- Action Button --}}
                <button @click="executeAction(item)" :class="getItemColor(item)"
                    class="w-12 h-12 rounded-full flex items-center justify-center text-lg shadow-lg transition-transform duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900"
                    :aria-label="item.label" :title="item.label">
                    <span x-text="item.icon"></span>
                </button>
            </div>
        </template>
    </div>

    {{-- Primary Action Button --}}
    <button @click="toggle()" :class="isOpen ? 'scale-110 rotate-45' : 'scale-100 rotate-0'"
        class="{{ $color }} w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-xl transition-all duration-300 hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900"
        :aria-label="isOpen ? 'Close menu' : primaryLabel" :title="primaryLabel">
        <span x-text="'{{ $primaryIcon }}'"></span>
    </button>

    {{-- Backdrop (when open, closes on click) --}}
    <div x-show="isOpen" @click="close()" class="fixed inset-0 z-30"
        x-transition:enter="transition opacity duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition opacity duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true">
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/animations.css'])
    @endpush
    @vite(['resources/js/components/quick-actions.js'])
@endonce
