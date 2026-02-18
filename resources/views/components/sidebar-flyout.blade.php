@props([
    'text' => '',
    'icon' => '',
])

<div x-data="{
    flyoutShow: false,
    flyoutStyle: {},
    showTimeout: null,
    show() {
        if (!$store.sidebar.minimized) return;
        const rect = $el.getBoundingClientRect();
        const viewportH = window.innerHeight;
        const spaceBelow = viewportH - rect.top;
        const placeAbove = spaceBelow < 200;

        this.flyoutStyle = {
            position: 'fixed',
            left: (rect.right + 8) + 'px',
            zIndex: '99999',
            ...(placeAbove ?
                { bottom: (viewportH - rect.bottom) + 'px' } :
                { top: rect.top + 'px' }),
        };
        this.flyoutShow = true;
    },
    hide() {
        if (this.showTimeout) {
            clearTimeout(this.showTimeout);
            this.showTimeout = null;
        }
        this.flyoutShow = false;
    }
}" @mouseenter="showTimeout = setTimeout(() => show(), 100)" @mouseleave="hide()"
    class="relative inline-block w-full">

    {{-- Icon trigger --}}
    {{ $trigger }}

    {{-- Flyout menu teleported to body --}}
    <template x-teleport="body">
        <div x-show="flyoutShow" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" :style="flyoutStyle" style="display: none;"
            @mouseenter="flyoutShow = true" @mouseleave="hide()"
            class="min-w-48 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 py-1"
            role="menu" :aria-label="'{{ $text }} submenu'">

            {{-- Header --}}
            <div
                class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                {{ $text }}
            </div>

            {{-- Menu items --}}
            {{ $slot }}

            {{-- Arrow pointing left --}}
            <div
                class="absolute w-2 h-2 rotate-45 -left-1 top-4 bg-white dark:bg-gray-800 border-l border-b border-gray-200 dark:border-gray-600">
            </div>
        </div>
    </template>
</div>
