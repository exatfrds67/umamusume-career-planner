<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkillHint>
 */
class SkillHintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SkillHint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'skill_id' => Skill::factory(),
            'source_type' => fake()->randomElement(['support_card', 'event', 'inheritance', 'training']),
            'source_name' => fake()->words(3, true),
            'source_id' => fake()->optional()->numberBetween(1, 100),
            'turn_obtained' => fake()->numberBetween(1, 72),
            'career_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
            'guaranteed_hint' => fake()->boolean(30),
            'training_type' => fake()->optional()->randomElement(['speed', 'stamina', 'power', 'guts', 'wit']),
            'training_participants' => fake()->optional()->randomElements([1, 2, 3, 4, 5], fake()->numberBetween(1, 3)),
            'friendship_training' => fake()->boolean(40),
            'discount_percentage' => fake()->randomElement([20.0, 40.0]),
            'is_used' => false,
            'used_at' => null,
            'hint_metadata' => [],
        ];
    }

    /**
     * Indicate that the hint is used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Indicate that the hint is guaranteed.
     */
    public function guaranteed(): static
    {
        return $this->state(fn (array $attributes) => [
            'guaranteed_hint' => true,
        ]);
    }

    /**
     * Set the hint as from a support card.
     */
    public function fromSupportCard(?string $cardName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'support_card',
            'source_name' => $cardName ?? fake()->words(3, true),
        ]);
    }

    /**
     * Set the hint as from an event.
     */
    public function fromEvent(?string $eventName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'event',
            'source_name' => $eventName ?? fake()->words(3, true),
        ]);
    }

    /**
     * Set the hint with a specific discount percentage.
     */
    public function withDiscount(float $percentage): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $percentage,
        ]);
    }
}
