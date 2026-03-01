@props(['card'])

<div
    class="card bg-white dark:bg-gray-800 rounded-lg shadow-xs hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 overflow-hidden group">
    <!-- Card Image/Placeholder -->
    <div
        class="relative h-48 bg-linear-to-br from-{{ $card->card_type ? ($card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray' }}-400 to-{{ $card->card_type ? ($card->card_type === 'speed' ? 'blue' : ($card->card_type === 'stamina' ? 'green' : ($card->card_type === 'power' ? 'red' : ($card->card_type === 'guts' ? 'orange' : ($card->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray' }}-600">
        @if ($card->artwork_url)
            <img src="{{ $card->artwork_url }}" alt="{{ $card->name }}" loading="lazy" decoding="async"
                width="400" height="192"
                class="w-full h-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-20 h-20 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        @endif

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex gap-2">
            <x-support-card-rarity-badge :rarity="$card->rarity" />
            <x-support-card-tier-badge :tier="$card->meta_tier" />
        </div>

        @if ($card->is_limited)
            <div class="absolute top-2 right-2">
                <span
                    class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                    Limited
                </span>
            </div>
        @endif
    </div>

    <!-- Card Info -->
    <div class="p-4">
        <div class="mb-2">
            <h3
                class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $card->name }}
            </h3>
            @if ($card->character_name)
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                    {{ $card->character_name }}
                </p>
            @endif
        </div>

        <!-- Type Badge -->
        <div class="mb-3">
            <x-support-card-type-badge :type="$card->card_type" />
        </div>

        <!-- Stats Preview -->
        <div class="grid grid-cols-3 gap-2 text-xs mb-3 min-h-10">
            @if ($card->speed_bonus > 0)
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Speed</div>
                    <div class="font-medium text-gray-900 dark:text-white">+{{ $card->speed_bonus }}</div>
                </div>
            @endif
            @if ($card->stamina_bonus > 0)
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Stamina</div>
                    <div class="font-medium text-gray-900 dark:text-white">+{{ $card->stamina_bonus }}</div>
                </div>
            @endif
            @if ($card->power_bonus > 0)
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Power</div>
                    <div class="font-medium text-gray-900 dark:text-white">+{{ $card->power_bonus }}</div>
                </div>
            @endif
            @if ($card->guts_bonus > 0)
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Guts</div>
                    <div class="font-medium text-gray-900 dark:text-white">+{{ $card->guts_bonus }}</div>
                </div>
            @endif
            @if ($card->wit_bonus > 0)
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">Wit</div>
                    <div class="font-medium text-gray-900 dark:text-white">+{{ $card->wit_bonus }}</div>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <a href="{{ route('support-cards.show', $card) }}" class="btn btn-sm btn-secondary flex-1">
                View Details
            </a>
        </div>
    </div>
</div>
