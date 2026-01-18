@props(['character', 'currentDeck'])

<div class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
    data-character-id="{{ $character->id }}">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Deck Slots ({{ $currentDeck->count() }}/6)
    </h2>

    <div class="space-y-3">
        @for ($i = 1; $i <= 6; $i++)
            @php
                $slotCard = $currentDeck->firstWhere('position_slot', $i);
                $isFriendSlot = $i === 6;
            @endphp
            <div class="deck-slot p-4 rounded-lg border-2 transition-all duration-200 {{ $slotCard ? 'border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/20 hover:border-primary-200 dark:hover:border-primary-700' }}"
                data-slot="{{ $i }}" draggable="{{ $slotCard ? 'true' : 'false' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="shrink-0">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $isFriendSlot ? 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} font-semibold text-sm">
                                {{ $i }}
                            </span>
                        </div>

                        @if ($slotCard?->supportCard)
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $slotCard->supportCard->name }}
                                    </h3>
                                    <x-support-card-type-badge :type="$slotCard->supportCard->card_type" />
                                    <x-support-card-rarity-badge :rarity="$slotCard->supportCard->rarity" />
                                    @if ($slotCard->is_friend_card)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            Friend
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $slotCard->supportCard->character_name }}</span>
                                    <span>•</span>
                                    <span>LB: {{ $slotCard->limit_break_level }}/4</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $slotCard->friendship_level }}%
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $isFriendSlot ? 'Friend Card Slot (Optional)' : 'Empty Slot' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Click "Add Card" or drag a card here
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($slotCard)
                            <button @click="removeCard({{ $i }})" type="button"
                                class="btn btn-sm btn-outline text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                aria-label="Remove card from slot {{ $i }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @else
                            <button
                                @click="openCardSelector({{ $i }}, {{ $isFriendSlot ? 'true' : 'false' }})"
                                type="button" class="btn btn-sm btn-primary"
                                aria-label="Add card to slot {{ $i }}">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add Card
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Deck Actions -->
    <div class="mt-6 flex flex-wrap gap-3">
        <button @click="autoOptimize" type="button" class="btn btn-primary" :disabled="isLoading">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Auto-Optimize
        </button>
        <button @click="clearDeck" type="button" class="btn btn-outline" :disabled="isLoading">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Clear All
        </button>
    </div>

    <!-- Validation Messages -->
    <div x-show="validationErrors.length > 0" x-cloak
        class="mt-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
        <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Deck Validation Errors</h3>
        <ul class="list-disc list-inside space-y-1">
            <template x-for="error in validationErrors" :key="error">
                <li class="text-sm text-red-700 dark:text-red-300" x-text="error"></li>
            </template>
        </ul>
    </div>

    <div x-show="validationWarnings.length > 0" x-cloak
        class="mt-4 p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Deck Warnings</h3>
        <ul class="list-disc list-inside space-y-1">
            <template x-for="warning in validationWarnings" :key="warning">
                <li class="text-sm text-yellow-700 dark:text-yellow-300" x-text="warning"></li>
            </template>
        </ul>
    </div>
</div>
