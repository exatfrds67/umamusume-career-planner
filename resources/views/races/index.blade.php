@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Race Calendar</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View and manage race history and schedule</p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Filters Sidebar -->
            <aside class="lg:col-span-1 space-y-4" aria-labelledby="filters-heading">
                <div class="card bg-white dark:bg-gray-800">
                    <div class="card-header">
                        <h2 id="filters-heading" class="font-medium text-gray-900 dark:text-white">Filters</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('races.index') }}" method="GET" class="space-y-4">
                            <!-- Grade Filter -->
                            <div>
                                <label for="grade" class="form-label">Grade</label>
                                <select name="grade" id="grade" class="form-select">
                                    <option value="">All Grades</option>
                                    @foreach(['G1', 'G2', 'G3', 'OP', 'Pre-OP'] as $grade)
                                        <option value="{{ $grade }}" {{ request('grade') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Distance Filter -->
                            <div>
                                <label for="distance" class="form-label">Distance</label>
                                <select name="distance" id="distance" class="form-select">
                                    <option value="">All Distances</option>
                                    @foreach(['Sprint', 'Mile', 'Medium', 'Long'] as $dist)
                                        <option value="{{ $dist }}" {{ request('distance') === $dist ? 'selected' : '' }}>{{ $dist }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Surface Filter -->
                            <div>
                                <label for="surface" class="form-label">Surface</label>
                                <select name="surface" id="surface" class="form-select">
                                    <option value="">All Surfaces</option>
                                    <option value="Turf" {{ request('surface') === 'Turf' ? 'selected' : '' }}>Turf</option>
                                    <option value="Dirt" {{ request('surface') === 'Dirt' ? 'selected' : '' }}>Dirt</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-full justify-center">
                                Apply Filters
                            </button>
                            
                            @if(request()->hasAny(['grade', 'distance', 'surface']))
                                <a href="{{ route('races.index') }}" class="btn btn-secondary w-full justify-center text-center">
                                    Clear Filters
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Race List -->
            <section class="lg:col-span-3" aria-labelledby="race-list-heading">
                <h2 id="race-list-heading" class="sr-only">Race List</h2>
                @if($races->isEmpty())
                    <div class="card bg-white dark:bg-gray-800">
                        <div class="card-body text-center py-12">
                             <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No races found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or start a new career run.</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" role="list">
                        {{-- TODO: Wire readiness and win probability once race analytics endpoints are available. --}}
                        @foreach($races as $race)
                            @php
                                $raceData = [
                                    'grade' => $race->race_grade,
                                    'name' => $race->race_name,
                                    'distance' => $race->distance_meters ?? $race->distance,
                                    'track' => $race->surface ? strtolower($race->surface) : ($race->track_type ? strtolower($race->track_type) : null),
                                    'style' => $race->running_style,
                                    'turn' => $race->turn_number,
                                    'weather' => $race->weather,
                                    'condition' => $race->track_condition,
                                ];
                            @endphp
                            <a href="{{ route('races.show', $race) }}" class="block" role="listitem">
                                <x-race-card :race="$raceData" :readiness="null" :win-prob="null" />
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $races->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
