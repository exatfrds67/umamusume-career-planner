<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportCard>
 */
class SupportCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cardTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'];
        $rarities = ['SSR', 'SR', 'R'];
        $tiers = ['S+', 'S', 'A', 'B', 'C'];

        return [
            'name' => fake()->name(),
            'internal_id' => fake()->unique()->uuid(),
            'card_type' => fake()->randomElement($cardTypes),
            'rarity' => fake()->randomElement($rarities),
            'max_level' => 50,
            'max_limit_break' => 4,
            'character_name' => fake()->name(),
            'character_internal_id' => fake()->uuid(),
            'speed_bonus' => fake()->numberBetween(0, 15),
            'stamina_bonus' => fake()->numberBetween(0, 15),
            'power_bonus' => fake()->numberBetween(0, 15),
            'guts_bonus' => fake()->numberBetween(0, 15),
            'wit_bonus' => fake()->numberBetween(0, 15),
            'friendship_bonus' => fake()->numberBetween(0, 10),
            'event_recovery_bonus' => fake()->numberBetween(0, 5),
            'event_effect_bonus' => fake()->numberBetween(0, 5),
            'training_effect_bonus' => fake()->numberBetween(0, 10),
            'unique_effects' => [],
            'skill_hints_provided' => [fake()->word(), fake()->word()],
            'guaranteed_events' => [],
            'special_conditions' => [],
            'is_limited' => fake()->boolean(20),
            'release_date' => fake()->date(),
            'availability_end' => null,
            'acquisition_methods' => ['gacha'],
            'meta_tier' => fake()->randomElement($tiers),
            'deck_synergies' => [],
            'recommended_scenarios' => ['ura_finale', 'unity_cup'],
            'strategic_notes' => [],
            'usage_rate' => fake()->randomFloat(2, 0, 100),
            'win_rate_contribution' => fake()->randomFloat(2, 0, 100),
            'performance_data' => [],
            'artwork_url' => null,
            'artwork_variants' => [],
            'flavor_text' => fake()->sentence(),
            'is_active' => true,
            'card_metadata' => [],
        ];
    }
}
