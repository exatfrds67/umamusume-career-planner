<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prediction Accuracy Model
 *
 * Tracks the accuracy of AI predictions by comparing predicted outcomes
 * with actual outcomes. Used for continuous improvement of recommendation models.
 *
 * @property int $id
 * @property int $career_id
 * @property int $turn_number
 * @property string $prediction_type
 * @property array<string, mixed> $predicted_value
 * @property array<string, mixed> $actual_value
 * @property float $accuracy_score
 * @property string $model_version
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Career $career
 */
class PredictionAccuracy extends Model
{
    /** @use HasFactory<\Database\Factories\PredictionAccuracyFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ucp_prediction_accuracy';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'career_id',
        'turn_number',
        'prediction_type',
        'predicted_value',
        'actual_value',
        'accuracy_score',
        'model_version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'turn_number' => 'integer',
            'predicted_value' => 'array',
            'actual_value' => 'array',
            'accuracy_score' => 'float',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the career that owns this prediction accuracy record.
     *
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * Scope a query to only include predictions for a specific career.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForCareer($query, int $careerId): void
    {
        $query->where('career_id', $careerId);
    }

    /**
     * Scope a query to only include predictions for a specific turn.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForTurn($query, int $turnNumber): void
    {
        $query->where('turn_number', $turnNumber);
    }

    /**
     * Scope a query to only include predictions of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOfType($query, string $type): void
    {
        $query->where('prediction_type', $type);
    }

    /**
     * Scope a query to only include predictions for a specific model version.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForModelVersion($query, string $version): void
    {
        $query->where('model_version', $version);
    }

    /**
     * Scope a query to only include predictions with accuracy above a threshold.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeAccurateAbove($query, float $threshold): void
    {
        $query->where('accuracy_score', '>=', $threshold);
    }

    /**
     * Scope a query to only include predictions with accuracy below a threshold.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeAccurateBelow($query, float $threshold): void
    {
        $query->where('accuracy_score', '<', $threshold);
    }

    /**
     * Scope a query to order by accuracy score.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByAccuracy($query, string $direction = 'desc'): void
    {
        $query->orderBy('accuracy_score', $direction);
    }

    /**
     * Scope a query to order by turn number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByTurn($query, string $direction = 'asc'): void
    {
        $query->orderBy('turn_number', $direction);
    }

    /**
     * Scope a query to order by creation date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByDate($query, string $direction = 'desc'): void
    {
        $query->orderBy('created_at', $direction);
    }

    /**
     * Check if this prediction is accurate (above 0.8 threshold).
     */
    public function isAccurate(): bool
    {
        return $this->accuracy_score >= 0.8;
    }

    /**
     * Check if this prediction is highly accurate (above 0.9 threshold).
     */
    public function isHighlyAccurate(): bool
    {
        return $this->accuracy_score >= 0.9;
    }

    /**
     * Check if this prediction is inaccurate (below 0.6 threshold).
     */
    public function isInaccurate(): bool
    {
        return $this->accuracy_score < 0.6;
    }

    /**
     * Get the accuracy score as a percentage.
     */
    public function getAccuracyPercentage(): string
    {
        return number_format($this->accuracy_score * 100, 1).'%';
    }

    /**
     * Get the accuracy level as a string.
     */
    public function getAccuracyLevel(): string
    {
        return match (true) {
            $this->accuracy_score >= 0.95 => 'Excellent',
            $this->accuracy_score >= 0.85 => 'Very Good',
            $this->accuracy_score >= 0.75 => 'Good',
            $this->accuracy_score >= 0.65 => 'Fair',
            $this->accuracy_score >= 0.50 => 'Poor',
            default => 'Very Poor',
        };
    }

    /**
     * Get a summary of this prediction accuracy record.
     */
    public function getSummary(): string
    {
        $percentage = $this->getAccuracyPercentage();
        $level = $this->getAccuracyLevel();

        return "{$this->prediction_type} (Turn {$this->turn_number}): {$percentage} ({$level})";
    }

    /**
     * Calculate the difference between predicted and actual values.
     *
     * @return array<string, mixed>
     */
    public function getDifferences(): array
    {
        $differences = [];

        foreach ($this->predicted_value as $key => $predictedVal) {
            $actualVal = $this->actual_value[$key] ?? null;

            if ($actualVal !== null && is_numeric($predictedVal) && is_numeric($actualVal)) {
                $differences[$key] = [
                    'predicted' => $predictedVal,
                    'actual' => $actualVal,
                    'difference' => $actualVal - $predictedVal,
                    'percentage_error' => $predictedVal != 0
                        ? abs(($actualVal - $predictedVal) / $predictedVal) * 100
                        : 0,
                ];
            }
        }

        return $differences;
    }

    /**
     * Get the average percentage error across all numeric predictions.
     */
    public function getAveragePercentageError(): float
    {
        $differences = $this->getDifferences();

        if (empty($differences)) {
            return 0.0;
        }

        $totalError = array_sum(array_column($differences, 'percentage_error'));

        return $totalError / count($differences);
    }
}
