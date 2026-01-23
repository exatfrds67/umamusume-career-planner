<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Career Model
 *
 * Represents a career run for a character in the game.
 *
 * @property int $id
 * @property int $character_id
 * @property int $user_id
 * @property string|null $career_name
 * @property string|null $scenario_type
 * @property string|null $status
 * @property int|null $current_turn
 * @property string|null $current_phase
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $final_speed
 * @property int|null $final_stamina
 * @property int|null $final_power
 * @property int|null $final_guts
 * @property int|null $final_wit
 * @property int|null $final_sp
 * @property array<string, mixed>|null $support_deck
 * @property array<string, mixed>|null $inheritance_factors
 * @property array<string, mixed>|null $rental_factors
 * @property array<string, mixed>|null $ura_finale_results
 * @property array<string, mixed>|null $unity_cup_matches
 * @property array<string, mixed>|null $strategic_goals
 * @property array<string, mixed>|null $lessons_learned
 * @property array<string, mixed>|null $career_metadata
 * @property array<string, mixed>|null $performance_analysis
 * @property array<string, mixed>|null $improvement_suggestions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Career extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ucp_careers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
        'user_id',
        'career_name',
        'scenario_type',
        'status',
        'current_turn',
        'current_phase',
        'started_at',
        'completed_at',
        'final_speed',
        'final_stamina',
        'final_power',
        'final_guts',
        'final_wit',
        'final_sp',
        'support_deck',
        'inheritance_factors',
        'rental_factors',
        'ura_finale_results',
        'unity_cup_matches',
        'strategic_goals',
        'lessons_learned',
        'career_metadata',
        'performance_analysis',
        'improvement_suggestions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character_id' => 'integer',
            'user_id' => 'integer',
            'current_turn' => 'integer',
            'final_speed' => 'integer',
            'final_stamina' => 'integer',
            'final_power' => 'integer',
            'final_guts' => 'integer',
            'final_wit' => 'integer',
            'final_sp' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'support_deck' => 'array',
            'inheritance_factors' => 'array',
            'rental_factors' => 'array',
            'ura_finale_results' => 'array',
            'unity_cup_matches' => 'array',
            'strategic_goals' => 'array',
            'lessons_learned' => 'array',
            'career_metadata' => 'array',
            'performance_analysis' => 'array',
            'improvement_suggestions' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the character that owns this career.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the user that owns this career.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the training sessions for this career.
     *
     * @return HasMany<TrainingSession, $this>
     */
    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    /**
     * Get the races for this career.
     *
     * @return HasMany<Race, $this>
     */
    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    /**
     * Get the events for this career.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the skill acquisitions for this career.
     *
     * @return HasMany<SkillAcquisition, $this>
     */
    public function skillAcquisitions(): HasMany
    {
        return $this->hasMany(SkillAcquisition::class);
    }
}
