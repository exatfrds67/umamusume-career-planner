<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CharacterSupportCard>
 */
class CharacterSupportCardFactory extends Factory
{
    /**
     * Track used position slots per character to avoid unique constraint violations.
     *
     * @var array<int, array<int>>
     */
    protected static array $usedPositions = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => \App\Models\Character::factory(),
            'support_card_id' => \App\Models\SupportCardDefinition::factory(),
            'limit_break_level' => fake()->numberBetween(0, 4),
            'friendship_level' => fake()->numberBetween(0, 100),
            'position_slot' => fake()->numberBetween(1, 6),
            'is_friend_card' => fake()->boolean(20), // 20% chance of being friend card
        ];
    }

    /**
     * Configure the factory to use a specific position slot.
     */
    public function position(int $slot): static
    {
        return $this->state(fn (array $attributes) => [
            'position_slot' => $slot,
        ]);
    }

    /**
     * Configure the factory as a friend card.
     */
    public function friendCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_friend_card' => true,
        ]);
    }
}
