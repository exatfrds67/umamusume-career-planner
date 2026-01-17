<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'uuid' => (string) Str::uuid(),
            'name' => fake()->name(),
            'scenario_type' => fake()->randomElement(['ura_finale', 'unity_cup']),
            'career_stage' => fake()->randomElement(['junior', 'classic', 'senior']),
            'current_turn' => fake()->numberBetween(1, 78),
            'current_stats' => [
                'speed' => fake()->numberBetween(0, 1200),
                'stamina' => fake()->numberBetween(0, 1200),
                'power' => fake()->numberBetween(0, 1200),
                'guts' => fake()->numberBetween(0, 1200),
                'wit' => fake()->numberBetween(0, 1200),
            ],
            'stat_priorities' => [],
            'stat_breakpoints' => [],
            'energy_level' => fake()->numberBetween(0, 100),
            'mood_status' => fake()->randomElement(['awful', 'bad', 'normal', 'good', 'great']),
            'conditions' => [],
            'days_until_race' => null,
            'goals' => [],
            'race_schedule' => [],
            'training_plan' => [],
            'growth_rates' => [],
            'inherited_factors' => [],
            'legacy_parents' => [],
            'team_composition' => [],
            'facility_levels' => [],
            'spirit_burst_data' => [],
            'status' => 'active',
            'completion_data' => [],
        ];
    }

    /**
     * Indicate that the character is in URA Finale scenario.
     */
    public function uraFinale(): static
    {
        return $this->state(fn (array $attributes) => [
            'scenario_type' => 'ura_finale',
        ]);
    }

    /**
     * Indicate that the character is in Unity Cup scenario.
     */
    public function unityCup(): static
    {
        return $this->state(fn (array $attributes) => [
            'scenario_type' => 'unity_cup',
        ]);
    }

    /**
     * Indicate that the character has specific stats.
     */
    public function withStats(array $stats): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stats' => array_merge($attributes['current_stats'], $stats),
        ]);
    }

    /**
     * Indicate that the character has goals set.
     */
    public function withGoals(array $goals): static
    {
        return $this->state(fn (array $attributes) => [
            'goals' => $goals,
        ]);
    }
}
