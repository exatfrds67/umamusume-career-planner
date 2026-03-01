@props(['card', 'position', 'clickable', 'size'])

<div
    {{ $attributes->merge([
        'class' => 'relative rounded-lg border-2 transition-all duration-200 ' . $getSizeClasses() . ' ' .
            ($clickable ? 'cursor-pointer hover:scale-105 hover:shadow-lg' : '') . ' ' .
            ($card ? 'border-blue-500 dark:border-blue-400 bg-linear-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900' : 'border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 hover:border-blue-400 dark:hover:border-blue-500')
    ]) }}
    role="button"
    tabindex="{{ $clickable ? '0' : '-1' }}"
    aria-label="{{ $getSlotLabel() }}"
>
    @if($card)
        {{-- Filled Slot --}}
        <div class="absolute inset-0 p-2 flex flex-col">
            {{-- Card Type Badge --}}
            <div class="flex items-center justify-between mb-1">
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                    {{ match($card['type'] ?? 'Speed') {
                        'Speed' => 'bg-uma-speed/20 text-uma-speed dark:bg-uma-speed/30',
                        'Stamina' => 'bg-uma-stamina/20 text-uma-stamina dark:bg-uma-stamina/30',
                        'Power' => 'bg-uma-power/20 text-uma-power dark:bg-uma-power/30',
                        'Guts' => 'bg-uma-guts/20 text-uma-guts dark:bg-uma-guts/30',
                        'Wit' => 'bg-uma-wit/20 text-uma-wit dark:bg-uma-wit/30',
                        'Friend' => 'bg-pink-500/20 text-pink-600 dark:bg-pink-500/30 dark:text-pink-400',
                        default => 'bg-gray-500/20 text-gray-600 dark:bg-gray-500/30'
                    } }}">
                    {{ $card['type'] ?? 'Speed' }}
                </span>

                {{-- Limit Break Indicator --}}
                @if(isset($card['limit_break']))
                    <div class="flex gap-0.5">
                        @for($i = 0; $i < 4; $i++)
                            <svg class="w-2 h-2 {{ $i < $card['limit_break'] ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2l2 6h6l-5 4 2 6-5-4-5 4 2-6-5-4h6z"/>
                            </svg>
                        @endfor
                    </div>
                @endif
            </div>

            {{-- Card Name --}}
            <div class="flex-1 flex items-center justify-center text-center">
                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 line-clamp-2">
                    {{ $card['name'] ?? 'Support Card' }}
                </p>
            </div>

            {{-- Bond Level --}}
            @if(isset($card['bond_level']))
                <div class="mt-auto">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Bond</span>
                        <span class="font-semibold {{ $card['bond_level'] >= 80 ? 'text-pink-600 dark:text-pink-400' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $card['bond_level'] }}%
                        </span>
                    </div>
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-300 {{ $card['bond_level'] >= 80 ? 'bg-linear-to-r from-pink-500 via-purple-500 to-blue-500' : 'bg-blue-500' }}"
                            style="width: {{ $card['bond_level'] }}%"
                        ></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Remove Button (if clickable) --}}
        @if($clickable)
            <button
                type="button"
                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors duration-200 z-10"
                aria-label="Remove card"
                onclick="event.stopPropagation()"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    @else
        {{-- Empty Slot --}}
        <div class="absolute inset-0 flex flex-col items-center justify-center p-2">
            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                {{ $getSlotLabel() }}
            </p>
        </div>
    @endif

    {{-- Slot Position Indicator --}}
    <div class="absolute bottom-1 left-1 w-5 h-5 rounded-full {{ $isMainSlot() ? 'bg-blue-500' : 'bg-purple-500' }} text-white text-xs font-bold flex items-center justify-center">
        {{ $position }}
    </div>
</div>
