@props(['trainingType', 'prediction', 'isRecommended' => false, 'rank' => null, 'scenarioType' => 'ura_finale'])

@php
    $trainingInfo = [
        'speed' => [
            'name' => 'Speed Training',
            'icon' => '⚡',
            'description' => 'Increases top speed capability',
            'color' => 'blue',
        ],
        'stamina' => [
            'name' => 'Stamina Training',
            'icon' => '💪',
            'description' => 'Extends duration at top speed',
            'color' => 'green',
        ],
        'power' => [
            'name' => 'Power Training',
            'icon' => '🔥',
            'description' => 'Improves acceleration rate',
            'color' => 'red',
        ],
        'guts' => [
            'name' => 'Guts Training',
            'icon' => '💎',
            'description' => 'Enhances final phase performance',
            'color' => 'purple',
        ],
        'wit' => [
            'name' => 'Wit Training',
            'icon' => '🧠',
            'description' => 'Boosts skill activation and positioning',
            'color' => 'yellow',
        ],
        'rest' => [
            'name' => 'Rest',
            'icon' => '😴',
            'description' => 'Recovers energy and reduces failure risk',
            'color' => 'gray',
        ],
    ];

    $info = $trainingInfo[$trainingType] ?? [
        'name' => $trainingType,
        'icon' => '❓',
        'description' => 'Unknown training type',
        'color' => 'gray',
    ];
@endphp

<div
    class="glass-card rounded-xl p-6 relative transition-all duration-300 {{ $isRecommended ? 'ring-2 ring-primary-500 dark:ring-primary-400' : '' }}">
    @if ($isRecommended)
        <div class="absolute top-4 right-4">
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-600 text-white dark:bg-primary-500 shadow-lg">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                Recommended #{{ $rank }}
            </span>
        </div>
    @endif

    <!-- Training Header -->
    <div class="mb-4">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-3xl" aria-hidden="true">{{ $info['icon'] }}</span>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-300">
                {{ $info['name'] }}
            </h3>
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300 transition-colors duration-300">
            {{ $info['description'] }}
        </p>
    </div>

    <!-- Stat Gains -->
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">Predicted
            Stat Gains</h4>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($prediction['stat_gains'] ?? [] as $stat => $gain)
                <div
                    class="flex justify-between items-center px-3 py-2 glass-card-inner rounded transition-colors duration-300">
                    <span
                        class="text-sm text-gray-700 dark:text-gray-300 capitalize transition-colors duration-300">{{ $stat }}</span>
                    <span
                        class="text-sm font-bold {{ $gain > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-300">
                        {{ $gain > 0 ? '+' : '' }}{{ $gain }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Energy Cost & Failure Risk -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="glass-card-inner rounded-lg p-3">
            <div class="text-xs text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-300">Energy Cost</div>
            <div class="text-lg font-bold text-gray-900 dark:text-white transition-colors duration-300">
                {{ $prediction['energy_cost'] ?? 0 }}%
            </div>
        </div>
        <div class="glass-card-inner rounded-lg p-3">
            <div class="text-xs text-gray-700 dark:text-gray-300 mb-1 transition-colors duration-300">Failure Risk
            </div>
            <div
                class="text-lg font-bold {{ ($prediction['failure_risk'] ?? 0) > 20 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} transition-colors duration-300">
                {{ number_format($prediction['failure_risk'] ?? 0, 1) }}%
            </div>
        </div>
    </div>

    <!-- Breakdown -->
    @if (isset($prediction['breakdown']))
        <x-training-breakdown :breakdown="$prediction['breakdown']" />
    @endif

    <!-- Scenario-Specific Info -->
    @if ($scenarioType === 'unity_cup' && isset($prediction['scenario_specific']))
        <x-unity-cup-info :data="$prediction['scenario_specific']" />
    @endif

    <!-- MCP Optimization -->
    @if (isset($prediction['mcp_optimization']))
        <x-mcp-optimization-info :data="$prediction['mcp_optimization']" />
    @endif

    <!-- Recommendation Reason -->
    @if (isset($prediction['recommendation']['reason']))
        <div
            class="mt-4 p-3 glass-card-inner rounded-lg border-2 border-primary-200 dark:border-primary-800 transition-colors duration-300">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-300 shrink-0 mt-0.5" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <div>
                    <div
                        class="text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1 transition-colors duration-300">
                        AI Reasoning</div>
                    <div class="text-sm text-gray-900 dark:text-white transition-colors duration-300">
                        {{ $prediction['recommendation']['reason'] }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
