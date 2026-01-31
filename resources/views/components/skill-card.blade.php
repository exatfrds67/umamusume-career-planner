@props(['skill', 'hintLevel', 'acquired', 'showCost', 'showDiscount', 'size'])

<div
    {{ $attributes->merge(['class' => 'skill-card rounded-lg border-2 transition-all duration-300 hover:shadow-lg ' . $getSizeClasses() . ' ' . ($acquired ? 'border-success-500 bg-success-50 dark:bg-success-900/20' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800')]) }}>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-3">
        {{-- Skill Name & Rarity --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <h4 class="text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">
                    {{ $getName() }}
                </h4>
                @if ($getRarity() !== 'normal')
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $getRarityColor() }}-100 text-{{ $getRarityColor() }}-700 dark:bg-{{ $getRarityColor() }}-900/30 dark:text-{{ $getRarityColor() }}-300">
                        {{ ucfirst($getRarity()) }}
                    </span>
                @endif
            </div>
            @if ($getType() !== 'general')
                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ ucfirst($getType()) }}
                </div>
            @endif
        </div>

        {{-- Acquired Badge --}}
        @if ($acquired)
            <div class="flex-shrink-0">
                <div class="bg-success-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Acquired</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Description --}}
    @if ($getDescription())
        <p class="text-sm text-neutral-700 dark:text-neutral-300 mb-3 line-clamp-2">
            {{ $getDescription() }}
        </p>
    @endif

    {{-- Cost & Hint Level --}}
    @if ($showCost)
        <div class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-700">
            {{-- SP Cost --}}
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-bold text-lg text-neutral-900 dark:text-neutral-100 tabular-nums">
                        {{ number_format($getFinalCost()) }}
                    </span>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">SP</span>
                </div>

                {{-- Original Cost (if discounted) --}}
                @if ($showDiscount && $hintLevel > 0)
                    <div class="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                        <span class="line-through">{{ number_format($getBaseCost()) }}</span>
                        <span class="text-success-600 dark:text-success-400 font-semibold">
                            -{{ $getDiscountPercentage() }}%
                        </span>
                    </div>
                @endif
            </div>

            {{-- Hint Level --}}
            @if ($hintLevel > 0)
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <div
                            class="w-2 h-2 rounded-full {{ $i <= $hintLevel ? 'bg-primary-500' : 'bg-neutral-300 dark:bg-neutral-600' }}">
                        </div>
                    @endfor
                    <span class="ml-1 text-xs font-semibold text-primary-600 dark:text-primary-400">
                        Lv{{ $hintLevel }}
                    </span>
                </div>
            @endif
        </div>
    @endif
</div>

@once
    @push('styles')
        @vite(['resources/css/components/skill-card.css'])
    @endpush
@endonce
