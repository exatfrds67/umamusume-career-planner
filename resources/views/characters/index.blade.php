@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="characterManager()">
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

        <!-- Filters & Search -->
        <div class="card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('characters.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label for="search"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Search by name...">
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label for="scenario"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scenario</label>
                        <select id="scenario" name="scenario"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Scenarios</option>
                            <option value="ura_finale" {{ request('scenario') === 'ura_finale' ? 'selected' : '' }}>URA
                                Finale</option>
                            <option value="unity_cup" {{ request('scenario') === 'unity_cup' ? 'selected' : '' }}>Unity Cup
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="status"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select id="status" name="status"
                            class="form-select block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        @if (request()->anyFilled(['search', 'scenario', 'status', 'sort']))
                            <a href="{{ route('characters.index') }}" class="btn btn-outline px-3" title="Clear Filters">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center gap-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</span>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $sortOptions = [
                                'updated_at' => 'Recently Updated',
                                'created_at' => 'Recently Created',
                                'name' => 'Name',
                                'progress' => 'Progress',
                            ];
                            $currentSort = request('sort', 'updated_at');
                        @endphp
                        @foreach ($sortOptions as $value => $label)
                            <button type="submit" name="sort" value="{{ $value }}"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition-colors
                                    {{ $currentSort === $value
                                        ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                                {{ $label }}
                                @if ($currentSort === $value)
                                    <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        <!-- Content -->
        @if ($characters->count() > 0)
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($characters as $character)
                    <a href="{{ route('characters.show', $character) }}"
                        class="glass-card-alt rounded-lg shadow-sm hover:shadow-md transition-all overflow-hidden group">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-12 w-12 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        {{ strtoupper(substr($character->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                            {{ $character->name }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup' }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $character->status === 'active' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ ucfirst($character->status) }}
                                </span>
                            </div>

                            <!-- Stat Grid -->
                            <div class="grid grid-cols-5 gap-2 mb-4">
                                @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ substr(ucfirst($stat), 0, 3) }}</span>
                                        <x-ui.grade-badge :grade="$character->getStatGrade($character->getStat($stat))" size="sm" class="mb-1" />
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $character->getStat($stat) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Progress -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Overall Progress</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $character->getProgressPercentage() }}%</span>
                                </div>
                                <x-ui.progress-bar :value="$character->getProgressPercentage()" :max="100" size="sm" :show-percentage="false" />
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $character->updated_at->diffForHumans() }}
                            </span>
                            <span
                                class="text-xs font-medium text-primary-600 dark:text-primary-400 group-hover:translate-x-1 transition-transform inline-flex items-center">
                                View Details
                                <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="space-y-3">
                @foreach ($characters as $character)
                    <a href="{{ route('characters.show', $character) }}"
                        class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 overflow-hidden group">
                        <div class="p-5">
                            <div class="flex items-center gap-6">
                                <!-- Avatar -->
                                <div class="shrink-0">
                                    <div
                                        class="h-16 w-16 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        {{ strtoupper(substr($character->name, 0, 2)) }}
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                            {{ $character->name }}
                                        </h3>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $character->status === 'active' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-700 dark:text-gray-400' }}">
                                            {{ ucfirst($character->status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup' }}</span>
                                        <span>•</span>
                                        <span>{{ $character->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="hidden lg:flex items-center gap-4">
                                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">{{ substr(ucfirst($stat), 0, 3) }}</span>
                                            <x-ui.grade-badge :grade="$character->getStatGrade($character->getStat($stat))" size="sm" />
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Progress -->
                                <div class="hidden md:block w-32">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Progress</div>
                                    <x-ui.progress-bar :value="$character->getProgressPercentage()" :max="100" size="sm"
                                        :show-percentage="false" />
                                    <div class="text-xs font-medium text-gray-900 dark:text-white mt-1">
                                        {{ $character->getProgressPercentage() }}%</div>
                                </div>

                                <!-- Arrow -->
                                <div class="shrink-0">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 group-hover:translate-x-1 transition-all"
                                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $characters->links() }}
            </div>
        @else
            <div
                class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No characters found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new character profile.
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
        @endif
    </div>

    <script>
        function characterManager() {
            return {
                viewMode: localStorage.getItem('characterViewMode') || 'grid',
                init() {
                    this.$watch('viewMode', value => {
                        localStorage.setItem('characterViewMode', value);
                    });
                }
            }
        }
    </script>
@endsection
