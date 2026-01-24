<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use App\Neuron\Agents\Tools\CharacterStatsTool;
use App\Neuron\Agents\Tools\SkillDataTool;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\SystemPrompt;

/**
 * Skill Recommendation Agent for Uma Musume Career Planner.
 *
 * Provides expert skill acquisition recommendations based on character build
 * and race preferences. Analyzes available skills, character stats, and
 * strategic goals to suggest optimal skill loadouts and acquisition priorities.
 *
 * **Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**
 */
class SkillRecommendationAgent extends BaseAgent
{
    /**
     * Create a new Skill Recommendation Agent instance.
     *
     * @param  int  $userId  The authenticated user ID for chat history
     * @param  int|null  $characterId  The character ID for session scoping
     */
    public function __construct(
        private int $userId,
        private ?int $characterId = null
    ) {}

    /**
     * Get the AI provider instance.
     *
     * Uses Anthropic Claude as the primary provider for skill recommendations.
     */
    protected function provider(): AIProviderInterface
    {
        /** @var AIProviderInterface $provider */
        $provider = AIProvider::driver('anthropic');

        return $provider;
    }

    /**
     * Get the agent's system instructions.
     *
     * Defines the agent's expertise, analysis steps, and output format
     * for providing skill recommendations.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert in Uma Musume Pretty Derby skill mechanics and synergies.',
                'You analyze character builds, race preferences, and available skills to provide optimal skill acquisition recommendations.',
                'You understand skill evolution chains, hint-based SP cost reduction (20% per duplicate, 40% max), and strategic skill loadouts.',
                'You consider skill effectiveness across different race distances, surfaces, and running styles.',
            ],
            steps: [
                'Analyze the character\'s current statistics and identify their build archetype (speed-focused, stamina-focused, balanced, etc.)',
                'Review the character\'s existing skills and identify gaps in their skill loadout',
                'Consider the character\'s race distance preferences and running style aptitudes',
                'Evaluate available skills and their synergies with the character\'s build',
                'Factor in skill hints that provide SP cost reduction (20% per duplicate, 40% maximum)',
                'Assess skill evolution chains and recommend base skills that can evolve',
                'Prioritize skills based on cost-effectiveness (SP cost vs. impact)',
                'Consider both immediate race performance and long-term career development',
                'Recommend specific skills to acquire with clear reasoning',
            ],
            output: [
                'Provide a prioritized list of recommended skills to acquire',
                'Explain the reasoning behind each skill recommendation in 20-500 characters',
                'Identify skill synergies and how they complement the character\'s build',
                'Note any skill hints available that reduce SP costs',
                'Suggest optimal skill loadout configuration for different race types',
                'Consider both offensive skills (speed/acceleration) and defensive skills (stamina/recovery)',
                'Recommend skill evolution paths when applicable',
                'Balance immediate needs with long-term strategic value',
            ]
        );
    }

    /**
     * Get the chat history instance for this agent.
     *
     * Uses EloquentChatHistory to maintain conversation context
     * across multiple skill recommendation discussions.
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
        $threadId = "skill_recommendation_user_{$this->userId}";

        if ($this->characterId !== null) {
            $threadId .= "_character_{$this->characterId}";
        }

        return $threadId;
    }

    /**
     * Register tools available to this agent.
     *
     * Provides access to skill data and character statistics
     * for informed skill recommendations.
     *
     * @return array<int, mixed>
     */
    protected function tools(): array
    {
        return [
            new SkillDataTool,
            new CharacterStatsTool,
        ];
    }
}
