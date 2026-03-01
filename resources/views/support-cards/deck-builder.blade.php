@extends('layouts.app')

@section('content')
    {{-- Data Preparation --}}
    @php
        // Ensure character exists
        if (!$character) {
            abort(404, 'Character not found');
        }

        $characterName = data_get($character, 'name', 'Unknown Character');

        // Transform deck data to match the structure expected by app.js
        $deckData = $currentDeck
            ->map(
                fn($card) => [
                    'position_slot' => $card->position_slot,
                    'support_card_id' => $card->support_card_id,
                    'is_friend_card' => $card->is_friend_card,
                    'limit_break_level' => $card->limit_break_level,
                    'friendship_level' => $card->friendship_level,
                    'supportCard' => $card->supportCard,
                ],
            )
            ->values();

        $availableCardsData = $availableCards;
    @endphp

    {{-- Pass data to window for the deck builder component --}}
    <script id="deck-builder-data" type="application/json">
        {!! json_encode([
            'deck' => $deckData,
            'availableCards' => $availableCardsData,
            'characterId' => $character->id
        ]) !!}
    </script>

    {{-- Load Page-Specific Assets --}}
    @vite(['resources/js/pages/support-cards/deck-builder.js'])

    {{-- Initialize Alpine Component --}}
    <div class="deck-builder-container space-y-6" x-data="deckBuilder()">

        <header class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Deck Builder - {{ $characterName }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Build and optimize your support card deck (6 cards required: 5 owned + 1 friend)
                </p>
                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        Click any card to auto-add
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Drag to reorder
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Arrow keys to move
                    </span>
                </div>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <a href="{{ route('characters.show', $character) }}" class="btn btn-outline">
                    Back to Character
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-4">
                <section class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xs" aria-labelledby="slots-heading">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="slots-heading" class="text-lg font-semibold text-gray-900 dark:text-white">
                            Deck Slots (<span x-text="deckCount"></span>/6)
                        </h2>
                        <div class="flex gap-2">
                            <button @click="saveDeck" class="btn btn-sm btn-primary" :disabled="!isDeckValid">Save</button>
                            <button @click="clearDeck" class="btn btn-sm btn-outline text-red-600">Clear</button>
                            <button @click="autoOptimize" class="btn btn-sm btn-secondary">Auto</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="i in 6" :key="i">
                            <div class="deck-slot p-4 rounded-lg border-2 transition-all" :class="getSlotClasses(i)"
                                :data-slot="i" draggable="true" @dragstart="handleDragStart($event, i)"
                                @dragend="handleDragEnd" @dragover.prevent="handleDragOver($event, i)"
                                @drop="handleDrop($event, i)" @click="handleSlotClick(i)"
                                @keydown.arrow-up.prevent="moveCardUp(i)" @keydown.arrow-down.prevent="moveCardDown(i)"
                                tabindex="0">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="shrink-0">
                                            <span class="slot-number" :class="i === 6 ? 'friend-slot' : 'normal-slot'"
                                                x-text="i"></span>
                                        </div>

                                        <template x-if="getCardAtSlot(i)">
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <img :src="getCardAtSlot(i).supportCard.artwork_url || '/placeholder.png'"
                                                    class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                                            x-text="getCardAtSlot(i).supportCard.name"></h3>
                                                        <span
                                                            class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700"
                                                            x-text="getCardAtSlot(i).supportCard.card_type"></span>
                                                    </div>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        LB: <span x-text="getCardAtSlot(i).limit_break_level"></span>/4 •
                                                        Bond: <span x-text="getCardAtSlot(i).friendship_level"></span>%
                                                    </p>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!getCardAtSlot(i)">
                                            <div class="flex-1 text-sm text-gray-500 dark:text-gray-400">
                                                <span
                                                    x-text="i === 6 ? 'Friend Card Slot (Required)' : 'Empty Slot'"></span>
                                                <p class="text-xs text-gray-400 mt-0.5">Click a card from library to add</p>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <template x-if="getCardAtSlot(i)">
                                            <div class="flex gap-1">
                                                <button @click.stop="openEditModal(i)"
                                                    class="btn-icon text-blue-600 hover:bg-blue-50">
                                                    <span class="sr-only">Edit</span>
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button @click.stop="removeCard(i)"
                                                    class="btn-icon text-red-600 hover:bg-red-50">
                                                    <span class="sr-only">Remove</span>
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="!getCardAtSlot(i)">
                                            <button @click.stop="openCardSelector(i)" class="btn btn-sm btn-primary">
                                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <div x-show="validationErrors.length > 0"
                    class="card bg-red-50 dark:bg-red-900/20 p-4 border-red-200 dark:border-red-800">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Validation Errors</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="error in validationErrors" :key="error">
                            <li class="text-sm text-red-700 dark:text-red-300" x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <div x-show="validationWarnings.length > 0"
                    class="card bg-yellow-50 dark:bg-yellow-900/20 p-4 border-yellow-200 dark:border-yellow-800">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Suggestions</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="warning in validationWarnings" :key="warning">
                            <li class="text-sm text-yellow-700 dark:text-yellow-300" x-text="warning"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="space-y-6">
                <section class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xs" aria-labelledby="stats-heading">
                    <h2 id="stats-heading" class="text-lg font-semibold mb-3">Deck Statistics</h2>

                    <dl class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Cards</dt>
                            <dd class="font-medium" x-text="deckCount + '/6'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Friend Cards</dt>
                            <dd class="font-medium" x-text="friendCardCount + '/1'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Unique Types</dt>
                            <dd class="font-medium" x-text="uniqueTypes"></dd>
                        </div>
                    </dl>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium uppercase text-gray-500">Synergy Score</span>
                            <span class="text-sm font-bold" x-text="synergyScore + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="h-2.5 rounded-full transition-all duration-500"
                                :class="{
                                    'bg-red-500': synergyScore < 30,
                                    'bg-yellow-500': synergyScore >= 30 && synergyScore <
                                        70,
                                    'bg-green-500': synergyScore >= 70
                                }"
                                :style="'width: ' + synergyScore + '%'"></div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Type Distribution</h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(count, type) in typeDistribution" :key="type">
                                <span
                                    class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                    <span x-text="type.charAt(0).toUpperCase() + type.slice(1)"></span>: <span
                                        class="font-bold" x-text="count"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <dl class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Avg Bond</dt>
                                <dd class="font-medium" x-text="averageBond + '%'"></dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Avg LB</dt>
                                <dd class="font-medium" x-text="averageLimitBreak + '★'"></dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <aside class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xs flex flex-col h-150"
                    aria-labelledby="library-heading">
                    <h2 id="library-heading" class="text-lg font-semibold mb-4">Available Cards</h2>

                    <div class="space-y-3 mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search cards..."
                            class="form-input w-full text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <select x-model="filterType" class="form-select w-full text-sm">
                                <option value="">All Types</option>
                                <option value="speed">Speed</option>
                                <option value="stamina">Stamina</option>
                                <option value="power">Power</option>
                                <option value="guts">Guts</option>
                                <option value="wit">Wit</option>
                                <option value="friend">Friend</option>
                            </select>
                            <select x-model="filterTier" class="form-select w-full text-sm">
                                <option value="">All Tiers</option>
                                <option value="SS">SS Tier</option>
                                <option value="S">S Tier</option>
                                <option value="A">A Tier</option>
                                <option value="B">B Tier</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2 flex-1 overflow-y-auto pr-2">
                        <template x-for="card in filteredCards" :key="card.id">
                            <div class="card-library-item group flex items-center gap-3 p-2" @click="selectCard(card.id)"
                                @keydown.enter="selectCard(card.id)" tabindex="0" role="button">
                                <img :src="card.artwork_url || '/placeholder.png'"
                                    class="w-10 h-10 rounded-md object-cover border border-gray-200 dark:border-gray-600" loading="lazy" decoding="async">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                        x-text="card.name"></h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500" x-text="card.card_type"></span>
                                        <span class="text-xs font-semibold px-1 rounded bg-gray-100 dark:bg-gray-700"
                                            x-text="card.meta_tier"></span>
                                    </div>
                                </div>
                                <button
                                    class="btn btn-xs btn-primary opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 focus:opacity-100 transition-opacity">+</button>
                            </div>
                        </template>
                        <template x-if="filteredCards.length === 0">
                            <div class="text-center text-gray-500 py-8 text-sm">No cards found matching filters.</div>
                        </template>
                    </div>
                </aside>
            </div>
        </div>

        <div x-show="showEditModal" x-cloak class="modal-backdrop" @keydown.escape.window="closeEditModal()">
            <div class="modal-content" @click.away="closeEditModal()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Card Details</h3>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Limit Break: <span x-text="editForm.limitBreak"></span>/4
                        </label>
                        <input type="range" x-model.number="editForm.limitBreak" min="0" max="4"
                            step="1" class="w-full">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>0</span><span>1</span><span>2</span><span>3</span><span>4</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bond Level: <span x-text="editForm.bondLevel"></span>%
                        </label>
                        <input type="range" x-model.number="editForm.bondLevel" min="0" max="100"
                            step="5" class="w-full">
                        <div class="flex gap-2 mt-2 justify-center">
                            <button @click="editForm.bondLevel = 0" class="btn btn-xs btn-outline">0%</button>
                            <button @click="editForm.bondLevel = 100" class="btn btn-xs btn-outline">Max</button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="closeEditModal" class="btn btn-outline">Cancel</button>
                    <button @click="saveEditModal" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>

    </div>
@endsection
