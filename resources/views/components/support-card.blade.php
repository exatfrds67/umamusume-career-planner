@props(['card', 'level', 'limitBreak', 'bondLevel', 'size', 'showEffects'])

<div
    {{ $attributes->merge(['class' => 'support-card ' . $getSizeClasses() . ' rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl hover:-translate-y-1']) }}>
    {{-- Card Image --}}
    <div
        class="relative aspect-2/3 overflow-hidden bg-linear-to-br from-neutral-200 to-neutral-300 dark:from-neutral-700 dark:to-neutral-800">
        @if ($getImageUrl())
            <img src="{{ $getImageUrl() }}" alt="{{ $getName() }}" class="w-full h-full object-cover" loading="lazy" />
        @else
            <div class="w-full h-full flex items-center justify-center text-neutral-400 dark:text-neutral-600">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                    <path fill-rule="evenodd"
                        d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        @endif

        {{-- Type Badge (Top Right) --}}
        <div class="absolute top-2 right-2">
            <div class="bg-{{ $getTypeColor() }}-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                {{ $getType() }}
            </div>
        </div>

        {{-- Rarity Stars (Top Left) --}}
        <div class="absolute top-2 left-2 flex gap-0.5">
            @for ($i = 0; $i < $getRarityStars(); $i++)
                <svg class="w-4 h-4 text-yellow-400 drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endfor
        </div>

        {{-- Limit Break Diamonds (Bottom) --}}
        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
            @for ($i = 0; $i < 4; $i++)
                <div
                    class="w-3 h-3 rotate-45 {{ $i < $limitBreak ? 'bg-yellow-400' : 'bg-neutral-400/50' }} border border-white/50 shadow-sm">
                </div>
            @endfor
        </div>

        {{-- Rainbow Bond Indicator --}}
        @if ($isRainbowBond())
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute inset-0 bg-linear-to-br from-pink-500/20 via-purple-500/20 to-blue-500/20 animate-pulse">
                </div>
                <div class="absolute top-0 left-0 right-0 h-1 bg-linear-to-r from-pink-500 via-purple-500 to-blue-500">
                </div>
            </div>
        @endif
    </div>

    {{-- Card Info --}}
    <div class="p-3 bg-white dark:bg-neutral-800">
        {{-- Name --}}
        <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-1 truncate">
            {{ $getName() }}
        </h4>

        {{-- Level & Bond --}}
        <div class="flex items-center justify-between text-xs text-neutral-600 dark:text-neutral-400 mb-2">
            <span class="font-semibold">Lv. {{ $level }}</span>
            @if ($bondLevel !== null)
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 {{ $isRainbowBond() ? 'text-pink-500' : 'text-neutral-400' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="{{ $isRainbowBond() ? 'text-pink-500 font-bold' : '' }}">{{ $bondLevel }}%</span>
                </div>
            @endif
        </div>

        {{-- Effects --}}
        @if ($showEffects && count($getEffects()) > 0)
            <div class="space-y-1">
                @foreach (array_slice($getEffects(), 0, 3) as $effect)
                    <div class="text-xs text-neutral-700 dark:text-neutral-300 flex items-start gap-1">
                        <span class="text-{{ $getTypeColor() }}-500 mt-0.5">•</span>
                        <span class="flex-1">{{ $effect }}</span>
                    </div>
                @endforeach
                @if (count($getEffects()) > 3)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 italic">
                        +{{ count($getEffects()) - 3 }} more effects
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/support-card.css'])
    @endpush
@endonce
