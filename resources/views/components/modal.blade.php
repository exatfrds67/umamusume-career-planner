{{--
Component: Modal
Purpose: Accessible modal dialog with focus trap
Props:
  - name (string, required): Modal identifier for Alpine events
  - title (string, optional): Modal title
  - size (string, optional): Modal size (sm|md|lg|xl|full), default md
  - closeable (bool, optional): Whether modal can be closed, default true
Usage:
  <x-modal name="confirm-delete" title="Confirm Delete">
      <p>Are you sure?</p>
      <x-slot:footer>
          <x-button @click="$dispatch('close-modal', 'confirm-delete')">Cancel</x-button>
      </x-slot:footer>
  </x-modal>
  Trigger: $dispatch('open-modal', 'confirm-delete')
Accessibility: WCAG 2.2 AA compliant, focus trap, Escape key closes
--}}

@props([
    'name',
    'title' => null,
    'size' => 'md',
    'closeable' => true,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    
    $modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div
    x-data="{ 
        open: false,
        focusables() {
            return [...this.$refs.panel.querySelectorAll('a, button, input:not([type=hidden]), textarea, select, details, [tabindex]:not([tabindex=-1])')]
                .filter(el => !el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
        },
        firstFocusable() { return this.focusables()[0]; },
        lastFocusable() { return this.focusables().slice(-1)[0]; },
        trapFocus(e) {
            if (e.key !== 'Tab') return;
            
            const first = this.firstFocusable();
            const last = this.lastFocusable();
            
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }"
    x-init="
        $watch('open', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
                $nextTick(() => firstFocusable()?.focus());
            } else {
                document.body.style.overflow = '';
            }
        })
    "
    @open-modal.window="$event.detail === '{{ $name }}' && (open = true)"
    @close-modal.window="$event.detail === '{{ $name }}' && (open = false)"
    @keydown.escape.window="@if($closeable) open = false @endif"
    {{ $attributes->merge(['class' => '']) }}
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm"
        @if($closeable) @click="open = false" @endif
        aria-hidden="true"
    ></div>
    
    {{-- Modal Container --}}
    <div
        x-show="open"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @if($title) aria-labelledby="modal-title-{{ $name }}" @endif
    >
        <div class="flex min-h-full items-center justify-center p-4">
            {{-- Modal Panel --}}
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-ref="panel"
                @keydown="trapFocus"
                @click.stop
                class="relative w-full {{ $modalSize }} transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl transition-all"
            >
                {{-- Header --}}
                @if($title || $closeable)
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        @if($title)
                            <h3 id="modal-title-{{ $name }}" class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $title }}
                            </h3>
                        @else
                            <div></div>
                        @endif
                        
                        @if($closeable)
                            <button
                                type="button"
                                @click="open = false"
                                class="rounded-lg p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                                aria-label="Close modal"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif
                
                {{-- Body --}}
                <div class="px-6 py-4">
                    {{ $slot }}
                </div>
                
                {{-- Footer (optional slot) --}}
                @if(isset($footer))
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
