<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'key' => $this->faker->unique()->slug(3),
            'category' => $this->faker->randomElement(['racing', 'training', 'skills', 'career', 'collection']),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'icon' => '🏆',
            'rarity' => $this->faker->randomElement(['common', 'rare', 'epic', 'legendary']),
            'progress' => 0,
            'target' => $this->faker->randomElement([1, 5, 10, 50]),
            'is_unlocked' => false,
            'unlocked_at' => null,
            'metadata' => null,
        ];
    }

    public function unlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'progress' => $attributes['target'],
            'is_unlocked' => true,
            'unlocked_at' => now(),
        ]);
    }

    public function legendary(): static
    {
        return $this->state(fn () => ['rarity' => 'legendary']);
    }

    public function epic(): static
    {
        return $this->state(fn () => ['rarity' => 'epic']);
    }
}
