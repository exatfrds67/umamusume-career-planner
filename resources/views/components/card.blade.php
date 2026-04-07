@props([
    'as' => 'div',
    'padding' => true,
    'hover' => false,
])

@php
    $classes = 'card';

    if ($padding) {
        $classes .= ' p-4';
    }

    if ($hover) {
        $classes .= ' transition duration-200 hover:-translate-y-0.5 hover:shadow-lg';
    }
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $as }}>
