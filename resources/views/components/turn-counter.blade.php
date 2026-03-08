@props(['current', 'total', 'showStage', 'showProgress', 'size'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 w-full ' . $getSizeClasses()]) }}>
    {{-- Turn Display --}}
    <div class="flex items-center gap-3 w-full">
        {{-- Turn Icon --}}
        <div
            class="shrink-0 w-10 h-10 rounded-full bg-linear-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd" />
            </svg>
        </div>

        {{-- Turn Number and Stage --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    Turn {{ $current }}
                </span>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    / {{ $total }}
                </span>
            </div>

            @if ($showStage)
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-sm font-semibold {{ $getStageColorClasses() }}">
                        {{ $getStage() }} Year
                    </span>

                    {{-- Stage Icon --}}
                    <span class="text-xs">
                        {{ match ($getStage()) {
                            'Junior' => '🌱',
                            'Classic' => '⭐',
                            'Senior' => '👑',
                            default => '📅',
                        } }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Percentage --}}
        <div class="text-right shrink-0">
            <div class="text-lg font-bold text-neutral-700 dark:text-neutral-300">
                {{ number_format($getProgressPercentage(), 1) }}%
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                Complete
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if ($showProgress)
        <div class="w-full space-y-2">
            <div class="relative h-3 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                {{-- Stage Markers --}}
                <div class="absolute inset-0 flex">
                    {{-- Junior (1-24) --}}
                    <div class="flex-1 border-r-2 border-white dark:border-neutral-800"></div>
                    {{-- Classic (25-48) --}}
                    <div class="flex-1 border-r-2 border-white dark:border-neutral-800"></div>
                    {{-- Senior (49-78) --}}
                    <div class="flex-1"></div>
                </div>

                {{-- Progress Fill --}}
                <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500 ease-out {{ match ($getStage()) {
                    'Junior' => 'bg-linear-to-r from-green-400 to-green-500',
                    'Classic' => 'bg-linear-to-r from-blue-400 to-blue-500',
                    'Senior' => 'bg-linear-to-r from-purple-400 to-purple-500',
                    default => 'bg-neutral-500',
                } }}"
                    style="width: {{ $getProgressPercentage() }}%" role="progressbar"
                    aria-valuenow="{{ $current }}" aria-valuemin="1" aria-valuemax="{{ $total }}"
                    aria-label="Turn {{ $current }} of {{ $total }}">
                    {{-- Shimmer Effect --}}
                    <div
                        class="absolute inset-0 bg-linear-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                    </div>
                </div>
            </div>

            {{-- Stage Labels --}}
            <div class="flex justify-between text-xs font-medium">
                <span class="text-green-600 dark:text-green-400">Junior (1-24)</span>
                <span class="text-blue-600 dark:text-blue-400">Classic (25-48)</span>
                <span class="text-purple-600 dark:text-purple-400">Senior (49-78)</span>
            </div>
        </div>
    @endif
</div>

@once
    @push('styles')
        @vite(['resources/css/components/turn-counter.css'])
    @endpush
@endonce
