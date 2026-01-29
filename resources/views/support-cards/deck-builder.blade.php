@extends('layouts.app')

@section('content')
    @php
        $characterName = data_get($character, 'name', 'Unknown Character');

        $deckData = $currentDeck
            ->map(function ($card) {
                return [
                    'position_slot' => $card->position_slot,
                    'support_card_id' => $card->support_card_id,
                    'is_friend_card' => $card->is_friend_card,
                    'limit_break_level' => $card->limit_break_level,
                    'friendship_level' => $card->friendship_level,
                    'supportCard' => $card->supportCard,
                ];
            })
            ->values()
            ->toArray();

        $availableCardsData = $availableCards->toArray();
    @endphp

    <!-- Initialize with available cards to avoid API call -->
    <script>
        // Pre-populate available cards to avoid 404 API call
        window.preloadedCards = @json($availableCardsData);
    </script>

    <div class="space-y-6" x-data="deckBuilder(@js($deckData), {{ $character->id }})" x-init="if (window.preloadedCards) { availableCards = window.preloadedCards; }">
        <!-- Header -->
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
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Character
                </a>
            </div>
        </header>

        <!-- Deck Status -->
        @isset($deckAnalysis)
            @if ($deckAnalysis && isset($deckAnalysis['synergy']))
                <section aria-labelledby="status-heading"
                    class="card bg-linear-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 p-4 rounded-lg border border-primary-200 dark:border-primary-700">
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
                                Synergy Pairs: {{ count($deckAnalysis['synergy']['synergy_pairs'] ?? []) }}
                            </p>
                            <p class="text-sm text-primary-700 dark:text-primary-300">
                                Strategy: {{ $deckAnalysis['synergy']['strategic_alignment']['primary_strategy'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif
        @endisset

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Deck Slots and Statistics (Left/Top) -->
            <div class="lg:col-span-2 space-y-4">
                <section aria-labelledby="slots-heading"
                    class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 id="slots-heading" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Deck Slots ({{ $currentDeck->count() }}/6)
                    </h2>

                    <div class="space-y-3">
                        @for ($i = 1; $i <= 6; $i++)
                            @php
                                $slotCard = $currentDeck->firstWhere('position_slot', $i);
                                $isFriendSlot = $i === 6;
                            @endphp
                            <div class="deck-slot p-4 rounded-lg border-2 transition-all {{ $slotCard ? 'border-primary-300 dark:border-primary-600 bg-primary-50 dark:bg-primary-900/20 cursor-move' : 'border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/20' }}"
                                data-slot="{{ $i }}" @dragstart="handleDragStart($event, {{ $i }})"
                                @dragend="handleDragEnd($event)"
                                @dragover.prevent="handleDragOver($event, {{ $i }})"
                                @drop="handleDrop($event, {{ $i }})"
                                @keydown.arrow-up.prevent="moveCardUp({{ $i }})"
                                @keydown.arrow-down.prevent="moveCardDown({{ $i }})"
                                tabindex="{{ $slotCard ? '0' : '-1' }}" draggable="{{ $slotCard ? 'true' : 'false' }}"
                                :class="{ 'ring-2 ring-primary-500 ring-offset-2': dragOverSlot === {{ $i }} }">
                                <div class="flex items-center justify-between">
                                    @if ($slotCard)
                                        <!-- Drag Handle -->
                                        <div class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                            title="Drag to reorder" role="button" aria-label="Drag to reorder card">
                                            <svg class="w-5 h-5 pointer-events-none" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="shrink-0">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $isFriendSlot ? 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} font-semibold text-sm">
                                                {{ $i }}
                                            </span>
                                        </div>

                                        @if ($slotCard && $slotCard->supportCard)
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                @if ($slotCard->supportCard->artwork_url)
                                                    <img src="{{ $slotCard->supportCard->artwork_url }}"
                                                        alt="{{ $slotCard->supportCard->name }}"
                                                        class="w-12 h-12 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-600 shrink-0">
                                                @else
                                                    <div
                                                        class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h3
                                                            class="text-sm font-medium text-gray-900 dark:text-white truncate">
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
                                                        LB: {{ $slotCard->limit_break_level }}/4
                                                        @for ($star = 0; $star < $slotCard->limit_break_level; $star++)
                                                            <span class="text-yellow-400">★</span>
                                                        @endfor
                                                        • Bond: {{ $slotCard->friendship_level }}%
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $isFriendSlot ? 'Friend Card Slot (Optional)' : 'Empty Slot' }}
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    Click any card from the library to add here
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($slotCard)
                                            <button
                                                @click="openEditModal({{ $i }}, {{ $slotCard->limit_break_level }}, {{ $slotCard->friendship_level }})"
                                                class="btn btn-sm btn-outline text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Edit card details">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
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

            <!-- Deck Statistics (WF-011/SPEC-005: Enhanced with type distribution and synergy) -->
            <section aria-labelledby="stats-heading"
                class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 id="stats-heading" class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Deck Statistics
                </h2>
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

                <!-- Type Distribution (WF-011) -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Type Distribution</h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(count, type) in typeDistribution" :key="type">
                            <span class="px-2 py-1 text-xs rounded-full"
                                :class="{
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': type === 'speed',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300': type === 'stamina',
                                    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300': type === 'power',
                                    'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300': type === 'guts',
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': type === 'wit',
                                    'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300': type === 'friend'
                                }">
                                <span x-text="type.charAt(0).toUpperCase() + type.slice(1)"></span>: <span
                                    x-text="count"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Average Stats (WF-011) -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Average Stats</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Avg Bond Level</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                <span x-text="averageBond"></span>%
                            </dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Avg Limit Break</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                <span x-text="averageLimitBreak"></span>★
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Synergy Score (WF-011) -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Synergy Score</h4>
                        <div class="flex items-center gap-2">
                            <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                    :class="{
                                        'bg-green-500': synergyScore >= 70,
                                        'bg-blue-500': synergyScore >= 50 && synergyScore < 70,
                                        'bg-yellow-500': synergyScore >= 30 && synergyScore < 50,
                                        'bg-red-500': synergyScore < 30
                                    }"
                                    :style="'width: ' + synergyScore + '%'">
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white"
                                x-text="synergyScore"></span>
                        </div>
                    </div>
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
    <aside aria-labelledby="library-heading" class="space-y-4">
        <div
            class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col">
            <h2 id="library-heading" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Available Cards
            </h2>

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
            <div class="space-y-2 flex-1 overflow-y-auto" style="max-height: calc(100vh - 400px); min-height: 600px;">
                @foreach ($availableCards as $card)
                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 hover:shadow-md transition-all cursor-pointer transform hover:scale-[1.02]"
                        @click="selectCard({{ $card->id }})" tabindex="0"
                        @keydown.enter="selectCard({{ $card->id }})"
                        @keydown.space.prevent="selectCard({{ $card->id }})">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                @if ($card->artwork_url)
                                    <img src="{{ $card->artwork_url }}" alt="{{ $card->name }}"
                                        class="w-10 h-10 rounded-md object-cover border border-gray-200 dark:border-gray-600 shrink-0">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-md bg-gray-200 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
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
                            </div>
                            <button class="btn btn-xs btn-primary shrink-0">
                                Add
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </aside>

    <!-- Edit Card Details Modal (Inside Alpine Component) -->
    <div x-show="showEditModal" x-cloak @keydown.escape.window="closeEditModal()"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeEditModal()"
                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75">
            </div>

            <!-- Modal panel -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Edit Card in Deck - Slot <span x-text="editingSlot"></span>
                    </h3>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Limit Break Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Limit Break Level (Stars)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="range" x-model.number="editForm.limitBreak" min="0" max="4"
                                step="1"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white min-w-12 text-center">
                                <span x-text="editForm.limitBreak"></span>/4
                            </span>
                        </div>
                        <div class="mt-2 flex gap-1">
                            <template x-for="i in 5" :key="i">
                                <button @click="editForm.limitBreak = i - 1" class="text-2xl transition-colors"
                                    :class="i - 1 <= editForm.limitBreak ? 'text-yellow-400' :
                                        'text-gray-300 dark:text-gray-600'">
                                    ★
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Friendship Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Friendship Level (Bond)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="range" x-model.number="editForm.bondLevel" min="0" max="100"
                                step="5"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white min-w-16 text-center">
                                <span x-text="editForm.bondLevel"></span>%
                            </span>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button @click="editForm.bondLevel = 0" class="btn btn-xs btn-outline">0%</button>
                            <button @click="editForm.bondLevel = 25" class="btn btn-xs btn-outline">25%</button>
                            <button @click="editForm.bondLevel = 50" class="btn btn-xs btn-outline">50%</button>
                            <button @click="editForm.bondLevel = 75" class="btn btn-xs btn-outline">75%</button>
                            <button @click="editForm.bondLevel = 100" class="btn btn-xs btn-outline">100%</button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 justify-end">
                    <button @click="closeEditModal()" class="btn btn-outline">
                        Cancel
                    </button>
                    <button @click="saveEditModal()" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div><!-- End grid -->
    </div><!-- End Alpine component -->
@endsection
