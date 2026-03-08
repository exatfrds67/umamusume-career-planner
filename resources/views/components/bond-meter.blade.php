@props(['value', 'threshold', 'showLabel', 'showPercentage', 'size'])

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    {{-- Header with label and percentage --}}
    @if ($showLabel || $showPercentage)
        <div class="flex items-center justify-between mb-1.5">
            @if ($showLabel)
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4 {{ $isThresholdReached() ? 'text-pink-500' : 'text-neutral-400' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd" />
                    </svg>
                    Bond Level
                </span>
            @endif

            @if ($showPercentage)
                <span class="text-sm font-semibold {{ $getTextColorClasses() }}">
                    {{ $value }}%
                    @if ($isThresholdReached())
                        <span class="ml-1 text-xs">✨</span>
                    @endif
                </span>
            @endif
        </div>
    @endif

    {{-- Progress Bar --}}
    <div class="relative {{ $getSizeClasses() }} bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
        {{-- Progress Fill --}}
        <div class="{{ $getProgressColorClasses() }} h-full rounded-full transition-all duration-500 ease-out relative"
            style="width: {{ $value }}%" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0"
            aria-valuemax="100" aria-label="Bond level: {{ $value }}%">
            {{-- Shimmer Effect --}}
            @if ($isThresholdReached())
                <div
                    class="absolute inset-0 bg-linear-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                </div>
            @endif
        </div>

        {{-- Threshold Marker --}}
        @if ($threshold > 0 && $threshold < 100)
            <div class="absolute top-0 bottom-0 w-0.5 {{ $value >= $threshold ? 'bg-white/50' : 'bg-neutral-400 dark:bg-neutral-500' }}"
                style="left: {{ $threshold }}%" title="Skill unlock threshold: {{ $threshold }}%">
                {{-- Threshold Label (for larger sizes) --}}
                @if ($size === 'lg')
                    <div
                        class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs font-medium text-neutral-600 dark:text-neutral-400 whitespace-nowrap">
                        {{ $threshold }}%
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Threshold Status Message --}}
    @if ($isThresholdReached())
        <p class="mt-1.5 text-xs font-medium text-pink-600 dark:text-pink-400 flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            Skills unlocked!
        </p>
    @elseif($value > 0)
        <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $threshold - $value }}% until skill unlock
        </p>
    @endif
</div>

@once
    @push('styles')
        @vite(['resources/css/components/gauges.css'])
    @endpush
@endonce
