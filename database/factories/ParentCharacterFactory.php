<?php

namespace Database\Factories;

use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentCharacter>
 */
class ParentCharacterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'slot' => fake()->randomElement(['parent_1', 'parent_2']),
            'character_name' => fake()->name(),
            'scenario_type' => fake()->randomElement(['ura_finale', 'unity_cup']),
            'running_style' => fake()->randomElement(['runner', 'leader', 'betweener', 'chaser']),
            'preferred_distance' => fake()->randomElement(['sprint', 'mile', 'medium', 'long']),
            'final_speed' => fake()->numberBetween(600, 1200),
            'final_stamina' => fake()->numberBetween(600, 1200),
            'final_power' => fake()->numberBetween(600, 1200),
            'final_guts' => fake()->numberBetween(600, 1200),
            'final_wit' => fake()->numberBetween(600, 1200),
            'affinity_grade' => fake()->randomElement(['high', 'standard', 'low']),
            'skill_pool' => [],
            'factor_summary' => [],
            'parent_metadata' => [],
        ];
    }

    /**
     * Create a high-affinity parent.
     */
    public function highAffinity(): static
    {
        return $this->state(fn (array $attributes) => [
            'affinity_grade' => 'high',
        ]);
    }

    /**
     * Create a parent with high stats (1100+).
     */
    public function highStats(string $primaryStat = 'speed'): static
    {
        return $this->state(fn (array $attributes) => [
            'final_'.$primaryStat => fake()->numberBetween(1100, 1300),
        ]);
    }

    /**
     * Assign to parent slot 1.
     */
    public function parent1(): static
    {
        return $this->state(fn (array $attributes) => [
            'slot' => 'parent_1',
        ]);
    }

    /**
     * Assign to parent slot 2.
     */
    public function parent2(): static
    {
        return $this->state(fn (array $attributes) => [
            'slot' => 'parent_2',
        ]);
    }
}
