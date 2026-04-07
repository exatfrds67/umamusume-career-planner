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

    // Shape icons reinforce grade tier for colour-blind users (aria-hidden — grade letter is the label)
    $iconChar = match ($gradeUpper) {
        'SS', 'S' => '★', // top tier: star
        'A' => '◆',       // second tier: diamond
        'B' => '▲',       // third tier: triangle
        default => null,
    };

    $classes = "grade-badge $gradeClass " . ($attributes->get('class') ?? '');

    $ariaLabelText = $ariaLabel ?? "Grade $gradeUpper";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $ariaLabelText }}" role="img">
    @if ($iconChar)
        <span class="text-[0.55rem] leading-none opacity-90" aria-hidden="true">{{ $iconChar }}</span>
    @endif
    {{ $gradeUpper }}
</span>
