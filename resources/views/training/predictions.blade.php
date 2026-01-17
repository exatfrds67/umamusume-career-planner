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

            <!-- Training Predictions Component -->
            <div id="training-predictions-app" data-character-id="{{ $selectedCharacter->id }}"
                data-scenario-type="{{ $selectedCharacter->scenario_type }}"
                data-api-url="{{ route('api.training-predictions.batch') }}" class="animate-fade-in-delay-3">
                <!-- Vue/React component will mount here -->
                <div class="glass-card rounded-xl p-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
                    <p class="mt-4 text-gray-700 dark:text-gray-300 transition-colors duration-300">Loading training
                        predictions...</p>
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

    @push('scripts')
        <script type="module">
            // Training Predictions Application
            document.addEventListener('DOMContentLoaded', function() {
                const app = document.getElementById('training-predictions-app');
                if (!app) return;

                const characterId = app.dataset.characterId;
                const scenarioType = app.dataset.scenarioType;
                const apiUrl = app.dataset.apiUrl;

                // Initialize the training predictions interface
                initTrainingPredictions(characterId, scenarioType, apiUrl);
            });

            async function initTrainingPredictions(characterId, scenarioType, apiUrl) {
                const app = document.getElementById('training-predictions-app');

                try {
                    // Fetch batch predictions for all training types
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            character_id: parseInt(characterId),
                            training_types: ['speed', 'stamina', 'power', 'guts', 'wit', 'rest'],
                            include_recommendations: true,
                            use_mcp: true
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    // Render the predictions
                    renderPredictions(app, data.data, scenarioType);
                } catch (error) {
                    console.error('Error fetching predictions:', error);
                    renderError(app, error.message);
                }
            }

            function renderPredictions(container, predictions, scenarioType) {
                // Sort predictions by recommendation rank
                const sortedPredictions = predictions.sort((a, b) => {
                    const rankA = a.recommendation?.rank || 999;
                    const rankB = b.recommendation?.rank || 999;
                    return rankA - rankB;
                });

                const html = `
                                    <div class="space-y-6">
                                        <!-- Recommendations Header -->
                                        <div class="glass-card-alt rounded-xl shadow-xl p-6 text-gray-900 dark:text-white transition-colors duration-300">
                                            <div class="flex items-center gap-3 mb-2">
                                                <svg class="w-8 h-8 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                                </svg>
                                                <h2 class="text-2xl font-bold">AI-Powered Training Recommendations</h2>
                                            </div>
                                            <p class="text-gray-700 dark:text-gray-300 transition-colors duration-300">
                                                Based on multi-agent analysis and ${scenarioType === 'unity_cup' ? 'Unity Cup team synergy' : 'URA Finale optimization'}
                                            </p>
                                        </div>

                                        <!-- Training Options Grid -->
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            ${sortedPredictions.map(prediction => renderTrainingCard(prediction, scenarioType)).join('')}
                                        </div>

                                        <!-- Agent Performance Metrics -->
                                        ${renderAgentMetrics(predictions)}
                                    </div>
                                `;

                container.innerHTML = html;
            }

            function renderTrainingCard(prediction, scenarioType) {
                const isRecommended = prediction.recommendation?.is_recommended || false;
                const rank = prediction.recommendation?.rank;
                const trainingType = prediction.training_type;
                const trainingInfo = getTrainingInfo(trainingType);

                return `
                    <div class="glass-card rounded-xl p-6 relative transition-all duration-300 ${isRecommended ? 'ring-2 ring-primary-500 dark:ring-primary-400' : ''}">
                        ${isRecommended ? `
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-600 text-white dark:bg-primary-500 shadow-lg">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Recommended #${rank}
                                </span>
                            </div>
                        ` : ''}

                        <!-- Training Header -->
                        <div class="mb-4">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-3xl">${trainingInfo.icon}</span>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-300">
                                    ${trainingInfo.name}
                                </h3>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
                                ${trainingInfo.description}
                            </p>
                        </div>

                        <!-- Stat Gains -->
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">Predicted Stat Gains</h4>
                            <div class="grid grid-cols-2 gap-2">
                                ${Object.entries(prediction.stat_gains || {}).map(([stat, gain]) => `
                                    <div class="flex justify-between items-center px-3 py-2 glass-card-inner rounded transition-colors duration-300">
                                        <span class="text-sm text-gray-700 dark:text-gray-300 capitalize transition-colors duration-300">${stat}</span>
                                        <span class="text-sm font-bold ${gain > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'} transition-colors duration-300">
                                            ${gain > 0 ? '+' : ''}${gain}
                                        </span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <!-- Energy Cost & Failure Risk -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="glass-card-inner rounded-lg p-3">
                                <div class="text-xs text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-300">Energy Cost</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white transition-colors duration-300">
                                 ${prediction.energy_cost || 0}%
                                </div>
                            </div>
                            <div class="glass-card-inner rounded-lg p-3">
                                <div class="text-xs text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-300">Failure Risk</div>
                                <div class="text-lg font-bold ${preolors duration-300">
                                    ${(prediction.failure_risk || 0).toFixed(1)}%
                                </div>
                            </div>
                        </div>

                        <!-- Breakdown -->
                        ${renderBreakdown(prediction.breakdown)}

                        <!-- Scenario-Specific Info -->
                        ${scenarioType === 'unity_cup' && prediction.scenario_specific ? renderUnityCupInfo(prediction.scenario_specific) : ''}

                        <!-- MCP Optimization -->
                        ${prediction.mcp_optimization ? renderMCPOptimization(prediction.mcp_optimization) : ''}

                        <!-- Recommendation Reason -->
                        ${prediction.recommendation?.reason ? `
                            <div class="mt-4 p-3 glass-card-inner rounded-lg border-2 border-primary-200 dark:border-primary-800 transition-colors duration-300">
>
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                   </svg>
                                    <div>
                                        <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1 transition-colors duration-300">AI Reasoning</div>
                                        <div class="text-sm text-gray-900 dark:text-white transition-colors duration-300">
                                            ${prediction.recommendation.reason}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            function renderBreakdown(breakdown) {
                if (!breakdown) return '';

                return `
                    <div class="mb-4 glass-card-inner rounded-lg p-3">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">Calculation Breakdown</h4>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Support Card Bonus</span>
                                <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">+${(breakdown.support_card_bonus || 0).toFixed(1)}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Friendship Multiplier</span>
                                <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">×${(breakdown.friendship_multiplier || 1).toFixed(2)}</span>
                            </div>
                            ${breakdown.facility_bonus ? `
                                                <div class="flex justify-between">
                                                    <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Facility Bonus</span>
                                                    <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">+${(breakdown.facility_bonus || 0).toFixed(1)}%</span>
                                                </div>
                                            ` : ''}
                            <div class="flex justify-between">
                                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Growth Rate Bonus</span>
                                <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">+${(breakdown.growth_rate_bonus || 0).toFixed(1)}%</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-gray-300 dark:border-gray-600">
                                <span class="text-gray-700 dark:text-gray-300 font-semibold transition-colors duration-300">Total Multiplier</span>
                                <span class="font-bold text-gray-900 dark:text-white transition-colors duration-300">×${(breakdown.total_multiplier || 1).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderUnityCupInfo(scenarioData) {
                return `
                    <div class="mb-4 p-3 glass-card-inner rounded-lg border-2 border-purple-200 dark:border-purple-800 transition-colors duration-300">
                        <h4 class="text-sm font-semibold text-purple-700 dark:text-purple-300 mb-2 transition-colors duration-300">
                            🏆 Unity Cup Mechanics
                        </h4>
                        <div class="space-y-2 text-xs">
                            ${scenarioData.spirit_burst_progress !== undefined ? `
                                                <div class="flex justify-between items-center">
                                                    <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Spirit Burst Progress</span>
                                                    <div class="flex items-center gap-1">
                                                        ${[1,2,3,4].map(i => `
                                            <span class="${i <= (scenarioData.spirit_burst_progress || 0) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600'}">
                                                ${i <= (scenarioData.spirit_burst_progress || 0) ? '🔥' : '○'}
                                            </span>
                                        `).join('')}
                                                    </div>
                                                </div>
                                            ` : ''}
                            ${scenarioData.team_synergy_bonus ? `
                                                <div class="flex justify-between">
                                                    <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Team Synergy Bonus</span>
                                                    <span class="font-medium text-purple-900 dark:text-purple-100 transition-colors duration-300">+${scenarioData.team_synergy_bonus}%</span>
                                                </div>
                                            ` : ''}
                            ${scenarioData.teammates_present ? `
                                                <div class="flex justify-between">
                                                    <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Teammates Present</span>
                                                    <span class="font-medium text-purple-900 dark:text-purple-100 transition-colors duration-300">${scenarioData.teammates_present}</span>
                                                </div>
                                            ` : ''}
                        </div>
                    </div>
                `;
            }

            function renderMCPOptimization(mcpData) {
                return `
                    <div class="mb-4 p-3 glass-card-inner rounded-lg border-2 border-blue-200 dark:border-blue-800 transition-colors duration-300">
                        <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2 transition-colors duration-300">
                            🤖 Agent Analysis
                        </h4>
                        <div class="space-y-2 text-xs">
                            ${mcpData.agent_workflow ? `
                                                <div>
                                                    <span class="text-blue-700 dark:text-blue-300 font-medium transition-colors duration-300">Workflow:</span>
                                                    <span class="text-blue-900 dark:text-blue-100 ml-1 transition-colors duration-300">${mcpData.agent_workflow}</span>
                                                </div>
                                            ` : ''}
                            ${mcpData.confidence_score !== undefined ? `
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700 dark:text-blue-300 transition-colors duration-300">Confidence Score</span>
                                                    <span class="font-medium text-blue-900 dark:text-blue-100 transition-colors duration-300">${(mcpData.confidence_score * 100).toFixed(0)}%</span>
                                                </div>
                                            ` : ''}
                            ${mcpData.agents_consulted ? `
                                                <div>
                                                    <span class="text-blue-700 dark:text-blue-300 font-medium transition-colors duration-300">Agents Consulted:</span>
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        ${mcpData.agents_consulted.map(agent => `
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs glass-card-inner transition-colors duration-300">
                                                ${agent}
                                            </span>
                                        `).join('')}
                                                    </div>
                                                </div>
                                            ` : ''}
                        </div>
                    </div>
                `;
            }

            function renderAgentMetrics(predictions) {
                const avgProcessingTime = predictions.reduce((sum, p) => sum + (p.processing_time_ms || 0), 0) / predictions
                    .length;
                const cachedCount = predictions.filter(p => p.cached).length;

                return `
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-300">
                    Performance Metrics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center glass-card-inner rounded-lg p-4">
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 transition-colors duration-300">
                            ${avgProcessingTime.toFixed(0)}ms
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
                            Avg Processing Time
                        </div>
                    </div>
                    <div class="text-center glass-card-inner rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 transition-colors duration-300">
                            ${cachedCount}/${predictions.length}
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
                            Cached Predictions
                        </div>
                    </div>
                    <div class="text-center glass-card-inner rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 transition-colors duration-300">
                            ${predictions.length}
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
                            Training Options
                        </div>
                    </div>
                </div>
            </div>
        `;
            }

            function renderError(container, message) {
                container.innerHTML = `
            <div class="glass-card rounded-xl p-6 text-center border-2 border-red-200 dark:border-red-800">
                <div class="text-red-600 dark:text-red-400 mb-2 transition-colors duration-300">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2 transition-colors duration-300">
                    Error Loading Predictions
                </h3>
                <p class="text-red-700 dark:text-red-300 transition-colors duration-300">
                    ${message}
                </p>
            </div>
        `;
            }

            function getTrainingInfo(type) {
                const info = {
                    speed: {
                        name: 'Speed Training',
                        icon: '⚡',
                        description: 'Increases top speed capability'
                    },
                    stamina: {
                        name: 'Stamina Training',
                        icon: '💪',
                        description: 'Extends duration at top speed'
                    },
                    power: {
                        name: 'Power Training',
                        icon: '🔥',
                        description: 'Improves acceleration rate'
                    },
                    guts: {
                        name: 'Guts Training',
                        icon: '💎',
                        description: 'Enhances final phase performance'
                    },
                    wit: {
                        name: 'Wit Training',
                        icon: '🧠',
                        description: 'Boosts skill activation and positioning'
                    },
                    rest: {
                        name: 'Rest',
                        icon: '😴',
                        description: 'Recovers energy and reduces failure risk'
                    }
                };
                return info[type] || {
                    name: type,
                    icon: '❓',
                    description: 'Unknown training type'
                };
            }
        </script>
    @endpush
@endsection
