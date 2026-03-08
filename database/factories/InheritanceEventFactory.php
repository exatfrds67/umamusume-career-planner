<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\ParentCharacter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InheritanceEvent>
 */
class InheritanceEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'parent_character_id' => ParentCharacter::factory(),
            'event_number' => fake()->numberBetween(1, 3),
            'spark_type' => fake()->randomElement(['blue', 'pink', 'green', 'white']),
            'star_level' => fake()->randomElement([1, 2, 3]),
            'is_applied' => false,
            'event_metadata' => [],
        ];
    }

    /**
     * Create a Blue spark (stat bonus) event.
     */
    public function blueSpark(string $statType = 'speed', int $starLevel = 3): static
    {
        $bonusValues = [1 => 9, 2 => 18, 3 => 27];

        return $this->state(fn (array $attributes) => [
            'spark_type' => 'blue',
            'star_level' => $starLevel,
            'target_stat' => $statType,
            'stat_bonus' => $bonusValues[$starLevel] ?? 9,
            'growth_rate_bonus' => null,
            'sp_bonus' => null,
            'inherited_skill_name' => null,
            'inherited_skill_data' => null,
        ]);
    }

    /**
     * Create a Pink spark (skill inheritance) event.
     */
    public function pinkSpark(string $skillName = 'Inherited Skill', int $starLevel = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'spark_type' => 'pink',
            'star_level' => $starLevel,
            'target_stat' => null,
            'stat_bonus' => null,
            'growth_rate_bonus' => null,
            'sp_bonus' => null,
            'inherited_skill_name' => $skillName,
            'inherited_skill_data' => ['name' => $skillName, 'sp_cost' => 120],
        ]);
    }

    /**
     * Create a Green spark (growth rate bonus) event.
     */
    public function greenSpark(string $statType = 'speed', int $starLevel = 2): static
    {
        $growthValues = [1 => 1.0, 2 => 2.0, 3 => 3.0];

        return $this->state(fn (array $attributes) => [
            'spark_type' => 'green',
            'star_level' => $starLevel,
            'target_stat' => $statType,
            'stat_bonus' => null,
            'growth_rate_bonus' => $growthValues[$starLevel] ?? 1.0,
            'sp_bonus' => null,
            'inherited_skill_name' => null,
            'inherited_skill_data' => null,
        ]);
    }

    /**
     * Create a White spark (SP bonus) event.
     */
    public function whiteSpark(int $starLevel = 2): static
    {
        $spValues = [1 => 20, 2 => 40, 3 => 60];

        return $this->state(fn (array $attributes) => [
            'spark_type' => 'white',
            'star_level' => $starLevel,
            'target_stat' => null,
            'stat_bonus' => null,
            'growth_rate_bonus' => null,
            'sp_bonus' => $spValues[$starLevel] ?? 20,
            'inherited_skill_name' => null,
            'inherited_skill_data' => null,
        ]);
    }

    /**
     * Set the event as applied.
     */
    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_applied' => true,
        ]);
    }

    /**
     * Set a specific event number (1, 2, or 3).
     */
    public function eventNumber(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'event_number' => $number,
        ]);
    }
}
