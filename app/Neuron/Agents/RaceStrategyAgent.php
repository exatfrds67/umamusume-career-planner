<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use App\Neuron\Agents\Tools\CharacterStatsTool;
use App\Neuron\Agents\Tools\RaceDataTool;
use App\Neuron\Agents\Tools\SkillDataTool;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\SystemPrompt;

/**
 * Race Strategy Agent for Uma Musume Career Planner.
 *
 * Provides expert race strategy recommendations based on race conditions
 * and character capabilities. Analyzes race requirements, character stats,
 * and available skills to suggest optimal race preparation and strategies.
 *
 * **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5**
 */
class RaceStrategyAgent extends BaseAgent
{
    /**
     * Create a new Race Strategy Agent instance.
     *
     * @param  int  $userId  The authenticated user ID for chat history
     * @param  int|null  $raceId  The race ID for session scoping
     */
    public function __construct(
        private int $userId,
        private ?int $raceId = null
    ) {}

    /**
     * Get the agent's system instructions.
     *
     * Defines the agent's expertise, analysis steps, and output format
     * for providing race strategy recommendations.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert in Uma Musume Pretty Derby race mechanics and strategy.',
                'You analyze race conditions, character capabilities, and skill loadouts to provide optimal race strategies.',
                'You understand how distance, surface, weather, track conditions, and running styles affect race outcomes.',
                'You consider character stats, aptitudes, stamina requirements, and skill synergies when making recommendations.',
            ],
            steps: [
                'Analyze the race conditions: distance, surface, grade, weather, track condition, and field size',
                'Evaluate the character\'s current statistics, aptitudes, and readiness for the race',
                'Assess stamina requirements based on race distance and character\'s stamina stat',
                'Review available skills and identify which skills are most effective for this race',
                'Consider the character\'s running style aptitude and recommend optimal positioning strategy',
                'Evaluate character condition, motivation, and energy level for race readiness',
                'Recommend specific skills to equip that synergize with race conditions',
                'Suggest race preparation strategies (training focus, rest needs, skill acquisition)',
                'Provide win probability assessment based on character readiness',
            ],
            output: [
                'Provide a clear race strategy recommendation with specific tactical advice',
                'List recommended skills to equip for this race with explanations',
                'Suggest optimal running strategy (positioning, pacing, when to activate skills)',
                'Identify any preparation needed before the race (stat improvements, skill acquisition, rest)',
                'Assess win probability and identify key factors that could improve chances',
                'Explain the reasoning behind your recommendations in 20-500 characters',
                'Consider both immediate race success and long-term career development',
            ]
        );
    }

    /**
     * Get the chat history instance for this agent.
     *
     * Uses EloquentChatHistory to maintain conversation context
     * across multiple race strategy discussions.
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
     * Creates a unique thread ID scoped to the user and race.
     */
    protected function getThreadId(): string
    {
        $threadId = "race_strategy_user_{$this->userId}";

        if ($this->raceId !== null) {
            $threadId .= "_race_{$this->raceId}";
        }

        return $threadId;
    }

    /**
     * Register tools available to this agent.
     *
     * Provides access to race data, character capabilities, and skill information
     * for informed race strategy recommendations.
     *
     * @return array<int, mixed>
     */
    protected function tools(): array
    {
        return [
            new RaceDataTool,
            new CharacterStatsTool,
            new SkillDataTool,
        ];
    }
}
