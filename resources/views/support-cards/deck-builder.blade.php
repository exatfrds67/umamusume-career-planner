@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="deckBuilder()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Deck Builder - {{ $character->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Build and optimize your support card deck (6 cards: 5 owned + 1 friend)
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <a href="{{ route('characters.show', $character) }}" class="btn btn-outline">
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Character
                </a>
            </div>
        </div>

        <!-- Deck Status -->
        @if ($synergyScore)
            <div
                class="card bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 p-4 rounded-lg border border-primary-200 dark:border-primary-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-primary-900 dark:text-primary-100">Current Deck Synergy</h3>
                        <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($synergyScore['total_score'], 1) }}%
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-primary-700 dark:text-primary-300">
                            Type Coverage: {{ $synergyScore['type_coverage'] ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-primary-700 dark:text-primary-300">
                            Avg Meta Tier: {{ $synergyScore['avg_tier'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Deck Slots (Left/Top) -->
            <div class="lg:col-span-2 space-y-4">
                <div
                    class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Deck Slots ({{ $currentDeck->count() }}/6)
                    </h2>

                    <div class="space-y-3">
                        @for ($i = 1; $i <= 6; $i++)
                            @php
                                $slotCard = $currentDeck->firstWhere('position_slot', $i);
                                $isFriendSlot = $i === 6;
                            @endphp
                            <div
                                class="deck-slot p-4 rounded-lg border-2 {{ $slotCard ? 'border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/20' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="flex-shrink-0">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $isFriendSlot ? 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} font-semibold text-sm">
                                                {{ $i }}
                                            </span>
                                        </div>

                                        @if ($slotCard && $slotCard->supportCard)
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
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
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $slotCard->supportCard->character_name }} •
                                                    LB: {{ $slotCard->limit_break_level }}/4 •
                                                    Bond: {{ $slotCard->friendship_level }}%
                                                </p>
                                            </div>
                                        @else
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $isFriendSlot ? 'Friend Card Slot (Optional)' : 'Empty Slot' }}
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    Click "Add Card" to fill this slot
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($slotCard)
                                            <button @click="removeCard({{ $i }})"
                                                class="btn btn-sm btn-outline text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @else
                                            <button
                                                @click="openCardSelector({{ $i }}, {{ $isFriendSlot ? 'true' : 'false' }})"
                                                class="btn btn-sm btn-primary">
                                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
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
                        <button @click="saveDeck" class="btn btn-primary" :disabled="!isDeckValid">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save Deck
                        </button>
                        <button @click="clearDeck" class="btn btn-outline">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear All
                        </button>
                        <button @click="autoOptimize" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Auto-Optimize
                        </button>
                    </div>
                </div>

                <!-- Validation Messages -->
                <div x-show="validationErrors.length > 0"
                    class="card bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Deck Validation Errors</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="error in validationErrors" :key="error">
                            <li class="text-sm text-red-700 dark:text-red-300" x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <div x-show="validationWarnings.length > 0"
                    class="card bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Deck Warnings</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="warning in validationWarnings" :key="warning">
                            <li class="text-sm text-yellow-700 dark:text-yellow-300" x-text="warning"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Card Library (Right/Bottom) -->
            <div class="space-y-4">
                <div
                    class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Available Cards</h2>

                    <!-- Filters -->
                    <div class="space-y-3 mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search cards..."
                            class="form-input w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">

                        <select x-model="filterType"
                            class="form-select w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            <option value="">All Types</option>
                            <option value="speed">Speed</option>
                            <option value="stamina">Stamina</option>
                            <option value="power">Power</option>
                            <option value="guts">Guts</option>
                            <option value="wit">Wit</option>
                            <option value="friend">Friend</option>
                        </select>

                        <select x-model="filterTier"
                            class="form-select w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            <option value="">All Tiers</option>
                            <option value="S+">S+ Tier</option>
                            <option value="S">S Tier</option>
                            <option value="A">A Tier</option>
                            <option value="B">B Tier</option>
                            <option value="C">C Tier</option>
                        </select>
                    </div>

                    <!-- Card List -->
                    <div class="space-y-2 max-h-[600px] overflow-y-auto">
                        @foreach ($availableCards as $card)
                            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer"
                                x-show="filterCard({{ json_encode([
                                    'name' => $card->name,
                                    'character_name' => $card->character_name,
                                    'card_type' => $card->card_type,
                                    'meta_tier' => $card->meta_tier,
                                ]) }})"
                                @click="selectCard({{ $card->id }})">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $card->name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $card->character_name }}
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            <x-support-card-type-badge :type="$card->card_type" size="xs" />
                                            <x-support-card-rarity-badge :rarity="$card->rarity" size="xs" />
                                            <x-support-card-tier-badge :tier="$card->meta_tier" size="xs" />
                                        </div>
                                    </div>
                                    <button class="btn btn-xs btn-primary flex-shrink-0">
                                        Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Deck Stats Summary -->
                <div
                    class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Deck Statistics</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Cards</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="deckCount + '/6'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Friend Cards</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="friendCardCount + '/1'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Unique Types</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="uniqueTypes"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deckBuilder() {
            return {
                deck: @json(
                    $currentDeck->map(fn($card) => [
                            'slot' => $card->position_slot,
                            'card_id' => $card->support_card_id,
                            'is_friend' => $card->is_friend_card,
                            'limit_break' => $card->limit_break_level,
                            'friendship' => $card->friendship_level,
                            'card' => $card->supportCard,
                        ])),
                selectedSlot: null,
                isFriendSlot: false,
                searchQuery: '',
                filterType: '',
                filterTier: '',
                validationErrors: [],
                validationWarnings: [],

                get deckCount() {
                    return this.deck.length;
                },

                get friendCardCount() {
                    return this.deck.filter(c => c.is_friend).length;
                },

                get uniqueTypes() {
                    const types = new Set(this.deck.map(c => c.card?.card_type).filter(Boolean));
                    return types.size;
                },

                get isDeckValid() {
                    return this.deckCount === 6 && this.friendCardCount <= 1 && this.validationErrors.length === 0;
                },

                filterCard(card) {
                    if (this.searchQuery && !card.name.toLowerCase().includes(this.searchQuery.toLowerCase()) &&
                        !card.character_name.toLowerCase().includes(this.searchQuery.toLowerCase())) {
                        return false;
                    }
                    if (this.filterType && card.card_type !== this.filterType) {
                        return false;
                    }
                    if (this.filterTier && card.meta_tier !== this.filterTier) {
                        return false;
                    }
                    return true;
                },

                openCardSelector(slot, isFriend) {
                    this.selectedSlot = slot;
                    this.isFriendSlot = isFriend;
                },

                selectCard(cardId) {
                    if (!this.selectedSlot) {
                        alert('Please select a deck slot first');
                        return;
                    }

                    // Add card to deck
                    const existingIndex = this.deck.findIndex(c => c.slot === this.selectedSlot);
                    if (existingIndex >= 0) {
                        this.deck.splice(existingIndex, 1);
                    }

                    this.deck.push({
                        slot: this.selectedSlot,
                        card_id: cardId,
                        is_friend: this.isFriendSlot,
                        limit_break: 0,
                        friendship: 0
                    });

                    this.selectedSlot = null;
                    this.validateDeck();

                    // Reload page to show updated deck
                    window.location.reload();
                },

                removeCard(slot) {
                    const index = this.deck.findIndex(c => c.slot === slot);
                    if (index >= 0) {
                        this.deck.splice(index, 1);
                        this.validateDeck();
                        window.location.reload();
                    }
                },

                clearDeck() {
                    if (confirm('Are you sure you want to clear the entire deck?')) {
                        this.deck = [];
                        this.validateDeck();
                        window.location.reload();
                    }
                },

                validateDeck() {
                    this.validationErrors = [];
                    this.validationWarnings = [];

                    if (this.deckCount !== 6) {
                        this.validationErrors.push('Deck must contain exactly 6 cards');
                    }

                    if (this.friendCardCount > 1) {
                        this.validationErrors.push('Deck can only have 1 friend card');
                    }

                    // Check for duplicates
                    const cardIds = this.deck.filter(c => !c.is_friend).map(c => c.card_id);
                    if (cardIds.length !== new Set(cardIds).size) {
                        this.validationErrors.push('Deck cannot contain duplicate cards');
                    }

                    // Warnings
                    if (this.uniqueTypes < 3) {
                        this.validationWarnings.push(
                        'Consider using at least 3 different card types for balanced training');
                    }
                },

                async saveDeck() {
                    if (!this.isDeckValid) {
                        alert('Please fix validation errors before saving');
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('api.v1.characters.deck.save', $character) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                cards: this.deck.map(c => ({
                                    support_card_id: c.card_id,
                                    is_friend_card: c.is_friend,
                                    limit_break_level: c.limit_break || 0,
                                    friendship_level: c.friendship || 0
                                }))
                            })
                        });

                        if (response.ok) {
                            alert('Deck saved successfully!');
                            window.location.reload();
                        } else {
                            alert('Failed to save deck. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error saving deck:', error);
                        alert('An error occurred while saving the deck.');
                    }
                },

                autoOptimize() {
                    alert(
                        'Auto-optimize feature coming soon! This will automatically select the best cards based on your character\'s goals.');
                }
            }
        }
    </script>
@endsection
