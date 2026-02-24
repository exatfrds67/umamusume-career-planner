<div>
    {{-- Tab Navigation --}}
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-4" aria-label="Analytics tabs">
            @foreach (['patterns' => 'Pattern Analysis', 'comparison' => 'Career Comparison', 'divergence' => 'Divergence Analysis'] as $tabKey => $tabLabel)
                <button
                    wire:click="setActiveTab('{{ $tabKey }}')"
                    class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $tabKey ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                >
                    {{ $tabLabel }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Career Selection --}}
    <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Select Careers to Analyze</h3>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($availableCareers as $career)
                <label class="flex items-center gap-2 rounded-md border p-2 cursor-pointer transition-colors {{ in_array($career['id'], $selectedCareerIds) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}" wire:key="career-select-{{ $career['id'] }}">
                    <input
                        type="checkbox"
                        wire:model.live="selectedCareerIds"
                        value="{{ $career['id'] }}"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $career['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $career['character'] }} · {{ $career['scenario'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>

        @if (empty($availableCareers))
            <p class="text-sm text-gray-500 dark:text-gray-400">No completed careers found. Complete some career runs to enable analytics.</p>
        @endif

        <div class="mt-4 flex items-center gap-4">
            <div class="flex items-center gap-2">
                <label for="clusterCount" class="text-sm text-gray-600 dark:text-gray-400">Clusters:</label>
                <input type="number" wire:model="clusterCount" id="clusterCount" min="2" max="10" class="w-16 rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
            </div>
            <button
                wire:click="analyzePatterns"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                {{ count($selectedCareerIds) < 2 ? 'disabled' : '' }}
            >
                <span wire:loading.remove wire:target="analyzePatterns">Analyze</span>
                <span wire:loading wire:target="analyzePatterns">Analyzing...</span>
            </button>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($selectedCareerIds) }} careers selected</span>
        </div>
        @error('selectedCareerIds') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Pattern Analysis Tab --}}
    @if ($activeTab === 'patterns')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Clusters --}}
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">K-Means Clusters</h3>
                @if (!empty($clusterResults['clusters']))
                    <div class="space-y-4">
                        @foreach ($clusterResults['clusters'] as $index => $cluster)
                            <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700" wire:key="cluster-{{ $index }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cluster {{ $index + 1 }}</span>
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $cluster['size'] }} careers</span>
                                </div>
                                <div class="grid grid-cols-5 gap-2 text-center">
                                    @foreach (['speed' => 'SPD', 'stamina' => 'STA', 'power' => 'POW', 'guts' => 'GUT', 'wit' => 'WIT'] as $statKey => $statLabel)
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $statLabel }}</p>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($cluster['centroid'][$statKey] ?? 0) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $clusterResults['converged'] ? 'Converged' : 'Did not converge' }}
                            in {{ $clusterResults['iterations'] }} iterations
                        </p>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Click "Analyze" to run clustering analysis.</p>
                @endif
            </div>

            {{-- Association Rules --}}
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Association Rules</h3>
                @if (!empty($associationRules['rules']))
                    <div class="space-y-2">
                        @foreach (array_slice($associationRules['rules'], 0, 10) as $rule)
                            <div class="rounded-md border border-gray-200 p-2 dark:border-gray-700" wire:key="rule-{{ $loop->index }}">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-medium">{{ implode(' + ', $rule['antecedent']) }}</span>
                                    <span class="mx-1 text-gray-400">→</span>
                                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ $rule['consequent'] }}</span>
                                </p>
                                <div class="mt-1 flex gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Support: {{ number_format($rule['support'] * 100, 1) }}%</span>
                                    <span>Confidence: {{ number_format($rule['confidence'] * 100, 1) }}%</span>
                                    <span>Lift: {{ number_format($rule['lift'], 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif (!empty($associationRules))
                    <p class="text-sm text-gray-500 dark:text-gray-400">No significant patterns found. Try with more careers.</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Click "Analyze" to discover association rules.</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Comparison Tab --}}
    @if ($activeTab === 'comparison')
        <div class="space-y-6">
            {{-- Summary Table --}}
            @if (!empty($comparisonSummary['careers']))
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Career Rankings</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left text-gray-500 dark:text-gray-400">Rank</th>
                                    <th class="px-3 py-2 text-left text-gray-500 dark:text-gray-400">Career</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">SPD</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">STA</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">POW</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">GUT</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">WIT</th>
                                    <th class="px-3 py-2 text-right text-gray-500 dark:text-gray-400">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($comparisonSummary['careers'] as $career)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50" wire:key="career-rank-{{ $career['id'] }}">
                                        <td class="px-3 py-2 font-medium text-gray-700 dark:text-gray-300">#{{ $career['rank'] }}</td>
                                        <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $career['name'] }}</td>
                                        <td class="px-3 py-2 text-right text-blue-600 dark:text-blue-400">{{ number_format($career['stats']['speed']) }}</td>
                                        <td class="px-3 py-2 text-right text-orange-600 dark:text-orange-400">{{ number_format($career['stats']['stamina']) }}</td>
                                        <td class="px-3 py-2 text-right text-red-600 dark:text-red-400">{{ number_format($career['stats']['power']) }}</td>
                                        <td class="px-3 py-2 text-right text-pink-600 dark:text-pink-400">{{ number_format($career['stats']['guts']) }}</td>
                                        <td class="px-3 py-2 text-right text-green-600 dark:text-green-400">{{ number_format($career['stats']['wit']) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($career['total_stats']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 dark:bg-gray-700/30">
                                    <td class="px-3 py-2" colspan="2"><span class="text-sm font-medium text-gray-500 dark:text-gray-400">Average</span></td>
                                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                        <td class="px-3 py-2 text-right text-sm text-gray-600 dark:text-gray-400">{{ number_format($comparisonSummary['averages'][$stat] ?? 0) }}</td>
                                    @endforeach
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-600 dark:text-gray-400">{{ number_format(array_sum($comparisonSummary['averages'] ?? [])) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Parallel Coordinates Chart Container --}}
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Parallel Coordinates</h3>
                    <div class="relative h-80">
                        <canvas id="parallelCoordinatesChart" wire:ignore></canvas>
                    </div>
                </div>
            @else
                <div class="rounded-lg bg-white p-6 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">Select careers and click "Analyze" to see comparison data.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Divergence Tab --}}
    @if ($activeTab === 'divergence')
        <div class="space-y-6">
            @if (!empty($divergenceData['divergence_points']))
                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Earliest Divergence</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Turn {{ $divergenceData['summary']['earliest_divergence'] ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Most Divergent Stat</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ ucfirst($divergenceData['summary']['most_divergent_stat'] ?? 'N/A') }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Max Magnitude</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($divergenceData['summary']['max_magnitude'] ?? 0) }}</p>
                    </div>
                </div>

                {{-- Divergence Chart --}}
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Divergence Points</h3>
                    <div class="relative h-80">
                        <canvas id="divergenceChart" wire:ignore></canvas>
                    </div>
                </div>

                {{-- Divergence Details --}}
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Top Divergence Points</h3>
                    <div class="space-y-2">
                        @foreach (array_slice($divergenceData['divergence_points'], 0, 10) as $dp)
                            <div class="flex items-center justify-between rounded-md border border-gray-200 p-2 dark:border-gray-700" wire:key="divergence-{{ $loop->index }}">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Turn {{ $dp['turn'] }}</span>
                                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($dp['stat']) }}</span>
                                </div>
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Δ {{ number_format($dp['magnitude']) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif (!empty($divergenceData))
                <div class="rounded-lg bg-white p-6 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">No significant divergence points found between selected careers.</p>
                </div>
            @else
                <div class="rounded-lg bg-white p-6 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">Select careers and click "Analyze" to see divergence data.</p>
                </div>
            @endif
        </div>
    @endif
</div>
