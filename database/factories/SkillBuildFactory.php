<?php

namespace Database\Factories;

use App\Models\SkillBuild;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkillBuild>
 */
class SkillBuildFactory extends Factory
{
    protected $model = SkillBuild::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Balanced'];
        $tiers = ['S+', 'S', 'A', 'B', 'C'];

        $totalCost = fake()->numberBetween(400, 1500);

        return [
            'user_id' => User::factory(),
            'character_id' => null,
            'name' => fake()->words(3, true).' Build',
            'category' => fake()->randomElement($categories),
            'meta_tier' => fake()->randomElement($tiers),
            'description' => fake()->sentence(),
            'skill_ids' => fake()->randomElements(range(1, 50), fake()->numberBetween(3, 12)),
            'total_sp_cost' => $totalCost,
            'optimized_cost' => (int) ($totalCost * fake()->randomFloat(2, 0.7, 0.95)),
            'tags' => fake()->randomElements(['Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Long Distance', 'Short Distance', 'Balanced'], fake()->numberBetween(1, 4)),
            'is_template' => false,
        ];
    }

    /**
     * Indicate that the build is a system template.
     */
    public function template(): static
    {
        return $this->state(fn () => [
            'is_template' => true,
        ]);
    }
}
