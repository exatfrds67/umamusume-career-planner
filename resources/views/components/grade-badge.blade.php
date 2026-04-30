@props(['grade', 'size' => 'md', 'showLabel' => false, 'label' => null])

@php
    $gradeColors = [
        'S' => ['bg' => 'linear-gradient(135deg, #FCD34D, #F59E0B)', 'color' => '#fff'],
        'A' => ['bg' => 'linear-gradient(135deg, #F9A8D4, #E879A0)', 'color' => '#fff'],
        'B' => ['bg' => 'linear-gradient(135deg, #A78BFA, #7C3AED)', 'color' => '#fff'],
        'C' => ['bg' => 'linear-gradient(135deg, #60A5FA, #3B82F6)', 'color' => '#fff'],
        'D' => ['bg' => '#6B7280', 'color' => '#fff'],
        'E' => ['bg' => '#9CA3AF', 'color' => '#fff'],
        'F' => ['bg' => '#D1D5DB', 'color' => '#374151'],
        'G' => ['bg' => '#E5E7EB', 'color' => '#6B7280'],
    ];

    $cfg = $gradeColors[strtoupper((string) $grade)] ?? $gradeColors['G'];

    $padding = match ($size) {
        'xs', 'sm' => '3px 9px',
        'lg', 'xl' => '10px 16px',
        default => '3px 9px',
    };
    $fontSize = match ($size) {
        'xs' => '10px',
        'sm' => '11px',
        'lg', 'xl' => '14px',
        default => '11px',
    };
@endphp

<span {{ $attributes }}
    style="display: inline-flex; align-items: center; justify-content: center; background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }}; font-weight: 800; border-radius: 6px; padding: {{ $padding }}; font-size: {{ $fontSize }}; letter-spacing: 1px; font-family: 'Nunito', sans-serif; white-space: nowrap;"
    title="{{ $getGradeDescription() }}">
    {{ strtoupper((string) $grade) }}
    @if ($showLabel)
        <span
            style="margin-left: 4px; font-weight: 600; font-size: {{ $fontSize }};">{{ $label ?? $getGradeDescription() }}</span>
    @endif
</span>
