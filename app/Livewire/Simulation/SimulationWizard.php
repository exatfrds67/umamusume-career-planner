<?php

declare(strict_types=1);

namespace App\Livewire\Simulation;

use App\Services\Simulation\BatchSimulationService;
use App\Services\Simulation\ComparisonReportService;
use App\Services\Simulation\SimulationEngine;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Simulation Wizard Livewire Component
 *
 * Multi-step wizard for configuring batch simulation scenarios,
 * viewing progress, and comparing results.
 *
 * Steps: 1) Configure scenarios  2) Processing  3) Results comparison
 * Covers FR-12.1, FR-12.2, FR-12.3
 */
class SimulationWizard extends Component
{
    public int $step = 1;

    public int $scenarioCount = 2;

    /** @var array<int, array{training_focus: string, scenario_type: string, support_deck_bonus: float, speed: int, stamina: int, power: int, guts: int, wit: int}> */
    public array $scenarios = [];

    public string $batchId = '';

    /** @var array<string, mixed> */
    public array $progress = [];

    /** @var array<string, mixed> */
    public array $report = [];

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    public bool $isRunning = false;

    public function mount(): void
    {
        $this->initializeScenarios();
    }

    /**
     * Initialize default scenarios based on count.
     */
    public function initializeScenarios(): void
    {
        $this->scenarios = [];

        for ($i = 0; $i < $this->scenarioCount; $i++) {
            $this->scenarios[$i] = [
                'training_focus' => 'balanced',
                'scenario_type' => 'ura_finale',
                'support_deck_bonus' => 1.0,
                'speed' => 600,
                'stamina' => 600,
                'power' => 600,
                'guts' => 600,
                'wit' => 600,
            ];
        }
    }

    /**
     * Update scenario count and re-initialize.
     */
    public function updatedScenarioCount(): void
    {
        $this->scenarioCount = max(
            BatchSimulationService::MIN_SCENARIOS,
            min(BatchSimulationService::MAX_SCENARIOS, $this->scenarioCount)
        );

        $this->initializeScenarios();
    }

    /**
     * Start the batch simulation (synchronous for immediate feedback).
     */
    public function runSimulation(): void
    {
        $this->validate([
            'scenarios' => ['required', 'array', 'min:2', 'max:10'],
            'scenarios.*.training_focus' => ['required', 'string', 'in:balanced,speed,stamina,power,guts,wit'],
            'scenarios.*.scenario_type' => ['required', 'string', 'in:ura_finale,unity_cup'],
            'scenarios.*.support_deck_bonus' => ['required', 'numeric', 'min:0.5', 'max:2.0'],
            'scenarios.*.speed' => ['required', 'integer', 'min:100', 'max:1200'],
            'scenarios.*.stamina' => ['required', 'integer', 'min:100', 'max:1200'],
            'scenarios.*.power' => ['required', 'integer', 'min:100', 'max:1200'],
            'scenarios.*.guts' => ['required', 'integer', 'min:100', 'max:1200'],
            'scenarios.*.wit' => ['required', 'integer', 'min:100', 'max:1200'],
        ]);

        $this->isRunning = true;
        $this->step = 2;

        $engine = app(SimulationEngine::class);
        $results = [];

        foreach ($this->scenarios as $index => $scenario) {
            $targetStats = [
                'speed' => $scenario['speed'],
                'stamina' => $scenario['stamina'],
                'power' => $scenario['power'],
                'guts' => $scenario['guts'],
                'wit' => $scenario['wit'],
            ];

            $parameters = [
                'training_focus' => $scenario['training_focus'],
                'support_deck_bonus' => (float) $scenario['support_deck_bonus'],
                'scenario_type' => $scenario['scenario_type'],
            ];

            $results[$index] = $engine->runScenario($targetStats, $parameters);
        }

        $this->results = $results;
        $this->isRunning = false;

        $reportService = app(ComparisonReportService::class);
        $this->report = $reportService->generateReport($results, $this->buildScenarioDefinitions());

        $this->step = 3;
    }

    /**
     * Go back to configuration step.
     */
    public function resetWizard(): void
    {
        $this->step = 1;
        $this->results = [];
        $this->report = [];
        $this->batchId = '';
        $this->isRunning = false;
    }

    /**
     * Build scenario definitions for the report service.
     *
     * @return array<int, array{target_stats: array<string, int>, parameters: array<string, mixed>}>
     */
    protected function buildScenarioDefinitions(): array
    {
        $definitions = [];

        foreach ($this->scenarios as $index => $scenario) {
            $definitions[$index] = [
                'target_stats' => [
                    'speed' => $scenario['speed'],
                    'stamina' => $scenario['stamina'],
                    'power' => $scenario['power'],
                    'guts' => $scenario['guts'],
                    'wit' => $scenario['wit'],
                ],
                'parameters' => [
                    'training_focus' => $scenario['training_focus'],
                    'support_deck_bonus' => $scenario['support_deck_bonus'],
                    'scenario_type' => $scenario['scenario_type'],
                ],
            ];
        }

        return $definitions;
    }

    public function render(): View
    {
        return view('livewire.simulation.simulation-wizard');
    }
}
