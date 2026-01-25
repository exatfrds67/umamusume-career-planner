<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Character;
use App\Models\SupportDeck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportDeck>
 */
class SupportDeckFactory extends Factory
{
    protected $model = SupportDeck::class;

    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'name' => fake()->words(3, true),
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
