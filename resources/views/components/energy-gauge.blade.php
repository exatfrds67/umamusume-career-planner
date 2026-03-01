@props(['value', 'trend', 'showIcon', 'showTrend', 'size'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 w-full ' . $getSizeClasses()]) }}>
    {{-- Energy Icon --}}
    @if ($showIcon)
        <div
            class="shrink-0 w-10 h-10 rounded-full {{ match ($getStatus()) {
                'high' => 'bg-green-100 dark:bg-green-900/30',
                'medium' => 'bg-amber-100 dark:bg-amber-900/30',
                'low' => 'bg-red-100 dark:bg-red-900/30',
                default => 'bg-gray-100 dark:bg-gray-800',
            } }} flex items-center justify-center">
            <svg class="w-6 h-6 {{ $getColorClasses() }}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    @endif

    {{-- Energy Display --}}
    <div class="flex-1 min-w-0 w-full">
        {{-- Label and Value --}}
        <div class="flex items-center justify-between mb-1.5 w-full">
            <span id="energy-label" class="text-sm font-medium text-gray-700 dark:text-gray-300">Energy</span>
            <div class="flex items-center gap-1.5">
                <span class="text-lg font-bold {{ $getColorClasses() }}">
                    {{ $value }}%
                </span>

                {{-- Trend Arrow --}}
                @if ($showTrend)
                    <span class="text-xl font-bold {{ $getTrendColorClasses() }}" title="Trend: {{ $trend }}">
                        {{ $getTrendArrow() }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden w-full">
            {{-- Energy Fill --}}
            <div class="{{ $getProgressColorClasses() }} h-full rounded-full transition-all duration-500 ease-out relative"
                style="width: {{ $value }}%" role="progressbar" aria-valuenow="{{ $value }}"
                aria-valuemin="0" aria-valuemax="100" aria-label="Energy: {{ $value }}%"
                aria-labelledby="energy-label">
                {{-- Shimmer Effect for High Energy --}}
                @if ($getStatus() === 'high')
                    <div
                        class="absolute inset-0 bg-linear-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                    </div>
                @endif

                {{-- Pulse Effect for Low Energy --}}
                @if ($getStatus() === 'low')
                    <div class="absolute inset-0 bg-red-400 animate-pulse"></div>
                @endif
            </div>

            {{-- Warning Threshold Markers --}}
            <div class="absolute top-0 bottom-0 left-[40%] w-0.5 bg-amber-400/50" title="Low energy threshold"></div>
            <div class="absolute top-0 bottom-0 left-[70%] w-0.5 bg-green-400/50" title="Good energy threshold"></div>
        </div>

        {{-- Status Text --}}
        <div class="mt-2 mb-1 text-xs font-medium text-gray-600 dark:text-gray-300">
            {{ match ($getStatus()) {
                'high' => '✓ Good condition',
                'medium' => '⚠ Moderate energy',
                'low' => '⚠ Low energy — rest recommended',
                default => 'Unknown',
            } }}
        </div>
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/gauges.css'])
    @endpush
@endonce
