<?php

namespace Database\Factories;

use App\Models\MCPAgent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MCPAgent>
 */
class MCPAgentFactory extends Factory
{
    protected $model = MCPAgent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $agentTypes = ['training-optimizer', 'skill-advisor', 'race-strategist', 'general-assistant'];
        $models = ['claude-3-5-sonnet', 'claude-3-opus', 'gpt-4', 'llama-3-70b'];
        $statuses = ['active', 'terminated'];
        $healthStatuses = ['healthy', 'degraded', 'unhealthy'];

        return [
            'user_id' => User::factory(),
            'agent_id' => 'agent_'.fake()->unique()->uuid(),
            'name' => fake()->words(2, true).' Agent',
            'type' => fake()->randomElement($agentTypes),
            'model' => fake()->randomElement($models),
            'instructions' => fake()->paragraph(),
            'tools' => ['training-prediction', 'stat-analysis', 'skill-optimization'],
            'memory_config' => [
                'max_tokens' => fake()->numberBetween(2000, 8000),
                'context_window' => fake()->numberBetween(4000, 16000),
            ],
            'guardrails' => [
                'max_cost_per_request' => fake()->randomFloat(4, 0.01, 1.0),
                'timeout_seconds' => fake()->numberBetween(30, 120),
            ],
            'metadata' => [
                'created_by' => 'user',
                'purpose' => fake()->sentence(),
            ],
            'status' => fake()->randomElement($statuses),
            'health_status' => fake()->randomElement($healthStatuses),
            'deployment_time' => fake()->randomFloat(3, 0.5, 5.0),
            'performance_metrics' => [
                'total_requests' => fake()->numberBetween(0, 1000),
                'successful_requests' => fake()->numberBetween(0, 950),
                'average_response_time' => fake()->randomFloat(3, 0.5, 3.0),
            ],
            'last_health_check' => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }

    /**
     * Indicate that the agent is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'health_status' => 'healthy',
            'terminated_at' => null,
        ]);
    }

    /**
     * Indicate that the agent is terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
            'terminated_at' => now(),
        ]);
    }

    /**
     * Indicate that the agent is unhealthy.
     */
    public function unhealthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'health_status' => 'unhealthy',
        ]);
    }
}
