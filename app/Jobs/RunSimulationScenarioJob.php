<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Simulation\BatchSimulationService;
use App\Services\Simulation\SimulationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs a single simulation scenario within a batch.
 *
 * Dispatched as part of a batch chain for parallel processing.
 * Results are recorded back to the BatchSimulationService.
 */
class RunSimulationScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $targetStats
     * @param  array{training_focus: string, support_deck_bonus: float, scenario_type: string}  $parameters
     */
    public function __construct(
        public string $batchId,
        public int $scenarioIndex,
        public array $targetStats,
        public array $parameters,
    ) {
        $this->onQueue('simulations');
    }

    /**
     * Execute the job.
     */
    public function handle(SimulationEngine $engine, BatchSimulationService $batchService): void
    {
        $batchData = $batchService->getBatch($this->batchId);

        if (! $batchData || $batchData['status'] === 'cancelled') {
            return;
        }

        $result = $engine->runScenario($this->targetStats, $this->parameters);

        $batchService->recordResult($this->batchId, $this->scenarioIndex, $result);
    }

    /**
     * Handle a failed job.
     */
    public function failed(?\Throwable $exception): void
    {
        $batchService = app(BatchSimulationService::class);

        $batchService->recordFailure(
            $this->batchId,
            $this->scenarioIndex,
            $exception?->getMessage() ?? 'Unknown error'
        );
    }
}
