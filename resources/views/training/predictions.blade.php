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

            <!-- Training Predictions App Container (WF-004: Six-Facility Grid) -->
            <div id="training-predictions-app" 
                data-character-id="{{ $selectedCharacter->id }}"
                data-scenario-type="{{ $selectedCharacter->scenario_type }}"
                data-api-url="{{ route('api.training-predictions.batch') }}"
                class="animate-fade-in-delay-3">
                
                <!-- Loading State -->
                <div id="predictions-loading" class="glass-card rounded-xl p-12 text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4">
                        <svg class="mx-auto h-16 w-16 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-300">
                        Loading Training Predictions...
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 transition-colors duration-300">
                        Analyzing training facilities and generating AI recommendations
                    </p>
                </div>

                <!-- Error State -->
                <div id="predictions-error" class="glass-card rounded-xl p-12 text-center hidden">
                    <div class="text-red-500 dark:text-red-400 mb-4">
                        <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 transition-colors duration-300">
                        Unable to Load Predictions
                    </h3>
                    <p id="predictions-error-message" class="text-gray-700 dark:text-gray-300 mb-4 transition-colors duration-300">
                        An error occurred while fetching training predictions.
                    </p>
                    <button onclick="refreshPredictions()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">
                        Try Again
                    </button>
                </div>

                <!-- Predictions Grid (6 facilities per WF-004) -->
                <div id="predictions-grid" class="hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Speed Facility -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="speed">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Speed</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Stat Gain:</span><span class="stat-gain font-medium">--</span></div>
                                <div class="flex justify-between"><span>Skill Points:</span><span class="skill-points font-medium">--</span></div>
                                <div class="flex justify-between"><span>Success Rate:</span><span class="success-rate font-medium">--</span></div>
                            </div>
                        </div>

                        <!-- Stamina Facility -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="stamina">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Stamina</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Stat Gain:</span><span class="stat-gain font-medium">--</span></div>
                                <div class="flex justify-between"><span>Skill Points:</span><span class="skill-points font-medium">--</span></div>
                                <div class="flex justify-between"><span>Success Rate:</span><span class="success-rate font-medium">--</span></div>
                            </div>
                        </div>

                        <!-- Power Facility -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="power">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Power</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Stat Gain:</span><span class="stat-gain font-medium">--</span></div>
                                <div class="flex justify-between"><span>Skill Points:</span><span class="skill-points font-medium">--</span></div>
                                <div class="flex justify-between"><span>Success Rate:</span><span class="success-rate font-medium">--</span></div>
                            </div>
                        </div>

                        <!-- Guts Facility -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="guts">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Guts</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Stat Gain:</span><span class="stat-gain font-medium">--</span></div>
                                <div class="flex justify-between"><span>Skill Points:</span><span class="skill-points font-medium">--</span></div>
                                <div class="flex justify-between"><span>Success Rate:</span><span class="success-rate font-medium">--</span></div>
                            </div>
                        </div>

                        <!-- Wit Facility -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="wit">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Wit</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Stat Gain:</span><span class="stat-gain font-medium">--</span></div>
                                <div class="flex justify-between"><span>Skill Points:</span><span class="skill-points font-medium">--</span></div>
                                <div class="flex justify-between"><span>Success Rate:</span><span class="success-rate font-medium">--</span></div>
                            </div>
                        </div>

                        <!-- Rest Option -->
                        <div class="glass-card rounded-xl p-6 training-facility" data-facility="rest">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Rest</h3>
                                </div>
                                <span class="facility-recommendation px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">--</span>
                            </div>
                            <div class="facility-stats space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between"><span>Energy Recovery:</span><span class="energy-recovery font-medium">--</span></div>
                                <div class="flex justify-between"><span>Mood Effect:</span><span class="mood-effect font-medium">--</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- AI Summary -->
                    <div id="predictions-summary" class="mt-6 glass-card rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            AI Recommendation
                        </h3>
                        <p id="ai-recommendation-text" class="text-gray-700 dark:text-gray-300">
                            Analyzing the best training option for your current situation...
                        </p>
                    </div>
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

@push('scripts')
<script type="module">
    import { initTrainingPredictions, fetchPredictionsWithRetry } from '/resources/js/training-predictions.js';
    
    // Global functions for button handlers
    window.refreshPredictions = async function() {
        const appEl = document.getElementById('training-predictions-app');
        if (!appEl) return;
        
        const characterId = appEl.dataset.characterId;
        const apiUrl = appEl.dataset.apiUrl;
        
        // Show loading state
        document.getElementById('predictions-loading')?.classList.remove('hidden');
        document.getElementById('predictions-grid')?.classList.add('hidden');
        document.getElementById('predictions-error')?.classList.add('hidden');
        
        try {
            const predictions = await fetchPredictionsWithRetry(apiUrl, characterId);
            updatePredictionsUI(predictions);
        } catch (error) {
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (response.ok) {
                // Show success toast
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', message: 'Cache cleared successfully' }
                }));
                // Refresh predictions
                await window.refreshPredictions();
            } else {
                throw new Error('Failed to clear cache');
            }
        } catch (error) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'error', message: 'Failed to clear cache: ' + error.message }
            }));
        }
    };
    
    function updatePredictionsUI(predictions) {
        document.getElementById('predictions-loading')?.classList.add('hidden');
        document.getElementById('predictions-error')?.classList.add('hidden');
        document.getElementById('predictions-grid')?.classList.remove('hidden');
        
        if (!predictions || !predictions.facilities) return;
        
        // Update each facility card
        Object.entries(predictions.facilities).forEach(([facility, data]) => {
            const card = document.querySelector(`.training-facility[data-facility="${facility}"]`);
            if (!card) return;
            
            // Update recommendation badge
            const badge = card.querySelector('.facility-recommendation');
            if (badge && data.recommendation) {
                badge.textContent = data.recommendation;
                badge.className = `facility-recommendation px-2 py-1 rounded text-xs font-medium ${getRecommendationClass(data.recommendation)}`;
            }
            
            // Update stats
            const statGain = card.querySelector('.stat-gain');
            if (statGain && data.stat_gain !== undefined) {
                statGain.textContent = `+${data.stat_gain}`;
            }
            
            const skillPoints = card.querySelector('.skill-points');
            if (skillPoints && data.skill_points !== undefined) {
                skillPoints.textContent = `+${data.skill_points}`;
            }
            
            const successRate = card.querySelector('.success-rate');
            if (successRate && data.success_rate !== undefined) {
                successRate.textContent = `${data.success_rate}%`;
            }
            
            // Rest-specific fields
            const energyRecovery = card.querySelector('.energy-recovery');
            if (energyRecovery && data.energy_recovery !== undefined) {
                energyRecovery.textContent = `+${data.energy_recovery}`;
            }
            
            const moodEffect = card.querySelector('.mood-effect');
            if (moodEffect && data.mood_effect) {
                moodEffect.textContent = data.mood_effect;
            }
        });
        
        // Update AI summary
        const summaryText = document.getElementById('ai-recommendation-text');
        if (summaryText && predictions.summary) {
            summaryText.textContent = predictions.summary;
        }
    }
    
    function getRecommendationClass(recommendation) {
        switch (recommendation?.toLowerCase()) {
            case 'recommended':
            case 'best':
                return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
            case 'good':
                return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
            case 'avoid':
            case 'risky':
                return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
            default:
                return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
        }
    }
    
    function showPredictionsError(message) {
        document.getElementById('predictions-loading')?.classList.add('hidden');
        document.getElementById('predictions-grid')?.classList.add('hidden');
        document.getElementById('predictions-error')?.classList.remove('hidden');
        
        const errorMsg = document.getElementById('predictions-error-message');
        if (errorMsg) {
            errorMsg.textContent = message || 'An error occurred while fetching training predictions.';
        }
    }
    
    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        const appEl = document.getElementById('training-predictions-app');
        if (appEl && appEl.dataset.characterId) {
            window.refreshPredictions();
        }
    });
</script>
@endpush
