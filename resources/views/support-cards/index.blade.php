@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="supportCardManager()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support Cards</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse and manage your support card collection
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <button @click="showExternalImport = !showExternalImport" class="btn btn-success"
                    :class="{ 'ring-2 ring-green-500': showExternalImport }">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span x-show="!showExternalImport">Import from API</span>
                    <span x-show="showExternalImport">Close Import</span>
                </button>
                <button @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'" class="btn btn-outline"
                    :aria-label="viewMode === 'grid' ? 'Switch to list view' : 'Switch to grid view'">
                    <svg x-show="viewMode === 'grid'" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="viewMode === 'list'" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- External API Import Panel -->
        <div x-show="showExternalImport" x-transition
            class="card bg-gradient-to-br from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 p-6 rounded-lg shadow-lg border-2 border-green-200 dark:border-green-800">
            @include('support-cards.partials.external-import')
        </div>

        <!-- Filters -->
        <div class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('support-cards.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search -->
                    <div class="md:col-span-3">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Search by name or character...">
                        </div>
                    </div>

                    <!-- Card Type -->
                    <div class="md:col-span-2">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Type
                        </label>
                        <select id="type" name="type"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Types</option>
                            @foreach ($cardTypes as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rarity -->
                    <div class="md:col-span-1">
                        <label for="rarity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Rarity
                        </label>
                        <select id="rarity" name="rarity"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach ($rarities as $rarity)
                                <option value="{{ $rarity }}" {{ request('rarity') === $rarity ? 'selected' : '' }}>
                                    {{ $rarity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meta Tier -->
                    <div class="md:col-span-1">
                        <label for="tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tier
                        </label>
                        <select id="tier" name="tier"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach ($tiers as $tier)
                                <option value="{{ $tier }}" {{ request('tier') === $tier ? 'selected' : '' }}>
                                    {{ $tier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Bond Level (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="bond_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Bond
                        </label>
                        <select id="bond_level" name="bond_level"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Any</option>
                            <option value="100" {{ request('bond_level') === '100' ? 'selected' : '' }}>100</option>
                            <option value="80" {{ request('bond_level') === '80' ? 'selected' : '' }}>80+</option>
                            <option value="50" {{ request('bond_level') === '50' ? 'selected' : '' }}>50+</option>
                            <option value="low" {{ request('bond_level') === 'low' ? 'selected' : '' }}>&lt;50</option>
                        </select>
                    </div>
                    
                    <!-- Limit Break (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="limit_break" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            LB
                        </label>
                        <select id="limit_break" name="limit_break"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Any</option>
                            <option value="4" {{ request('limit_break') === '4' ? 'selected' : '' }}>4★</option>
                            <option value="3" {{ request('limit_break') === '3' ? 'selected' : '' }}>3★</option>
                            <option value="2" {{ request('limit_break') === '2' ? 'selected' : '' }}>2★</option>
                            <option value="1" {{ request('limit_break') === '1' ? 'selected' : '' }}>1★</option>
                            <option value="0" {{ request('limit_break') === '0' ? 'selected' : '' }}>0★</option>
                        </select>
                    </div>
                    
                    <!-- Sort (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sort
                        </label>
                        <select id="sort" name="sort"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="rarity" {{ request('sort') === 'rarity' ? 'selected' : '' }}>Rarity</option>
                            <option value="tier" {{ request('sort') === 'tier' ? 'selected' : '' }}>Tier</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Type</option>
                            <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Recent</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="md:col-span-2 flex gap-2 h-full items-end pb-0.5">
                        <button type="submit" class="btn btn-secondary flex-1 h-[38px] flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        @if (request()->anyFilled(['search', 'type', 'rarity', 'tier', 'bond_level', 'limit_break', 'sort']))
                            <a href="{{ route('support-cards.index') }}" class="btn btn-outline px-3"
                                title="Clear Filters">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Collection Stats Widget (WF-010 requirement) -->
        <div class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $cards->total() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Cards</div>
                    </div>
                    <div class="h-8 border-l border-gray-200 dark:border-gray-600"></div>
                    <div class="flex gap-4">
                        @php
                            $rarityCounts = $cards->getCollection()->groupBy(fn($c) => $c->rarity)->map->count();
                        @endphp
                        @foreach (['SSR' => 'text-yellow-500', 'SR' => 'text-purple-500', 'R' => 'text-blue-500'] as $r => $color)
                            <div class="text-center">
                                <div class="text-lg font-semibold {{ $color }}">{{ $rarityCounts->get($r, 0) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $r }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="h-8 border-l border-gray-200 dark:border-gray-600"></div>
                    <div class="flex gap-3 text-xs">
                        @php
                            $typeCounts = $cards->getCollection()->groupBy(fn($c) => $c->card_type)->map->count();
                        @endphp
                        @foreach ($typeCounts as $type => $count)
                            <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ ucfirst($type) }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @if(auth()->check())
                <a href="{{ route('support-cards.deck-builder') }}" class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Build Deck
                </a>
                @endif
            </div>
        </div>

        <!-- Cards Grid -->
        @if ($cards->count() > 0)
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($cards as $card)
                    <x-support-card-tile :card="$card" />
                @endforeach
            </div>

            <!-- Cards List -->
            <div x-show="viewMode === 'list'" class="space-y-3">
                @foreach ($cards as $card)
                    <x-support-card-list-item :card="$card" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $cards->links() }}
            </div>
        @else
            <div
                class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No cards found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Try adjusting your filters or search criteria.
                </p>
            </div>
        @endif
    </div>

    <script>
        function supportCardManager() {
            return {
                viewMode: localStorage.getItem('supportCardViewMode') || 'grid',
                showExternalImport: false,

                // External API state
                externalCards: [],
                externalLoading: false,
                externalError: null,
                externalFilters: {
                    rarity: '',
                    importStatus: ''
                },
                importedCards: new Set(),

                init() {
                    this.$watch('viewMode', value => {
                        localStorage.setItem('supportCardViewMode', value);
                    });

                    // Load external cards when panel opens
                    this.$watch('showExternalImport', value => {
                        if (value && this.externalCards.length === 0) {
                            this.loadExternalCards();
                        }
                    });
                },

                async loadExternalCards() {
                    this.externalLoading = true;
                    this.externalError = null;

                    try {
                        const response = await fetch('/api/external/support-cards', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.externalCards = data.data || [];
                        } else {
                            throw new Error(data.message || 'Failed to fetch support cards');
                        }
                    } catch (error) {
                        console.error('External API error:', error);
                        this.externalError = error.message || 'Failed to load external support cards';
                    } finally {
                        this.externalLoading = false;
                    }
                },

                async importCard(card) {
                    if (this.importedCards.has(card.id)) {
                        return; // Already imported
                    }

                    try {
                        // Construct image URL from card ID (gametora.com pattern)
                        const imageUrl = card.id ?
                            `https://gametora.com/images/umamusume/supports/tex_support_card_${card.id}.png` :
                            null;

                        const response = await fetch('/api/support-cards/import-external', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                external_id: card.id,
                                title_en: card.title_en || card.name,
                                chara_id: card.chara_id,
                                gametora: card.gametora,
                                rarity: card.rarity,
                                image_url: imageUrl,
                                source: 'umapyoi.net'
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.importedCards.add(card.id);

                            // Show success notification
                            this.showNotification('success', result.message || 'Card imported successfully!');

                            // Reload page after a short delay to show the new card
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            throw new Error(result.message || 'Import failed');
                        }
                    } catch (error) {
                        console.error('Import error:', error);
                        this.showNotification('error', error.message || 'Failed to import card');
                    }
                },

                isImported(cardId) {
                    return this.importedCards.has(cardId);
                },

                filteredExternalCards() {
                    let filtered = this.externalCards;

                    if (this.externalFilters.rarity) {
                        filtered = filtered.filter(card => card.rarity === this.externalFilters.rarity);
                    }

                    if (this.externalFilters.importStatus === 'imported') {
                        filtered = filtered.filter(card => this.isImported(card.id));
                    } else if (this.externalFilters.importStatus === 'not_imported') {
                        filtered = filtered.filter(card => !this.isImported(card.id));
                    }

                    return filtered;
                },

                showNotification(type, message) {
                    // Simple notification - you can enhance this with a toast library
                    const color = type === 'success' ? 'green' : 'red';
                    const notification = document.createElement('div');
                    notification.className =
                        `fixed top-4 right-4 bg-${color}-100 border border-${color}-400 text-${color}-700 px-4 py-3 rounded shadow-lg z-50`;
                    notification.textContent = message;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
            }
        }
    </script>
@endsection
