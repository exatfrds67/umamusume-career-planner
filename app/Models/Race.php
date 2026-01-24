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
 * @property string $race_name
 * @property int|null $finish_position
 * @property int|null $distance_meters
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Race extends Model
{
    /** @use HasFactory<\Database\Factories\RaceFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ucp_races';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'career_id',
        'character_id',
        'race_name',
        'race_internal_id',
        'turn_number',
        'career_phase',
        'race_grade',
        'distance_category',
        'distance_meters',
        'distance',
        'track_type',
        'surface',
        'running_style',
        'weather',
        'track_condition',
        'field_size',
        'race_conditions',
        'character_condition',
        'motivation',
        'energy_level',
        'speed_at_race',
        'stamina_at_race',
        'power_at_race',
        'guts_at_race',
        'wit_at_race',
        'finish_position',
        'finish_time',
        'won_race',
        'margin_of_victory',
        'race_result',
        'skills_activated',
        'race_segments',
        'speed_rating',
        'performance_analysis',
        'fans_gained',
        'sp_reward',
        'item_rewards',
        'stat_bonuses',
        'injury_occurred',
        'is_ura_finale_race',
        'ura_finale_stage',
        'ura_finale_requirements',
        'is_unity_cup_match',
        'unity_cup_points_earned',
        'unity_cup_opponent_rank',
        'race_notes',
        'strategic_importance',
        'preparation_strategy',
        'lessons_learned',
        'race_metadata',
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
            'character_id' => 'integer',
            'turn_number' => 'integer',
            'distance_meters' => 'integer',
            'field_size' => 'integer',
            'speed_at_race' => 'integer',
            'stamina_at_race' => 'integer',
            'power_at_race' => 'integer',
            'guts_at_race' => 'integer',
            'wit_at_race' => 'integer',
            'finish_position' => 'integer',
            'fans_gained' => 'integer',
            'sp_reward' => 'integer',
            'unity_cup_points_earned' => 'integer',
            'race_conditions' => 'array',
            'skills_activated' => 'array',
            'race_segments' => 'array',
            'performance_analysis' => 'array',
            'item_rewards' => 'array',
            'stat_bonuses' => 'array',
            'ura_finale_requirements' => 'array',
            'preparation_strategy' => 'array',
            'lessons_learned' => 'array',
            'race_metadata' => 'array',
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
}
