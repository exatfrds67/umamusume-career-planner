@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'border-b border-neutral-200 pb-3 dark:border-neutral-700']) }}>
    @if ($title)
        <h3 class="text-base font-black tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ $title }}
        </h3>
    @endif

    @isset($badges)
        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-neutral-600 dark:text-neutral-300">
            {{ $badges }}
        </div>
    @endisset

    @if ($subtitle)
        <div class="mt-2">
            {{ $subtitle }}
        </div>
    @endif

    {{ $slot }}
</div>
