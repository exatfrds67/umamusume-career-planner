@props(['card'])

<a href="{{ route('support-cards.show', $card) }}"
    class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs hover:shadow-md transition-all border border-neutral-200 dark:border-neutral-700 overflow-hidden group">
    <div class="p-4">
        <div class="flex items-center gap-4">
            <!-- Thumbnail -->
            <div
                class="shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-linear-to-br from-{{ $card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink')))) }}-400 to-{{ $card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink')))) }}-600">
                @if ($card->artwork_url)
                    <img src="{{ $card->artwork_url }}" alt="{{ $card->name }}" loading="lazy" decoding="async"
                        class="w-full h-full object-cover">
                @endif
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3
                        class="text-base font-semibold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                        {{ $card->name }}
                    </h3>
                    <x-support-card-rarity-badge :rarity="$card->rarity" />
                    <x-support-card-tier-badge :tier="$card->meta_tier" />
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-support-card-type-badge :type="$card->card_type" />
                    @if ($card->character_name)
                        <span class="text-neutral-500 dark:text-neutral-400">{{ $card->character_name }}</span>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="hidden lg:flex items-center gap-4 text-sm">
                @if ($card->speed_bonus > 0)
                    <div class="text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 text-xs">Speed</div>
                        <div class="font-medium text-neutral-900 dark:text-white">+{{ $card->speed_bonus }}</div>
                    </div>
                @endif
                @if ($card->stamina_bonus > 0)
                    <div class="text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 text-xs">Stamina</div>
                        <div class="font-medium text-neutral-900 dark:text-white">+{{ $card->stamina_bonus }}</div>
                    </div>
                @endif
                @if ($card->power_bonus > 0)
                    <div class="text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 text-xs">Power</div>
                        <div class="font-medium text-neutral-900 dark:text-white">+{{ $card->power_bonus }}</div>
                    </div>
                @endif
                @if ($card->guts_bonus > 0)
                    <div class="text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 text-xs">Guts</div>
                        <div class="font-medium text-neutral-900 dark:text-white">+{{ $card->guts_bonus }}</div>
                    </div>
                @endif
                @if ($card->wit_bonus > 0)
                    <div class="text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 text-xs">Wit</div>
                        <div class="font-medium text-neutral-900 dark:text-white">+{{ $card->wit_bonus }}</div>
                    </div>
                @endif
            </div>

            <!-- Arrow -->
            <div class="shrink-0">
                <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 group-hover:translate-x-1 transition-all"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </div>
    </div>
</a>
