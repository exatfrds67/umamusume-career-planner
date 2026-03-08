@extends('layouts.app')

@php
    $characterName = data_get($character, 'name', 'Unknown Character');
    $characterStats = data_get($character, 'current_stats', []);
    $energyLevel = (int) data_get($character, 'energy_level', 0);
    $moodStatus = data_get($character, 'mood_status', 'normal');
    $scenarioType = data_get($character, 'scenario_type', 'ura_finale');
    $supportCardCount = data_get($character, 'supportCards', collect())->count();
    $characterId = data_get($character, 'id', '');
@endphp

@section('title', 'Training Predictions - ' . $characterName)

@section('content')
    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Training Predictions', 'url' => route('training.predictions')],
            ['label' => $characterName],
        ]" class="mb-6" />

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('training.predictions') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Training Predictions
            </a>
        </div>

        <!-- Character Header -->
        <header class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                {{ $characterName }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stats Column -->
                <section aria-labelledby="stats-heading">
                    <h2 id="stats-heading" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Current Stats
                    </h2>
                    <ul class="space-y-2">
                        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                            <li class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <x-type-icon :stat="$stat" size="sm" />
                                    <span
                                        class="text-sm text-neutral-600 dark:text-neutral-400 capitalize">{{ $stat }}</span>
                                </div>
                                <span class="text-sm font-bold text-neutral-900 dark:text-white">
                                    {{ $characterStats[$stat] ?? 0 }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <!-- Status Column -->
                <section aria-labelledby="status-heading">
                    <h2 id="status-heading" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Status</h2>
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs text-neutral-600 dark:text-neutral-400 block mb-2">Energy</span>
                            <x-energy-gauge :level="$energyLevel" size="sm" />
                        </div>
                        <div>
                            <span class="text-xs text-neutral-600 dark:text-neutral-400 block mb-2">Mood</span>
                            <x-condition-badge :condition="$moodStatus" />
                        </div>
                    </div>
                </section>

                <!-- Scenario Column -->
                <section aria-labelledby="scenario-heading">
                    <h2 id="scenario-heading" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Scenario
                    </h2>
                    <p class="text-sm text-neutral-900 dark:text-white capitalize">
                        {{ str_replace('_', ' ', $scenarioType) }}
                    </p>
                </section>

                <!-- Support Cards Column -->
                <section aria-labelledby="support-cards-heading">
                    <h2 id="support-cards-heading" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">
                        Support Cards</h2>
                    <p class="text-sm text-neutral-900 dark:text-white">
                        <span class="font-bold">{{ $supportCardCount }}</span> / 6 equipped
                    </p>
                </section>
            </div>
        </header>

        <!-- Training Predictions -->
        <section id="training-predictions-app" aria-label="Training Predictions" data-character-id="{{ $characterId }}"
            data-scenario-type="{{ $scenarioType }}" data-api-url="{{ route('api.training-predictions.batch') }}">
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500" role="status"
                    aria-label="Loading"></div>
                <p class="mt-4 text-neutral-600 dark:text-neutral-400">Loading training predictions...</p>
            </div>
        </section>
        {{-- AI Advisory Panel --}}
        <livewire:advisory-panel />
    </main>

    @vite(['resources/js/pages/training/show.js'])
@endsection
