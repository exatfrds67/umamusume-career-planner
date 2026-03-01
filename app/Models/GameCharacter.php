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
     * @return BelongsToMany<GameRace, $this>
     */
    public function targetRaces(): BelongsToMany
    {
        return $this->belongsToMany(
            GameRace::class,
            'ucp_game_character_target_races',
            'game_character_id',
            'game_race_id'
        )->withPivot('race_type', 'priority', 'notes')
            ->orderByPivot('priority');
    }

    /** @return BelongsToMany<GameRace, $this> */
    public function goalRaces(): BelongsToMany
    {
        return $this->targetRaces()->wherePivot('race_type', 'goal');
    }

    /** @return BelongsToMany<GameRace, $this> */
    public function requiredRaces(): BelongsToMany
    {
        return $this->targetRaces()->wherePivotIn('race_type', ['required', 'goal']);
    }
}
