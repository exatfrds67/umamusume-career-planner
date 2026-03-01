<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Career>
 */
class CareerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'character_id' => \App\Models\Character::factory(),
            'star_level' => fake()->numberBetween(1, 5),
            'career_name' => fake()->words(3, true).' Career',
            'scenario_type' => fake()->randomElement(['ura_finale', 'unity_cup']),
            'status' => 'active',
            'current_turn' => fake()->numberBetween(1, 78),
            'current_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
            'started_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'completed_at' => null,
            'final_speed' => null,
            'final_stamina' => null,
            'final_power' => null,
            'final_guts' => null,
            'final_wit' => null,
            'final_sp' => null,
            'total_races_won' => fake()->numberBetween(0, 20),
            'total_races_participated' => fake()->numberBetween(0, 30),
            'win_rate' => null,
            'total_fans_gained' => fake()->numberBetween(0, 100000),
            'ura_finale_cleared' => null,
            'ura_finale_difficulty' => null,
            'ura_finale_results' => null,
            'unity_cup_points' => null,
            'unity_cup_rank' => null,
            'unity_cup_matches' => null,
            'total_training_sessions' => fake()->numberBetween(0, 50),
            'total_rest_sessions' => fake()->numberBetween(0, 10),
            'total_infirmary_visits' => fake()->numberBetween(0, 5),
            'total_skill_points_earned' => fake()->numberBetween(0, 500),
            'total_skill_points_spent' => fake()->numberBetween(0, 400),
            'support_deck' => null,
            'inheritance_factors' => null,
            'rental_factors' => null,
            'career_notes' => null,
            'strategic_goals' => null,
            'lessons_learned' => null,
            'career_metadata' => null,
            'efficiency_rating' => null,
            'performance_analysis' => null,
            'improvement_suggestions' => null,
        ];
    }

    /**
     * Indicate that the career is in URA Finale scenario.
     */
    public function uraFinale(): static
    {
        return $this->state(fn (array $attributes) => [
            'scenario_type' => 'ura_finale',
        ]);
    }

    /**
     * Indicate that the career is in Unity Cup scenario.
     */
    public function unityCup(): static
    {
        return $this->state(fn (array $attributes) => [
            'scenario_type' => 'unity_cup',
        ]);
    }

    /**
     * Indicate that the career is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
            'final_speed' => fake()->numberBetween(800, 1200),
            'final_stamina' => fake()->numberBetween(800, 1200),
            'final_power' => fake()->numberBetween(800, 1200),
            'final_guts' => fake()->numberBetween(800, 1200),
            'final_wit' => fake()->numberBetween(800, 1200),
            'final_sp' => fake()->numberBetween(100, 300),
        ]);
    }

    /**
     * Set a specific star level for the career.
     */
    public function withStarLevel(int $starLevel): static
    {
        return $this->state(fn (array $attributes) => [
            'star_level' => max(1, min(5, $starLevel)),
        ]);
    }
}
