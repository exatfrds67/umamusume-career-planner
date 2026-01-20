<?php

namespace Database\Factories;

use App\Models\MCPServer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MCPServer>
 */
class MCPServerFactory extends Factory
{
    protected $model = MCPServer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serverTypes = ['memory', 'filesystem', 'web', 'ai', 'infrastructure'];
        $statuses = ['active', 'inactive', 'error', 'maintenance'];

        return [
            'server_name' => fake()->unique()->word().'-server',
            'server_type' => fake()->randomElement($serverTypes),
            'server_version' => fake()->semver(),
            'server_config' => [
                'timeout' => fake()->numberBetween(10, 60),
                'retry_attempts' => fake()->numberBetween(1, 5),
            ],
            'connection_params' => [
                'host' => fake()->ipv4(),
                'port' => fake()->numberBetween(8000, 9000),
            ],
            'endpoint_url' => fake()->url(),
            'status' => fake()->randomElement($statuses),
            'last_health_check' => fake()->dateTimeBetween('-1 hour', 'now'),
            'health_status' => [
                'status' => 'healthy',
                'uptime_checks' => fake()->numberBetween(50, 100),
                'successful_checks' => fake()->numberBetween(45, 95),
            ],
            'consecutive_failures' => fake()->numberBetween(0, 2),
            'supported_tools' => ['tool1', 'tool2', 'tool3'],
            'supported_resources' => ['resource1', 'resource2'],
            'server_capabilities' => ['capability1', 'capability2'],
            'total_requests' => fake()->numberBetween(0, 1000),
            'successful_requests' => fake()->numberBetween(0, 950),
            'failed_requests' => fake()->numberBetween(0, 50),
            'average_response_time' => fake()->randomFloat(3, 0.1, 5.0),
            'last_used_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'auto_start' => true,
            'auto_restart' => true,
            'max_restart_attempts' => 3,
            'restart_count' => 0,
            'requires_authentication' => fake()->boolean(),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the server is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'consecutive_failures' => 0,
        ]);
    }

    /**
     * Indicate that the server has errors.
     */
    public function withErrors(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'consecutive_failures' => fake()->numberBetween(3, 10),
            'last_error_message' => fake()->sentence(),
            'last_error_at' => now(),
        ]);
    }

    /**
     * Indicate that the server is slow.
     */
    public function slow(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_response_time' => fake()->randomFloat(3, 5.0, 15.0),
        ]);
    }
}
