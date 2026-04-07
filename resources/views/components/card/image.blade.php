@props([
    'src' => null,
    'alt' => '',
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-lg bg-linear-to-br from-neutral-100 via-white to-neutral-200 dark:from-neutral-800 dark:via-neutral-900 dark:to-neutral-800']) }}>
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
            loading="lazy"
            decoding="async"
        >
    @endif

    {{ $slot }}

    @isset($overlay)
        {{ $overlay }}
    @endisset
</div>
