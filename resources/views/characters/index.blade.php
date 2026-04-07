@extends('layouts.app')

@section('title', 'My Characters')

@section('content')
    <x-breadcrumb :items="[['label' => 'Characters']]" />

    @php
        $sortAria = match ($activeFilters['sort']) {
            'name' => 'ascending',
            'created_at' => 'descending',
            default => 'descending',
        };
    @endphp

    <div class="page-stack" x-data="charactersIndexPage()" x-init="init()">
        <div class="page-hero">
            <div class="page-hero__content">
                <div>
                    <div class="page-hero__eyebrow">
                        <span>Character Roster</span>
                    </div>
                    <h1 class="page-hero__title sm:truncate">My Characters</h1>
                    <p class="page-hero__body text-sm sm:text-base">
                        Browse and manage account and seeded runners with server-side filters.
                    </p>
                </div>
                <div class="page-hero__actions">
                    <button type="button" class="btn btn-outline min-h-11" data-testid="compact-toggle" @click="toggleCompactMode"
                        onclick="var _v=localStorage.getItem('characters:compact-mode')==='true';localStorage.setItem('characters:compact-mode',_v?'false':'true');"
                        :aria-pressed="compactMode.toString()">
                        <span x-text="compactMode ? 'Standard Mode' : 'Compact Mode'"></span>
                    </button>
                    <a href="{{ route('characters.create') }}" class="btn btn-primary">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        New Character
                    </a>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('characters.index') }}" @submit="setLoading(true)"
            class="filter-surface space-y-4 p-4" role="search" aria-label="Character search and filters">
            <div class="flex items-center justify-between md:hidden">
                <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Filters</span>
                <button type="button" @click="filtersOpen = !filtersOpen"
                    class="btn btn-outline text-xs" :aria-expanded="filtersOpen.toString()">
                    <span x-show="filtersOpen" x-cloak>Hide</span>
                    <span x-show="!filtersOpen">Show Filters</span>
                </button>
            </div>
            <div x-show="filtersOpen" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label for="search" class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Search</label>
                    <input id="search" name="search" type="text" value="{{ $activeFilters['search'] }}"
                        placeholder="Character name or title"
                        class="form-input block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                </div>

                <div>
                    <label for="scenario" class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Scenario</label>
                    <select id="scenario" name="scenario"
                        class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                        <option value="">All</option>
                        <option value="ura_finale" @selected($activeFilters['scenario'] === 'ura_finale')>URA Finale</option>
                        <option value="unity_cup" @selected($activeFilters['scenario'] === 'unity_cup')>Unity Cup</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Status</label>
                    <select id="status" name="status"
                        class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                        <option value="">All</option>
                        <option value="active" @selected($activeFilters['status'] === 'active')>Active</option>
                        <option value="completed" @selected($activeFilters['status'] === 'completed')>Completed</option>
                        <option value="retired" @selected($activeFilters['status'] === 'retired')>Retired</option>
                        <option value="archived" @selected($activeFilters['status'] === 'archived')>Archived</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Sort</label>
                    <select id="sort" name="sort" onchange="this.form.submit()"
                        aria-sort="{{ $sortAria }}"
                        aria-describedby="sort-help"
                        class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                        <option value="updated_at" @selected($activeFilters['sort'] === 'updated_at')>Recently Updated</option>
                        <option value="created_at" @selected($activeFilters['sort'] === 'created_at')>Recently Created</option>
                        <option value="name" @selected($activeFilters['sort'] === 'name')>Name</option>
                    </select>
                    <p id="sort-help" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Current direction: {{ $sortAria }}
                    </p>
                </div>

                <div>
                    <label for="per_page" class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Per Page</label>
                    <select id="per_page" name="per_page"
                        class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                        <option value="25" @selected($activeFilters['per_page'] === 25)>25</option>
                        <option value="50" @selected($activeFilters['per_page'] === 50)>50</option>
                        <option value="75" @selected($activeFilters['per_page'] === 75)>75</option>
                        <option value="100" @selected($activeFilters['per_page'] === 100)>100</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                    Showing {{ $characters->count() }} of {{ $characters->total() }} characters
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span x-show="!loading">Apply</span>
                        <span x-show="loading" x-cloak>Loading...</span>
                    </button>
                    <a href="{{ route('characters.index') }}" class="btn btn-outline">Clear</a>
                </div>
            </div>
            </div>{{-- /x-show filtersOpen --}}

            <div class="rounded-md border border-neutral-200 bg-neutral-50 p-3 text-xs dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2" aria-label="Stat bar legend" role="note">
                    <span class="font-semibold text-neutral-700 dark:text-neutral-200">Stat Bar Legend</span>
                    <span class="inline-flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        80% and above
                    </span>
                    <span class="inline-flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                        50-79%
                    </span>
                    <span class="inline-flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500" aria-hidden="true"></span>
                        Under 50%
                    </span>
                </div>
            </div>
        </form>

        @php
            $hasActiveFilters = $activeFilters['search'] !== '' || $activeFilters['scenario'] !== '' || $activeFilters['status'] !== '';
            $activeFilterCount = collect([
                $activeFilters['search'],
                $activeFilters['scenario'],
                $activeFilters['status'],
            ])->filter(fn ($value) => $value !== '')->count();
            $charFilterChipLabels = [
                'search'   => ['label' => 'Search', 'value' => $activeFilters['search']],
                'scenario' => ['label' => 'Scenario', 'value' => str_replace('_', ' ', $activeFilters['scenario'])],
                'status'   => ['label' => 'Status', 'value' => $activeFilters['status']],
            ];
        @endphp

        @if ($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2" aria-label="Active filters">
                <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Active:</span>
                @foreach ($charFilterChipLabels as $key => $info)
                    @if ($info['value'] !== '')
                        <a href="{{ route('characters.index', request()->except($key)) }}"
                            class="inline-flex items-center gap-1 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-800 hover:bg-primary-200 dark:bg-primary-900/40 dark:text-primary-300 dark:hover:bg-primary-900/60 transition-colors"
                            title="Remove {{ $info['label'] }} filter">
                            <span>{{ $info['label'] }}: {{ $info['value'] }}</span>
                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </a>
                    @endif
                @endforeach
                <a href="{{ route('characters.index') }}"
                    class="text-xs text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif

        {{-- Stats widget: scenario + status counts as clickable chips --}}
        @if ($characters->total() > 0 && ($scenarioCounts->isNotEmpty() || $statusCounts->isNotEmpty()))
            <aside class="filter-surface p-4" aria-label="Character statistics">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $characters->total() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Total</div>
                    </div>
                    @if ($scenarioCounts->isNotEmpty())
                        <div class="hidden sm:block h-8 border-l border-neutral-200 dark:border-neutral-600"></div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Scenario:</span>
                            @foreach ($scenarioCounts as $scenario => $cnt)
                                <a href="{{ route('characters.index', array_merge(request()->except('page'), ['scenario' => $scenario])) }}"
                                    class="inline-flex items-center rounded px-2 py-1 text-xs font-medium bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                                    title="Filter by {{ str_replace('_', ' ', $scenario) }}">
                                    {{ ucfirst(str_replace('_', ' ', $scenario)) }}: {{ $cnt }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if ($statusCounts->isNotEmpty())
                        <div class="hidden sm:block h-8 border-l border-neutral-200 dark:border-neutral-600"></div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Status:</span>
                            @foreach ($statusCounts as $status => $cnt)
                                <a href="{{ route('characters.index', array_merge(request()->except('page'), ['status' => $status])) }}"
                                    class="inline-flex items-center rounded px-2 py-1 text-xs font-medium capitalize bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 transition-colors"
                                    title="Filter by {{ $status }} status">
                                    {{ ucfirst($status) }}: {{ $cnt }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>
        @endif

        @if ($characters->isEmpty())
            <div class="rounded-lg border border-neutral-200 bg-white py-14 text-center dark:border-neutral-700 dark:bg-neutral-900">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">No characters found</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">Try adjusting your filters or create a new character.</p>
                <a href="{{ route('characters.create') }}" class="btn btn-primary mt-5">Create Character</a>
            </div>
        @else
            {{-- View mode toggle --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-neutral-200 bg-white p-2.5 dark:border-neutral-700 dark:bg-neutral-900" role="group" aria-label="View mode and roster scan controls">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-600 dark:text-neutral-300">
                    <span class="font-semibold">Sort: {{ str_replace('_', ' ', $activeFilters['sort']) }}</span>
                    <span class="font-semibold">Active filters: {{ $activeFilterCount }}</span>
                    <span class="hidden sm:inline">Compare View is optimized for fast stat scan.</span>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" @click="toggleViewMode('list')"
                        data-testid="compare-view-toggle"
                        :class="viewMode === 'list' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'text-neutral-500 hover:bg-neutral-100 hover:text-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200'"
                        class="inline-flex min-h-11 items-center gap-1.5 rounded px-3 py-2 text-sm font-semibold transition-colors"
                        :aria-pressed="(viewMode === 'list').toString()"
                        title="Compare view">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>Compare</span>
                    </button>

                    <button type="button" @click="toggleViewMode('grid')"
                        data-testid="gallery-view-toggle"
                        :class="viewMode === 'grid' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'text-neutral-500 hover:bg-neutral-100 hover:text-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200'"
                        class="inline-flex min-h-11 items-center gap-1.5 rounded px-3 py-2 text-sm font-semibold transition-colors"
                        :aria-pressed="(viewMode === 'grid').toString()"
                        title="Gallery view">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Gallery</span>
                    </button>
                </div>
            </div>

            {{-- Gallery View (grouped by letter) --}}
            <div x-show="viewMode === 'grid'" class="space-y-6">
                @foreach ($groupedCharacters as $letter => $sectionCharacters)
                    <section id="group-{{ $letter }}" aria-labelledby="heading-{{ $letter }}" class="space-y-3">
                        <h2 id="heading-{{ $letter }}"
                            data-testid="group-heading-{{ $letter }}"
                            class="sticky top-16 z-10 rounded-md border border-neutral-200 bg-white/95 px-4 py-2 text-sm font-black tracking-[0.25em] text-neutral-800 backdrop-blur dark:border-neutral-700 dark:bg-neutral-900/95 dark:text-neutral-100">
                            {{ $letter }}
                        </h2>

                        <div class="grid gap-4" :class="compactMode ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4' : 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4'">
                            @foreach ($sectionCharacters as $character)
                                @php
                                    $defaultAvatar = $character['avatar_processed'] ?: ($character['avatar_url'] ?: $character['avatar_fallback_url']);
                                    if (! str_starts_with($defaultAvatar, 'http://') && ! str_starts_with($defaultAvatar, 'https://') && ! str_starts_with($defaultAvatar, '/')) {
                                        $defaultAvatar = '/'.$defaultAvatar;
                                    }
                                    $xData = 'characterVariantCard(' . \Illuminate\Support\Js::from($character) . ', ' . \Illuminate\Support\Js::from($defaultAvatar) . ')';
                                @endphp
                                <x-card
                                    as="article"
                                    :padding="false"
                                    :hover="true"
                                    :x-data="$xData"
                                    x-bind:aria-labelledby="`character-name-${character.id}`"
                                    class="group relative"
                                >
                                    <a :href="`{{ url('/characters') }}/${selectedVariant.id}`"
                                        class="absolute inset-0 z-0 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                        :aria-label="`View ${selectedVariant.name}`">
                                        <span class="sr-only" x-text="`View ${selectedVariant.name}`"></span>
                                    </a>

                                    <div class="pointer-events-none relative z-10 space-y-3 p-4">
                                        <x-card.image class="aspect-4/3">
                                            <img :src="selectedAvatar"
                                                :alt="selectedVariant.name"
                                                class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.03]"
                                                loading="lazy" decoding="async">
                                            <x-slot:overlay>
                                                <span x-show="selectedVariant.is_pinned"
                                                    class="absolute right-2 top-2 rounded-full bg-primary-700 px-2 py-1 text-[11px] font-bold text-white">
                                                    Pinned
                                                </span>
                                            </x-slot:overlay>
                                        </x-card.image>

                                        <x-card.header>
                                            <h3 :id="`character-name-${character.id}`"
                                                class="text-base font-black tracking-tight text-neutral-900 dark:text-neutral-100"
                                                x-text="character.name"></h3>

                                            <x-slot:badges>
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"
                                                    x-text="selectedScenarioLabel"></span>
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium capitalize bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"
                                                    x-text="selectedVariant.status"></span>
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"
                                                    x-text="selectedVariant.is_seeded ? 'Seeded' : 'My Run'"></span>
                                            </x-slot:badges>

                                            <x-slot:subtitle>
                                                <div class="mb-1 flex items-center justify-between text-xs text-neutral-600 dark:text-neutral-300">
                                                    <span class="font-semibold">Career</span>
                                                    <span x-text="`${selectedVariant.progress}%`"></span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded bg-neutral-200 dark:bg-neutral-700" role="progressbar" :aria-valuenow="selectedVariant.progress" aria-valuemin="0" aria-valuemax="100" :aria-label="`Career progress ${selectedVariant.progress}%`">
                                                    <div class="h-full rounded bg-primary-500" :style="`width: ${selectedVariant.progress}%`"></div>
                                                </div>
                                            </x-slot:subtitle>
                                        </x-card.header>

                                        <x-card.stats aria-label="Character stats">
                                            <template x-for="stat in statKeys" :key="stat">
                                                <li class="space-y-1">
                                                    <div class="flex items-center justify-between text-neutral-600 dark:text-neutral-300">
                                                        <span class="font-semibold" x-text="statLabel(stat)"></span>
                                                        <span class="font-bold text-neutral-800 dark:text-neutral-100" x-text="displayStat(stat)"></span>
                                                    </div>
                                                    <div class="h-1.5 overflow-hidden rounded bg-neutral-200 dark:bg-neutral-700" role="progressbar" :aria-valuenow="statPercent(stat)" aria-valuemin="0" aria-valuemax="100" :aria-label="`${statLabel(stat)} ${displayStat(stat)} of 1200`">
                                                        <div class="h-full rounded" :class="statBarColor(statPercent(stat))" :style="`width: ${statPercent(stat)}%`"></div>
                                                    </div>
                                                </li>
                                            </template>
                                        </x-card.stats>

                                        <button
                                            type="button"
                                            @click.stop.prevent="showSecondary = !showSecondary"
                                            class="pointer-events-auto inline-flex min-h-11 items-center gap-1 rounded px-2 py-1 text-xs font-semibold text-neutral-600 underline decoration-dotted underline-offset-4 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:text-neutral-300 dark:hover:text-primary-300"
                                            :aria-expanded="showSecondary.toString()"
                                            :aria-label="showSecondary ? 'Hide additional details' : 'Show additional details'"
                                        >
                                            <span x-text="showSecondary ? 'Hide details' : 'Show details'"></span>
                                        </button>

                                        <div x-show="showSecondary" x-cloak class="space-y-3 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                                            <x-card.goal>
                                                <template x-if="selectedVariant.next_goal">
                                                    <div>
                                                        <p class="font-semibold text-neutral-800 dark:text-neutral-100" x-text="`Next Goal: ${selectedVariant.next_goal.name}`"></p>
                                                        <p class="mt-1 text-neutral-600 dark:text-neutral-300">
                                                            <span x-text="`${selectedVariant.next_goal.grade} • ${selectedVariant.next_goal.distance}m`"></span>
                                                        </p>
                                                        <button
                                                            x-show="selectedVariant.remaining_goal_count > 0"
                                                            @click.stop.prevent="showAllGoals = !showAllGoals"
                                                            type="button"
                                                            class="pointer-events-auto mt-2 inline-flex min-h-11 items-center rounded bg-neutral-200 px-2 py-1 font-semibold text-neutral-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:bg-neutral-700 dark:text-neutral-200"
                                                            :aria-expanded="showAllGoals.toString()"
                                                            :aria-label="`Show ${selectedVariant.remaining_goal_count} more goals`"
                                                        >
                                                            <span x-text="`+${selectedVariant.remaining_goal_count} more`"></span>
                                                        </button>
                                                        <ul x-show="showAllGoals" x-cloak class="mt-2 space-y-1 text-neutral-700 dark:text-neutral-200">
                                                            <template x-for="goal in selectedVariant.goal_races" :key="`${selectedVariant.id}-${goal.id}`">
                                                                <li>
                                                                    <span x-text="`${goal.grade}: ${goal.name}`"></span>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedVariant.next_goal">
                                                    <p class="font-semibold text-neutral-600 dark:text-neutral-300">No goal races configured.</p>
                                                </template>
                                            </x-card.goal>

                                            <x-card.footer x-show="variants.length > 1">
                                                <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Versions</span>
                                                <x-character-variant-switcher class="pointer-events-auto" />
                                            </x-card.footer>
                                        </div>
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>{{-- /grid view --}}

            {{-- Compare View --}}
            <div x-show="viewMode === 'list'" x-cloak class="space-y-0 overflow-hidden rounded-lg border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900" data-testid="compare-view-list">
                <div class="hidden grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_auto] items-center gap-2 border-b border-neutral-200 bg-neutral-50 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-neutral-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 md:grid">
                    <span>Identity</span>
                    <span>Core Stats</span>
                    <span class="text-right">Progress</span>
                </div>
                @foreach ($groupedCharacters as $letter => $sectionCharacters)
                    @foreach ($sectionCharacters as $character)
                        <x-character-list-item :character="$character" />
                    @endforeach
                @endforeach
            </div>

            <div class="pt-4" data-testid="roster-pagination">
                {{ $characters->links() }}
            </div>
        @endif

        <button type="button" @click="scrollToTop" x-show="showBackToTop" x-cloak data-testid="back-to-top"
            class="fixed bottom-6 right-6 rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-primary-500 focus:outline-hidden focus:ring-2 focus:ring-primary-400">
            Back to top
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        window.charactersIndexPage = window.charactersIndexPage || function () {
            return {
                compactMode: false,
                showBackToTop: false,
                loading: false,
                viewMode: localStorage.getItem('characters:viewMode') ?? 'list',
                filtersOpen: window.innerWidth >= 768,
                init() {
                    this.compactMode = window.localStorage.getItem('characters:compact-mode') === 'true';
                    window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
                    this.handleScroll();
                },
                setLoading(value) {
                    this.loading = Boolean(value);
                },
                toggleCompactMode() {
                    this.compactMode = !this.compactMode;
                    window.localStorage.setItem('characters:compact-mode', String(this.compactMode));
                },
                toggleViewMode(mode) {
                    this.viewMode = mode;
                    localStorage.setItem('characters:viewMode', mode);
                },
                handleScroll() {
                    this.showBackToTop = window.scrollY > 500;
                },
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
            };
        };

        window.characterVariantCard = window.characterVariantCard || function (character, defaultAvatar) {
            return {
                character,
                defaultAvatar,
                selectedVariantId: String(character.id),
                showAllGoals: false,
                showSecondary: false,
                statKeys: ['speed', 'stamina', 'power', 'guts', 'wit'],
                get variants() {
                    return this.character.variants && this.character.variants.length > 0
                        ? this.character.variants
                        : [this.character];
                },
                get selectedVariant() {
                    return this.variants.find((variant) => String(variant.id) === String(this.selectedVariantId)) || this.variants[0];
                },
                get selectedAvatar() {
                    const avatar = this.selectedVariant.avatar_processed || this.selectedVariant.avatar_url || this.selectedVariant.avatar_fallback_url || this.defaultAvatar;
                    return avatar && !avatar.startsWith('http://') && !avatar.startsWith('https://') && !avatar.startsWith('/')
                        ? `/${avatar}`
                        : avatar;
                },
                get selectedScenarioLabel() {
                    return String(this.selectedVariant.scenario_type || '').replaceAll('_', ' ').toUpperCase();
                },
                selectVariant(variantId) {
                    this.selectedVariantId = String(variantId);
                    this.showAllGoals = false;
                    this.showSecondary = false;
                },
                formatVariantLabel(variant) {
                    const status = variant.status ? ` (${variant.status})` : '';
                    const scenario = variant.scenario_type ? ` - ${String(variant.scenario_type).replaceAll('_', ' ')}` : '';
                    return `${variant.name}${scenario}${status}`;
                },
                statLabel(statKey) {
                    return statKey.charAt(0).toUpperCase() + statKey.slice(1);
                },
                displayStat(statKey) {
                    const value = this.selectedVariant.current_stats?.[statKey];
                    return Number.isInteger(value) ? value : '—';
                },
                statPercent(statKey) {
                    const value = this.selectedVariant.current_stats?.[statKey];
                    if (!Number.isInteger(value)) {
                        return 0;
                    }

                    return Math.min(100, Math.round((value / 1200) * 100));
                },
                statBarColor(percent) {
                    if (percent >= 80) {
                        return 'bg-emerald-500';
                    }

                    if (percent >= 50) {
                        return 'bg-amber-500';
                    }

                    return 'bg-rose-500';
                },
            };
        };

        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined') {
                Alpine.data('charactersIndexPage', window.charactersIndexPage);
                Alpine.data('characterVariantCard', window.characterVariantCard);
            }
        });
    </script>
    @vite('resources/js/pages/characters/index.js')
@endpush
