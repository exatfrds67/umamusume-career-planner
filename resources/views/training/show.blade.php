@extends('layouts.app')

@section('title', 'Training Predictions - ' . $character->name)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('training.predictions') }}"
                class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Training Predictions
            </a>
        </div>

        <!-- Character Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $character->name }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stats Column -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Current Stats</h3>
                    <div class="space-y-2">
                        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $stat }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $character->current_stats[$stat] ?? 0 }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Status Column -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Status</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Energy</span>
                            <div class="mt-1">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full"
                                        style="width: {{ $character->energy_level }}%"></div>
                                </div>
                                <span
                                    class="text-xs font-medium text-gray-900 dark:text-white">{{ $character->energy_level }}%</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Mood</span>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                                {{ $character->mood_status }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scenario Column -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Scenario</h3>
                    <div class="text-sm text-gray-900 dark:text-white capitalize">
                        {{ str_replace('_', ' ', $character->scenario_type) }}
                    </div>
                </div>

                <!-- Support Cards Column -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Support Cards</h3>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $character->supportCards->count() }} / 6 equipped
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Predictions -->
        <div id="training-predictions-app" data-character-id="{{ $character->id }}"
            data-scenario-type="{{ $character->scenario_type }}"
            data-api-url="{{ route('api.training-predictions.batch') }}">
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading training predictions...</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module" src="{{ asset('js/training-predictions.js') }}"></script>
    @endpush
@endsection
