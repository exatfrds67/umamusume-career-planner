@props([
    'href' => '#',
    'active' => false,
    'icon' => '',
    'label' => '',
])

<div x-data>
    <!-- Expanded State -->
    <a x-show="!$store.sidebar.minimized" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" href="{{ $href }}" @class([
            'group flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 transition-colors',
            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' => $active,
            'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' => !$active,
        ])
        aria-current="{{ $active ? 'page' : 'false' }}">
        @if ($icon)
            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                {!! $icon !!}
            </svg>
        @endif
        <span>{{ $label }}</span>
    </a>

    <!-- Minimized State with Tooltip -->
    <div x-show="$store.sidebar.minimized" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <x-sidebar-tooltip :text="$label">
            <a href="{{ $href }}" @class([
                'group flex justify-center rounded-md p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500',
                'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' => $active,
                'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' => !$active,
            ]) aria-current="{{ $active ? 'page' : 'false' }}">
                @if ($icon)
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        {!! $icon !!}
                    </svg>
                @endif
            </a>
        </x-sidebar-tooltip>
    </div>
</div>
