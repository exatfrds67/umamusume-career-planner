<?php

namespace Database\Factories;

use App\Models\MCPToolUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MCPToolUsage>
 */
class MCPToolUsageFactory extends Factory
{
    protected $model = MCPToolUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serverNames = ['memory-server', 'filesystem-server', 'web-server', 'ai-server', 'infrastructure-server'];
        $toolNames = ['create_entity', 'search_nodes', 'read_file', 'invoke_model', 'get_pricing'];
        $statuses = ['success', 'failure', 'timeout', 'cancelled'];

        return [
            'user_id' => User::factory(),
            'server_name' => fake()->randomElement($serverNames),
            'tool_name' => fake()->randomElement($toolNames),
            'tool_category' => fake()->randomElement(['ai', 'infrastructure', 'data', 'memory']),
            'tool_parameters' => [
                'param1' => fake()->word(),
                'param2' => fake()->numberBetween(1, 100),
            ],
            'tool_result' => [
                'status' => 'success',
                'data' => fake()->sentence(),
            ],
            'execution_status' => fake()->randomElement($statuses),
            'execution_time' => fake()->randomFloat(3, 0.1, 5.0),
            'tokens_used' => fake()->numberBetween(100, 5000),
            'cost_estimate' => fake()->randomFloat(6, 0.001, 0.1),
            'cost_model' => 'claude-3-5-sonnet',
            'request_id' => fake()->uuid(),
            'executed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the execution was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => 'success',
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the execution failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => 'failure',
            'error_message' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the execution was expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'tokens_used' => fake()->numberBetween(10000, 50000),
            'cost_estimate' => fake()->randomFloat(6, 0.5, 2.0),
        ]);
    }

    /**
     * Indicate that the execution was slow.
     */
    public function slow(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_time' => fake()->randomFloat(3, 5.0, 15.0),
        ]);
    }
}
