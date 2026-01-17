<?php

namespace Database\Factories;

use App\Models\Aptitude;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aptitude>
 */
class AptitudeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'grade' => fake()->randomElement(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S']),
        ];
    }

    /**
     * Create a distance aptitude
     */
    public function distance(string $distanceType = 'mile'): static
    {
        return $this->state(fn (array $attributes) => [
            'distance_type' => $distanceType,
            'surface_type' => null,
            'running_style' => null,
        ]);
    }

    /**
     * Create a surface aptitude
     */
    public function surface(string $surfaceType = 'turf'): static
    {
        return $this->state(fn (array $attributes) => [
            'distance_type' => null,
            'surface_type' => $surfaceType,
            'running_style' => null,
        ]);
    }

    /**
     * Create a running style aptitude
     */
    public function runningStyle(string $runningStyle = 'pace_chaser'): static
    {
        return $this->state(fn (array $attributes) => [
            'distance_type' => null,
            'surface_type' => null,
            'running_style' => $runningStyle,
        ]);
    }
}
