<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $career_id
 * @property int $character_id
 * @property int $turn_number
 * @property string $career_phase
 * @property string $training_type
 * @property int $speed_gain
 * @property int $stamina_gain
 * @property int $power_gain
 * @property int $guts_gain
 * @property int $wit_gain
 * @property int $sp_gain
 * @property array<string, int> $stat_gains
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TrainingSession extends Model
{
    use HasFactory;

    protected $table = 'ucp_training_sessions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'career_id',
        'character_id',
        'turn_number',
        'career_phase',
        'training_type',
        'speed_gain',
        'stamina_gain',
        'power_gain',
        'guts_gain',
        'wit_gain',
        'sp_gain',
        'support_cards_present',
        'participating_support_cards',
        'skill_hints_obtained',
        'events_triggered',
        'training_bonuses',
        'training_penalties',
        'decision_factors',
        'training_metadata',
        'training_notes',
        'training_failed',
        'failure_reason',
        'character_condition',
        'motivation',
        'had_failure_rate',
        'failure_rate_percentage',
        'success_rate',
        'friendship_training',
        'friendship_level_bonus',
        'energy_cost',
        'energy_before',
        'energy_after',
        'injury_occurred',
        'injury_type',
        'training_efficiency',
        'total_stat_points_gained',
        'strategic_priority',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'character_id' => 'integer',
            'turn_number' => 'integer',
            'speed_gain' => 'integer',
            'stamina_gain' => 'integer',
            'power_gain' => 'integer',
            'guts_gain' => 'integer',
            'wit_gain' => 'integer',
            'sp_gain' => 'integer',
            'support_cards_present' => 'array',
            'participating_support_cards' => 'array',
            'skill_hints_obtained' => 'array',
            'events_triggered' => 'array',
            'training_bonuses' => 'array',
            'training_penalties' => 'array',
            'decision_factors' => 'array',
            'training_metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * @return array<string, int>
     */
    public function getStatGainsAttribute(): array
    {
        return [
            'speed' => (int) $this->speed_gain,
            'stamina' => (int) $this->stamina_gain,
            'power' => (int) $this->power_gain,
            'guts' => (int) $this->guts_gain,
            'wit' => (int) $this->wit_gain,
            'sp' => (int) $this->sp_gain,
        ];
    }
}
