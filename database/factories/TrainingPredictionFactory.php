<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Career;
use App\Models\TrainingPrediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingPrediction>
 */
class TrainingPredictionFactory extends Factory
{
    protected $model = TrainingPrediction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'user_id' => User::factory(),
            'turn_number' => fake()->numberBetween(1, 72),
            'prediction_type' => fake()->randomElement(['speed', 'stamina', 'power', 'guts', 'wit', 'overall']),
            'predicted_value' => ['speed' => fake()->numberBetween(100, 1200), 'stamina' => fake()->numberBetween(100, 1200)],
            'actual_value' => null,
            'confidence_score' => fake()->randomFloat(4, 0.5, 1.0),
            'accuracy_score' => null,
            'model_version' => 'v1.0',
            'is_ab_test' => false,
            'ab_variant' => null,
        ];
    }

    /**
     * Create a prediction with actual results recorded.
     */
    public function withActualResult(): static
    {
        return $this->state(fn (array $attributes): array => [
            'actual_value' => ['speed' => fake()->numberBetween(100, 1200), 'stamina' => fake()->numberBetween(100, 1200)],
            'accuracy_score' => fake()->randomFloat(4, 0.5, 1.0),
        ]);
    }

    /**
     * Create an A/B test prediction.
     */
    public function abTest(string $variant = 'A'): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_ab_test' => true,
            'ab_variant' => $variant,
        ]);
    }
}
