@extends('layouts.app')

@section('title', 'Training Predictions')

@section('content')
    <x-breadcrumb :items="[['label' => 'Training', 'url' => route('characters.index')], ['label' => 'Predictions']]" />

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 page-stack">
        <!-- Page Header -->
        <div class="page-hero animate-fade-in">
            <div class="page-hero__content">
                <div>
                    <div class="page-hero__eyebrow">
                        <span>Training Intelligence</span>
                    </div>
                    <h1 class="page-hero__title mb-2">Training Predictions</h1>
                    <p class="page-hero__body text-base sm:text-lg">
                        AI-powered training recommendations with multi-agent analysis
                    </p>
                </div>
                @if ($selectedCharacter)
                    <div class="page-hero__actions">
                        <button onclick="refreshPredictions()"
                            class="btn btn-secondary btn-md rounded-xl flex items-center gap-2"
                            aria-label="Refresh predictions">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-sm font-medium text-neutral-900 dark:text-white">Refresh</span>
                        </button>
                        <button onclick="clearCache()"
                            class="btn btn-outline btn-md rounded-xl flex items-center gap-2"
                            aria-label="Clear cache">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="text-sm font-medium text-neutral-900 dark:text-white">Clear Cache</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Character Selection -->
        <section class="filter-surface p-6 animate-fade-in-delay-1" aria-labelledby="select-character-heading">
            <div class="section-kicker">
                <span>Scenario Setup</span>
            </div>
            <h2 id="select-character-heading" class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Select Character</h2>
            <form method="GET" action="{{ route('training.predictions') }}" role="search" aria-label="Select character for training predictions">
                <div>
                    <label for="character_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Character
                    </label>
                    <div class="relative">
                        <select id="character_id" name="character_id"
                            class="w-full px-4 py-3 bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-900 dark:text-white appearance-none"
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
                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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

            <x-modal name="training-confirmation" title="Confirm Training Action" size="lg">
                <div class="space-y-4" id="training-confirmation-content" aria-live="polite">
                    <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h4 id="training-confirmation-facility" class="text-base font-semibold text-neutral-900 dark:text-white">Training</h4>
                            <span id="training-confirmation-risk" class="px-2 py-1 rounded text-xs font-semibold bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300">Fail: --%</span>
                        </div>
                        <p id="training-confirmation-status" class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">Preparing preview...</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-3">
                            <div class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Energy Change</div>
                            <div id="training-confirmation-energy" class="mt-1 text-sm font-semibold text-neutral-900 dark:text-white">--</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-3">
                            <div class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Energy After</div>
                            <div id="training-confirmation-energy-after" class="mt-1 text-sm font-semibold text-neutral-900 dark:text-white">--</div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-3">
                        <div class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mb-2">Expected Stat Gains</div>
                        <div id="training-confirmation-gains" class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm text-neutral-700 dark:text-neutral-200">
                            <span>Loading...</span>
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <x-button variant="secondary" @click="$dispatch('close-modal', 'training-confirmation')">Cancel</x-button>
                    <button type="button" id="training-confirmation-submit" class="btn btn-primary btn-md" disabled>
                        Confirm
                    </button>
                </x-slot:footer>
            </x-modal>
        @else
            <div class="card rounded-xl p-12 text-center animate-fade-in-delay-2">
                <div class="mb-4">
                    <img src="/images/app_logo/uma_musume_race_planner_logo_128.png"
                         alt="{{ config('app.name') }}"
                         class="mx-auto h-20 w-20 opacity-40 dark:opacity-30">
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">No Character Selected</h3>
                <p class="text-neutral-700 dark:text-neutral-300 mb-5">Choose a character to unlock AI predictions, risk analysis, and turn-by-turn recommendations.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('character_id')?.focus()">
                        Quick Switch
                    </button>
                    <a href="{{ route('characters.create') }}" class="btn btn-primary">
                        Create Character
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- Extracted: JS functions (refreshPredictions, clearCache, showAIDetails) moved to resources/js/pages/training/predictions.js --}}
    @vite(['resources/js/pages/training/predictions.js'])
@endpush
