<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $trainingType = fake()->randomElement(['speed', 'stamina', 'power', 'guts', 'wit']);
        $energyBefore = fake()->numberBetween(30, 100);
        $energyCost = $trainingType === 'wit' ? -5 : fake()->numberBetween(15, 25);
        $energyAfter = max(0, min(100, $energyBefore - $energyCost));

        $speedGain = $trainingType === 'speed' ? fake()->numberBetween(8, 15) : fake()->numberBetween(0, 5);
        $staminaGain = $trainingType === 'stamina' ? fake()->numberBetween(8, 15) : fake()->numberBetween(0, 5);
        $powerGain = $trainingType === 'power' ? fake()->numberBetween(8, 15) : fake()->numberBetween(0, 5);
        $gutsGain = $trainingType === 'guts' ? fake()->numberBetween(8, 15) : fake()->numberBetween(0, 5);
        $witGain = $trainingType === 'wit' ? fake()->numberBetween(8, 15) : fake()->numberBetween(0, 5);

        $totalGain = $speedGain + $staminaGain + $powerGain + $gutsGain + $witGain;

        return [
            'career_id' => Career::factory(),
            'character_id' => Character::factory(),
            'turn_number' => fake()->numberBetween(1, 72),
            'career_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
            'training_type' => $trainingType,
            'support_cards_present' => [],
            'participating_support_cards' => [],
            'friendship_training' => fake()->boolean(20),
            'friendship_level_bonus' => fake()->numberBetween(0, 3),
            'character_condition' => fake()->randomElement(['perfect', 'good', 'normal', 'bad', 'very_bad']),
            'motivation' => fake()->randomElement(['very_high', 'high', 'normal', 'low', 'very_low']),
            'had_failure_rate' => fake()->boolean(30),
            'failure_rate_percentage' => fake()->optional()->randomFloat(2, 0, 50),
            'speed_gain' => $speedGain,
            'stamina_gain' => $staminaGain,
            'power_gain' => $powerGain,
            'guts_gain' => $gutsGain,
            'wit_gain' => $witGain,
            'sp_gain' => fake()->numberBetween(0, 10),
            'skill_hints_obtained' => [],
            'events_triggered' => [],
            'training_failed' => false,
            'failure_reason' => null,
            'energy_cost' => $energyCost,
            'energy_before' => $energyBefore,
            'energy_after' => $energyAfter,
            'injury_occurred' => false,
            'injury_type' => null,
            'training_efficiency' => fake()->randomFloat(2, 50, 100),
            'total_stat_points_gained' => $totalGain,
            'training_bonuses' => [],
            'training_penalties' => [],
            'training_notes' => null,
            'strategic_priority' => fake()->randomElement(['high', 'medium', 'low']),
            'decision_factors' => [],
            'training_metadata' => null,
        ];
    }

    /**
     * Indicate that the training session has prediction metadata.
     */
    public function withPredictions(): static
    {
        return $this->state(function (array $attributes) {
            $predictedTotal = $attributes['total_stat_points_gained'] + fake()->numberBetween(-5, 5);

            return [
                'training_metadata' => [
                    'predicted_gains' => [
                        'speed' => $attributes['speed_gain'] + fake()->numberBetween(-2, 2),
                        'stamina' => $attributes['stamina_gain'] + fake()->numberBetween(-2, 2),
                        'power' => $attributes['power_gain'] + fake()->numberBetween(-2, 2),
                        'guts' => $attributes['guts_gain'] + fake()->numberBetween(-2, 2),
                        'wit' => $attributes['wit_gain'] + fake()->numberBetween(-2, 2),
                    ],
                    'predicted_total_gain' => $predictedTotal,
                    'prediction_confidence' => fake()->randomFloat(2, 0.6, 0.95),
                ],
            ];
        });
    }

    /**
     * Indicate that the training session failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'training_failed' => true,
            'failure_reason' => fake()->randomElement(['Low energy', 'Bad condition', 'Random failure']),
            'speed_gain' => 0,
            'stamina_gain' => 0,
            'power_gain' => 0,
            'guts_gain' => 0,
            'wit_gain' => 0,
            'total_stat_points_gained' => 0,
        ]);
    }

    /**
     * Indicate that the training session was friendship training.
     */
    public function friendshipTraining(): static
    {
        return $this->state(function (array $attributes) {
            $bonus = fake()->numberBetween(2, 5);

            return [
                'friendship_training' => true,
                'friendship_level_bonus' => $bonus,
                'speed_gain' => $attributes['speed_gain'] + $bonus,
                'stamina_gain' => $attributes['stamina_gain'] + $bonus,
                'power_gain' => $attributes['power_gain'] + $bonus,
                'guts_gain' => $attributes['guts_gain'] + $bonus,
                'wit_gain' => $attributes['wit_gain'] + $bonus,
                'total_stat_points_gained' => $attributes['total_stat_points_gained'] + ($bonus * 5),
            ];
        });
    }

    /**
     * Set specific training type.
     */
    public function trainingType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'training_type' => $type,
        ]);
    }
}
