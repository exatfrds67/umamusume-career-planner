<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['mcp', 'ai', 'ui', 'workflow', 'notifications'];
        $keys = ['default_agent', 'default_server', 'theme', 'language', 'auto_save'];

        return [
            'user_id' => User::factory(),
            'preference_category' => fake()->randomElement($categories),
            'preference_key' => fake()->randomElement($keys),
            'preference_value' => [
                'value' => fake()->word(),
            ],
            'value_type' => 'string',
            'description' => fake()->sentence(),
            'scope' => 'global',
            'last_modified_at' => now(),
            'modified_by' => 'user',
            'is_system_managed' => false,
            'sync_across_devices' => true,
            'has_local_override' => false,
            'requires_restart' => false,
            'is_sensitive' => false,
            'privacy_level' => 'private',
            'encrypted' => false,
            'access_count' => fake()->numberBetween(0, 100),
            'modification_count' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the preference is for MCP configuration.
     */
    public function mcp(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_category' => 'mcp',
            'preference_key' => fake()->randomElement(['default_agent', 'default_server', 'cost_limit', 'auto_restart']),
        ]);
    }

    /**
     * Indicate that the preference is for AI configuration.
     */
    public function ai(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_category' => 'ai',
            'preference_key' => fake()->randomElement(['model', 'temperature', 'max_tokens', 'streaming']),
        ]);
    }

    /**
     * Indicate that the preference is system-managed.
     */
    public function systemManaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system_managed' => true,
            'modified_by' => 'system',
        ]);
    }

    /**
     * Indicate that the preference is sensitive.
     */
    public function sensitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sensitive' => true,
            'privacy_level' => 'confidential',
            'encrypted' => true,
        ]);
    }
}
