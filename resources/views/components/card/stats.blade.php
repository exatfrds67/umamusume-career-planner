@props([])

<ul {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-x-3 gap-y-2 text-xs', 'role' => 'list']) }}>
    {{ $slot }}
</ul>
