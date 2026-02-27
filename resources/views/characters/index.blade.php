@extends('layouts.app')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Characters']]" />

    <div class="space-y-6" x-data="charactersList()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    My Characters
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your Umamusume training career strategies
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <a href="{{ route('external-data.browse') }}" class="btn btn-outline">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Browse External Data
                </a>
                <a href="{{ route('characters.create') }}" class="btn btn-primary">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    New Character
                </a>
            </div>
        </div>

        <!-- Instant Search Filtering System -->
        <div class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Phase 1: Search Bar (Instant) -->
                <div class="p-4">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" x-model="filters.search" @input="filterCharacters"
                            class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Search characters... (e.g., Vodka, Special Week)">
                    </div>
                </div>

                <!-- Phase 2: Dropdown Filters (Instant) -->
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scenario</label>
                            <select x-model="filters.scenario" @change="filterCharacters"
                                class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Scenarios</option>
                                <option value="ura_finale">URA Finale</option>
                                <option value="unity_cup">Unity Cup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select x-model="filters.status" @change="filterCharacters"
                                class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort</label>
                            <select x-model="filters.sort" @change="filterCharacters"
                                class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="name">Name</option>
                                <option value="updated_at">Recently Updated</option>
                                <option value="created_at">Recently Created</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button @click="clearFilters" type="button" class="btn btn-secondary w-full"
                                x-show="hasActiveFilters">
                                Clear Filters
                            </button>
                            <div x-show="!hasActiveFilters" class="text-sm text-gray-500 dark:text-gray-400 py-2">
                                <span x-text="filteredCharacters.length"></span> characters
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
            <div>
                Showing <span class="font-semibold" x-text="filteredCharacters.length"></span> of
                <span class="font-semibold" x-text="allCharacters.length"></span> characters
            </div>
        </div>

        <!-- Content -->
        <div x-show="filteredCharacters.length > 0">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-4">
                <template x-for="character in filteredCharacters" :key="character.id">
                    <a :href="`/characters/${character.id}`" class="block relative">
                        <div x-show="character.is_pinned"
                            class="absolute -top-2 -right-2 z-10 bg-primary-500 text-white rounded-full p-1.5 shadow-lg"
                            title="Pinned">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                            </svg>
                        </div>
                        <div
                            class="character-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="aspect-3/4 relative">
                                <img :src="character.avatar_url || '/images/trainee_images/default.png'"
                                    :alt="character.name" class="w-full h-full object-cover">
                            </div>
                            <div class="p-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                                    x-text="character.name"></h3>
                                <div class="mt-1 grid grid-cols-5 gap-1 text-xs">
                                    <div class="text-center">
                                        <div class="text-stat-speed-600 dark:text-stat-speed-400 font-bold"
                                            x-text="character.current_stats?.speed || 0"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-stat-stamina-600 dark:text-stat-stamina-400 font-bold"
                                            x-text="character.current_stats?.stamina || 0"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-stat-power-600 dark:text-stat-power-400 font-bold"
                                            x-text="character.current_stats?.power || 0"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-stat-guts-600 dark:text-stat-guts-400 font-bold"
                                            x-text="character.current_stats?.guts || 0"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-stat-wit-600 dark:text-stat-wit-400 font-bold"
                                            x-text="character.current_stats?.wit || 0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        <div x-show="filteredCharacters.length === 0"
            class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No characters found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Try adjusting your filters or get started by creating a new character profile.
            </p>
            <div class="mt-6">
                <a href="{{ route('characters.create') }}" class="btn btn-primary">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Create Character
                </a>
            </div>
        </div>
    </div>
@endsection

{{-- JS extracted to resources/js/pages/characters/index.js --}}
@push('scripts')
    <script id="characters-data" type="application/json">
        {!! json_encode($characters) !!}
    </script>
    @vite('resources/js/pages/characters/index.js')
@endpush
