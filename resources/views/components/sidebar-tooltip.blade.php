@props([
    'text' => '',
    'position' => 'right',
    'delay' => 200,
])

<div x-data="{
    show: false,
    timeout: null,
    enter() {
        this.timeout = setTimeout(() => {
            this.show = true;
        }, {{ $delay }});
    },
    leave() {
        clearTimeout(this.timeout);
        this.show = false;
    }
}" @mouseenter="enter()" @mouseleave="leave()" @focus="enter()" @blur="leave()"
    class="relative inline-block w-full">
    {{ $slot }}

    <div x-show="show" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-2" @class([
            'absolute z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none',
            'left-full ml-2 top-1/2 -translate-y-1/2' => $position === 'right',
            'right-full mr-2 top-1/2 -translate-y-1/2' => $position === 'left',
        ]) role="tooltip"
        style="display: none;">
        {{ $text }}

        <!-- Arrow -->
        <div @class([
            'absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45',
            '-left-1 top-1/2 -translate-y-1/2' => $position === 'right',
            '-right-1 top-1/2 -translate-y-1/2' => $position === 'left',
        ])></div>
    </div>
</div>
