@props([])

<div {{ $attributes->merge(['class' => 'flex items-center justify-between border-t border-neutral-200 pt-3 dark:border-neutral-700']) }}>
    {{ $slot }}
</div>
