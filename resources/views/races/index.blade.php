@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Race Calendar</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View and manage race history and schedule</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1 space-y-4">
                <div class="card bg-white dark:bg-gray-800">
                    <div class="card-header">
                        <h3 class="font-medium text-gray-900 dark:text-white">Filters</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('races.index') }}" method="GET" class="space-y-4">
                            <!-- Grade Filter -->
                            <div>
                                <label for="grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grade</label>
                                <select name="grade" id="grade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Grades</option>
                                    @foreach(['G1', 'G2', 'G3', 'OP', 'Pre-OP'] as $grade)
                                        <option value="{{ $grade }}" {{ request('grade') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Distance Filter -->
                            <div>
                                <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Distance</label>
                                <select name="distance" id="distance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">All Distances</option>
                                    @foreach(['Sprint', 'Mile', 'Medium', 'Long'] as $dist)
                                        <option value="{{ $dist }}" {{ request('distance') === $dist ? 'selected' : '' }}>{{ $dist }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Surface Filter -->
                            <div>
                                <label for="surface" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Surface</label>
                                <select name="surface" id="surface" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
            </div>

            <!-- Race List -->
            <div class="lg:col-span-3">
                @if($races->isEmpty())
                    <div class="card bg-white dark:bg-gray-800">
                        <div class="card-body text-center py-12">
                             <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No races found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or start a new career run.</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($races as $race)
                            <a href="{{ route('races.show', $race) }}" class="card bg-white dark:bg-gray-800 hover:ring-2 hover:ring-primary-500 transition-all cursor-pointer group">
                                <div class="card-body">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold 
                                                    {{ $race->race_grade === 'G1' ? 'bg-yellow-100 text-yellow-800' : 
                                                       ($race->race_grade === 'G2' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                                                    {{ $race->race_grade }}
                                                </span>
                                                <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
                                                    {{ $race->race_name }}
                                                </h3>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $race->distance_meters }}m • {{ $race->surface }} • {{ $race->distance_category }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            @if($race->finish_position)
                                                <div class="text-lg font-bold {{ $race->finish_position == 1 ? 'text-yellow-600' : 'text-gray-700 dark:text-gray-300' }}">
                                                    {{ $race->finish_position }}{{ \Illuminate\Support\Str::ordinal($race->finish_position) }}
                                                </div>
                                                <div class="text-xs text-gray-500">Result</div>
                                            @else
                                                <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Scheduled</div>
                                                <div class="text-xs text-gray-500">Turn {{ $race->turn_number }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex items-center gap-3 text-xs text-gray-500">
                                        @if($race->weather)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                                </svg>
                                                {{ $race->weather }}
                                            </span>
                                        @endif
                                        @if($race->character)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $race->character->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $races->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
