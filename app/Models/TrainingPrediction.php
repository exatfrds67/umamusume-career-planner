<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Training prediction record linking predictions to actual results.
 *
 * @property int $id
 * @property int $career_id
 * @property int $user_id
 * @property int $turn_number
 * @property string $prediction_type
 * @property array $predicted_value
 * @property array|null $actual_value
 * @property float $confidence_score
 * @property float|null $accuracy_score
 * @property string $model_version
 * @property bool $is_ab_test
 * @property string|null $ab_variant
 */
class TrainingPrediction extends Model
{
    use HasFactory;

    protected $table = 'ucp_training_predictions';

    protected $fillable = [
        'career_id',
        'user_id',
        'turn_number',
        'prediction_type',
        'predicted_value',
        'actual_value',
        'confidence_score',
        'accuracy_score',
        'model_version',
        'is_ab_test',
        'ab_variant',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'predicted_value' => 'array',
            'actual_value' => 'array',
            'confidence_score' => 'decimal:4',
            'accuracy_score' => 'decimal:4',
            'is_ab_test' => 'boolean',
            'turn_number' => 'integer',
        ];
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this prediction has been linked to actual results.
     */
    public function hasActualResult(): bool
    {
        return $this->actual_value !== null;
    }

    /**
     * Check if this prediction is accurate (score >= 0.8).
     */
    public function isAccurate(): bool
    {
        return $this->accuracy_score !== null && $this->accuracy_score >= 0.8;
    }
}
