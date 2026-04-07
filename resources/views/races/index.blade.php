@extends('layouts.app')

@section('title', 'Race Calendar')

@section('content')
    <x-breadcrumb :items="[['label' => 'Races']]" />

    @php
        $yearOrder = ['year_1', 'year_2', 'year_other'];
        $yearLabels = [
            'year_1'     => 'Year 1 — Junior',
            'year_2'     => 'Year 2 — Classic',
            'year_other' => 'Year 3+ — Senior',
        ];
        $seasonOrder = ['spring', 'summer', 'autumn', 'winter', 'other'];
        $seasonLabels = [
            'spring' => 'Spring',
            'summer' => 'Summer',
            'autumn' => 'Autumn',
            'winter' => 'Winter',
            'other'  => 'Year-Round',
        ];
        $seasonIcons = [
            'spring' => '🌸',
            'summer' => '☀️',
            'autumn' => '🍂',
            'winter' => '❄️',
            'other'  => '📅',
        ];

        $byYear = $catalog->getCollection()->groupBy(function ($race) {
            return match (true) {
                $race->year_in_scenario === 1 => 'year_1',
                $race->year_in_scenario === 2 => 'year_2',
                default => 'year_other',
            };
        });

        // Build season keys for Alpine initial state
        $seasonKeys = [];
        foreach ($yearOrder as $yearKey) {
            if ($byYear->has($yearKey)) {
                $yearSeasons = $byYear[$yearKey]->groupBy(fn ($r) => $r->season ?? 'other');
                foreach ($seasonOrder as $season) {
                    if ($yearSeasons->has($season)) {
                        $seasonKeys[] = $yearKey . '_' . $season;
                    }
                }
            }
        }

        $filterChipLabels = [
            'grade'    => 'Grade',
            'phase'    => 'Phase',
            'surface'  => 'Surface',
            'distance' => 'Distance',
            'season'   => 'Season',
            'venue'    => 'Venue',
            'min_fans' => 'Min Fans',
        ];

        $hasActiveFilters = collect($activeFilters)
            ->except('sort')
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->isNotEmpty();

        $activeFilterCount = collect($activeFilters)
            ->except('sort')
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->count();

        $visibleCount = $catalog->count();
        $totalCount = $catalog->total();
    @endphp

    <div class="page-stack"
        x-data="{
            showFilters: window.innerWidth >= 1024,
            openSeasons: {{ Js::from(array_fill_keys($seasonKeys, false)) }},
            init() {
                if (window.innerWidth >= 1024) {
                    Object.keys(this.openSeasons).forEach(k => this.openSeasons[k] = true);
                }
            },
            toggleSeason(key) {
                if (this.openSeasons[key] === undefined) {
                    this.openSeasons[key] = false;
                }
                this.openSeasons[key] = !this.openSeasons[key];
            }
        }">

        {{-- Header --}}
        <header class="page-hero">
            <div class="page-hero__content">
                <div>
                    <div class="page-hero__eyebrow">
                        <span>Campaign Planning</span>
                    </div>
                    <h1 class="page-hero__title">Race Calendar</h1>
                    <p class="page-hero__body text-sm sm:text-base">
                        All available races in the URA scenario — Junior, Classic, and Senior phases
                    </p>
                </div>
                <div class="page-hero__actions">
                    <span class="hero-chip">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ $catalog->total() }} Races
                    </span>

                    @if ($hasActiveFilters)
                        <span class="hero-chip">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                            </svg>
                            {{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'filter' : 'filters' }} active
                        </span>
                    @endif

                    {{-- Mobile filter toggle (hidden on lg+ since filter is always visible) --}}
                    <button type="button"
                        class="lg:hidden btn btn-outline"
                        @click="showFilters = !showFilters"
                        :aria-expanded="showFilters.toString()"
                        aria-controls="race-filter-form">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>
                        <span x-text="showFilters ? 'Hide Filters' : 'Filters'">Filters</span>
                    </button>
                </div>
            </div>
        </header>

        @php
            $visibleYearKeys = collect($yearOrder)->filter(fn ($key) => $byYear->has($key))->values();
        @endphp

        @if ($visibleYearKeys->isNotEmpty())
            <nav class="race-year-nav" aria-label="Jump to race year groups">
                @foreach ($visibleYearKeys as $yearKey)
                    <a href="#year-{{ $yearKey }}-heading" class="race-year-nav__link">
                        <span>{{ $yearLabels[$yearKey] }}</span>
                        <span class="race-year-nav__count">{{ $byYear[$yearKey]->count() }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- Filter Form (always visible on desktop, toggled on mobile) --}}
        <aside id="race-filter-form"
            class="filter-surface race-filters-sticky p-4"
            :class="{ 'hidden': !showFilters }"
            aria-label="Race Filters">
            <h2 class="sr-only">Race Filters</h2>
            <form method="GET" action="{{ route('races.index') }}" class="space-y-4"
                role="search" aria-label="Filter and sort races">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

                    {{-- Grade --}}
                    <div>
                        <label for="grade"
                            class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                            Grade
                        </label>
                        <select id="grade" name="grade"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                            <option value="">All Grades</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade }}" @selected($activeFilters['grade'] === $grade)>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Season --}}
                    <div>
                        <label for="season"
                            class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                            Season
                        </label>
                        <select id="season" name="season"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                            <option value="">All Seasons</option>
                            @foreach ($availableSeasons as $season)
                                <option value="{{ $season }}" @selected($activeFilters['season'] === $season)>
                                    {{ ucfirst($season) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Venue --}}
                    <div>
                        <label for="venue"
                            class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                            Venue
                        </label>
                        <select id="venue" name="venue"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                            <option value="">All Venues</option>
                            @foreach ($availableVenues as $venue)
                                <option value="{{ $venue }}" @selected($activeFilters['venue'] === $venue)>
                                    {{ $venue }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Minimum Fan Requirement --}}
                    <div>
                        <label for="min_fans"
                            class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                            Min Fans
                        </label>
                        <select id="min_fans" name="min_fans"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                            <option value="">Any</option>
                            @foreach ($fanThresholds as $threshold)
                                <option value="{{ $threshold }}"
                                    @selected($activeFilters['min_fans'] == $threshold && $activeFilters['min_fans'] !== '')>
                                    {{ number_format($threshold) }}+ fans
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort By --}}
                    <div>
                        <label for="sort"
                            class="mb-1 block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                            Sort By
                        </label>
                        <select id="sort" name="sort"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($activeFilters['sort'] === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="btn btn-primary flex-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                            Apply
                        </button>
                        @if ($hasActiveFilters)
                            <a href="{{ route('races.index') }}"
                                class="btn btn-outline focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 focus-visible:ring-offset-2">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </aside>

        <section class="race-summary-bar" role="status" aria-live="polite" data-testid="race-summary-bar">
            <div class="race-summary-bar__meta">
                <span class="race-summary-bar__primary">Showing {{ $visibleCount }} of {{ $totalCount }} races</span>
                <span class="race-summary-bar__divider" aria-hidden="true">•</span>
                <span>Sorted by {{ $sortOptions[$activeFilters['sort']] ?? 'Date (Default)' }}</span>
            </div>
            <div class="race-summary-bar__meta">
                <span>{{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'active filter' : 'active filters' }}</span>
                @if ($catalog->hasPages())
                    <span class="race-summary-bar__divider" aria-hidden="true">•</span>
                    <span>Page {{ $catalog->currentPage() }} of {{ $catalog->lastPage() }}</span>
                @endif
            </div>
        </section>

        {{-- Active Filter Chips --}}
        @if ($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-primary-200 bg-primary-50/50 px-4 py-3 text-sm dark:border-primary-700 dark:bg-primary-900/20"
                aria-live="polite" role="status" aria-label="Active filters">
                <span class="font-semibold text-primary-900 dark:text-primary-100">Active filters:</span>
                <span class="rounded-full border border-primary-300 bg-white px-3 py-1 text-xs font-semibold text-primary-800 dark:border-primary-600 dark:bg-neutral-800 dark:text-primary-200">
                    Showing {{ $visibleCount }} of {{ $totalCount }} races
                </span>
                @foreach ($filterChipLabels as $key => $label)
                    @if (!empty($activeFilters[$key]))
                        <span
                            class="inline-flex items-center gap-1 rounded-full border border-primary-300 bg-white px-3 py-1 text-xs font-medium text-primary-800 dark:border-primary-600 dark:bg-neutral-800 dark:text-primary-200">
                            {{ $label }}:
                            @if ($key === 'min_fans')
                                {{ number_format((int) $activeFilters[$key]) }}+ fans
                            @elseif ($key === 'season')
                                {{ ucfirst($activeFilters[$key]) }}
                            @else
                                {{ $activeFilters[$key] }}
                            @endif
                        </span>
                    @endif
                @endforeach
                @if ($activeFilters['sort'] !== 'date')
                    <span
                        class="inline-flex items-center gap-1 rounded-full border border-primary-300 bg-white px-3 py-1 text-xs font-medium text-primary-800 dark:border-primary-600 dark:bg-neutral-800 dark:text-primary-200">
                        Sort: {{ $sortOptions[$activeFilters['sort']] ?? $activeFilters['sort'] }}
                    </span>
                @endif
                <a href="{{ route('races.index') }}"
                    class="ml-auto text-xs font-semibold text-primary-700 underline dark:text-primary-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                    Clear all
                </a>
            </div>
        @endif

        {{-- Race Catalog --}}
        @if ($catalog->isEmpty())
            <div class="filter-surface">
                <div class="card-body py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white">No races found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        No races match your current filters. Try adjusting or clearing them.
                    </p>
                    <a href="{{ route('races.index') }}"
                        class="mt-4 inline-block btn btn-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 focus-visible:ring-offset-2 rounded">
                        Clear All Filters
                    </a>
                </div>
            </div>
        @else
            {{-- Year → Season Groups --}}
            @foreach ($yearOrder as $yearKey)
                @if ($byYear->has($yearKey))
                    @php $yearRaces = $byYear[$yearKey]; @endphp

                    <section aria-labelledby="year-{{ $yearKey }}-heading">
                        <h2 id="year-{{ $yearKey }}-heading"
                            class="mb-3 text-sm font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">
                            {{ $yearLabels[$yearKey] }}
                            <span class="ml-1 normal-case tracking-normal font-normal text-neutral-400 dark:text-neutral-500">
                                ({{ $yearRaces->count() }} {{ $yearRaces->count() === 1 ? 'race' : 'races' }})
                            </span>
                        </h2>

                        @php
                            $bySeason = $yearRaces->groupBy(fn ($race) => $race->season ?? 'other');
                        @endphp

                        <div class="space-y-3">
                            @foreach ($seasonOrder as $season)
                                @if ($bySeason->has($season))
                                    @php
                                        $seasonRaces = $bySeason[$season];
                                        $seasonKey = $yearKey . '_' . $season;
                                    @endphp

                                    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-xs">
                                        {{-- Season card header / accordion trigger --}}
                                        <button type="button"
                                            class="flex w-full items-center justify-between bg-neutral-50 px-4 py-3 transition-colors hover:bg-neutral-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500 dark:bg-neutral-800 dark:hover:bg-neutral-700/60"
                                            @click="toggleSeason('{{ $seasonKey }}')"
                                            :aria-expanded="(openSeasons['{{ $seasonKey }}'] ?? false).toString()"
                                            aria-controls="season-content-{{ $seasonKey }}">
                                            <div class="flex items-center gap-2.5">
                                                <span class="text-base leading-none" aria-hidden="true">
                                                    {{ $seasonIcons[$season] ?? '📅' }}
                                                </span>
                                                <span class="font-semibold text-neutral-800 dark:text-neutral-200">
                                                    {{ $seasonLabels[$season] ?? ucfirst($season) }}
                                                </span>
                                                <x-badge variant="secondary" class="text-xs tabular-nums">
                                                    {{ $seasonRaces->count() }} {{ $seasonRaces->count() === 1 ? 'race' : 'races' }}
                                                </x-badge>
                                            </div>
                                            <svg class="h-5 w-5 shrink-0 text-neutral-400 transition-transform duration-200"
                                                :class="{ 'rotate-180': openSeasons['{{ $seasonKey }}'] ?? false }"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {{-- Season card body (collapsible) --}}
                                        <div id="season-content-{{ $seasonKey }}"
                                            x-show="openSeasons['{{ $seasonKey }}'] ?? false"
                                            x-collapse>
                                            <div class="divide-y divide-neutral-100 bg-white px-2 py-1 dark:divide-neutral-700/60 dark:bg-neutral-800/50">
                                                @foreach ($seasonRaces as $race)
                                                    <x-race-row :race="$race" />
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            {{-- Pagination --}}
            @if ($catalog->hasPages())
                <div class="mt-2">
                    {{ $catalog->links() }}
                </div>
            @endif
        @endif

        {{-- Recent Race History (authenticated users only) --}}
        @auth
            @if ($recentResults->isNotEmpty())
                <section aria-labelledby="race-history-heading" class="mt-8">
                    <h2 id="race-history-heading"
                        class="mb-3 text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                        Recent Race History
                    </h2>
                    <div class="card overflow-hidden bg-white dark:bg-neutral-800">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-700/50">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Race
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Position
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        Recorded
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach ($recentResults as $result)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/30">
                                        <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white">
                                            {{ $result->race_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($result->finish_position === 1)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-bold text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                    <span aria-hidden="true">🥇</span>
                                                    <span class="sr-only">1st place</span>
                                                    1st
                                                </span>
                                            @elseif ($result->finish_position === 2)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-bold text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">
                                                    <span aria-hidden="true">🥈</span>
                                                    <span class="sr-only">2nd place</span>
                                                    2nd
                                                </span>
                                            @elseif ($result->finish_position === 3)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                                    <span aria-hidden="true">🥉</span>
                                                    <span class="sr-only">3rd place</span>
                                                    3rd
                                                </span>
                                            @elseif ($result->finish_position)
                                                <span class="text-neutral-500 dark:text-neutral-400">
                                                    {{ $result->finish_position }}th
                                                </span>
                                            @else
                                                <span class="text-neutral-400 dark:text-neutral-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $result->created_at?->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endauth
    </div>
@endsection
