<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\TrainingPrediction;
use Illuminate\Support\Facades\Cache;

/**
 * Manages ML model retraining pipeline for prediction accuracy.
 *
 * Records predictions, calculates accuracy metrics (RMSE, MAE, MAPE),
 * and triggers retraining when sufficient data is available.
 *
 * Covers FR-07.5, FR-12.3
 */
class ModelRetrainingService
{
    public const RETRAINING_THRESHOLD = 1000;

    public const CACHE_PREFIX = 'model_retraining:';

    /**
     * Record a new prediction.
     *
     * @param  array<string, mixed>  $predictedValue
     */
    public function recordPrediction(
        int $careerId,
        int $userId,
        int $turnNumber,
        string $predictionType,
        array $predictedValue,
        float $confidenceScore,
        string $modelVersion = 'v1.0',
    ): TrainingPrediction {
        return TrainingPrediction::query()->create([
            'career_id' => $careerId,
            'user_id' => $userId,
            'turn_number' => $turnNumber,
            'prediction_type' => $predictionType,
            'predicted_value' => $predictedValue,
            'confidence_score' => $confidenceScore,
            'model_version' => $modelVersion,
        ]);
    }

    /**
     * Link actual result to a prediction and calculate accuracy.
     *
     * @param  array<string, mixed>  $actualValue
     */
    public function recordActualResult(TrainingPrediction $prediction, array $actualValue): TrainingPrediction
    {
        $accuracyScore = $this->calculateAccuracyScore($prediction->predicted_value, $actualValue);

        $prediction->update([
            'actual_value' => $actualValue,
            'accuracy_score' => $accuracyScore,
        ]);

        $this->checkRetrainingTrigger($prediction->model_version);

        return $prediction->fresh() ?? $prediction;
    }

    /**
     * Calculate accuracy score between predicted and actual values.
     *
     * @param  array<string, mixed>  $predicted
     * @param  array<string, mixed>  $actual
     */
    public function calculateAccuracyScore(array $predicted, array $actual): float
    {
        if (empty($predicted) || empty($actual)) {
            return 0.0;
        }

        $totalError = 0;
        $count = 0;

        foreach ($predicted as $key => $value) {
            if (isset($actual[$key]) && is_numeric($value) && is_numeric($actual[$key])) {
                $maxVal = max(abs((float) $value), abs((float) $actual[$key]), 1);
                $relativeError = abs($value - $actual[$key]) / $maxVal;
                $totalError += $relativeError;
                $count++;
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        return round(max(0, 1 - ($totalError / $count)), 4);
    }

    /**
     * Calculate RMSE (Root Mean Square Error) for a model version.
     */
    public function calculateRMSE(string $modelVersion, ?string $predictionType = null): float
    {
        $query = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->whereNotNull('accuracy_score');

        if ($predictionType) {
            $query->where('prediction_type', $predictionType);
        }

        $predictions = $query->get();

        if ($predictions->isEmpty()) {
            return 0.0;
        }

        $sumSquaredErrors = 0;
        $count = 0;

        foreach ($predictions as $prediction) {
            foreach ($prediction->predicted_value as $key => $value) {
                if (isset($prediction->actual_value[$key]) && is_numeric($value) && is_numeric($prediction->actual_value[$key])) {
                    $actualVal = (float) $prediction->actual_value[$key];
                    $error = (float) $value - $actualVal;
                    $sumSquaredErrors += $error * $error;
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        return round(sqrt($sumSquaredErrors / $count), 4);
    }

    /**
     * Calculate MAE (Mean Absolute Error) for a model version.
     */
    public function calculateMAE(string $modelVersion, ?string $predictionType = null): float
    {
        $query = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->whereNotNull('accuracy_score');

        if ($predictionType) {
            $query->where('prediction_type', $predictionType);
        }

        $predictions = $query->get();

        if ($predictions->isEmpty()) {
            return 0.0;
        }

        $sumAbsErrors = 0;
        $count = 0;

        foreach ($predictions as $prediction) {
            foreach ($prediction->predicted_value as $key => $value) {
                if (isset($prediction->actual_value[$key]) && is_numeric($value) && is_numeric($prediction->actual_value[$key])) {
                    $sumAbsErrors += abs((float) $value - (float) $prediction->actual_value[$key]);
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        return round($sumAbsErrors / $count, 4);
    }

    /**
     * Calculate MAPE (Mean Absolute Percentage Error) for a model version.
     */
    public function calculateMAPE(string $modelVersion, ?string $predictionType = null): float
    {
        $query = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->whereNotNull('accuracy_score');

        if ($predictionType) {
            $query->where('prediction_type', $predictionType);
        }

        $predictions = $query->get();

        if ($predictions->isEmpty()) {
            return 0.0;
        }

        $sumPercentErrors = 0;
        $count = 0;

        foreach ($predictions as $prediction) {
            foreach ($prediction->predicted_value as $key => $value) {
                if (isset($prediction->actual_value[$key]) && is_numeric($value) && is_numeric($prediction->actual_value[$key]) && $prediction->actual_value[$key] != 0) {
                    $percentError = abs(((float) $value - (float) $prediction->actual_value[$key]) / (float) $prediction->actual_value[$key]) * 100;
                    $sumPercentErrors += $percentError;
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        return round($sumPercentErrors / $count, 4);
    }

    /**
     * Get accuracy metrics grouped by prediction type.
     *
     * @return array<string, array{count: int, avg_accuracy: float, rmse: float, mae: float}>
     */
    public function getAccuracyByType(string $modelVersion): array
    {
        $types = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->whereNotNull('accuracy_score')
            ->distinct('prediction_type')
            ->pluck('prediction_type');

        $metrics = [];

        foreach ($types as $type) {
            /** @var string $type */
            $predictions = TrainingPrediction::query()
                ->where('model_version', $modelVersion)
                ->where('prediction_type', $type)
                ->whereNotNull('accuracy_score');

            $metrics[$type] = [
                'count' => $predictions->count(),
                'avg_accuracy' => round((float) $predictions->avg('accuracy_score'), 4),
                'rmse' => $this->calculateRMSE($modelVersion, $type),
                'mae' => $this->calculateMAE($modelVersion, $type),
            ];
        }

        return $metrics;
    }

    /**
     * Get total prediction count for a model version.
     */
    public function getPredictionCount(string $modelVersion): int
    {
        return TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->count();
    }

    /**
     * Check if retraining should be triggered.
     */
    public function shouldRetrain(string $modelVersion): bool
    {
        $count = TrainingPrediction::query()
            ->where('model_version', $modelVersion)
            ->whereNotNull('accuracy_score')
            ->count();

        return $count >= self::RETRAINING_THRESHOLD;
    }

    /**
     * Get the current active model version from cache.
     */
    public function getActiveModelVersion(): string
    {
        /** @var string $version */
        $version = Cache::get(self::CACHE_PREFIX.'active_version', 'v1.0');

        return $version;
    }

    /**
     * Set the active model version.
     */
    public function setActiveModelVersion(string $version): void
    {
        Cache::put(self::CACHE_PREFIX.'active_version', $version);
    }

    /**
     * Check retraining trigger and return status.
     */
    protected function checkRetrainingTrigger(string $modelVersion): void
    {
        if ($this->shouldRetrain($modelVersion)) {
            Cache::put(self::CACHE_PREFIX."needs_retraining:{$modelVersion}", true, now()->addDay());
        }
    }

    /**
     * Check if a model version needs retraining.
     */
    public function needsRetraining(string $modelVersion): bool
    {
        return (bool) Cache::get(self::CACHE_PREFIX."needs_retraining:{$modelVersion}", false);
    }
}
