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

        <!-- Filters -->
        <div class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('support-cards.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search -->
                    <div class="md:col-span-4">
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
                    <div class="md:col-span-2">
                        <label for="rarity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Rarity
                        </label>
                        <select id="rarity" name="rarity"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Rarities</option>
                            @foreach ($rarities as $rarity)
                                <option value="{{ $rarity }}" {{ request('rarity') === $rarity ? 'selected' : '' }}>
                                    {{ $rarity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meta Tier -->
                    <div class="md:col-span-2">
                        <label for="tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Meta Tier
                        </label>
                        <select id="tier" name="tier"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Tiers</option>
                            @foreach ($tiers as $tier)
                                <option value="{{ $tier }}" {{ request('tier') === $tier ? 'selected' : '' }}>
                                    {{ $tier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        @if (request()->anyFilled(['search', 'type', 'rarity', 'tier']))
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
                init() {
                    this.$watch('viewMode', value => {
                        localStorage.setItem('supportCardViewMode', value);
                    });
                }
            }
        }
    </script>
@endsection
