<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AlertType;
use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CriticalAlert>
 */
class CriticalAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(AlertType::cases());
        $turnsUntilCritical = fake()->numberBetween(0, 5);

        return [
            'career_id' => Career::factory(),
            'turn_number' => fake()->numberBetween(1, 72),
            'alert_type' => $type,
            'message' => $this->generateMessage($type),
            'action_items' => $this->generateActionItems($type),
            'turns_until_critical' => $turnsUntilCritical,
            'was_dismissed' => fake()->boolean(30), // 30% chance of being dismissed
            'dismissed_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    /**
     * Generate a realistic message based on alert type.
     */
    private function generateMessage(AlertType $type): string
    {
        return match ($type) {
            AlertType::STAMINA_CRISIS => 'Stamina critically low for upcoming '.fake()->randomElement(['Medium', 'Long']).' race',
            AlertType::SP_SHORTAGE => 'SP budget insufficient for planned skill purchases',
            AlertType::ENERGY_CRITICAL => 'Energy at '.fake()->numberBetween(20, 39).' - high failure rate risk',
            AlertType::BOND_BEHIND_SCHEDULE => 'Support card bonds below 80 by Turn '.fake()->numberBetween(20, 30),
            AlertType::FACILITY_IMBALANCE => 'Facility levels significantly unbalanced',
            AlertType::RACE_UNREADY => 'Character not ready for upcoming race',
            AlertType::TEAM_RACE_UNPREPARED => 'Team race requirements not met',
        };
    }

    /**
     * Generate action items based on alert type.
     *
     * @return array<string>
     */
    private function generateActionItems(AlertType $type): array
    {
        return match ($type) {
            AlertType::STAMINA_CRISIS => [
                'Focus next '.fake()->numberBetween(2, 4).' turns on Stamina training',
                'Prioritize Friendship Training at Stamina facility',
                'Consider purchasing stamina recovery skills',
            ],
            AlertType::SP_SHORTAGE => [
                'Prioritize essential skills only',
                'Wait for better hint levels before purchasing',
                'Focus on gold skills with Level 3+ hints',
            ],
            AlertType::ENERGY_CRITICAL => [
                'Rest immediately or train Wisdom',
                'Avoid high-risk training until energy recovers to 50+',
            ],
            AlertType::BOND_BEHIND_SCHEDULE => [
                'Train at facilities with multiple support cards',
                'Prioritize bond building over stat gains',
                'Aim for 80 bond by Turn '.fake()->numberBetween(25, 30),
            ],
            AlertType::FACILITY_IMBALANCE => [
                'Train at lower-level facilities to balance progression',
                'Avoid over-training at max-level facilities',
            ],
            AlertType::RACE_UNREADY => [
                'Increase '.fake()->randomElement(['Speed', 'Stamina', 'Power']).' stat',
                'Purchase race-specific skills',
                'Ensure adequate stamina for distance',
            ],
            AlertType::TEAM_RACE_UNPREPARED => [
                'Complete team training requirements',
                'Ensure all team members meet stat thresholds',
                'Practice team race strategies',
            ],
        };
    }

    /**
     * Indicate that the alert is active (not dismissed).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'was_dismissed' => false,
            'dismissed_at' => null,
        ]);
    }

    /**
     * Indicate that the alert is dismissed.
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'was_dismissed' => true,
            'dismissed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create an immediate critical alert (0 turns).
     */
    public function immediateCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'turns_until_critical' => 0,
        ]);
    }

    /**
     * Create an approaching critical alert (1-3 turns).
     */
    public function approachingCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'turns_until_critical' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a stamina crisis alert.
     */
    public function staminaCrisis(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => AlertType::STAMINA_CRISIS,
            'message' => 'Stamina critically low for upcoming '.fake()->randomElement(['Medium', 'Long']).' race',
        ]);
    }

    /**
     * Create an energy critical alert.
     */
    public function energyCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => AlertType::ENERGY_CRITICAL,
            'message' => 'Energy at '.fake()->numberBetween(20, 39).' - high failure rate risk',
            'turns_until_critical' => 0,
        ]);
    }
}
