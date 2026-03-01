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
 * Career Planning Agent for Uma Musume Career Planner.
 *
 * Provides long-term career planning guidance based on character stats,
 * upcoming races, skill acquisition progress, and strategic goals. Focuses
 * on milestone planning and balancing short-term gains with long-term targets.
 *
 * **Validates: Requirements 6.6, 6.7, 6.8, 6.9**
 */
class CareerPlanningAgent extends BaseAgent
{
    /**
     * Create a new Career Planning Agent instance.
     *
     * @param  int  $userId  The authenticated user ID for chat history
     * @param  int|null  $characterId  The character ID for session scoping
     * @param  int|null  $careerId  The career ID for session scoping
     */
    public function __construct(
        private int $userId,
        private ?int $characterId = null,
        private ?int $careerId = null
    ) {}

    /**
     * Get the agent's system instructions.
     *
     * Defines the agent's expertise, analysis steps, and output format
     * for providing long-term career planning recommendations.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert in Uma Musume Pretty Derby long-term career planning.',
                'You analyze character stats, upcoming races, and strategic goals to plan milestones and training focus.',
                'You balance immediate gains with future objectives, ensuring the player reaches target stats on time.',
                'You understand the impact of race scheduling, skill acquisition, and support card synergies on long-term success.',
            ],
            steps: [
                'Analyze current character stats, goals, and career stage',
                'Review upcoming races, deadlines, and target performance thresholds',
                'Identify key milestones needed to meet final build goals',
                'Recommend focus areas for the next 3-5 turns',
                'Suggest preparation strategies for upcoming high-priority races',
                'Highlight risks and potential adjustments if targets are missed',
            ],
            output: [
                'Provide a concise career planning summary (20-500 characters)',
                'List 3-6 milestone steps with timing and rationale',
                'Specify primary focus areas (stats, skills, or prep priorities)',
                'Include an upcoming race preparation plan if applicable',
                'Call out risks or watch-outs that could derail targets',
            ]
        );
    }

    /**
     * Get the chat history instance for this agent.
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
     */
    protected function getThreadId(): string
    {
        $threadId = "career_planning_user_{$this->userId}";

        if ($this->characterId !== null) {
            $threadId .= "_character_{$this->characterId}";
        }

        if ($this->careerId !== null) {
            $threadId .= "_career_{$this->careerId}";
        }

        return $threadId;
    }

    /**
     * Register tools available to this agent.
     *
     * @return array<int, mixed>
     */
    protected function tools(): array
    {
        return [
            new CharacterStatsTool,
            new RaceDataTool,
            new SkillDataTool,
        ];
    }
}
