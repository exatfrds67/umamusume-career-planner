<?php

namespace Database\Factories;

use App\Models\GameCharacter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameCharacter>
 */
class GameCharacterFactory extends Factory
{
    protected $model = GameCharacter::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(3),
            'name_en' => $this->faker->name(),
            'name_jp' => null,
            'title' => $this->faker->words(2, true),
            'primary_distance' => $this->faker->randomElement(['sprint', 'mile', 'medium', 'long', 'super_long']),
            'preferred_style' => $this->faker->randomElement(['escape', 'leader', 'insert', 'tracking']),
            'real_horse_name' => $this->faker->name(),
            'image_path' => null,
            'notes' => null,
        ];
    }
}
