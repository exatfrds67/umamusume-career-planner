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
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2"
                            aria-label="Refresh predictions">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Refresh</span>
                        </button>
                        <button onclick="clearCache()"
                            class="px-4 py-2 card hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2"
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
        <section class="card rounded-xl p-6 mb-6 animate-fade-in-delay-1">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Select Character</h2>
            <form method="GET" action="{{ route('training.predictions') }}">
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
                                    {{ $char->name }} ({{ ucfirst($char->scenario_type) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    @vite(['resources/js/pages/training/predictions.js'])
    <script>
        window.refreshPredictions = async function() {
            const appEl = document.getElementById('training-predictions-app');
            if (!appEl) return;
            const characterId = appEl.dataset.characterId;
            const apiUrl = appEl.dataset.apiUrl;
            document.getElementById('predictions-loading')?.classList.remove('hidden');
            document.getElementById('predictions-grid')?.classList.add('hidden');
            document.getElementById('predictions-error')?.classList.add('hidden');
            try {
                const {
                    fetchPredictionsWithRetry,
                    updatePredictionsUI
                } = await import('/resources/js/pages/training/predictions.js');
                const predictions = await fetchPredictionsWithRetry(apiUrl, characterId);
                updatePredictionsUI(predictions);
            } catch (error) {
                const {
                    showPredictionsError
                } = await import('/resources/js/pages/training/predictions.js');
                showPredictionsError(error.message);
            }
        };

        window.clearCache = async function() {
            const appEl = document.getElementById('training-predictions-app');
            if (!appEl) return;
            const characterId = appEl.dataset.characterId;
            try {
                const response = await fetch(`/api/training-predictions/cache/${characterId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    }
                });
                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            message: 'Cache cleared successfully'
                        }
                    }));
                    await window.refreshPredictions();
                } else {
                    throw new Error('Failed to clear cache');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to clear cache: ' + error.message
                    }
                }));
            }
        };

        window.showAIDetails = function() {
            const panel = document.getElementById('ai-analysis-panel');
            if (panel) {
                panel.classList.toggle('hidden');
                return;
            }

            const banner = document.getElementById('ai-advisor-banner');
            if (!banner) {
                return;
            }

            const advisorMessage = document.getElementById('ai-advisor-message')?.textContent?.trim() ||
                'No analysis available';
            const confidenceBadge = document.getElementById('ai-confidence-badge');
            const confidence = confidenceBadge ? confidenceBadge.textContent.trim() : 'N/A';
            const recommendationText = document.getElementById('ai-recommendation-text')?.textContent?.trim() || '';

            const breakdownEl = document.getElementById('calculation-breakdown');
            const baseGain = breakdownEl?.querySelector('.breakdown-base')?.textContent?.trim() || '--';
            const growthRate = breakdownEl?.querySelector('.breakdown-growth')?.textContent?.trim() || '--';
            const moodMod = breakdownEl?.querySelector('.breakdown-mood')?.textContent?.trim() || '--';

            const drilldown = document.createElement('div');
            drilldown.id = 'ai-analysis-panel';
            drilldown.className =
                'card rounded-xl p-6 mb-6 border border-primary-200 dark:border-primary-800 animate-fade-in';
            drilldown.setAttribute('role', 'region');
            drilldown.setAttribute('aria-label', 'AI Analysis Details');
            drilldown.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        AI Analysis Drilldown
                    </h3>
                    <button onclick="document.getElementById('ai-analysis-panel').classList.add('hidden')"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                        aria-label="Close analysis panel">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Recommendation</p>
                        <p class="text-sm text-gray-900 dark:text-white">${advisorMessage}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Confidence</p>
                        <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">${confidence}</p>
                    </div>
                </div>

                ${recommendationText ? `
                    <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg mb-4">
                        <p class="text-xs font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wider mb-1">Summary</p>
                        <p class="text-sm text-gray-900 dark:text-white">${recommendationText}</p>
                    </div>` : ''}

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Calculation Breakdown</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Base Gain</p>
                            <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${baseGain}</p>
                        </div>
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Growth Rate</p>
                            <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${growthRate}</p>
                        </div>
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Mood Modifier</p>
                            <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${moodMod}</p>
                        </div>
                    </div>
                </div>
            `;

            banner.insertAdjacentElement('afterend', drilldown);
        };
    </script>
@endpush
