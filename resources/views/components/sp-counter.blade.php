@props(['current', 'available', 'showBar', 'showIcon', 'size'])

<div {{ $attributes->merge(['class' => 'inline-flex flex-col gap-2']) }}>
    {{-- SP Display --}}
    <div class="flex items-center gap-2 {{ $getSizeClasses() }}">
        @if ($showIcon)
            <div
                class="shrink-0 w-8 h-8 rounded-full {{ $isExceeded() ? 'bg-red-100 dark:bg-red-900/30' : ($isLow() ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-green-100 dark:bg-green-900/30') }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $getStatusColorClasses() }}" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
        @endif

        <div class="flex-1">
            <div class="flex items-baseline gap-1.5">
                <span class="font-bold {{ $getStatusColorClasses() }}">
                    {{ number_format($current) }}
                </span>
                <span class="text-neutral-500 dark:text-neutral-400">/</span>
                <span class="font-semibold text-neutral-700 dark:text-neutral-300">
                    {{ number_format($available) }}
                </span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-1">SP</span>
            </div>

            {{-- Remaining Display --}}
            <div class="text-xs font-medium {{ $getStatusColorClasses() }}">
                @if ($isExceeded())
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        Over by {{ number_format(abs($getRemaining())) }}
                    </span>
                @else
                    <span class="flex items-center gap-1">
                        @if ($isLow())
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        @endif
                        {{ number_format($getRemaining()) }} remaining
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if ($showBar)
        <div class="w-full h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
            <div class="{{ $getProgressColorClasses() }} h-full rounded-full transition-all duration-300 relative"
                style="width: {{ min(100, $getPercentage()) }}%" role="progressbar"
                aria-valuenow="{{ $current }}" aria-valuemin="0" aria-valuemax="{{ $available }}"
                aria-label="SP usage: {{ $current }} of {{ $available }}">
                {{-- Pulse animation when exceeded --}}
                @if ($isExceeded())
                    <div class="absolute inset-0 bg-red-400 animate-pulse"></div>
                @endif
            </div>

            {{-- Overflow indicator --}}
            @if ($isExceeded())
                <div class="absolute top-0 bottom-0 left-full w-1 bg-red-500 animate-pulse"
                    style="transform: translateX(-100%)"></div>
            @endif
        </div>

        {{-- Percentage Display --}}
        <div class="text-xs text-center {{ $getStatusColorClasses() }} font-medium">
            {{ number_format($getPercentage(), 1) }}% used
        </div>
    @endif
</div>
