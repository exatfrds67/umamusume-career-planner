<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Career;
use App\Models\PredictionAccuracy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PredictionAccuracy>
 */
class PredictionAccuracyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PredictionAccuracy>
     */
    protected $model = PredictionAccuracy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $predictionType = $this->faker->randomElement([
            'training_gain',
            'skill_hint',
            'race_placement',
            'bond_increase',
            'energy_recovery',
            'stat_growth',
        ]);

        return [
            'career_id' => Career::factory(),
            'turn_number' => $this->faker->numberBetween(1, 78),
            'prediction_type' => $predictionType,
            'predicted_value' => $this->generatePredictedValue($predictionType),
            'actual_value' => $this->generateActualValue($predictionType),
            'accuracy_score' => $this->faker->randomFloat(4, 0.5, 1.0),
            'model_version' => $this->faker->randomElement([
                'ollama-v1.0',
                'bedrock-claude-v3',
                'rule-based-v1.0',
                'hybrid-v2.1',
            ]),
        ];
    }

    /**
     * Generate predicted value based on prediction type.
     *
     * @return array<string, mixed>
     */
    private function generatePredictedValue(string $type): array
    {
        return match ($type) {
            'training_gain' => [
                'speed' => $this->faker->numberBetween(30, 60),
                'stamina' => $this->faker->numberBetween(25, 55),
                'power' => $this->faker->numberBetween(30, 60),
                'guts' => $this->faker->numberBetween(20, 50),
                'wisdom' => $this->faker->numberBetween(25, 55),
            ],
            'skill_hint' => [
                'skill_id' => $this->faker->numberBetween(1, 150),
                'hint_level' => $this->faker->numberBetween(1, 5),
                'probability' => $this->faker->randomFloat(2, 0.3, 0.9),
            ],
            'race_placement' => [
                'placement' => $this->faker->numberBetween(1, 12),
                'win_probability' => $this->faker->randomFloat(2, 0.2, 0.95),
            ],
            'bond_increase' => [
                'card_1' => $this->faker->numberBetween(5, 10),
                'card_2' => $this->faker->numberBetween(5, 10),
                'card_3' => $this->faker->numberBetween(5, 10),
            ],
            'energy_recovery' => [
                'energy_gain' => $this->faker->numberBetween(20, 50),
            ],
            'stat_growth' => [
                'total_gain' => $this->faker->numberBetween(100, 200),
                'primary_stat' => $this->faker->randomElement(['speed', 'stamina', 'power']),
            ],
            default => [
                'value' => $this->faker->numberBetween(1, 100),
            ],
        };
    }

    /**
     * Generate actual value based on prediction type (with some variance).
     *
     * @return array<string, mixed>
     */
    private function generateActualValue(string $type): array
    {
        $predicted = $this->generatePredictedValue($type);
        $actual = [];

        foreach ($predicted as $key => $value) {
            if (is_numeric($value)) {
                // Add variance of ±20%
                $variance = $this->faker->randomFloat(2, -0.2, 0.2);
                $actual[$key] = (int) round($value * (1 + $variance));
            } else {
                $actual[$key] = $value;
            }
        }

        return $actual;
    }

    /**
     * Indicate that the prediction is highly accurate.
     */
    public function highlyAccurate(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_score' => $this->faker->randomFloat(4, 0.9, 1.0),
        ]);
    }

    /**
     * Indicate that the prediction is accurate.
     */
    public function accurate(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_score' => $this->faker->randomFloat(4, 0.8, 0.9),
        ]);
    }

    /**
     * Indicate that the prediction is inaccurate.
     */
    public function inaccurate(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_score' => $this->faker->randomFloat(4, 0.3, 0.6),
        ]);
    }

    /**
     * Set the prediction type to training gain.
     */
    public function trainingGain(): static
    {
        return $this->state(fn (array $attributes) => [
            'prediction_type' => 'training_gain',
            'predicted_value' => $this->generatePredictedValue('training_gain'),
            'actual_value' => $this->generateActualValue('training_gain'),
        ]);
    }

    /**
     * Set the prediction type to skill hint.
     */
    public function skillHint(): static
    {
        return $this->state(fn (array $attributes) => [
            'prediction_type' => 'skill_hint',
            'predicted_value' => $this->generatePredictedValue('skill_hint'),
            'actual_value' => $this->generateActualValue('skill_hint'),
        ]);
    }

    /**
     * Set the prediction type to race placement.
     */
    public function racePlacement(): static
    {
        return $this->state(fn (array $attributes) => [
            'prediction_type' => 'race_placement',
            'predicted_value' => $this->generatePredictedValue('race_placement'),
            'actual_value' => $this->generateActualValue('race_placement'),
        ]);
    }

    /**
     * Set the prediction type to bond increase.
     */
    public function bondIncrease(): static
    {
        return $this->state(fn (array $attributes) => [
            'prediction_type' => 'bond_increase',
            'predicted_value' => $this->generatePredictedValue('bond_increase'),
            'actual_value' => $this->generateActualValue('bond_increase'),
        ]);
    }

    /**
     * Set a specific model version.
     */
    public function modelVersion(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'model_version' => $version,
        ]);
    }

    /**
     * Set a specific turn number.
     */
    public function forTurn(int $turnNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'turn_number' => $turnNumber,
        ]);
    }

    /**
     * Set a specific career.
     */
    public function forCareer(Career $career): static
    {
        return $this->state(fn (array $attributes) => [
            'career_id' => $career->id,
        ]);
    }
}
