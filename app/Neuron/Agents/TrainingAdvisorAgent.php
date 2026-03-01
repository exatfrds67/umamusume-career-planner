<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use App\Neuron\Agents\Tools\CharacterStatsTool;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\SystemPrompt;

/**
 * Training Advisor Agent for Uma Musume Career Planner.
 *
 * Provides expert training recommendations based on character stats,
 * aptitudes, and support card bonuses. Analyzes current character state
 * and suggests optimal training choices with detailed reasoning.
 *
 * **Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.5, 4.6**
 */
class TrainingAdvisorAgent extends BaseAgent
{
    /**
     * Create a new Training Advisor Agent instance.
     *
     * @param  int  $userId  The authenticated user ID for chat history
     * @param  int|null  $characterId  The character ID for session scoping
     */
    public function __construct(
        private int $userId,
        private ?int $characterId = null
    ) {}

    /**
     * Get the agent's system instructions.
     *
     * Defines the agent's expertise, analysis steps, and output format
     * for providing training recommendations.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert in Uma Musume Pretty Derby training mechanics.',
                'You analyze character stats, aptitudes, and support card bonuses to provide optimal training recommendations.',
                'You understand stat growth patterns, training efficiency, and how to balance stat development.',
                'You consider character goals, current career stage, and remaining turns when making recommendations.',
            ],
            steps: [
                'Analyze the character\'s current statistics and identify strengths and weaknesses',
                'Consider character aptitudes for different training types (speed, stamina, power, guts, wit)',
                'Factor in support card bonuses and friendship levels that affect training outcomes',
                'Evaluate the character\'s career stage, remaining turns, and target goals',
                'Assess energy level and mood status to determine if rest is needed',
                'Recommend the optimal training choice with clear reasoning',
                'Provide expected stat gains based on current bonuses and aptitudes',
                'Suggest alternative training options if applicable',
            ],
            output: [
                'Provide a clear, specific training recommendation (speed, stamina, power, guts, wit, or rest)',
                'Explain the reasoning behind your recommendation in 20-500 characters',
                'Include expected stat gains as specific numbers for each affected stat',
                'List alternative training options with brief explanations if they are viable',
                'Consider both immediate gains and long-term character development',
            ]
        );
    }

    /**
     * Get the chat history instance for this agent.
     *
     * Uses EloquentChatHistory to maintain conversation context
     * across multiple training decisions.
     */
    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            threadId: $this->getThreadId(),
            modelClass: \App\Models\ChatMessage::class
        );
    }

    /**
     * Get the thread identifier for chat history.
     *
     * Creates a unique thread ID scoped to the user and character.
     */
    protected function getThreadId(): string
    {
        $threadId = "training_advisor_user_{$this->userId}";

        if ($this->characterId !== null) {
            $threadId .= "_character_{$this->characterId}";
        }

        return $threadId;
    }

    /**
     * Register tools available to this agent.
     *
     * Provides access to character statistics for informed recommendations.
     *
     * @return array<int, mixed>
     */
    protected function tools(): array
    {
        return [
            new CharacterStatsTool,
        ];
    }
}
