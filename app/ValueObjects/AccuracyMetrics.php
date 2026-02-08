<?php

declare(strict_types=1);

namespace App\ValueObjects;

/**
 * Accuracy Metrics Value Object
 *
 * Represents aggregated accuracy metrics for AI predictions.
 * Used to track and analyze the performance of recommendation models
 * over time and identify areas for improvement.
 *
 * @see \App\Services\PredictionAccuracyTracker
 */
final readonly class AccuracyMetrics
{
    /**
     * Create new Accuracy Metrics
     *
     * @param  int  $totalPredictions  Total number of predictions made
     * @param  float  $averageAccuracy  Average accuracy score (0.0-1.0)
     * @param  float  $minAccuracy  Minimum accuracy score observed
     * @param  float  $maxAccuracy  Maximum accuracy score observed
     * @param  int  $accuratePredictions  Number of predictions above 0.8 threshold
     * @param  int  $inaccuratePredictions  Number of predictions below 0.6 threshold
     * @param  array<string, float>  $accuracyByType  Accuracy breakdown by prediction type
     * @param  array<string, int>  $countByType  Count breakdown by prediction type
     * @param  bool  $needsImprovement  Whether accuracy is below acceptable threshold
     * @param  string|null  $improvementReason  Reason why improvement is needed
     */
    public function __construct(
        public int $totalPredictions,
        public float $averageAccuracy,
        public float $minAccuracy,
        public float $maxAccuracy,
        public int $accuratePredictions,
        public int $inaccuratePredictions,
        public array $accuracyByType,
        public array $countByType,
        public bool $needsImprovement = false,
        public ?string $improvementReason = null,
    ) {}

    /**
     * Check if there are any predictions
     */
    public function hasPredictions(): bool
    {
        return $this->totalPredictions > 0;
    }

    /**
     * Get accuracy as a percentage string
     */
    public function getAverageAccuracyPercentage(): string
    {
        return number_format($this->averageAccuracy * 100, 1).'%';
    }

    /**
     * Get accuracy rate (accurate predictions / total predictions)
     */
    public function getAccuracyRate(): float
    {
        if ($this->totalPredictions === 0) {
            return 0.0;
        }

        return $this->accuratePredictions / $this->totalPredictions;
    }

    /**
     * Get accuracy rate as a percentage string
     */
    public function getAccuracyRatePercentage(): string
    {
        return number_format($this->getAccuracyRate() * 100, 1).'%';
    }

    /**
     * Get inaccuracy rate (inaccurate predictions / total predictions)
     */
    public function getInaccuracyRate(): float
    {
        if ($this->totalPredictions === 0) {
            return 0.0;
        }

        return $this->inaccuratePredictions / $this->totalPredictions;
    }

    /**
     * Get inaccuracy rate as a percentage string
     */
    public function getInaccuracyRatePercentage(): string
    {
        return number_format($this->getInaccuracyRate() * 100, 1).'%';
    }

    /**
     * Get accuracy level as a string
     */
    public function getAccuracyLevel(): string
    {
        return match (true) {
            $this->averageAccuracy >= 0.95 => 'Excellent',
            $this->averageAccuracy >= 0.85 => 'Very Good',
            $this->averageAccuracy >= 0.75 => 'Good',
            $this->averageAccuracy >= 0.65 => 'Fair',
            $this->averageAccuracy >= 0.50 => 'Poor',
            default => 'Very Poor',
        };
    }

    /**
     * Check if accuracy is excellent (≥0.95)
     */
    public function isExcellent(): bool
    {
        return $this->averageAccuracy >= 0.95;
    }

    /**
     * Check if accuracy is good (≥0.75)
     */
    public function isGood(): bool
    {
        return $this->averageAccuracy >= 0.75;
    }

    /**
     * Check if accuracy is poor (<0.65)
     */
    public function isPoor(): bool
    {
        return $this->averageAccuracy < 0.65;
    }

    /**
     * Get a summary string for display
     */
    public function getSummary(): string
    {
        $percentage = $this->getAverageAccuracyPercentage();
        $level = $this->getAccuracyLevel();
        $rate = $this->getAccuracyRatePercentage();

        return "{$this->totalPredictions} predictions, {$percentage} average ({$level}), {$rate} accurate";
    }

    /**
     * Convert metrics to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_predictions' => $this->totalPredictions,
            'average_accuracy' => $this->averageAccuracy,
            'average_accuracy_percentage' => $this->getAverageAccuracyPercentage(),
            'min_accuracy' => $this->minAccuracy,
            'max_accuracy' => $this->maxAccuracy,
            'accurate_predictions' => $this->accuratePredictions,
            'inaccurate_predictions' => $this->inaccuratePredictions,
            'accuracy_rate' => $this->getAccuracyRate(),
            'accuracy_rate_percentage' => $this->getAccuracyRatePercentage(),
            'inaccuracy_rate' => $this->getInaccuracyRate(),
            'inaccuracy_rate_percentage' => $this->getInaccuracyRatePercentage(),
            'accuracy_by_type' => $this->accuracyByType,
            'count_by_type' => $this->countByType,
            'accuracy_level' => $this->getAccuracyLevel(),
            'needs_improvement' => $this->needsImprovement,
            'improvement_reason' => $this->improvementReason,
        ];
    }

    /**
     * Create empty metrics (no predictions yet)
     */
    public static function empty(): self
    {
        return new self(
            totalPredictions: 0,
            averageAccuracy: 0.0,
            minAccuracy: 0.0,
            maxAccuracy: 0.0,
            accuratePredictions: 0,
            inaccuratePredictions: 0,
            accuracyByType: [],
            countByType: [],
            needsImprovement: false,
            improvementReason: null,
        );
    }
}
