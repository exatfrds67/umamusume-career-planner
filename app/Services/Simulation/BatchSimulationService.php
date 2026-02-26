<?php

declare(strict_types=1);

namespace App\Services\Simulation;

use App\Jobs\RunSimulationScenarioJob;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Manages batch simulation sessions with 2-10 scenarios per batch.
 *
 * Dispatches scenarios to queues for parallel processing and
 * tracks progress via cache.
 *
 * @see Requirements: FR-12.1, FR-12.2
 */
class BatchSimulationService
{
    public const MIN_SCENARIOS = 2;

    public const MAX_SCENARIOS = 10;

    /**
     * Create a new batch simulation session.
     *
     * @param  array<int, array{target_stats: array<string, int>, parameters: array<string, mixed>}>  $scenarios
     * @return array{batch_id: string, scenario_count: int, status: string}
     */
    public function createBatch(User $user, array $scenarios): array
    {
        $count = count($scenarios);

        if ($count < self::MIN_SCENARIOS || $count > self::MAX_SCENARIOS) {
            throw new \InvalidArgumentException(
                'Batch must contain between '.self::MIN_SCENARIOS.' and '.self::MAX_SCENARIOS." scenarios. Got: {$count}"
            );
        }

        $batchId = Str::uuid()->toString();

        $batchData = [
            'batch_id' => $batchId,
            'user_id' => $user->id,
            'scenario_count' => $count,
            'scenarios' => $scenarios,
            'results' => [],
            'completed' => 0,
            'failed' => 0,
            'status' => 'pending',
            'created_at' => now()->toIso8601String(),
        ];

        Cache::put("simulation_batch:{$batchId}", $batchData, now()->addHours(24));

        return [
            'batch_id' => $batchId,
            'scenario_count' => $count,
            'status' => 'pending',
        ];
    }

    /**
     * Dispatch all scenarios in a batch to the queue.
     */
    public function dispatchBatch(string $batchId): void
    {
        $batchData = $this->getBatch($batchId);

        if (! $batchData) {
            throw new \RuntimeException("Batch not found: {$batchId}");
        }

        $this->updateBatchStatus($batchId, 'processing');

        $jobs = [];
        foreach ($batchData['scenarios'] as $index => $scenario) {
            $jobs[] = new RunSimulationScenarioJob(
                $batchId,
                $index,
                $scenario['target_stats'],
                $scenario['parameters']
            );
        }

        Bus::chain($jobs)->dispatch();
    }

    /**
     * Record a completed scenario result.
     *
     * @param  array<string, mixed>  $result
     */
    public function recordResult(string $batchId, int $scenarioIndex, array $result): void
    {
        $batchData = $this->getBatch($batchId);

        if (! $batchData) {
            return;
        }

        $batchData['results'][$scenarioIndex] = $result;
        $batchData['completed']++;

        if ($batchData['scenario_count'] <= $batchData['completed'] + $batchData['failed']) {
            $batchData['status'] = $batchData['failed'] > 0 ? 'completed_with_errors' : 'completed';
        }

        Cache::put("simulation_batch:{$batchId}", $batchData, now()->addHours(24));
    }

    /**
     * Record a failed scenario.
     */
    public function recordFailure(string $batchId, int $scenarioIndex, string $reason): void
    {
        $batchData = $this->getBatch($batchId);

        if (! $batchData) {
            return;
        }

        $batchData['results'][$scenarioIndex] = ['error' => $reason];
        $batchData['failed']++;

        if ($batchData['scenario_count'] <= $batchData['completed'] + $batchData['failed']) {
            $batchData['status'] = 'completed_with_errors';
        }

        Cache::put("simulation_batch:{$batchId}", $batchData, now()->addHours(24));
    }

    /**
     * Get batch data.
     *
     * @return array{batch_id: string, user_id: int, scenario_count: int, scenarios: array<int, array{target_stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, parameters: array{training_focus: string, support_deck_bonus: float, scenario_type: string}}>, results: array<int, array<string, mixed>>, completed: int, failed: int, status: string, created_at: string}|null
     */
    public function getBatch(string $batchId): ?array
    {
        /** @var array{batch_id: string, user_id: int, scenario_count: int, scenarios: array<int, array{target_stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, parameters: array{training_focus: string, support_deck_bonus: float, scenario_type: string}}>, results: array<int, array<string, mixed>>, completed: int, failed: int, status: string, created_at: string}|null $data */
        $data = Cache::get("simulation_batch:{$batchId}");

        return $data;
    }

    /**
     * Get batch progress.
     *
     * @return array{completed: int, failed: int, total: int, percentage: float, status: string}
     */
    public function getProgress(string $batchId): array
    {
        $batchData = $this->getBatch($batchId);

        if (! $batchData) {
            return [
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
                'percentage' => 0.0,
                'status' => 'not_found',
            ];
        }

        $total = $batchData['scenario_count'];
        $done = $batchData['completed'] + $batchData['failed'];

        return [
            'completed' => $batchData['completed'],
            'failed' => $batchData['failed'],
            'total' => $total,
            'percentage' => $total > 0 ? round(($done / $total) * 100, 2) : 0.0,
            'status' => $batchData['status'],
        ];
    }

    /**
     * Get batch results when complete.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getResults(string $batchId): array
    {
        $batchData = $this->getBatch($batchId);

        return $batchData['results'] ?? [];
    }

    /**
     * Update batch status.
     */
    protected function updateBatchStatus(string $batchId, string $status): void
    {
        $batchData = $this->getBatch($batchId);

        if ($batchData) {
            $batchData['status'] = $status;
            Cache::put("simulation_batch:{$batchId}", $batchData, now()->addHours(24));
        }
    }

    /**
     * Cancel a pending or processing batch.
     */
    public function cancelBatch(string $batchId): bool
    {
        $batchData = $this->getBatch($batchId);

        if (! $batchData || $batchData['status'] === 'completed') {
            return false;
        }

        $batchData['status'] = 'cancelled';
        Cache::put("simulation_batch:{$batchId}", $batchData, now()->addHours(1));

        return true;
    }
}
