@props([
    'stat',
    'current',
    'max',
    'target',
    'factorBonus',
    'showIcon',
    'showPercentage',
    'showSoftCap',
    'showLabel',
    'size',
])

@php
    $statIcons = [
        'speed' => '⚡',
        'stamina' => '🌿',
        'power' => '🔥',
        'guts' => '❤️',
        'wit' => '💙',
        'wisdom' => '💙',
    ];
    $statColors = [
        'speed' => '#E879A0',
        'stamina' => '#10B981',
        'power' => '#F59E0B',
        'guts' => '#EF4444',
        'wit' => '#3B82F6',
        'wisdom' => '#3B82F6',
    ];
    $icon = $statIcons[strtolower($stat)] ?? '⭐';
    $color = $statColors[strtolower($stat)] ?? '#7C3AED';

    // Determine grade from value
    $val = (int) $current;
    $grade = match (true) {
        $val >= 1000 => 'S',
        $val >= 800 => 'A',
        $val >= 600 => 'B',
        $val >= 400 => 'C',
        $val >= 200 => 'D',
        $val >= 100 => 'E',
        default => 'F',
    };
@endphp

<div {{ $attributes->merge(['class' => 'stat-bar-container']) }} style="{{ $getStatStyle() }}">
    {{-- Label and Value --}}
    @if ($showLabel)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                @if ($showIcon)
                    <span style="font-size: 14px; line-height: 1;" aria-hidden="true">{{ $icon }}</span>
                @endif
                <span style="font-size: 13px; font-weight: 700; color: #4A3570; font-family: 'Nunito', sans-serif;">
                    {{ $getStatLabel() }}
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <x-grade-badge :grade="$grade" size="xs" />
                <span
                    style="font-size: 14px; font-weight: 800; color: {{ $color }}; font-family: 'Nunito', sans-serif; font-variant-numeric: tabular-nums;">
                    {{ number_format($current) }}
                </span>
                @if ($isAboveSoftCap())
                    <span style="font-size: 11px; color: #7C6FAB;">
                        ({{ number_format($getEffectiveValue()) }} eff.)
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Progress Bar --}}
    <div class="relative w-full rounded-full overflow-visible"
        style="height: 8px; background: #EDE9FE; border-radius: 99px;">

        {{-- Soft Cap Indicator --}}
        @if ($showSoftCap && $getSoftCapPercentage() < 100)
            <div class="absolute top-0 bottom-0 z-10"
                style="left: {{ $getSoftCapPercentage() }}%; width: 1px; background: rgba(124,58,237,0.25);"
                title="Soft cap at 1200 (diminishing returns above this)">
            </div>
        @endif

        {{-- Target Indicator --}}
        @if ($getTargetPercentage() !== null)
            <div class="absolute top-0 bottom-0 z-10"
                style="left: {{ $getTargetPercentage() }}%; width: 1px; background: {{ $color }}; opacity: 0.5;"
                title="Target: {{ number_format($target) }}">
            </div>
        @endif

        {{-- Progress Fill --}}
        <div class="h-full relative overflow-hidden"
            style="width: {{ min(100, $getPercentage()) }}%; background: linear-gradient(90deg, {{ $color }}99, {{ $color }}); border-radius: 99px; transition: width 0.6s cubic-bezier(.4,0,.2,1);">
        </div>
    </div>

    {{-- Factor Bonus Indicator --}}
    @if ($factorBonus && $factorBonus > 0)
        <div
            style="margin-top: 4px; display: flex; align-items: center; gap: 4px; font-size: 11px; color: {{ $color }};">
            <svg style="width: 12px; height: 12px;" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z" />
            </svg>
            <span>+{{ $factorBonus }} from factors</span>
        </div>
    @endif
</div>
