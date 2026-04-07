@props([])

<div {{ $attributes->merge(['class' => 'rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2 text-xs dark:border-neutral-700 dark:bg-neutral-800']) }}>
    {{ $slot }}
</div>
