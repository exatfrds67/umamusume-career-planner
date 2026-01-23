<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkillAcquisition>
 */
class SkillAcquisitionFactory extends Factory
{
    protected $model = SkillAcquisition::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $baseCost = fake()->numberBetween(120, 240);
        $hintsUsed = fake()->numberBetween(0, 2);
        $discountPercentage = $hintsUsed * 20.0;
        $spSaved = (int) ($baseCost * $discountPercentage / 100);
        $finalCost = $baseCost - $spSaved;

        return [
            'character_id' => Character::factory(),
            'skill_id' => Skill::factory(),
            'career_id' => null,
            'turn_acquired' => fake()->numberBetween(1, 72),
            'career_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
            'acquisition_method' => fake()->randomElement(['training', 'event', 'inheritance', 'evolution']),
            'base_sp_cost' => $baseCost,
            'hints_used' => $hintsUsed,
            'total_discount_percentage' => $discountPercentage,
            'final_sp_cost' => $finalCost,
            'sp_saved' => $spSaved,
            'is_evolution' => false,
            'evolved_from_skill_id' => null,
            'replaced_skill' => false,
            'acquisition_context' => [
                'method' => fake()->randomElement(['training', 'event', 'inheritance']),
                'notes' => fake()->optional()->sentence(),
            ],
            'hint_sources' => [],
            'priority_level' => fake()->randomElement(['low', 'medium', 'high']),
            'races_used' => 0,
            'performance_data' => [],
            'effectiveness_rating' => null,
            'is_active' => true,
            'acquisition_metadata' => [],
            'is_equipped' => false,
        ];
    }

    /**
     * Indicate that the acquisition is from evolution.
     */
    public function evolution(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_method' => 'evolution',
            'is_evolution' => true,
            'replaced_skill' => true,
        ]);
    }

    /**
     * Indicate that the acquisition is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the acquisition is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the number of hints used.
     */
    public function withHints(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $baseCost = $attributes['base_sp_cost'];
            $discountPercentage = min($count, 2) * 20.0;
            $spSaved = (int) ($baseCost * $discountPercentage / 100);
            $finalCost = $baseCost - $spSaved;

            return [
                'hints_used' => $count,
                'total_discount_percentage' => $discountPercentage,
                'sp_saved' => $spSaved,
                'final_sp_cost' => $finalCost,
            ];
        });
    }

    /**
     * Set the priority level.
     */
    public function priority(string $level): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => $level,
        ]);
    }
}
