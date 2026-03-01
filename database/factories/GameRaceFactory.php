<?php

namespace Database\Factories;

use App\Models\GameRace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameRace>
 */
class GameRaceFactory extends Factory
{
    protected $model = GameRace::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(3),
            'name_en' => $this->faker->words(3, true),
            'name_jp' => null,
            'grade' => $this->faker->randomElement(['G1', 'G2', 'G3', 'OP', 'Pre-OP']),
            'phase' => $this->faker->randomElement(['junior', 'classic', 'senior', 'all']),
            'surface' => 'turf',
            'distance_meters' => $this->faker->randomElement([1200, 1400, 1600, 1800, 2000, 2200, 2400]),
            'distance_category' => 'medium',
            'hand' => 'right',
            'venue' => $this->faker->city(),
            'season' => $this->faker->randomElement(['spring', 'summer', 'autumn', 'winter']),
            'month_label' => null,
            'year_in_scenario' => null,
            'fan_requirement' => 0,
            'stat_requirements' => null,
            'fans_reward' => 0,
            'sp_reward' => 0,
            'notes' => null,
            'is_ura_finale' => false,
        ];
    }
}
