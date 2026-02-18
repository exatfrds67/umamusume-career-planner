<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdvisoryRecommendation>
 */
class AdvisoryRecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(RecommendationType::cases());

        return [
            'career_id' => Career::factory(),
            'turn_number' => fake()->numberBetween(1, 72),
            'recommendation_type' => $type,
            'priority' => fake()->randomElement(Priority::cases()),
            'action' => $this->generateAction($type),
            'reasoning' => fake()->sentence(12),
            'expected_outcomes' => $this->generateExpectedOutcomes($type),
            'confidence_score' => fake()->randomFloat(4, 0.7, 0.99),
            'was_followed' => fake()->boolean(60), // 60% chance of being followed
        ];
    }

    /**
     * Generate a realistic action based on recommendation type.
     */
    private function generateAction(RecommendationType $type): string
    {
        return match ($type) {
            RecommendationType::TRAINING_FACILITY => fake()->randomElement([
                'Speed Training',
                'Stamina Training',
                'Power Training',
                'Guts Training',
                'Wisdom Training',
            ]),
            RecommendationType::SKILL_PURCHASE => 'Purchase '.fake()->randomElement([
                'Swinging Maestro',
                'Lane Legerdemain',
                'Furious Feat',
                'In Body and Mind',
                'Adrenaline Rush',
            ]),
            RecommendationType::RACE_STRATEGY => fake()->randomElement([
                'Use Escape running style',
                'Use Lead running style',
                'Use Pace running style',
                'Use Chase running style',
            ]),
            RecommendationType::REST_RECOVERY => 'Rest',
            RecommendationType::BOND_BUILDING => 'Train at '.fake()->randomElement([
                'Speed',
                'Stamina',
                'Power',
                'Guts',
                'Wisdom',
            ]).' to build bonds',
        };
    }

    /**
     * Generate expected outcomes based on recommendation type.
     *
     * @return array<string, mixed>
     */
    private function generateExpectedOutcomes(RecommendationType $type): array
    {
        return match ($type) {
            RecommendationType::TRAINING_FACILITY => [
                'stat_gain' => '+'.fake()->numberBetween(40, 60),
                'bond_increases' => ['+7', '+7', '+7'],
                'skill_hints' => ['Possible Level 2 hint'],
            ],
            RecommendationType::SKILL_PURCHASE => [
                'sp_cost' => fake()->numberBetween(80, 150),
                'sp_remaining' => fake()->numberBetween(50, 200),
                'impact' => 'Improves race performance',
            ],
            RecommendationType::RACE_STRATEGY => [
                'win_probability' => fake()->randomFloat(2, 0.6, 0.95),
                'readiness' => 'Ready',
            ],
            RecommendationType::REST_RECOVERY => [
                'energy_gain' => '+'.fake()->numberBetween(20, 40),
            ],
            RecommendationType::BOND_BUILDING => [
                'bond_gain' => '+7 per card',
                'turns_to_friendship' => fake()->numberBetween(2, 8),
            ],
        };
    }

    /**
     * Indicate that the recommendation is critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::CRITICAL,
        ]);
    }

    /**
     * Indicate that the recommendation is high priority.
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::HIGH,
        ]);
    }

    /**
     * Indicate that the recommendation was followed.
     */
    public function followed(): static
    {
        return $this->state(fn (array $attributes) => [
            'was_followed' => true,
        ]);
    }

    /**
     * Indicate that the recommendation was not followed.
     */
    public function notFollowed(): static
    {
        return $this->state(fn (array $attributes) => [
            'was_followed' => false,
        ]);
    }

    /**
     * Create a training facility recommendation.
     */
    public function trainingFacility(): static
    {
        return $this->state(fn (array $attributes) => [
            'recommendation_type' => RecommendationType::TRAINING_FACILITY,
            'action' => fake()->randomElement([
                'Speed Training',
                'Stamina Training',
                'Power Training',
                'Guts Training',
                'Wisdom Training',
            ]),
        ]);
    }

    /**
     * Create a skill purchase recommendation.
     */
    public function skillPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'recommendation_type' => RecommendationType::SKILL_PURCHASE,
            'action' => 'Purchase '.fake()->randomElement([
                'Swinging Maestro',
                'Lane Legerdemain',
                'Furious Feat',
            ]),
        ]);
    }
}
