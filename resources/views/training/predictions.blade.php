@extends('layouts.app')

@section('title', 'Training Predictions')

@section('content')
    <x-breadcrumb :items="[['label' => 'Training', 'url' => route('training.predictions')], ['label' => 'Predictions']]" />

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">Training Predictions</h1>
                    <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300">
                        AI-powered training recommendations with multi-agent analysis
                    </p>
                </div>
                @if ($selectedCharacter)
                    <div class="flex items-center gap-2">
                        <button onclick="refreshPredictions()"
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 rounded-xl"
                            aria-label="Refresh predictions">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Refresh</span>
                        </button>
                        <button onclick="clearCache()"
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 rounded-xl"
                            aria-label="Clear cache">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
        <section class="card rounded-xl p-6 mb-6 animate-fade-in-delay-1" aria-labelledby="select-character-heading">
            <h2 id="select-character-heading" class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Select Character</h2>
            <form method="GET" action="{{ route('training.predictions') }}" role="search" aria-label="Select character for training predictions">
                <div>
                    <label for="character_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Character
                    </label>
                    <div class="relative">
                        <select id="character_id" name="character_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white appearance-none"
                            onchange="this.form.submit()">
                            <option value="">-- Select a character --</option>
                            @foreach ($characters as $char)
                                <option value="{{ $char->id }}"
                                    {{ $selectedCharacter && $selectedCharacter->id === $char->id ? 'selected' : '' }}>
                                    {{ $char->name }} ({{ ucwords(str_replace('_', ' ', $char->scenario_type)) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none" aria-hidden="true">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        @if ($selectedCharacter)
            @include('training.partials.status-bar', ['character' => $selectedCharacter])
            @include('training.partials.character-overview', ['character' => $selectedCharacter])
            @include('training.partials.ai-advisor-banner')
            @include('training.partials.predictions-grid', ['character' => $selectedCharacter])
        @else
            <div class="card rounded-xl p-12 text-center animate-fade-in-delay-2">
                <div class="text-gray-500 dark:text-gray-400 mb-4" aria-hidden="true">
                    <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Character Selected</h3>
                <p class="text-gray-700 dark:text-gray-300">Please select a character to view training predictions</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- Extracted: JS functions (refreshPredictions, clearCache, showAIDetails) moved to resources/js/pages/training/predictions.js --}}
    @vite(['resources/js/pages/training/predictions.js'])
@endpush
