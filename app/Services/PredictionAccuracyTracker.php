<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RecommendationType;
use App\Models\PredictionAccuracy;
use App\ValueObjects\AccuracyMetrics;
use App\ValueObjects\RaceResult;
use App\ValueObjects\RaceStrategy;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingOutcome;
use Illuminate\Support\Facades\Log;

/**
 * Prediction Accuracy Tracker Service
 *
 * Tracks the accuracy of AI predictions by comparing predicted outcomes
 * with actual outcomes. Calculates accuracy metrics and flags models
 * for improvement when accuracy falls below acceptable thresholds.
 *
 * This service is critical for continuous improvement of the AI-powered
 * Training Advisory System, enabling data-driven model refinement.
 *
 * Key responsibilities:
 * - Record training outcome predictions vs actuals
 * - Record race outcome predictions vs actuals
 * - Calculate accuracy metrics per recommendation type
 * - Flag models for review when accuracy is poor
 * - Support both local and account storage modes
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Models\PredictionAccuracy
 */
class PredictionAccuracyTracker
{
    /**
     * Accuracy threshold for flagging models (below this = needs improvement)
     */
    protected const ACCURACY_THRESHOLD = 0.75;

    /**
     * Minimum predictions required before calculating meaningful metrics
     */
    protected const MIN_PREDICTIONS_FOR_METRICS = 5;

    /**
     * Record a training outcome for accuracy tracking.
     *
     * Compares the predicted stat gains from a recommendation against
     * the actual stat gains achieved during training. Calculates an
     * accuracy score and persists the record for analysis.
     *
     * Accuracy calculation:
     * - For each predicted stat, calculate percentage error
     * - Average the errors across all stats
     * - Accuracy = 1.0 - (average_error / 100)
     *
     * @param  int  $careerId  Career run ID
     * @param  int  $turnNumber  Turn number when training occurred
     * @param  Recommendation  $recommendation  Original recommendation with predictions
     * @param  TrainingOutcome  $actual  Actual training outcome
     * @param  string  $modelVersion  AI model version used for prediction
     * @return PredictionAccuracy The created prediction accuracy record
     */
    public function recordTrainingOutcome(
        int $careerId,
        int $turnNumber,
        Recommendation $recommendation,
        TrainingOutcome $actual,
        string $modelVersion = 'unknown'
    ): PredictionAccuracy {
        try {
            // Extract predicted stat gains from recommendation
            $predictedGains = $recommendation->expectedOutcomes['stat_gains'] ?? [];

            // Ensure predictedGains is an array with integer values
            if (! is_array($predictedGains)) {
                $predictedGains = [];
            }

            // Filter to ensure all values are integers
            /** @var array<string, int> $validPredictedGains */
            $validPredictedGains = array_filter($predictedGains, fn ($value) => is_int($value));

            // Calculate accuracy score
            $accuracyScore = $this->calculateTrainingAccuracy($validPredictedGains, $actual->statGains);

            // Create prediction accuracy record
            $prediction = PredictionAccuracy::create([
                'career_id' => $careerId,
                'turn_number' => $turnNumber,
                'prediction_type' => RecommendationType::TRAINING_FACILITY->value,
                'predicted_value' => [
                    'facility' => $recommendation->action,
                    'stat_gains' => $predictedGains,
                    'expected_outcomes' => $recommendation->expectedOutcomes,
                ],
                'actual_value' => $actual->toArray(),
                'accuracy_score' => $accuracyScore,
                'model_version' => $modelVersion,
            ]);

            Log::info('[PredictionAccuracyTracker] Training outcome recorded', [
                'career_id' => $careerId,
                'turn_number' => $turnNumber,
                'accuracy_score' => $accuracyScore,
                'model_version' => $modelVersion,
            ]);

            return $prediction;
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to record training outcome', [
                'error' => $e->getMessage(),
                'career_id' => $careerId,
                'turn_number' => $turnNumber,
            ]);

            throw new \RuntimeException(
                "Failed to record training outcome: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Calculate accuracy score for training predictions.
     *
     * Compares predicted stat gains against actual stat gains and
     * calculates a normalized accuracy score (0.0-1.0).
     *
     * @param  array<string, int>  $predicted  Predicted stat gains
     * @param  array<string, int>  $actual  Actual stat gains
     * @return float Accuracy score (0.0-1.0)
     */
    protected function calculateTrainingAccuracy(array $predicted, array $actual): float
    {
        if (empty($predicted) || empty($actual)) {
            return 0.0;
        }

        $errors = [];

        foreach ($predicted as $stat => $predictedValue) {
            // Ensure predictedValue is an integer
            if (! is_int($predictedValue)) {
                continue;
            }

            $actualValue = $actual[$stat] ?? 0;

            // Skip if predicted value is 0 to avoid division by zero
            if ($predictedValue === 0) {
                continue;
            }

            // Calculate percentage error
            $percentageError = abs(($actualValue - $predictedValue) / $predictedValue) * 100;

            // Cap error at 100% for extreme misses
            $errors[] = min($percentageError, 100);
        }

        if (empty($errors)) {
            return 0.0;
        }

        // Calculate average error
        $averageError = array_sum($errors) / count($errors);

        // Convert error to accuracy (0% error = 1.0 accuracy, 100% error = 0.0 accuracy)
        $accuracy = 1.0 - ($averageError / 100);

        // Ensure accuracy is between 0.0 and 1.0
        return max(0.0, min(1.0, $accuracy));
    }

    /**
     * Record a race outcome for accuracy tracking.
     *
     * Compares the predicted race strategy (running style, win probability)
     * against the actual race result. Calculates an accuracy score based on
     * placement prediction accuracy.
     *
     * Accuracy calculation:
     * - If predicted win and actual win: 1.0
     * - If predicted win and actual placed: 0.7
     * - If predicted win and actual loss: 0.0
     * - If predicted placed and actual placed: 0.8
     * - If predicted placed and actual loss: 0.3
     * - Otherwise: based on win probability vs actual placement
     *
     * @param  int  $careerId  Career run ID
     * @param  RaceStrategy  $strategy  Predicted race strategy
     * @param  RaceResult  $actual  Actual race result
     * @return PredictionAccuracy The created prediction accuracy record
     */
    public function recordRaceOutcome(
        int $careerId,
        RaceStrategy $strategy,
        RaceResult $actual
    ): PredictionAccuracy {
        try {
            // Calculate accuracy score
            $accuracyScore = $this->calculateRaceAccuracy($strategy, $actual);

            // Create prediction accuracy record
            $prediction = PredictionAccuracy::create([
                'career_id' => $careerId,
                'turn_number' => 0, // Races don't have turn numbers in the same way
                'prediction_type' => RecommendationType::RACE_STRATEGY->value,
                'predicted_value' => $strategy->toArray(),
                'actual_value' => $actual->toArray(),
                'accuracy_score' => $accuracyScore,
                'model_version' => $strategy->modelVersion,
            ]);

            Log::info('[PredictionAccuracyTracker] Race outcome recorded', [
                'career_id' => $careerId,
                'race_id' => $strategy->raceId,
                'accuracy_score' => $accuracyScore,
                'model_version' => $strategy->modelVersion,
            ]);

            return $prediction;
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to record race outcome', [
                'error' => $e->getMessage(),
                'career_id' => $careerId,
                'race_id' => $strategy->raceId,
            ]);

            throw new \RuntimeException(
                "Failed to record race outcome: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Calculate accuracy score for race predictions.
     *
     * Compares predicted race strategy against actual race result.
     *
     * @param  RaceStrategy  $strategy  Predicted strategy
     * @param  RaceResult  $actual  Actual result
     * @return float Accuracy score (0.0-1.0)
     */
    protected function calculateRaceAccuracy(RaceStrategy $strategy, RaceResult $actual): float
    {
        $winProbability = $strategy->winProbability;
        $actualWin = $actual->isWin();
        $actualPlaced = $actual->isPlaced();

        // High win probability (≥0.7) predictions
        if ($winProbability >= 0.7) {
            if ($actualWin) {
                return 1.0; // Perfect prediction
            }
            if ($actualPlaced) {
                return 0.7; // Close, but not quite
            }

            return 0.0; // Completely wrong
        }

        // Medium win probability (0.4-0.7) predictions
        if ($winProbability >= 0.4) {
            if ($actualPlaced) {
                return 0.8; // Good prediction
            }

            return 0.3; // Missed the mark
        }

        // Low win probability (<0.4) predictions
        if (! $actualPlaced) {
            return 0.9; // Correctly predicted difficulty
        }

        if (! $actualWin) {
            return 0.5; // Underestimated chances
        }

        return 0.2; // Significantly underestimated
    }

    /**
     * Safely cast a mixed value to float, returning 0.0 if null or invalid.
     */
    protected function toFloat(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    /**
     * Get prediction accuracy metrics for a career run.
     *
     * Calculates aggregated accuracy metrics for all predictions
     * of a specific type within a career run. Returns comprehensive
     * statistics including average accuracy, min/max, and breakdown
     * by prediction type.
     *
     * @param  int  $careerId  Career run ID
     * @param  RecommendationType|null  $type  Filter by recommendation type (null = all types)
     * @return AccuracyMetrics Aggregated accuracy metrics
     */
    public function getPredictionAccuracy(
        int $careerId,
        ?RecommendationType $type = null
    ): AccuracyMetrics {
        try {
            // Build query
            $query = PredictionAccuracy::query()
                ->where('career_id', $careerId);

            // Filter by type if specified
            if ($type !== null) {
                $query->where('prediction_type', $type->value);
            }

            // Get all predictions
            $predictions = $query->get();

            // Return empty metrics if no predictions
            if ($predictions->isEmpty()) {
                return AccuracyMetrics::empty();
            }

            // Calculate metrics
            $totalPredictions = $predictions->count();
            $averageAccuracy = $this->toFloat($predictions->avg('accuracy_score'));
            $minAccuracy = $this->toFloat($predictions->min('accuracy_score'));
            $maxAccuracy = $this->toFloat($predictions->max('accuracy_score'));

            // Count accurate and inaccurate predictions
            $accuratePredictions = $predictions->filter(fn ($p) => $p->isAccurate())->count();
            $inaccuratePredictions = $predictions->filter(fn ($p) => $p->isInaccurate())->count();

            // Calculate accuracy by type
            $accuracyByType = [];
            $countByType = [];

            foreach ($predictions->groupBy('prediction_type') as $predictionType => $typePredictions) {
                $accuracyByType[$predictionType] = $this->toFloat($typePredictions->avg('accuracy_score'));
                $countByType[$predictionType] = $typePredictions->count();
            }

            // Determine if improvement is needed
            $needsImprovement = false;
            $improvementReason = null;

            if ($totalPredictions >= self::MIN_PREDICTIONS_FOR_METRICS) {
                if ($averageAccuracy < self::ACCURACY_THRESHOLD) {
                    $needsImprovement = true;
                    $improvementReason = sprintf(
                        'Average accuracy (%.1f%%) is below threshold (%.1f%%)',
                        $averageAccuracy * 100,
                        self::ACCURACY_THRESHOLD * 100
                    );
                }
            }

            return new AccuracyMetrics(
                totalPredictions: $totalPredictions,
                averageAccuracy: $averageAccuracy,
                minAccuracy: $minAccuracy,
                maxAccuracy: $maxAccuracy,
                accuratePredictions: $accuratePredictions,
                inaccuratePredictions: $inaccuratePredictions,
                accuracyByType: $accuracyByType,
                countByType: $countByType,
                needsImprovement: $needsImprovement,
                improvementReason: $improvementReason,
            );
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to get prediction accuracy', [
                'error' => $e->getMessage(),
                'career_id' => $careerId,
                'type' => $type?->value,
            ]);

            // Return empty metrics on error
            return AccuracyMetrics::empty();
        }
    }

    /**
     * Flag a model for review based on accuracy threshold.
     *
     * Checks if a specific model version has accuracy below the
     * acceptable threshold across all predictions. Returns true
     * if the model should be reviewed or retrained.
     *
     * Criteria for flagging:
     * - Average accuracy < threshold (default 0.75)
     * - Minimum number of predictions met (default 5)
     * - Consistent poor performance across prediction types
     *
     * @param  string  $modelVersion  AI model version to check
     * @param  float|null  $accuracyThreshold  Custom threshold (null = use default)
     * @return bool True if model should be flagged for review
     */
    public function flagModelForReview(
        string $modelVersion,
        ?float $accuracyThreshold = null
    ): bool {
        try {
            $threshold = $accuracyThreshold ?? self::ACCURACY_THRESHOLD;

            // Get all predictions for this model version
            $predictions = PredictionAccuracy::query()
                ->where('model_version', $modelVersion)
                ->get();

            // Need minimum predictions to make a determination
            if ($predictions->count() < self::MIN_PREDICTIONS_FOR_METRICS) {
                Log::info('[PredictionAccuracyTracker] Insufficient predictions for model review', [
                    'model_version' => $modelVersion,
                    'prediction_count' => $predictions->count(),
                    'required' => self::MIN_PREDICTIONS_FOR_METRICS,
                ]);

                return false;
            }

            // Calculate average accuracy
            $averageAccuracy = $predictions->avg('accuracy_score');

            // Flag if below threshold
            $shouldFlag = $averageAccuracy < $threshold;

            if ($shouldFlag) {
                Log::warning('[PredictionAccuracyTracker] Model flagged for review', [
                    'model_version' => $modelVersion,
                    'average_accuracy' => $averageAccuracy,
                    'threshold' => $threshold,
                    'prediction_count' => $predictions->count(),
                ]);
            }

            return $shouldFlag;
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to flag model for review', [
                'error' => $e->getMessage(),
                'model_version' => $modelVersion,
            ]);

            return false;
        }
    }

    /**
     * Get accuracy metrics for a specific model version.
     *
     * Returns comprehensive accuracy statistics for all predictions
     * made by a specific AI model version. Useful for comparing
     * model performance and identifying which models need improvement.
     *
     * @param  string  $modelVersion  AI model version
     * @return AccuracyMetrics Aggregated accuracy metrics for the model
     */
    public function getModelAccuracy(string $modelVersion): AccuracyMetrics
    {
        try {
            // Get all predictions for this model
            $predictions = PredictionAccuracy::query()
                ->where('model_version', $modelVersion)
                ->get();

            // Return empty metrics if no predictions
            if ($predictions->isEmpty()) {
                return AccuracyMetrics::empty();
            }

            // Calculate metrics
            $totalPredictions = $predictions->count();
            $averageAccuracy = $this->toFloat($predictions->avg('accuracy_score'));
            $minAccuracy = $this->toFloat($predictions->min('accuracy_score'));
            $maxAccuracy = $this->toFloat($predictions->max('accuracy_score'));

            // Count accurate and inaccurate predictions
            $accuratePredictions = $predictions->filter(fn ($p) => $p->isAccurate())->count();
            $inaccuratePredictions = $predictions->filter(fn ($p) => $p->isInaccurate())->count();

            // Calculate accuracy by type
            $accuracyByType = [];
            $countByType = [];

            foreach ($predictions->groupBy('prediction_type') as $type => $typePredictions) {
                $accuracyByType[$type] = $this->toFloat($typePredictions->avg('accuracy_score'));
                $countByType[$type] = $typePredictions->count();
            }

            // Determine if improvement is needed
            $needsImprovement = $this->flagModelForReview($modelVersion);
            $improvementReason = $needsImprovement
                ? sprintf(
                    'Model accuracy (%.1f%%) is below threshold (%.1f%%)',
                    $averageAccuracy * 100,
                    self::ACCURACY_THRESHOLD * 100
                )
                : null;

            return new AccuracyMetrics(
                totalPredictions: $totalPredictions,
                averageAccuracy: $averageAccuracy,
                minAccuracy: $minAccuracy,
                maxAccuracy: $maxAccuracy,
                accuratePredictions: $accuratePredictions,
                inaccuratePredictions: $inaccuratePredictions,
                accuracyByType: $accuracyByType,
                countByType: $countByType,
                needsImprovement: $needsImprovement,
                improvementReason: $improvementReason,
            );
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to get model accuracy', [
                'error' => $e->getMessage(),
                'model_version' => $modelVersion,
            ]);

            return AccuracyMetrics::empty();
        }
    }

    /**
     * Get recent prediction accuracy trends.
     *
     * Returns accuracy metrics for predictions made within a specified
     * time period. Useful for monitoring recent model performance and
     * detecting degradation over time.
     *
     * @param  int  $days  Number of days to look back (default 7)
     * @param  string|null  $modelVersion  Filter by model version (null = all models)
     * @return AccuracyMetrics Aggregated accuracy metrics for the period
     */
    public function getRecentAccuracy(int $days = 7, ?string $modelVersion = null): AccuracyMetrics
    {
        try {
            // Build query
            $query = PredictionAccuracy::query()
                ->where('created_at', '>=', now()->subDays($days));

            // Filter by model version if specified
            if ($modelVersion !== null) {
                $query->where('model_version', $modelVersion);
            }

            // Get predictions
            $predictions = $query->get();

            // Return empty metrics if no predictions
            if ($predictions->isEmpty()) {
                return AccuracyMetrics::empty();
            }

            // Calculate metrics (same as getPredictionAccuracy)
            $totalPredictions = $predictions->count();
            $averageAccuracy = $this->toFloat($predictions->avg('accuracy_score'));
            $minAccuracy = $this->toFloat($predictions->min('accuracy_score'));
            $maxAccuracy = $this->toFloat($predictions->max('accuracy_score'));

            $accuratePredictions = $predictions->filter(fn ($p) => $p->isAccurate())->count();
            $inaccuratePredictions = $predictions->filter(fn ($p) => $p->isInaccurate())->count();

            $accuracyByType = [];
            $countByType = [];

            foreach ($predictions->groupBy('prediction_type') as $type => $typePredictions) {
                $accuracyByType[$type] = $this->toFloat($typePredictions->avg('accuracy_score'));
                $countByType[$type] = $typePredictions->count();
            }

            $needsImprovement = false;
            $improvementReason = null;

            if ($totalPredictions >= self::MIN_PREDICTIONS_FOR_METRICS) {
                if ($averageAccuracy < self::ACCURACY_THRESHOLD) {
                    $needsImprovement = true;
                    $improvementReason = sprintf(
                        'Recent accuracy (%.1f%%) is below threshold (%.1f%%)',
                        $averageAccuracy * 100,
                        self::ACCURACY_THRESHOLD * 100
                    );
                }
            }

            return new AccuracyMetrics(
                totalPredictions: $totalPredictions,
                averageAccuracy: $averageAccuracy,
                minAccuracy: $minAccuracy,
                maxAccuracy: $maxAccuracy,
                accuratePredictions: $accuratePredictions,
                inaccuratePredictions: $inaccuratePredictions,
                accuracyByType: $accuracyByType,
                countByType: $countByType,
                needsImprovement: $needsImprovement,
                improvementReason: $improvementReason,
            );
        } catch (\Exception $e) {
            Log::error('[PredictionAccuracyTracker] Failed to get recent accuracy', [
                'error' => $e->getMessage(),
                'days' => $days,
                'model_version' => $modelVersion,
            ]);

            return AccuracyMetrics::empty();
        }
    }
}
