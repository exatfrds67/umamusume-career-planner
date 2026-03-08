@props(['level', 'showDiscount', 'showIcon', 'size'])

<div
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-1.5 rounded-full border-2 font-semibold transition-all duration-200 ' .
            $getColorClasses() . ' ' . $getSizeClasses() . ' ' .
            ($level >= 5 ? 'shadow-lg shadow-purple-500/50 dark:shadow-pink-500/50' : '')
    ]) }}
    title="Hint Level {{ $level }}: {{ $getDiscountPercentage() }}% discount"
>
    @if($showIcon)
        <svg class="w-4 h-4 {{ $level === 0 ? 'opacity-50' : '' }}" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
    @endif

    <span>
        @if($level === 0)
            No Hints
        @else
            {{ $level }} {{ Str::plural('Hint', $level) }}
        @endif
    </span>

    @if($showDiscount && $level > 0)
        <span class="ml-0.5 {{ $level >= 5 ? 'animate-pulse' : '' }}">
            -{{ $getDiscountPercentage() }}%
        </span>
    @endif

    {{-- Max Level Indicator --}}
    @if($level >= 5)
        <span class="ml-0.5 text-xs">✨</span>
    @endif
</div>

{{-- Hint Level Dots (Visual Indicator) --}}
@if($level > 0)
    <div class="inline-flex items-center gap-0.5 ml-1" aria-hidden="true">
        @for($i = 1; $i <= 5; $i++)
            <div class="w-1.5 h-1.5 rounded-full {{ $i <= $level ? 'bg-current' : 'bg-neutral-300 dark:bg-neutral-600' }}"></div>
        @endfor
    </div>
@endif
