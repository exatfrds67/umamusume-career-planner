@props(['availableCards', 'cardsByTier'])

<div class="card bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Card Library</h2>

    <!-- Filters -->
    <div class="space-y-3 mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-neutral-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <input type="text" x-model="searchQuery" placeholder="Search cards..."
                class="form-input w-full pl-10 rounded-md border-neutral-300 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                aria-label="Search support cards">
        </div>

        <select x-model="filterType"
            class="form-select w-full rounded-md border-neutral-300 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
            aria-label="Filter by card type">
            <option value="">All Types</option>
            <option value="speed">Speed</option>
            <option value="stamina">Stamina</option>
            <option value="power">Power</option>
            <option value="guts">Guts</option>
            <option value="wit">Wit</option>
            <option value="friend">Friend</option>
        </select>

        <select x-model="filterRarity"
            class="form-select w-full rounded-md border-neutral-300 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
            aria-label="Filter by rarity">
            <option value="">All Rarities</option>
            <option value="SSR">SSR</option>
            <option value="SR">SR</option>
            <option value="R">R</option>
        </select>

        <select x-model="filterTier"
            class="form-select w-full rounded-md border-neutral-300 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
            aria-label="Filter by meta tier">
            <option value="">All Tiers</option>
            <option value="S+">S+ Tier</option>
            <option value="S">S Tier</option>
            <option value="A">A Tier</option>
            <option value="B">B Tier</option>
            <option value="C">C Tier</option>
        </select>

        <select x-model="sortBy"
            class="form-select w-full rounded-md border-neutral-300 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
            aria-label="Sort cards by">
            <option value="meta_tier">Sort by Meta Tier</option>
            <option value="name">Sort by Name</option>
            <option value="rarity">Sort by Rarity</option>
            <option value="usage_rate">Sort by Usage Rate</option>
        </select>
    </div>

    <!-- Card List -->
    <div class="space-y-2 max-h-[600px] overflow-y-auto">
        @foreach ($availableCards as $card)
            <div class="p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer"
                x-show="filterCard({{ json_encode([
                    'name' => $card->name,
                    'character_name' => $card->character_name,
                    'card_type' => $card->card_type,
                    'meta_tier' => $card->meta_tier,
                    'rarity' => $card->rarity,
                ]) }})"
                @click="selectCard({{ $card->id }})" draggable="true" data-card-id="{{ $card->id }}"
                role="listitem">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                            {{ $card->name }}
                        </h4>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            {{ $card->character_name }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                            <x-support-card-type-badge :type="$card->card_type" size="xs" />
                            <x-support-card-rarity-badge :rarity="$card->rarity" size="xs" />
                            <x-support-card-tier-badge :tier="$card->meta_tier" size="xs" />
                        </div>
                    </div>
                    <button type="button" class="btn btn-xs btn-primary shrink-0"
                        aria-label="Add {{ $card->name }} to deck">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Meta Tier Quick Filter -->
    <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
        <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">Quick Filter by Tier:</p>
        <div class="flex flex-wrap gap-2">
            @foreach (['S+', 'S', 'A', 'B', 'C'] as $tier)
                <button type="button"
                    @click="filterTier = filterTier === '{{ $tier }}' ? '' : '{{ $tier }}'"
                    :class="filterTier === '{{ $tier }}' ?
                        'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' :
                        'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-600'"
                    class="px-3 py-1 text-xs font-medium rounded-md border transition-colors hover:bg-primary-50 dark:hover:bg-primary-900/50"
                    aria-label="Filter by {{ $tier }} tier">
                    {{ $tier }}
                    @if (isset($cardsByTier[$tier]))
                        <span class="ml-1 text-xs opacity-75">({{ $cardsByTier[$tier]->count() }})</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
