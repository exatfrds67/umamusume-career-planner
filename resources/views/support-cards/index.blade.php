@extends('layouts.app')

@section('title', 'Support Cards - ' . config('app.name'))

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Support Cards']]" />

    <div class="page-stack" x-data="supportCardManager()">
        <!-- Header -->
        <header class="page-hero">
            <div class="page-hero__content">
            <div>
                <div class="page-hero__eyebrow">
                    <span>Collection Deck</span>
                </div>
                <h1 class="page-hero__title">Support Cards</h1>
                <p class="page-hero__body text-sm sm:text-base">
                    Browse and manage your support card collection
                </p>
            </div>
            <div class="page-hero__actions">
                @auth
                    <a href="{{ route('characters.index') }}" class="btn btn-primary flex items-center gap-2"
                        title="Select a character to build a deck">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Build Deck
                    </a>
                @endauth
                <button @click="showExternalImport = !showExternalImport" class="btn btn-outline"
                    :class="{ 'ring-2 ring-primary-400': showExternalImport }" :aria-expanded="showExternalImport.toString()" aria-controls="external-import-panel">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span x-show="!showExternalImport">Import from API</span>
                    <span x-show="showExternalImport" x-cloak>Close Import</span>
                </button>
            </div>
            </div>
        </header>

        <!-- External API Import Panel -->
        <div id="external-import-panel" x-show="showExternalImport" x-transition
            class="filter-surface p-6 border-2 border-green-200 dark:border-green-800 bg-linear-to-br from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20" role="region" aria-label="External API Import">
            @include('support-cards.partials.external-import')
        </div>

        <!-- Filters -->
        <aside class="filter-surface p-4" aria-label="Filters">
            <div class="flex items-center justify-between md:hidden mb-3">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                    Filters
                    <template x-if="activeFilterCount > 0">
                        <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-primary-600 px-1.5 py-0.5 text-xs font-bold text-white" x-text="activeFilterCount"></span>
                    </template>
                </h2>
                <button type="button" @click="filtersOpen = !filtersOpen"
                    class="btn btn-outline text-xs" :aria-expanded="filtersOpen.toString()">
                    <span x-show="filtersOpen" x-cloak>Hide Filters</span>
                    <span x-show="!filtersOpen">Show Filters</span>
                </button>
            </div>
            <h2 class="sr-only hidden md:block">Collection Filters</h2>
            <div x-show="filtersOpen" x-cloak>
            <form method="GET" action="{{ route('support-cards.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search -->
                    <div class="md:col-span-3">
                        <label for="search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Search
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-neutral-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                class="form-input block w-full rounded-md border-neutral-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white"
                                placeholder="Search by name or character...">
                        </div>
                    </div>

                    <!-- Card Type -->
                    <div class="md:col-span-2">
                        <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Type
                        </label>
                        <select id="type" name="type"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
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
                        <label for="rarity" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Rarity
                        </label>
                        <select id="rarity" name="rarity"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">All</option>
                            @foreach ($rarities as $rarity)
                                <option value="{{ $rarity }}" {{ request('rarity') === $rarity ? 'selected' : '' }}>
                                    {{ $rarity }} ({{ $totalRarityCounts->get($rarity, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meta Tier -->
                    <div class="md:col-span-1">
                        <label for="tier" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Tier
                        </label>
                        <select id="tier" name="tier"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
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
                        <label for="bond_level" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Bond
                        </label>
                        <select id="bond_level" name="bond_level"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">Any</option>
                            <option value="100" {{ request('bond_level') === '100' ? 'selected' : '' }}>100</option>
                            <option value="80" {{ request('bond_level') === '80' ? 'selected' : '' }}>80+</option>
                            <option value="50" {{ request('bond_level') === '50' ? 'selected' : '' }}>50+</option>
                            <option value="low" {{ request('bond_level') === 'low' ? 'selected' : '' }}>&lt;50</option>
                        </select>
                    </div>

                    <!-- Limit Break (WF-010 requirement) -->
                    <div class="md:col-span-1">
                        <label for="limit_break" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1" title="Limit Break">
                            LB <span class="sr-only">(Limit Break)</span>
                        </label>
                        <select id="limit_break" name="limit_break"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
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
                        <label for="sort" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Sort
                        </label>
                        <select id="sort" name="sort" onchange="this.form.submit()"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="tier" {{ request('sort', 'tier') === 'tier' ? 'selected' : '' }}>Tier</option>
                            <option value="usage_rate" {{ request('sort') === 'usage_rate' ? 'selected' : '' }}>Most Used</option>
                            <option value="rarity" {{ request('sort') === 'rarity' ? 'selected' : '' }}>Rarity</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Type</option>
                            <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Recent</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="md:col-span-2 flex gap-2 h-full items-end pb-0.5">
                        <button type="submit" class="btn btn-secondary flex-1 h-9.5 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        @if (request()->anyFilled(['search', 'type', 'rarity', 'tier', 'bond_level', 'limit_break', 'sort']))
                            <a href="{{ route('support-cards.index') }}" class="btn btn-outline px-3"
                                title="Clear Filters" aria-label="Clear all filters">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
            </div>
        </aside>

        {{-- F-04: Active filter chips --}}
        @php
            $activeFilterParams = [
                'search'      => request('search'),
                'type'        => request('type'),
                'rarity'      => request('rarity'),
                'tier'        => request('tier'),
                'bond_level'  => request('bond_level'),
                'limit_break' => request('limit_break'),
            ];
            $filterChipLabels = [
                'search'      => 'Search',
                'type'        => 'Type',
                'rarity'      => 'Rarity',
                'tier'        => 'Tier',
                'bond_level'  => 'Bond',
                'limit_break' => 'LB',
            ];
        @endphp
        @if (collect($activeFilterParams)->filter()->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2" aria-label="Active filters">
                <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Active:</span>
                @foreach ($activeFilterParams as $key => $value)
                    @if ($value)
                        @php
                            $clearUrl = route('support-cards.index', request()->except($key));
                        @endphp
                        <a href="{{ $clearUrl }}"
                            class="inline-flex items-center gap-1 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-800 hover:bg-primary-200 dark:bg-primary-900/40 dark:text-primary-300 dark:hover:bg-primary-900/60 transition-colors"
                            title="Remove {{ $filterChipLabels[$key] }} filter">
                            <span>{{ $filterChipLabels[$key] }}: {{ $value }}</span>
                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </a>
                    @endif
                @endforeach
                <a href="{{ route('support-cards.index') }}"
                    class="text-xs text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif

        <!-- Collection Stats Widget -->
        <aside class="filter-surface p-4" aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">Collection Statistics</h2>
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $cards->total() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Total Cards</div>
                    </div>
                    <div class="hidden sm:block h-8 border-l border-neutral-200 dark:border-neutral-600"></div>
                    <div class="flex gap-4">
                        @foreach (['SSR' => 'text-yellow-500', 'SR' => 'text-purple-500', 'R' => 'text-blue-500'] as $r => $color)
                            <div class="text-center">
                                <div class="text-lg font-semibold {{ $color }}">{{ $totalRarityCounts->get($r, 0) }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $r }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="hidden sm:block h-8 border-l border-neutral-200 dark:border-neutral-600"></div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($typeCounts as $type => $count)
                            <a href="{{ route('support-cards.index', array_merge(request()->except('page'), ['type' => $type])) }}"
                                class="inline-flex items-center rounded px-2 py-1 text-xs font-medium bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                                title="Filter by {{ ucfirst($type) }} cards">
                                {{ ucfirst($type) }}: {{ $count }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <!-- Cards Grid / List -->
        <section aria-label="Support Card List">
            @if ($cards->count() > 0)
                {{-- F-10: View mode toggle row --}}
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Showing {{ $cards->firstItem() }}–{{ $cards->lastItem() }} of {{ $cards->total() }} cards
                    </p>
                    <div class="flex items-center gap-1" role="group" aria-label="View mode">
                        <button type="button" @click="toggleViewMode('grid')"
                            :class="viewMode === 'grid' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200'"
                            class="rounded p-2 transition-colors" :aria-pressed="(viewMode === 'grid').toString()" title="Grid view">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="sr-only">Grid view</span>
                        </button>
                        <button type="button" @click="toggleViewMode('list')"
                            :class="viewMode === 'list' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200'"
                            class="rounded p-2 transition-colors" :aria-pressed="(viewMode === 'list').toString()" title="List view">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span class="sr-only">List view</span>
                        </button>
                    </div>
                </div>

                {{-- Grid View --}}
                <div x-show="viewMode === 'grid'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($cards as $card)
                        <x-support-card-tile :card="$card" />
                    @endforeach
                </div>

                {{-- List View --}}
                <div x-show="viewMode === 'list'" x-cloak class="space-y-2">
                    @foreach ($cards as $card)
                        <x-support-card-list-item :card="$card" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $cards->links() }}
                </div>
            @else
                {{-- F-19: Empty state with "Clear all filters" --}}
                <div
                    class="text-center py-12 bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No cards found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Try adjusting your filters or search criteria.
                    </p>
                    @if (request()->anyFilled(['search', 'type', 'rarity', 'tier', 'bond_level', 'limit_break']))
                        <a href="{{ route('support-cards.index') }}" class="btn btn-primary mt-4">
                            Clear all filters
                        </a>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
