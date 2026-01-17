@props([
    'grade' => 'C',
    'ariaLabel' => null,
])

@php
    $gradeUpper = strtoupper($grade);
    $gradeClass = match ($gradeUpper) {
        'SS' => 'grade-ss',
        'S' => 'grade-s',
        'A' => 'grade-a',
        'B' => 'grade-b',
        'C' => 'grade-c',
        'D' => 'grade-d',
        'E' => 'grade-e',
        'F' => 'grade-f',
        'G' => 'grade-g',
        default => 'grade-c',
    };

    $classes = "grade-badge $gradeClass " . ($attributes->get('class') ?? '');

    $ariaLabelText = $ariaLabel ?? "Grade $gradeUpper";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $ariaLabelText }}" role="img">
    {{ $gradeUpper }}
</span>
