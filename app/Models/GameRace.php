<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_en
 * @property string|null $name_jp
 * @property string $grade
 * @property string $phase
 * @property string $surface
 * @property int $distance_meters
 * @property string $distance_category
 * @property string|null $hand
 * @property string|null $venue
 * @property string|null $season
 * @property string|null $month_label
 * @property int|null $year_in_scenario
 * @property int $fan_requirement
 * @property array<string, int>|null $stat_requirements
 * @property int $fans_reward
 * @property int $sp_reward
 * @property string|null $notes
 * @property bool $is_ura_finale
 * @property-read GoalRacePivot $pivot
 *
 * @use HasFactory<\Database\Factories\GameRaceFactory>
 */
class GameRace extends Model
{
    /** @use HasFactory<\Database\Factories\GameRaceFactory> */
    use HasFactory;

    protected $table = 'ucp_game_races';

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name_en',
        'name_jp',
        'grade',
        'phase',
        'surface',
        'distance_meters',
        'distance_category',
        'hand',
        'venue',
        'season',
        'month_label',
        'year_in_scenario',
        'fan_requirement',
        'stat_requirements',
        'fans_reward',
        'sp_reward',
        'notes',
        'is_ura_finale',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_requirements' => 'array',
            'is_ura_finale' => 'boolean',
            'fan_requirement' => 'integer',
            'fans_reward' => 'integer',
            'sp_reward' => 'integer',
            'distance_meters' => 'integer',
        ];
    }

    /** @param Builder<GameRace> $query */
    public function scopeGrade(Builder $query, string $grade): void
    {
        $query->where('grade', $grade);
    }

    /** @param Builder<GameRace> $query */
    public function scopePhase(Builder $query, string $phase): void
    {
        $query->where('phase', $phase)->orWhere('phase', 'all');
    }

    /** @param Builder<GameRace> $query */
    public function scopeSurface(Builder $query, string $surface): void
    {
        $query->where('surface', $surface);
    }

    /** @param Builder<GameRace> $query */
    public function scopeDistanceCategory(Builder $query, string $category): void
    {
        $query->where('distance_category', $category);
    }

    public function getGradeBadgeColorAttribute(): string
    {
        return match ($this->grade) {
            'G1' => 'red',
            'G2' => 'purple',
            'G3' => 'blue',
            'OP' => 'green',
            default => 'gray',
        };
    }

    /** @return BelongsToMany<GameCharacter, $this, GoalRacePivot, 'pivot'> */
    public function gameCharacters(): BelongsToMany
    {
        return $this->belongsToMany(
            GameCharacter::class,
            'ucp_game_character_target_races',
            'game_race_id',
            'game_character_id'
        )->using(GoalRacePivot::class)
            ->withPivot('race_type', 'priority', 'notes');
    }
}
