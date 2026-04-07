<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareerPlan>
 */
class CareerPlanFactory extends Factory
{
    protected $model = CareerPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'character_id' => Character::factory(),
            'goal' => fake()->sentence(),
            'plan' => [
                'plan_id' => (string) Str::uuid(),
                'character_id' => 1,
                'created_at' => now()->toIso8601String(),
                'goal' => 'Complete the career',
                'status' => 'completed',
                'total_turns' => 3,
                'timeline' => [
                    [
                        'turn' => 1,
                        'action' => [
                            'type' => 'training',
                            'facility' => 'speed',
                            'expected_gains' => ['speed' => 20, 'stamina' => 0, 'power' => 5, 'guts' => 0, 'wit' => 0],
                            'energy_after' => 70,
                            'risk_percentage' => 10,
                            'reasoning' => 'Open with speed training to build early momentum.',
                        ],
                        'state_after' => [
                            'stats' => ['speed' => 320, 'stamina' => 200, 'power' => 180, 'guts' => 150, 'wit' => 140],
                            'energy' => 70,
                            'mood' => 'good',
                            'sp' => 45,
                            'skills' => [],
                        ],
                    ],
                ],
                'summary' => [
                    'final_predicted_stats' => ['speed' => 900, 'stamina' => 700, 'power' => 600, 'guts' => 400, 'wit' => 500],
                    'total_sp_earned' => 850,
                    'races_won' => 6,
                    'confidence' => 0.8,
                ],
            ],
            'is_locked' => false,
            'locked_at' => null,
            'current_turn' => 1,
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'is_locked' => true,
            'locked_at' => now(),
        ]);
    }

    public function queued(): static
    {
        return $this->state(function (array $attributes): array {
            $plan = $attributes['plan'];
            if (is_array($plan)) {
                $plan['status'] = 'queued';
                $plan['timeline'] = [];
                $plan['summary'] = [];
                $plan['total_turns'] = 0;
            }

            return ['plan' => $plan];
        });
    }
}
