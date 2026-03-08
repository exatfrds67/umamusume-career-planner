<div class="space-y-6">
    {{-- Step Indicator --}}
    <div class="flex items-center justify-center gap-4">
        @foreach ([1 => 'Configure', 2 => 'Processing', 3 => 'Results'] as $num => $label)
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium
                    {{ $step >= $num ? 'bg-blue-600 text-white' : 'bg-neutral-200 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
                    {{ $num }}
                </div>
                <span class="text-sm {{ $step >= $num ? 'font-medium text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400' }}">
                    {{ $label }}
                </span>
            </div>
            @if ($num < 3)
                <div class="h-0.5 w-8 {{ $step > $num ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-700' }}"></div>
            @endif
        @endforeach
    </div>

    {{-- Step 1: Configure Scenarios --}}
    @if ($step === 1)
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Configure Scenarios</h2>
                <div class="flex items-center gap-2">
                    <label for="scenario-count" class="text-sm text-neutral-700 dark:text-neutral-300">Scenarios:</label>
                    <select wire:model.live="scenarioCount" id="scenario-count"
                        class="rounded-md border-neutral-300 text-sm shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                        @for ($i = 2; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($scenarios as $index => $scenario)
                    <div wire:key="scenario-{{ $index }}" class="rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                        <h3 class="mb-3 font-medium text-neutral-900 dark:text-white">Scenario {{ $index + 1 }}</h3>

                        <div class="space-y-3">
                            {{-- Training Focus --}}
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Training Focus</label>
                                <select wire:model="scenarios.{{ $index }}.training_focus"
                                    class="mt-1 block w-full rounded-md border-neutral-300 text-sm shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                                    <option value="balanced">Balanced</option>
                                    <option value="speed">Speed</option>
                                    <option value="stamina">Stamina</option>
                                    <option value="power">Power</option>
                                    <option value="guts">Guts</option>
                                    <option value="wit">Wit</option>
                                </select>
                            </div>

                            {{-- Scenario Type --}}
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Scenario Type</label>
                                <select wire:model="scenarios.{{ $index }}.scenario_type"
                                    class="mt-1 block w-full rounded-md border-neutral-300 text-sm shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                                    <option value="ura_finale">URA Finale</option>
                                    <option value="unity_cup">Unity Cup</option>
                                </select>
                            </div>

                            {{-- Support Deck Bonus --}}
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Support Bonus</label>
                                <input type="number" wire:model="scenarios.{{ $index }}.support_deck_bonus"
                                    step="0.1" min="0.5" max="2.0"
                                    class="mt-1 block w-full rounded-md border-neutral-300 text-sm shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                            </div>

                            {{-- Target Stats --}}
                            <div class="grid grid-cols-5 gap-2">
                                @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                    <div>
                                        <label class="block text-center text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                            {{ ucfirst(substr($stat, 0, 3)) }}
                                        </label>
                                        <input type="number" wire:model="scenarios.{{ $index }}.{{ $stat }}"
                                            min="100" max="1200"
                                            class="mt-1 block w-full rounded-md border-neutral-300 text-center text-xs shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button wire:click="runSimulation" wire:loading.attr="disabled"
                    class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-xs hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-neutral-900">
                    <span wire:loading.remove wire:target="runSimulation">Run Simulation</span>
                    <span wire:loading wire:target="runSimulation">Processing...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Step 2: Processing --}}
    @if ($step === 2)
        <div class="flex flex-col items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-neutral-200 border-t-blue-600"></div>
            <p class="mt-4 text-neutral-600 dark:text-neutral-400">Running {{ count($scenarios) }} scenarios...</p>
        </div>
    @endif

    {{-- Step 3: Results --}}
    @if ($step === 3 && !empty($report))
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Comparison Results</h2>
                <button wire:click="resetWizard"
                    class="rounded-md border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-800">
                    New Simulation
                </button>
            </div>

            {{-- Summary --}}
            @if (!empty($report['summary']))
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                        <p class="text-xs text-blue-600 dark:text-blue-400">Avg Win Rate</p>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ $report['summary']['avg_win_rate'] ?? 0 }}%</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                        <p class="text-xs text-green-600 dark:text-green-400">Best Win Rate</p>
                        <p class="text-lg font-semibold text-green-900 dark:text-green-100">{{ $report['summary']['max_win_rate'] ?? 0 }}%</p>
                    </div>
                    <div class="rounded-lg bg-purple-50 p-3 dark:bg-purple-900/20">
                        <p class="text-xs text-purple-600 dark:text-purple-400">Avg Efficiency</p>
                        <p class="text-lg font-semibold text-purple-900 dark:text-purple-100">{{ $report['summary']['avg_efficiency'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                        <p class="text-xs text-amber-600 dark:text-amber-400">Avg SP</p>
                        <p class="text-lg font-semibold text-amber-900 dark:text-amber-100">{{ $report['summary']['avg_sp_earned'] ?? 0 }}</p>
                    </div>
                </div>
            @endif

            {{-- Best Scenario Highlight --}}
            @if (isset($report['best_scenario']) && $report['best_scenario'] >= 0)
                <div class="rounded-lg border-2 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex items-center gap-2">
                        <span class="text-green-600 dark:text-green-400">★</span>
                        <h3 class="font-medium text-green-900 dark:text-green-100">
                            Best Scenario: #{{ $report['best_scenario'] + 1 }}
                        </h3>
                    </div>
                    @if (isset($report['recommendations'][$report['best_scenario']]))
                        <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                            {{ $report['recommendations'][$report['best_scenario']] }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- Stat Comparison Table --}}
            @if (!empty($report['stat_comparison']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-neutral-500 dark:text-neutral-400">Stat</th>
                                @foreach (array_keys($results) as $idx)
                                    <th class="px-4 py-2 text-center text-xs font-medium uppercase text-neutral-500 dark:text-neutral-400
                                        {{ isset($report['best_scenario']) && $report['best_scenario'] === $idx ? 'text-green-600 dark:text-green-400' : '' }}">
                                        #{{ $idx + 1 }}
                                        @if (isset($report['best_scenario']) && $report['best_scenario'] === $idx)
                                            ★
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($report['stat_comparison'] as $stat => $values)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($stat) }}</td>
                                    @foreach ($values as $idx => $value)
                                        <td class="px-4 py-2 text-center text-sm text-neutral-700 dark:text-neutral-300
                                            {{ $value === max($values) ? 'font-bold text-green-600 dark:text-green-400' : '' }}">
                                            {{ $value }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Recommendations --}}
            @if (!empty($report['recommendations']))
                <div class="space-y-3">
                    <h3 class="font-medium text-neutral-900 dark:text-white">Recommendations</h3>
                    @foreach ($report['recommendations'] as $idx => $recommendation)
                        <div class="rounded-lg bg-neutral-50 p-3 dark:bg-neutral-800">
                            <p class="text-sm">
                                <span class="font-medium text-neutral-900 dark:text-white">Scenario {{ $idx + 1 }}:</span>
                                <span class="text-neutral-600 dark:text-neutral-400">{{ $recommendation }}</span>
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
