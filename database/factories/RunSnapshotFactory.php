<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\RunSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RunSnapshot>
 */
class RunSnapshotFactory extends Factory
{
    protected $model = RunSnapshot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'turn_number' => fake()->numberBetween(1, 78),
            'trigger_type' => fake()->randomElement(['manual', 'auto_pre_race', 'auto_phase_transition', 'auto_save']),
            'description' => fake()->optional()->sentence(),
            'snapshot_data' => [
                'stats' => [
                    'speed' => fake()->numberBetween(100, 1200),
                    'stamina' => fake()->numberBetween(100, 1200),
                    'power' => fake()->numberBetween(100, 1200),
                    'guts' => fake()->numberBetween(100, 1200),
                    'wit' => fake()->numberBetween(100, 1200),
                ],
                'sp_available' => fake()->numberBetween(0, 5000),
                'energy' => fake()->numberBetween(0, 100),
                'mood' => fake()->randomElement(['great', 'good', 'normal', 'bad']),
                'skills_acquired' => [],
                'races_completed' => [],
            ],
            'checksum' => md5(fake()->uuid()),
        ];
    }

    /**
     * State for pre-race snapshots.
     */
    public function preRace(): static
    {
        return $this->state(fn () => [
            'trigger_type' => 'auto_pre_race',
            'description' => 'Auto-saved before race entry',
        ]);
    }

    /**
     * State for phase-transition snapshots.
     */
    public function phaseTransition(): static
    {
        return $this->state(fn () => [
            'trigger_type' => 'auto_phase_transition',
            'description' => 'Auto-saved at phase transition',
        ]);
    }
}
