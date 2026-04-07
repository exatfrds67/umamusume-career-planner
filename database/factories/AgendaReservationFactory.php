<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgendaReservation;
use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaReservation>
 */
class AgendaReservationFactory extends Factory
{
    protected $model = AgendaReservation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'reserved_coins' => fake()->numberBetween(0, 999),
            'reserved_hammers' => fake()->numberBetween(0, 10),
            'target_ts_distance' => fake()->optional()->randomElement(['mile', 'medium', 'long']),
        ];
    }
}
