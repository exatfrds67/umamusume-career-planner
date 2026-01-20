<?php

namespace Database\Factories;

use App\Models\AIConversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConversationMessage>
 */
class ConversationMessageFactory extends Factory
{
    protected $model = ConversationMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => AIConversation::factory(),
            'user_id' => User::factory(),
            'message_type' => fake()->randomElement(['user', 'ai', 'system']),
            'message_content' => fake()->paragraph(),
            'message_metadata' => [],
            'agent_id' => fake()->optional()->uuid(),
            'agent_type' => fake()->optional()->randomElement(['TrainingOptimizationAgent', 'CareerStrategyAgent', 'RaceAnalysisAgent']),
            'agent_name' => fake()->optional()->words(2, true),
            'agent_context' => [],
            'tools_used' => [],
            'tool_results' => [],
            'tool_call_count' => 0,
            'parent_message_id' => null,
            'branch_id' => null,
            'branch_depth' => 0,
            'is_branch_point' => false,
            'branch_metadata' => null,
            'ai_model_used' => fake()->optional()->randomElement(['claude-3.5-sonnet', 'gpt-4', 'llama-3.3']),
            'processing_time' => fake()->optional()->randomFloat(2, 0.5, 10),
            'tokens_used' => fake()->optional()->numberBetween(50, 500),
            'cost_estimate' => fake()->optional()->randomFloat(6, 0.0001, 0.01),
            'model_parameters' => [],
            'quality_rating' => fake()->optional()->numberBetween(1, 5),
            'is_helpful' => fake()->optional()->boolean(),
            'user_feedback' => fake()->optional()->sentence(),
            'quality_metrics' => [],
            'status' => 'completed',
            'is_visible' => true,
            'is_pinned' => false,
            'is_bookmarked' => false,
            'sent_at' => now(),
            'edited_at' => null,
        ];
    }

    /**
     * Indicate that the message is from a user.
     */
    public function userMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'user',
            'agent_id' => null,
            'agent_type' => null,
            'agent_name' => null,
            'tools_used' => [],
            'tool_results' => [],
            'tool_call_count' => 0,
        ]);
    }

    /**
     * Indicate that the message is from an AI agent.
     */
    public function aiMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'ai',
            'agent_id' => fake()->uuid(),
            'agent_type' => fake()->randomElement(['TrainingOptimizationAgent', 'CareerStrategyAgent', 'RaceAnalysisAgent']),
            'agent_name' => fake()->words(2, true),
        ]);
    }

    /**
     * Indicate that the message has tool usage.
     */
    public function withTools(): static
    {
        return $this->state(fn (array $attributes) => [
            'tools_used' => ['tool1', 'tool2'],
            'tool_results' => ['tool1' => ['success' => true], 'tool2' => ['success' => true]],
            'tool_call_count' => 2,
        ]);
    }

    /**
     * Indicate that the message is a branch point.
     */
    public function branchPoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_branch_point' => true,
            'branch_metadata' => [
                'reason' => 'Exploring alternatives',
                'alternatives' => ['Option A', 'Option B'],
            ],
        ]);
    }

    /**
     * Indicate that the message is in a branch.
     */
    public function inBranch(string $branchId, int $depth = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branchId,
            'branch_depth' => $depth,
        ]);
    }

    /**
     * Indicate that the message has a quality rating.
     */
    public function rated(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_rating' => $rating,
            'is_helpful' => $rating >= 4,
        ]);
    }
}
