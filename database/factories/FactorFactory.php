<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Factor>
 */
class FactorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $factorType = fake()->randomElement(['blue_stats', 'red_aptitudes', 'green_unique_skills', 'white_normal_skills']);
        $starLevel = fake()->randomElement(['1_star', '2_star', '3_star']);

        $data = [
            'character_id' => Character::factory(),
            'factor_type' => $factorType,
            'factor_name' => fake()->words(2, true).' Factor',
            'star_level' => $starLevel,
            'source_parent' => fake()->randomElement(['main_parent_1', 'main_parent_2', 'grandparent_1', 'grandparent_2', 'grandparent_3', 'grandparent_4']),
            'source_character_name' => fake()->name(),
            'inheritance_rate' => fake()->randomFloat(2, 50, 100),
            'affinity_compatible' => fake()->boolean(30),
            'is_active' => true,
            'factor_metadata' => [],
        ];

        // Add type-specific fields
        if ($factorType === 'blue_stats') {
            $data['stat_type'] = fake()->randomElement(['speed', 'stamina', 'power', 'guts', 'wit']);
            $data['stat_bonus'] = match ($starLevel) {
                '1_star' => 5,
                '2_star' => 12,
                '3_star' => 21,
            };
        } elseif ($factorType === 'red_aptitudes') {
            $data['aptitude_type'] = fake()->randomElement(['sprint', 'mile', 'medium', 'long', 'turf', 'dirt', 'front_runner', 'pace_chaser', 'late_surger', 'end_closer']);
            $data['grade_improvement'] = fake()->numberBetween(1, 3);
        } elseif ($factorType === 'green_unique_skills') {
            $data['unique_skill_name'] = fake()->words(3, true);
            $data['skill_effects'] = ['effect' => fake()->sentence()];
        } else {
            $data['normal_skill_name'] = fake()->words(2, true);
            $data['race_bonuses'] = ['bonus' => fake()->sentence()];
        }

        return $data;
    }

    /**
     * Create a blue stat factor.
     */
    public function blueStat(string $statType = 'speed', string $starLevel = '3_star'): static
    {
        return $this->state(fn (array $attributes) => [
            'factor_type' => 'blue_stats',
            'factor_name' => ucfirst($statType).' Factor',
            'stat_type' => $statType,
            'star_level' => $starLevel,
            'stat_bonus' => match ($starLevel) {
                '1_star' => 5,
                '2_star' => 12,
                '3_star' => 21,
            },
            'aptitude_type' => null,
            'grade_improvement' => null,
            'unique_skill_name' => null,
            'skill_effects' => null,
            'normal_skill_name' => null,
            'race_bonuses' => null,
        ]);
    }

    /**
     * Create a red aptitude factor.
     */
    public function redAptitude(string $aptitudeType = 'turf', int $gradeImprovement = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'factor_type' => 'red_aptitudes',
            'factor_name' => ucfirst(str_replace('_', ' ', $aptitudeType)).' Aptitude Factor',
            'star_level' => '1_star', // Default to 1_star for red factors
            'aptitude_type' => $aptitudeType,
            'grade_improvement' => $gradeImprovement,
            'stat_type' => null,
            'stat_bonus' => null,
            'unique_skill_name' => null,
            'skill_effects' => null,
            'normal_skill_name' => null,
            'race_bonuses' => null,
        ]);
    }

    /**
     * Create a green unique skill factor.
     */
    public function greenUniqueSkill(string $skillName = 'Unique Skill'): static
    {
        return $this->state(fn (array $attributes) => [
            'factor_type' => 'green_unique_skills',
            'factor_name' => $skillName,
            'star_level' => '3_star',
            'unique_skill_name' => $skillName,
            'skill_effects' => [
                'effect_type' => 'speed_boost',
                'effect_value' => fake()->numberBetween(5, 15),
                'condition' => 'final_straight',
            ],
            'stat_type' => null,
            'stat_bonus' => null,
            'aptitude_type' => null,
            'grade_improvement' => null,
            'normal_skill_name' => null,
            'race_bonuses' => null,
        ]);
    }

    /**
     * Create a white normal skill factor.
     */
    public function whiteNormalSkill(string $skillName = 'Normal Skill'): static
    {
        return $this->state(fn (array $attributes) => [
            'factor_type' => 'white_normal_skills',
            'factor_name' => $skillName,
            'normal_skill_name' => $skillName,
            'race_bonuses' => [
                'distance_type' => fake()->randomElement(['sprint', 'mile', 'medium', 'long']),
                'bonus_value' => fake()->numberBetween(3, 10),
            ],
            'stat_type' => null,
            'stat_bonus' => null,
            'aptitude_type' => null,
            'grade_improvement' => null,
            'unique_skill_name' => null,
            'skill_effects' => null,
        ]);
    }

    /**
     * Create a 1-star factor.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'star_level' => '1_star',
        ]);
    }

    /**
     * Create a 2-star factor.
     */
    public function twoStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'star_level' => '2_star',
        ]);
    }

    /**
     * Create a 3-star factor.
     */
    public function threeStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'star_level' => '3_star',
        ]);
    }

    /**
     * Create an active factor.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive factor.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a factor from main parent 1.
     */
    public function fromMainParent1(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_parent' => 'main_parent_1',
        ]);
    }

    /**
     * Create a factor from main parent 2.
     */
    public function fromMainParent2(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_parent' => 'main_parent_2',
        ]);
    }
}
