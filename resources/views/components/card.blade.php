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
        $classes .= ' transition-all duration-150 hover:-translate-y-0.5';
    }
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    </{{ $as }}>
