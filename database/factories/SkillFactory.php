<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $rarities = ['normal', 'rare', 'unique'];
        $skillTypes = ['speed', 'passive', 'recovery', 'debuff', 'unique'];
        $metaTiers = ['S+', 'S', 'A', 'B', 'C'];

        $rarity = fake()->randomElement($rarities);

        // SP costs based on rarity
        $baseCost = match ($rarity) {
            'normal' => fake()->numberBetween(120, 180),
            'rare' => fake()->numberBetween(180, 240),
            'unique' => fake()->numberBetween(280, 320),
        };

        return [
            'name' => fake()->words(3, true),
            'internal_id' => 'skill_'.fake()->unique()->numberBetween(1000, 9999),
            'skill_type' => fake()->randomElement($skillTypes),
            'rarity' => $rarity,
            'base_sp_cost' => $baseCost,
            'evolution_target_id' => null,
            'evolution_source_id' => null,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => [
                'primary' => fake()->sentence(),
                'secondary' => fake()->optional()->sentence(),
            ],
            'description' => fake()->paragraph(),
            'activation_conditions' => [
                'position' => fake()->optional()->randomElement(['front', 'middle', 'back']),
                'phase' => fake()->optional()->randomElement(['early', 'mid', 'late']),
            ],
            'stat_requirements' => [],
            'support_card_sources' => [],
            'event_sources' => [],
            'inheritance_sources' => [],
            'meta_tier' => fake()->randomElement($metaTiers),
            'strategic_notes' => [
                'usage' => fake()->sentence(),
                'synergy' => fake()->optional()->sentence(),
            ],
            'synergy_skills' => [],
            'is_active' => true,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the skill can evolve.
     */
    public function canEvolve(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_evolve' => true,
            'rarity' => 'normal',
        ]);
    }

    /**
     * Indicate that the skill is an evolved version.
     */
    public function evolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_evolution' => true,
            'rarity' => 'rare',
        ]);
    }

    /**
     * Set the skill as a Normal rarity.
     */
    public function normal(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'normal',
            'base_sp_cost' => fake()->numberBetween(120, 180),
        ]);
    }

    /**
     * Set the skill as a Rare rarity.
     */
    public function rare(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'rare',
            'base_sp_cost' => fake()->numberBetween(180, 240),
        ]);
    }

    /**
     * Set the skill as a Unique rarity.
     */
    public function unique(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'unique',
            'base_sp_cost' => fake()->numberBetween(280, 320),
        ]);
    }

    /**
     * Set the skill type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'skill_type' => $type,
        ]);
    }

    /**
     * Set the meta tier.
     */
    public function metaTier(string $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_tier' => $tier,
        ]);
    }

    /**
     * Add stat requirements.
     */
    public function withStatRequirements(array $requirements): static
    {
        return $this->state(fn (array $attributes) => [
            'stat_requirements' => $requirements,
        ]);
    }

    /**
     * Add synergy skills.
     */
    public function withSynergySkills(array $skillIds): static
    {
        return $this->state(fn (array $attributes) => [
            'synergy_skills' => $skillIds,
        ]);
    }
}
