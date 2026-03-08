<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @use HasFactory<\Database\Factories\GameCharacterFactory>
 *
 * @property int $id
 * @property string $slug
 * @property string $name_en
 * @property string|null $name_jp
 * @property string|null $title
 * @property string $primary_distance
 * @property string|null $preferred_style
 * @property string|null $real_horse_name
 * @property string|null $image_path
 * @property array<string, mixed>|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GameCharacter extends Model
{
    private const DEFAULT_GOAL_RACE_POLICY = [
        'starts_with' => 'debut-race',
        'ends_with' => 'arima-kinen',
        'mode_extensions' => [
            'ura_finale' => ['ura-preliminary', 'ura-semifinal', 'ura-finals'],
        ],
    ];

    /** @use HasFactory<\Database\Factories\GameCharacterFactory> */
    use HasFactory;

    protected $table = 'ucp_game_characters';

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name_en',
        'name_jp',
        'title',
        'primary_distance',
        'preferred_style',
        'real_horse_name',
        'image_path',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'notes' => 'array',
        ];
    }

    /**
     * Races this character targets during their career run.
     *
     * @return BelongsToMany<GameRace, $this, GoalRacePivot, 'pivot'>
     */
    public function targetRaces(): BelongsToMany
    {
        return $this->belongsToMany(
            GameRace::class,
            'ucp_game_character_target_races',
            'game_character_id',
            'game_race_id'
        )->using(GoalRacePivot::class)
            ->withPivot('race_type', 'priority', 'notes')
            ->orderByPivot('priority');
    }

    /** @return BelongsToMany<GameRace, $this, GoalRacePivot, 'pivot'> */
    public function goalRaces(): BelongsToMany
    {
        return $this->targetRaces()->wherePivot('race_type', 'goal');
    }

    /** @return BelongsToMany<GameRace, $this, GoalRacePivot, 'pivot'> */
    public function requiredRaces(): BelongsToMany
    {
        return $this->targetRaces()->wherePivotIn('race_type', ['required', 'goal']);
    }

    /** @return array<string, mixed> */
    public function goalRacePolicy(): array
    {
        $notes = is_array($this->notes) ? $this->notes : [];
        $policy = $notes['goal_race_policy'] ?? [];

        return is_array($policy)
            ? array_replace_recursive(self::DEFAULT_GOAL_RACE_POLICY, $policy)
            : self::DEFAULT_GOAL_RACE_POLICY;
    }

    /** @return array<int, array<string, mixed>> */
    public function goalRaceSources(): array
    {
        $notes = is_array($this->notes) ? $this->notes : [];
        $sources = $notes['goal_race_sources'] ?? [];

        if (! is_array($sources)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $result */
        $result = array_values(array_filter($sources, 'is_array'));

        return $result;
    }

    /** @return array<int, array<string, mixed>> */
    public function goalRacePolicySources(): array
    {
        $notes = is_array($this->notes) ? $this->notes : [];
        $sources = $notes['goal_race_policy_sources'] ?? [];

        if (! is_array($sources)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $result */
        $result = array_values(array_filter($sources, 'is_array'));

        return $result;
    }
}
