<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\TrainingPrediction;
use Illuminate\Support\Facades\Cache;

/**
 * A/B testing service for AI prediction models.
 *
 * Routes traffic between model variants (50/50 split),
 * tracks performance, and implements automatic rollback
 * if accuracy drops by more than 2%.
 *
 * @see Requirements: FR-07.5, FR-07.7
 */
class ABTestingService
{
    public const CACHE_PREFIX = 'ab_test:';

    public const ACCURACY_DROP_THRESHOLD = 0.02;

    public const MIN_SAMPLES_FOR_EVALUATION = 50;

    /**
     * Start a new A/B test between two model versions.
     *
     * @return array{test_id: string, variant_a: string, variant_b: string, status: string}
     */
    public function startTest(string $variantA, string $variantB): array
    {
        $testId = "{$variantA}_vs_{$variantB}";

        $testData = [
            'test_id' => $testId,
            'variant_a' => $variantA,
            'variant_b' => $variantB,
            'status' => 'running',
            'started_at' => now()->toIso8601String(),
            'traffic_split' => 0.5,
        ];

        Cache::put(self::CACHE_PREFIX.$testId, $testData, now()->addDays(30));

        return $testData;
    }

    /**
     * Route a request to either variant A or B (50/50 split).
     */
    public function routeTraffic(string $testId): string
    {
        $testData = $this->getTest($testId);

        if (! $testData || $testData['status'] !== 'running') {
            return (string) ($testData['variant_a'] ?? 'v1.0');
        }

        return mt_rand(0, 99) < ((float) $testData['traffic_split'] * 100)
            ? (string) $testData['variant_a']
            : (string) $testData['variant_b'];
    }

    /**
     * Evaluate A/B test results and determine winner.
     *
     * @return array{winner: string|null, variant_a_accuracy: float, variant_b_accuracy: float, should_rollback: bool, evaluation: string}
     */
    public function evaluateTest(string $testId): array
    {
        $testData = $this->getTest($testId);

        if (! $testData) {
            return [
                'winner' => null,
                'variant_a_accuracy' => 0.0,
                'variant_b_accuracy' => 0.0,
                'should_rollback' => false,
                'evaluation' => 'Test not found',
            ];
        }

        $variantAAccuracy = $this->getVariantAccuracy((string) $testData['variant_a'], 'A');
        $variantBAccuracy = $this->getVariantAccuracy((string) $testData['variant_b'], 'B');

        $sampleCountA = $this->getVariantSampleCount((string) $testData['variant_a'], 'A');
        $sampleCountB = $this->getVariantSampleCount((string) $testData['variant_b'], 'B');

        if ($sampleCountA < self::MIN_SAMPLES_FOR_EVALUATION || $sampleCountB < self::MIN_SAMPLES_FOR_EVALUATION) {
            return [
                'winner' => null,
                'variant_a_accuracy' => $variantAAccuracy,
                'variant_b_accuracy' => $variantBAccuracy,
                'should_rollback' => false,
                'evaluation' => "Insufficient samples. A: {$sampleCountA}, B: {$sampleCountB} (need ".self::MIN_SAMPLES_FOR_EVALUATION.' each)',
            ];
        }

        $accuracyDrop = $variantAAccuracy - $variantBAccuracy;
        $shouldRollback = $accuracyDrop > self::ACCURACY_DROP_THRESHOLD;

        $winner = $variantBAccuracy >= $variantAAccuracy ? (string) $testData['variant_b'] : (string) $testData['variant_a'];

        $evaluation = $shouldRollback
            ? 'Variant B accuracy dropped by '.round($accuracyDrop * 100, 2).'%. Rolling back to variant A.'
            : 'Variant B ('.round($variantBAccuracy * 100, 2).'%) vs Variant A ('.round($variantAAccuracy * 100, 2)."%). Winner: {$winner}";

        return [
            'winner' => $winner,
            'variant_a_accuracy' => $variantAAccuracy,
            'variant_b_accuracy' => $variantBAccuracy,
            'should_rollback' => $shouldRollback,
            'evaluation' => $evaluation,
        ];
    }

    /**
     * Stop an A/B test and optionally apply the winner.
     */
    public function stopTest(string $testId, ?string $applyVersion = null): bool
    {
        $testData = $this->getTest($testId);

        if (! $testData) {
            return false;
        }

        $testData['status'] = 'completed';
        $testData['completed_at'] = now()->toIso8601String();
        $testData['applied_version'] = $applyVersion;

        Cache::put(self::CACHE_PREFIX.$testId, $testData, now()->addDays(90));

        if ($applyVersion) {
            $retrainingService = app(ModelRetrainingService::class);
            $retrainingService->setActiveModelVersion($applyVersion);
        }

        return true;
    }

    /**
     * Get A/B test data.
     *
     * @return array{test_id: string, variant_a: string, variant_b: string, status: string, started_at: string, traffic_split: float, completed_at?: string, applied_version?: string|null}|null
     */
    public function getTest(string $testId): ?array
    {
        /** @var array{test_id: string, variant_a: string, variant_b: string, status: string, started_at: string, traffic_split: float, completed_at?: string, applied_version?: string|null}|null $data */
        $data = Cache::get(self::CACHE_PREFIX.$testId);

        return $data;
    }

    /**
     * Check if an A/B test is currently running.
     */
    public function isTestRunning(string $testId): bool
    {
        $testData = $this->getTest($testId);

        return $testData !== null && $testData['status'] === 'running';
    }

    /**
     * Get accuracy for a specific variant.
     */
    protected function getVariantAccuracy(string $modelVersion, string $variant): float
    {
        $avg = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->where('is_ab_test', true)
            ->where('ab_variant', $variant)
            ->whereNotNull('accuracy_score')
            ->avg('accuracy_score');

        return round((float) ($avg ?? 0), 4);
    }

    /**
     * Get sample count for a specific variant.
     */
    protected function getVariantSampleCount(string $modelVersion, string $variant): int
    {
        return TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->where('is_ab_test', true)
            ->where('ab_variant', $variant)
            ->whereNotNull('accuracy_score')
            ->count();
    }
}
