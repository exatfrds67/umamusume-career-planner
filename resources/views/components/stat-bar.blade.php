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

<div {{ $attributes->merge(['class' => 'stat-bar-container']) }}>
    {{-- Label and Value --}}
    @if ($showLabel)
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                @if ($showIcon)
                    <div
                        class="stat-icon-{{ $getStatColor() }} w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($getStatLabel(), 0, 1)) }}
                    </div>
                @endif
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    {{ $getStatLabel() }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100 tabular-nums">
                    {{ number_format($current) }}
                </span>
                @if ($isAboveSoftCap())
                    <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
                        ({{ number_format($getEffectiveValue()) }} eff.)
                    </span>
                @endif
                @if ($showPercentage)
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ number_format($getPercentage(), 1) }}%
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Progress Bar --}}
    <div
        class="relative w-full {{ $size === 'sm' ? 'h-2' : ($size === 'lg' ? 'h-4' : 'h-3') }} bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-visible">
        {{-- Soft Cap Indicator --}}
        @if ($showSoftCap && $getSoftCapPercentage() < 100)
            <div class="absolute top-0 bottom-0 w-0.5 bg-neutral-400 dark:bg-neutral-500 z-10"
                style="left: {{ $getSoftCapPercentage() }}%" title="Soft cap at 1200 (diminishing returns above this)">
                <div
                    class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-neutral-400 dark:bg-neutral-500 rounded-full">
                </div>
            </div>
        @endif

        {{-- Target Indicator --}}
        @if ($getTargetPercentage() !== null)
            <div class="absolute top-0 bottom-0 w-0.5 bg-{{ $getStatColor() }}-600 dark:bg-{{ $getStatColor() }}-400 z-10 opacity-50"
                style="left: {{ $getTargetPercentage() }}%" title="Target: {{ number_format($target) }}">
                <div
                    class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-{{ $getStatColor() }}-600 dark:bg-{{ $getStatColor() }}-400 rounded-full opacity-50">
                </div>
            </div>
        @endif

        {{-- Progress Fill --}}
        <div class="h-full bg-gradient-to-r from-{{ $getStatColor() }}-400 to-{{ $getStatColor() }}-500 rounded-full transition-all duration-300 ease-out relative overflow-hidden"
            style="width: {{ min(100, $getPercentage()) }}%">
            {{-- Shine effect --}}
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer">
            </div>

            {{-- Above soft cap indicator (different color) --}}
            @if ($isAboveSoftCap())
                <div class="absolute top-0 right-0 bottom-0 bg-{{ $getStatColor() }}-600/50 dark:bg-{{ $getStatColor() }}-700/50"
                    style="width: {{ (($current - 1200) / $current) * 100 }}%"
                    title="Diminishing returns (50% effectiveness)"></div>
            @endif
        </div>
    </div>

    {{-- Factor Bonus Indicator --}}
    @if ($factorBonus && $factorBonus > 0)
        <div
            class="mt-1 flex items-center gap-1 text-xs text-{{ $getStatColor() }}-600 dark:text-{{ $getStatColor() }}-400">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z" />
            </svg>
            <span>+{{ $factorBonus }} from factors</span>
        </div>
    @endif
</div>

@once
    @push('styles')
        @vite(['resources/css/components/stat-bar.css'])
    @endpush
@endonce
