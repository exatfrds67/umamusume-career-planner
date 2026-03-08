@props([
    'grade' => 'B', // SS, S, A, B, C, D, E, F, G
])

@php
    $gradeColors = [
        'SS' => 'bg-linear-to-r from-yellow-400 to-amber-500 text-white',
        'S' => 'bg-linear-to-r from-purple-500 to-pink-500 text-white',
        'A' => 'bg-red-500 text-white',
        'B' => 'bg-orange-500 text-white',
        'C' => 'bg-yellow-500 text-neutral-900',
        'D' => 'bg-green-500 text-white',
        'E' => 'bg-blue-500 text-white',
        'F' => 'bg-neutral-500 text-white',
        'G' => 'bg-neutral-500 text-white',
    ];
    $colorClass = $gradeColors[$grade] ?? 'bg-neutral-400 text-white';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded $colorClass"]) }}>
    {{ $grade }}
</span>
