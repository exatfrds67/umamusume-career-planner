<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for the game_character ↔ game_race many-to-many relationship.
 *
 * @property string $race_type
 * @property int|null $priority
 * @property string|null $notes
 */
class GoalRacePivot extends Pivot
{
    protected $table = 'ucp_game_character_target_races';
}
