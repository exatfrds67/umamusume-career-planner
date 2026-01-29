@extends('layouts.app')

@section('content')
    {{-- Data Preparation --}}
    @php
        $characterName = data_get($character, 'name', 'Unknown Character');

        // Transform deck data for JS
        $deckData = $currentDeck->map(function ($card) {
            return [
                'position_slot' => $card->position_slot,
                'support_card_id' => $card->support_card_id,
                'is_friend_card' => $card->is_friend_card,
                'limit_break_level' => $card->limit_break_level,
                'friendship_level' => $card->friendship_level,
                'supportCard' => $card->supportCard,
            ];
        })->values();

        $availableCardsData = $availableCards;
    @endphp

    {{-- Pass data to window for easy JS access --}}
    <script>
        window.deckBuilderData = {
            deck: @json($deckData),
            availableCards: @json($availableCardsData),
            characterId: @json($character->id)
        };
    </script>

    {{-- Load Assets --}}
    @vite(['resources/css/deck-builder.css', 'resources/js/deck-builder.js'])

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        Click any card to auto-add
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Drag to reorder
                    </span>
                </div>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <a href="{{ route('characters.show', $character) }}" class="btn btn-outline">
                    Back to Character
                </a>
            </div>
        </header>

        @if (isset($deckAnalysis['synergy']))
            <section aria-labelledby="status-heading" class="card synergy-banner">
                <h2 id="status-heading" class="sr-only">Deck Analysis</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-primary-900 dark:text-primary-100">Synergy Score</h3>
                        <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($deckAnalysis['synergy']['synergy_score'] ?? 0, 1) }}%
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-primary-700 dark:text-primary-300">
                            Strategy: {{ $deckAnalysis['synergy']['strategic_alignment']['primary_strategy'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <section class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Deck Slots (<span x-text="deckCount"></span>/6)
                    </h2>

                    <div class="space-y-3">
                        <template x-for="i in 6" :key="i">
                            <div class="deck-slot p-4 rounded-lg border-2 transition-all"
                                :class="getSlotClasses(i)"
                                :data-slot="i"
                                draggable="true"
                                @dragstart="handleDragStart($event, i)"
                                @dragend="handleDragEnd"
                                @dragover.prevent="handleDragOver($event, i)"
                                @drop="handleDrop($event, i)"
                                @click="handleSlotClick(i)">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="shrink-0">
                                            <span class="slot-number" :class="i === 6 ? 'friend-slot' : 'normal-slot'" x-text="i"></span>
                                        </div>

                                        <template x-if="getCardAtSlot(i)">
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <img :src="getCardAtSlot(i).supportCard.artwork_url || '/placeholder.png'" class="w-12 h-12 rounded-lg object-cover">
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="getCardAtSlot(i).supportCard.name"></h3>
                                                    <p class="mt-1 text-xs text-gray-500" x-text="'Bond: ' + getCardAtSlot(i).friendship_level + '%'"></p>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!getCardAtSlot(i)">
                                            <div class="flex-1 text-sm text-gray-500">
                                                <span x-text="i === 6 ? 'Friend Card Slot' : 'Empty Slot'"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <template x-if="getCardAtSlot(i)">
                                            <div class="flex gap-2">
                                                <button @click.stop="openEditModal(i)" class="btn-icon text-blue-600">Edit</button>
                                                <button @click.stop="removeCard(i)" class="btn-icon text-red-600">Remove</button>
                                            </div>
                                        </template>
                                        <template x-if="!getCardAtSlot(i)">
                                            <button @click.stop="openCardSelector(i)" class="btn btn-sm btn-primary">Add</button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button @click="saveDeck" class="btn btn-primary" :disabled="!isDeckValid">Save Deck</button>
                        <button @click="clearDeck" class="btn btn-outline">Clear All</button>
                        <button @click="autoOptimize" class="btn btn-secondary">Auto-Optimize</button>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h2 class="text-lg font-semibold mb-3">Deck Statistics</h2>
                    <dl class="space-y-2">
                        <div class="stat-row"><dt>Cards</dt><dd x-text="deckCount + '/6'"></dd></div>
                        <div class="stat-row"><dt>Friend Cards</dt><dd x-text="friendCardCount + '/1'"></dd></div>
                        <div class="stat-row"><dt>Synergy</dt><dd x-text="synergyScore + '%'"></dd></div>
                    </dl>
                </section>

                <aside class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm flex flex-col h-[600px]">
                    <h2 class="text-lg font-semibold mb-4">Available Cards</h2>
                    <div class="space-y-3 mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search cards..." class="form-input w-full">
                        <select x-model="filterType" class="form-select w-full">
                            <option value="">All Types</option>
                            <option value="speed">Speed</option>
                            <option value="stamina">Stamina</option>
                            </select>
                    </div>

                    <div class="space-y-2 flex-1 overflow-y-auto">
                        <template x-for="card in filteredCards" :key="card.id">
                            <div class="card-library-item" @click="selectCard(card.id)">
                                <div class="flex items-center gap-2">
                                    <img :src="card.artwork_url || '/placeholder.png'" class="w-10 h-10 rounded-md">
                                    <div>
                                        <h4 class="text-sm font-medium" x-text="card.name"></h4>
                                        <span class="text-xs text-gray-500" x-text="card.card_type"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </aside>
            </div>
        </div>

        <div x-show="validationErrors.length > 0" class="error-banner">
             <template x-for="error in validationErrors" :key="error"><div x-text="error"></div></template>
        </div>

        <div x-show="showEditModal" x-cloak class="modal-backdrop">
            <div class="modal-content">
                <h3 class="text-lg font-bold">Edit Card</h3>
                <div class="mt-4">
                    <label>Bond Level: <span x-text="editForm.bondLevel"></span>%</label>
                    <input type="range" x-model.number="editForm.bondLevel" min="0" max="100" class="w-full">
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button @click="closeEditModal" class="btn btn-outline">Cancel</button>
                    <button @click="saveEditModal" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>

    </div>
@endsection