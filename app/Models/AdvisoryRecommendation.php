<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Priority;
use App\Enums\RecommendationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Advisory Recommendation Model
 *
 * Stores AI-generated and rule-based recommendations for career runs.
 * Tracks recommendation history, outcomes, and player follow-through.
 *
 * @property int $id
 * @property int $career_id
 * @property int $turn_number
 * @property RecommendationType $recommendation_type
 * @property Priority $priority
 * @property string $action
 * @property string $reasoning
 * @property array<string, mixed> $expected_outcomes
 * @property float|null $confidence_score
 * @property bool $was_followed
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Career $career
 */
class AdvisoryRecommendation extends Model
{
    /** @use HasFactory<\Database\Factories\AdvisoryRecommendationFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'advisory_recommendations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'career_id',
        'turn_number',
        'recommendation_type',
        'priority',
        'action',
        'reasoning',
        'expected_outcomes',
        'confidence_score',
        'was_followed',
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
            'recommendation_type' => RecommendationType::class,
            'priority' => Priority::class,
            'expected_outcomes' => 'array',
            'confidence_score' => 'float',
            'was_followed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the career that owns this recommendation.
     *
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * Scope a query to only include recommendations for a specific career.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForCareer($query, int $careerId): void
    {
        $query->where('career_id', $careerId);
    }

    /**
     * Scope a query to only include recommendations for a specific turn.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForTurn($query, int $turnNumber): void
    {
        $query->where('turn_number', $turnNumber);
    }

    /**
     * Scope a query to only include recommendations of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOfType($query, RecommendationType $type): void
    {
        $query->where('recommendation_type', $type);
    }

    /**
     * Scope a query to only include recommendations with a specific priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeWithPriority($query, Priority $priority): void
    {
        $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include critical recommendations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeCritical($query): void
    {
        $query->where('priority', Priority::CRITICAL);
    }

    /**
     * Scope a query to only include high or critical priority recommendations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeHighPriority($query): void
    {
        $query->whereIn('priority', [Priority::CRITICAL, Priority::HIGH]);
    }

    /**
     * Scope a query to only include followed recommendations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeFollowed($query): void
    {
        $query->where('was_followed', true);
    }

    /**
     * Scope a query to only include unfollowed recommendations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeNotFollowed($query): void
    {
        $query->where('was_followed', false);
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
     * Scope a query to order by priority (highest first).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByPriority($query): void
    {
        $query->orderByRaw("
            CASE priority
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END
        ");
    }

    /**
     * Mark this recommendation as followed.
     */
    public function markAsFollowed(): bool
    {
        $this->was_followed = true;

        return $this->save();
    }

    /**
     * Mark this recommendation as not followed.
     */
    public function markAsNotFollowed(): bool
    {
        $this->was_followed = false;

        return $this->save();
    }

    /**
     * Check if this recommendation is critical.
     */
    public function isCritical(): bool
    {
        return $this->priority === Priority::CRITICAL;
    }

    /**
     * Check if this recommendation is high priority.
     */
    public function isHighPriority(): bool
    {
        return $this->priority === Priority::HIGH || $this->priority === Priority::CRITICAL;
    }

    /**
     * Check if this recommendation was followed.
     */
    public function wasFollowed(): bool
    {
        return $this->was_followed;
    }

    /**
     * Get the confidence score as a percentage.
     */
    public function getConfidencePercentage(): ?string
    {
        if ($this->confidence_score === null) {
            return null;
        }

        return number_format($this->confidence_score * 100, 1).'%';
    }

    /**
     * Get a summary of this recommendation.
     */
    public function getSummary(): string
    {
        $priority = $this->priority->label();
        $type = $this->recommendation_type->label();

        return "[{$priority}] {$type}: {$this->action}";
    }
}
