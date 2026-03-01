@props(['condition', 'trend', 'turnsActive', 'size', 'showTrend', 'showDuration'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    {{-- Condition Badge --}}
    <div class="condition-badge {{ $getSizeClasses() }} {{ $getConditionColor() }} rounded-full font-semibold text-white shadow-md flex items-center gap-1.5 transition-all hover:scale-105"
        title="{{ $getConditionDescription() }}">
        {{-- Trend Icon --}}
        @if ($showTrend && $getTrendIcon())
            <span class="text-lg leading-none">{{ $getTrendIcon() }}</span>
        @endif

        {{-- Condition Text --}}
        <span>{{ $condition }}</span>

        {{-- Duration --}}
        @if ($showDuration && $turnsActive)
            <span class="text-xs opacity-90">({{ $turnsActive }}T)</span>
        @endif
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/condition-badge.css'])
    @endpush
@endonce
