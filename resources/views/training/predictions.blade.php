@extends('layouts.app')

@section('title', 'Training Predictions')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1
                        class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2 transition-colors duration-300">
                        Training Predictions
                    </h1>
                    <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300 transition-colors duration-300">
                        AI-powered training recommendations with multi-agent analysis and detailed stat predictions
                    </p>
                </div>
                @if ($selectedCharacter)
                    <div class="flex items-center gap-2">
                        <button onclick="refreshPredictions()"
                            class="px-4 py-2 glass-card rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Refresh</span>
                        </button>
                        <button onclick="clearCache()"
                            class="px-4 py-2 glass-card rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Clear Cache</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Character Selection -->
        <div class="glass-card rounded-xl p-6 mb-6 animate-fade-in-delay-1">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-300">
                Select Character
            </h2>

            <form method="GET" action="{{ route('training.predictions') }}" class="space-y-4">
                <div>
                    <label for="character_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">
                        Character
                    </label>
                    <select id="character_id" name="character_id"
                        class="w-full px-4 py-3 glass-card-inner rounded-lg focus:ring-4 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 text-gray-900 dark:text-white"
                        onchange="this.form.submit()">
                        <option value="">-- Select a character --</option>
                        @foreach ($characters as $char)
                            <option value="{{ $char->id }}"
                                {{ $selectedCharacter && $selectedCharacter->id === $char->id ? 'selected' : '' }}>
                                {{ $char->name }} ({{ ucfirst($char->scenario_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if ($selectedCharacter)
            <!-- Character Overview -->
            <div class="glass-card rounded-xl p-6 mb-6 animate-fade-in-delay-2">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-300">
                    {{ $selectedCharacter->name }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Current Stats -->
                    <div class="glass-card-inner rounded-lg p-4">
                        <h3
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-300">
                            Current Stats</h3>
                        <div class="space-y-2">
                            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm text-gray-700 dark:text-gray-300 capitalize transition-colors duration-300">{{ $stat }}</span>
                                    <span
                                        class="text-sm font-semibold text-gray-900 dark:text-white transition-colors duration-300">
                                        {{ $selectedCharacter->current_stats[$stat] ?? 0 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Energy & Mood -->
                    <div class="glass-card-inner rounded-lg p-4">
                        <h3
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-300">
                            Status</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">Energy</span>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white transition-colors duration-300">
                                    {{ $selectedCharacter->energy_level }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">Mood</span>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white capitalize transition-colors duration-300">
                                    {{ $selectedCharacter->mood_status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">Scenario</span>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white capitalize transition-colors duration-300">
                                    {{ str_replace('_', ' ', $selectedCharacter->scenario_type) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Support Cards -->
                    <div class="glass-card-inner rounded-lg p-4">
                        <h3
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 transition-colors duration-300">
                            Support Cards</h3>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
                                {{ $selectedCharacter->supportCards->count() }} cards equipped
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Training Predictions App Container -->
            <div id="training-predictions-app" data-character-id="{{ $selectedCharacter->id }}"
                data-scenario-type="{{ $selectedCharacter->scenario_type }}"
                data-api-url="{{ route('api.training-predictions.batch') }}"
                class="glass-card rounded-xl p-12 text-center animate-fade-in-delay-3">
                <div class="text-gray-400 dark:text-gray-500 mb-4 transition-colors duration-300">
                    <svg class="mx-auto h-20 w-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 transition-colors duration-300">
                    AI Training Predictions Coming Soon
                </h3>
                <p class="text-lg text-gray-700 dark:text-gray-300 mb-6 max-w-2xl mx-auto transition-colors duration-300">
                    AI-powered training recommendations with multi-agent analysis are currently under development.
                    This feature will provide intelligent suggestions based on your character's stats, goals, and scenario
                    type.
                </p>
                <div class="inline-flex items-center gap-2 px-6 py-3 glass-card-inner rounded-lg">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors duration-300">
                        Check back later for this feature
                    </span>
                </div>
            </div>
        @else
            <!-- No Character Selected -->
            <div class="glass-card rounded-xl p-12 text-center animate-fade-in-delay-2">
                <div class="text-gray-400 dark:text-gray-500 mb-4 transition-colors duration-300">
                    <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-300">
                    No Character Selected
                </h3>
                <p class="text-gray-700 dark:text-gray-300 transition-colors duration-300">
                    Please select a character to view training predictions
                </p>
            </div>
        @endif
    </div>


@endsection
