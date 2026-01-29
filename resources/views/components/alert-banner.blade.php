{{--
Component: AlertBanner
Purpose: Full-width alert banner for important messages
Props:
  - type (string, optional): Alert type (success|error|warning|info), default info
  - title (string, optional): Alert title
  - dismissible (bool, optional): Whether alert can be dismissed, default true
Usage:
  <x-alert-banner type="warning" title="Maintenance">
      System will be unavailable from 2-4 AM.
  </x-alert-banner>
Accessibility: WCAG 2.2 AA compliant, role="alert", visible focus on dismiss
--}}

@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
    $typeConfig = [
        'success' => [
            'bg' => 'bg-success-50 dark:bg-success-900/30',
            'border' => 'border-success-200 dark:border-success-800',
            'icon' => 'text-success-500',
            'title' => 'text-success-800 dark:text-success-200',
            'text' => 'text-success-700 dark:text-success-300',
            'button' => 'text-success-500 hover:bg-success-100 dark:hover:bg-success-900/50 focus:ring-success-600',
        ],
        'error' => [
            'bg' => 'bg-error-50 dark:bg-error-900/30',
            'border' => 'border-error-200 dark:border-error-800',
            'icon' => 'text-error-500',
            'title' => 'text-error-800 dark:text-error-200',
            'text' => 'text-error-700 dark:text-error-300',
            'button' => 'text-error-500 hover:bg-error-100 dark:hover:bg-error-900/50 focus:ring-error-600',
        ],
        'warning' => [
            'bg' => 'bg-warning-50 dark:bg-warning-900/30',
            'border' => 'border-warning-200 dark:border-warning-800',
            'icon' => 'text-warning-500',
            'title' => 'text-warning-800 dark:text-warning-200',
            'text' => 'text-warning-700 dark:text-warning-300',
            'button' => 'text-warning-500 hover:bg-warning-100 dark:hover:bg-warning-900/50 focus:ring-warning-600',
        ],
        'info' => [
            'bg' => 'bg-info-50 dark:bg-info-900/30',
            'border' => 'border-info-200 dark:border-info-800',
            'icon' => 'text-info-500',
            'title' => 'text-info-800 dark:text-info-200',
            'text' => 'text-info-700 dark:text-info-300',
            'button' => 'text-info-500 hover:bg-info-100 dark:hover:bg-info-900/50 focus:ring-info-600',
        ],
    ];
    
    $config = $typeConfig[$type] ?? $typeConfig['info'];
    
    $icons = [
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />',
        'error' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />',
        'warning' => '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />',
        'info' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />',
    ];
    
    $icon = $icons[$type] ?? $icons['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="-translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="-translate-y-full opacity-0"
    {{ $attributes->merge([
        'class' => "rounded-lg border p-4 {$config['bg']} {$config['border']}",
        'role' => 'alert',
    ]) }}
>
    <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="shrink-0">
            <svg class="h-5 w-5 {{ $config['icon'] }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                {!! $icon !!}
            </svg>
        </div>
        
        {{-- Content --}}
        <div class="flex-1 min-w-0">
            @if($title)
                <h3 class="text-sm font-semibold {{ $config['title'] }}">
                    {{ $title }}
                </h3>
            @endif
            <div class="text-sm {{ $config['text'] }} {{ $title ? 'mt-1' : '' }}">
                {{ $slot }}
            </div>
        </div>
        
        {{-- Dismiss Button --}}
        @if($dismissible)
            <div class="shrink-0">
                <button
                    type="button"
                    @click="show = false"
                    class="inline-flex rounded-md p-1.5 {{ $config['button'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                    aria-label="Dismiss alert"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
