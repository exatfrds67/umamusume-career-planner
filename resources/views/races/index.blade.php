@extends('layouts.app')

@section('title', 'Race Calendar')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Races']]" />

    <div class="page-stack">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ $catalog->count() }} Races
                </span>
            </div>
            </div>
        </header>

        {{-- Filters --}}
        <aside class="filter-surface p-4"
            aria-label="Race Filters">
            <h2 class="sr-only">Race Filters</h2>
            <form method="GET" action="{{ route('races.index') }}" class="space-y-4" role="search" aria-label="Filter races">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    {{-- Grade --}}
                    <div class="md:col-span-2">
                        <label for="grade" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Grade
                        </label>
                        <select id="grade" name="grade"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">All Grades</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade }}" {{ request('grade') === $grade ? 'selected' : '' }}>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Phase --}}
                    <div class="md:col-span-2">
                        <label for="phase" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Phase
                        </label>
                        <select id="phase" name="phase"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">All Phases</option>
                            @foreach ($phases as $phase)
                                <option value="{{ $phase }}" {{ request('phase') === $phase ? 'selected' : '' }}>
                                    {{ ucfirst($phase) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Surface --}}
                    <div class="md:col-span-2">
                        <label for="surface" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Surface
                        </label>
                        <select id="surface" name="surface"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">All Surfaces</option>
                            @foreach ($surfaces as $surface)
                                <option value="{{ $surface }}" {{ request('surface') === $surface ? 'selected' : '' }}>
                                    {{ ucfirst($surface) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Distance Category --}}
                    <div class="md:col-span-2">
                        <label for="distance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Distance
                        </label>
                        <select id="distance" name="distance"
                            class="form-select block w-full rounded-md border-neutral-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                            <option value="">All Distances</option>
                            @foreach ($distances as $dist)
                                <option value="{{ $dist }}" {{ request('distance') === $dist ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $dist)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit" class="btn btn-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                            </svg>
                            Filter
                        </button>
                        @if (request()->hasAny(['grade', 'phase', 'surface', 'distance']))
                            <a href="{{ route('races.index') }}" class="btn btn-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 focus-visible:ring-offset-2 rounded">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </aside>

        {{-- Race Catalog --}}
        @if ($catalog->isEmpty())
            <div class="filter-surface">
                <div class="card-body text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white">No races found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Try adjusting your filters to see more races.</p>
                    <a href="{{ route('races.index') }}" class="mt-4 inline-block btn btn-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 focus-visible:ring-offset-2 rounded">Clear Filters</a>
                </div>
            </div>
        @else
            @php
                $phaseOrder = [
                    'junior'  => 'Junior (Year 1)',
                    'classic' => 'Classic (Year 2)',
                    'senior'  => 'Senior (Year 3+)',
                    'all'     => 'All Phases',
                ];
                $phaseColors = [
                    'junior'  => 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300',
                    'classic' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/30 dark:text-violet-300',
                    'senior'  => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                    'all'     => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                ];
                $gradeColors = [
                    'G1'     => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800',
                    'G2'     => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 ring-1 ring-purple-200 dark:ring-purple-800',
                    'G3'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ring-1 ring-blue-200 dark:ring-blue-800',
                    'OP'     => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 ring-1 ring-green-200 dark:ring-green-800',
                    'Pre-OP' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400 ring-1 ring-teal-200 dark:ring-teal-800',
                    'Debut'  => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300 ring-1 ring-neutral-200 dark:ring-neutral-600',
                ];
                $grouped = $catalog->groupBy('phase');
            @endphp

            @foreach ($phaseOrder as $phaseKey => $phaseLabel)
                @if ($grouped->has($phaseKey))
                    <section aria-labelledby="phase-{{ $phaseKey }}-heading">
                        <h2 id="phase-{{ $phaseKey }}-heading"
                            class="mb-3 text-lg font-semibold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $phaseColors[$phaseKey] ?? '' }}">
                                {{ $phaseLabel }}
                            </span>
                            <span class="text-sm font-normal text-neutral-500 dark:text-neutral-400">
                                ({{ $grouped[$phaseKey]->count() }} races)
                            </span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach ($grouped[$phaseKey] as $race)
                                <a href="{{ route('races.show', $race->slug) }}"
                                    class="group flex flex-col rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-xs hover:shadow-md hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">

                                    {{-- Top row: grade badge + surface + distance --}}
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $gradeColors[$race->grade] ?? 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                            {{ $race->grade }}
                                        </span>
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400 flex items-center gap-1">
                                            @if ($race->surface === 'turf')
                                                <span aria-label="Turf"><span aria-hidden="true">🟩</span><span class="sr-only">Turf</span></span>
                                            @else
                                                <span aria-label="Dirt"><span aria-hidden="true">🟫</span><span class="sr-only">Dirt</span></span>
                                            @endif
                                            {{ number_format($race->distance_meters) }}m
                                        </span>
                                    </div>

                                    {{-- Race name --}}
                                    <h3 class="font-semibold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 text-sm leading-snug mb-0.5">
                                        {{ $race->name_en }}
                                    </h3>
                                    @if ($race->name_jp)
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500 mb-2">{{ $race->name_jp }}</p>
                                    @endif

                                    {{-- Venue + timing --}}
                                    <div class="mt-auto space-y-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        @if ($race->venue)
                                            <div class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $race->venue }}
                                            </div>
                                        @endif
                                        @if ($race->month_label)
                                            <div class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $race->month_label }}
                                            </div>
                                        @endif
                                        @if ($race->fan_requirement)
                                            <div class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                                {{ number_format($race->fan_requirement) }} fans req.
                                            </div>
                                        @endif
                                    </div>

                                    @if ($race->is_ura_finale)
                                        <div class="mt-2">
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                ⭐ URA Finale
                                            </span>
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach


        @endif

        {{-- Recent Race History (authenticated users only) --}}
        @auth
            @if ($recentResults->isNotEmpty())
                <section aria-labelledby="race-history-heading" class="mt-8">
                    <h2 id="race-history-heading" class="mb-3 text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                        Recent Race History
                    </h2>
                    <div class="card bg-white dark:bg-neutral-800 overflow-hidden">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-700/50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Race</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Position</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Recorded</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach ($recentResults as $result)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/30">
                                        <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white">{{ $result->race_name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($result->finish_position === 1)
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-bold text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"><span aria-hidden="true">🥇</span><span class="sr-only">1st place</span> 1st</span>
                                            @elseif ($result->finish_position === 2)
                                                <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-bold text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"><span aria-hidden="true">🥈</span><span class="sr-only">2nd place</span> 2nd</span>
                                            @elseif ($result->finish_position === 3)
                                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-800 dark:bg-orange-900/30 dark:text-orange-400"><span aria-hidden="true">🥉</span><span class="sr-only">3rd place</span> 3rd</span>
                                            @elseif ($result->finish_position)
                                                <span class="text-neutral-500 dark:text-neutral-400">{{ $result->finish_position }}th</span>
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
