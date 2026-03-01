{{--
Component: SupportCardMini
Purpose: Compact support card display for deck slots and selections

Features:
  - Minimal card layout optimized for grids
  - Support card image/icon
  - Character name and rarity badge
  - Bond level indicator (optional)
  - Limit break counter (optional)
  - Hover effects and selection state
  - Game-aligned styling

Props:
  - card (array|object): Support card data
    { name, character, rarity, bondLevel, limitBreaks, imageUrl, type }
  - selected (bool): Selection state highlight
  - removable (bool): Show remove button
  - size (string): 'xs' (60px), 'sm' (80px), 'md' (100px) - default: sm
  - showBond (bool): Show bond level indicator (default: false)
  - showLimitBreak (bool): Show limit break counter (default: false)

Usage:
  <x-support-card-mini 
      :card="$supportCard"
      size="sm"
      :selected="true"
      @card-selected="handleSelect($event)"
  />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'card' => null,
    'selected' => false,
    'removable' => false,
    'size' => 'sm',
    'showBond' => false,
    'showLimitBreak' => false,
])

@php
    $sizeMap = [
        'xs' => ['w-16 h-20', 'text-xs'],
        'sm' => ['w-20 h-28', 'text-sm'],
        'md' => ['w-24 h-32', 'text-base'],
    ];
    [$dimensions, $fontSize] = $sizeMap[$size] ?? $sizeMap['sm'];

    // Rarity colors
    $rarityColors = [
        '★★★★★' => 'bg-yellow-400 dark:bg-yellow-500',
        '★★★★' => 'bg-purple-400 dark:bg-purple-500',
        '★★★' => 'bg-blue-400 dark:bg-blue-500',
        '★★' => 'bg-green-400 dark:bg-green-500',
        '★' => 'bg-gray-400 dark:bg-gray-500',
    ];
    $rarityClass = $rarityColors[$card['rarity'] ?? ''] ?? 'bg-gray-400 dark:bg-gray-500';
@endphp

<div class="group cursor-pointer" @click="$dispatch('card-selected', { id: '{{ $card['id'] ?? '' }}' })" role="button"
    :aria-pressed="selected" tabindex="0" @keydown.enter="$dispatch('card-selected', { id: '{{ $card['id'] ?? '' }}' })"
    @keydown.space="$dispatch('card-selected', { id: '{{ $card['id'] ?? '' }}' })"
    :aria-label="'{{ $card['name'] ?? 'Support Card' }} - {{ $card['character'] ?? '' }}'">

    <div class="{{ $dimensions }} relative rounded-lg overflow-hidden shadow-md transition-all duration-200"
        :class="selected ? 'ring-2 ring-blue-500 dark:ring-blue-400 shadow-lg scale-105' :
            'hover:shadow-lg hover:scale-[1.02] group-hover:ring-2 group-hover:ring-blue-300 dark:group-hover:ring-blue-600'">

        {{-- Card Image/Icon Background --}}
        @if (isset($card['imageUrl']))
            <img src="{{ $card['imageUrl'] }}" alt="{{ $card['name'] ?? 'Card' }}"
                class="w-full h-full object-cover bg-gray-300 dark:bg-gray-700" loading="lazy" decoding="async">
        @else
            <div
                class="w-full h-full bg-linear-to-br from-blue-400 to-purple-500 dark:from-blue-600 dark:to-purple-700 flex items-center justify-center text-2xl opacity-80">
                🎴
            </div>
        @endif

        {{-- Rarity Badge --}}
        <div
            class="absolute top-1 left-1 {{ $rarityClass }} text-white {{ $fontSize }} font-bold px-1.5 rounded-xs shadow-md">
            ★
        </div>

        {{-- Card Stats Overlay --}}
        <div
            class="absolute inset-0 bg-linear-to-t from-black/70 via-transparent to-transparent flex flex-col justify-end p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            {{-- Card Name --}}
            <p class="text-white {{ $fontSize }} font-semibold truncate">
                {{ $card['name'] ?? 'Card' }}
            </p>

            {{-- Character Name --}}
            <p class="text-white/80 {{ $fontSize }} text-xs truncate">
                {{ $card['character'] ?? '' }}
            </p>

            {{-- Bond Level --}}
            @if ($showBond && isset($card['bondLevel']))
                <div class="text-yellow-300 text-xs font-medium mt-1">
                    💝 Lv. {{ $card['bondLevel'] }}
                </div>
            @endif

            {{-- Limit Break --}}
            @if ($showLimitBreak && isset($card['limitBreaks']))
                <div class="text-orange-300 text-xs font-medium">
                    ⚡ LB{{ $card['limitBreaks'] }}
                </div>
            @endif
        </div>

        {{-- Selection Indicator --}}
        <div class="absolute top-2 right-2 w-5 h-5 rounded-full border-2 border-white shadow-md flex items-center justify-center transition-all duration-200"
            :class="selected ? 'bg-blue-500 dark:bg-blue-400' : 'bg-transparent group-hover:bg-blue-400/50'">
            <svg class="w-3 h-3 text-white" :class="selected ? 'opacity-100' : 'opacity-0'" fill="currentColor"
                viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </div>

        {{-- Remove Button --}}
        @if ($removable)
            <button @click.stop="$dispatch('card-removed', { id: '{{ $card['id'] ?? '' }}' })"
                class="absolute bottom-2 right-2 w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 focus:outline-hidden focus:ring-2 focus:ring-red-400"
                aria-label="Remove card">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/animations.css'])
    @endpush
@endonce
