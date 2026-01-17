<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportCardDefinition>
 */
class SupportCardDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cardTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'];
        $cardType = fake()->randomElement($cardTypes);

        return [
            'name' => fake()->unique()->name().' Support Card',
            'internal_id' => 'SC_'.fake()->unique()->numberBetween(1000, 9999),
            'card_type' => $cardType,
            'rarity' => fake()->randomElement(['R', 'SR', 'SSR']),
            'max_level' => 50,
            'max_limit_break' => 4,
            'character_name' => $cardType !== 'friend' ? fake()->name() : null,
            'character_internal_id' => $cardType !== 'friend' ? 'CHAR_'.fake()->numberBetween(1000, 9999) : null,
            'speed_bonus' => $cardType === 'speed' ? fake()->numberBetween(10, 30) : 0,
            'stamina_bonus' => $cardType === 'stamina' ? fake()->numberBetween(10, 30) : 0,
            'power_bonus' => $cardType === 'power' ? fake()->numberBetween(10, 30) : 0,
            'guts_bonus' => $cardType === 'guts' ? fake()->numberBetween(10, 30) : 0,
            'wit_bonus' => $cardType === 'wit' ? fake()->numberBetween(10, 30) : 0,
            'friendship_bonus' => fake()->numberBetween(5, 15),
            'event_recovery_bonus' => fake()->numberBetween(0, 10),
            'event_effect_bonus' => fake()->numberBetween(0, 10),
            'training_effect_bonus' => fake()->numberBetween(5, 20),
            'unique_effects' => [],
            'skill_hints_provided' => [],
            'guaranteed_events' => [],
            'special_conditions' => [],
            'is_limited' => fake()->boolean(30),
            'release_date' => fake()->date(),
            'availability_end' => null,
            'acquisition_methods' => ['gacha'],
            'meta_tier' => fake()->randomElement(['S+', 'S', 'A', 'B', 'C']),
            'deck_synergies' => [],
            'recommended_scenarios' => [],
            'strategic_notes' => [],
            'usage_rate' => fake()->randomFloat(2, 0, 100),
            'win_rate_contribution' => fake()->randomFloat(2, 0, 100),
            'performance_data' => [],
            'artwork_url' => fake()->imageUrl(),
            'artwork_variants' => [],
            'flavor_text' => fake()->sentence(),
            'is_active' => true,
            'card_metadata' => [],
        ];
    }
}
