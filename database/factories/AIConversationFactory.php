<?php

namespace Database\Factories;

use App\Models\AIConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIConversation>
 */
class AIConversationFactory extends Factory
{
    protected $model = AIConversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'conversation_id' => fake()->uuid(),
            'conversation_type' => fake()->randomElement(['career_planning', 'skill_optimization', 'training_advice', 'race_strategy', 'general_help']),
            'conversation_title' => fake()->sentence(),
            'context_entities' => [],
            'status' => 'active',
            'message_count' => 0,
            'last_activity_at' => now(),
            'started_at' => now(),
            'ended_at' => null,
            'ai_model' => 'claude-3.5-sonnet',
            'ai_version' => '1.0',
            'ai_configuration' => [],
            'system_prompt' => null,
            'conversation_summary' => null,
            'key_topics' => null,
            'recommendations_made' => null,
            'user_feedback' => null,
            'user_satisfaction_rating' => null,
            'helpful_responses' => 0,
            'unhelpful_responses' => 0,
            'quality_metrics' => null,
            'contains_sensitive_data' => false,
            'data_retention_policy' => null,
            'user_consented_storage' => true,
            'scheduled_deletion_at' => null,
            'workflow_state' => null,
            'action_items' => null,
            'follow_up_tasks' => null,
            'requires_human_review' => false,
            'tags' => null,
            'custom_metadata' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the conversation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'ended_at' => now()->subDays(7),
        ]);
    }
}
