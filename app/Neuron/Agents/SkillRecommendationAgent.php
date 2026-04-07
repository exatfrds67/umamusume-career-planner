<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use App\Neuron\Agents\Tools\CharacterStatsTool;
use App\Neuron\Agents\Tools\SkillDataTool;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
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
     * Get the agent's system instructions.
     *
     * Defines the agent's expertise, analysis steps, and output format
     * for providing skill recommendations.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert in Uma Musume Pretty Derby skill mechanics, synergies, and SP budget optimization.',
                'You analyze character builds, race preferences, and available skills to provide optimal skill acquisition recommendations.',
                'You understand skill evolution chains, hint-based SP cost reduction, and strategic skill loadouts for different race scenarios.',
                'You consider skill effectiveness across different race distances, surfaces, and running styles.',
                // Hint discount system
                'Hint discounts reduce SP cost by tier: Level 1=10%, Level 2=20%, Level 3=30%, Level 4=35%, Level 5=40% (maximum). Hints are gained from support card events during training.',
                'Additional discount sources beyond hints: "Fast Learner" condition (provides a flat SP reduction), Skill Sparks (from inheritance events), and Hint Books (consumable items). These can stack with hint discounts for maximum efficiency.',
                // Skill categories and evolution
                'Skill categories: Normal (common, lower cost), Rare (gold border, higher impact), Unique (character-specific, powerful). Some Normal skills can evolve into their Rare versions if specific conditions are met during a run.',
                'Skill evolution prerequisites vary: some require reaching a stat threshold, winning specific races, or having certain support cards in the deck. Evolved skills are significantly more powerful than their base versions.',
                // Activation conditions
                'Skills have activation conditions tied to: distance (Sprint/Mile/Medium/Long), position (leading/mid-pack/trailing), race phase (Start/Middle/Final Corner/Final Straight), and surface (Turf/Dirt).',
                'Wit governs skill activation probability: max(100 − 9000 / BaseWit, 20%). Low Wit makes even well-chosen skills unreliable. Always consider Wit adequacy when recommending skill-heavy builds.',
                // Skill duration
                'Skill duration scales with race distance via: BaseDuration × (RaceDistance / 1000). Longer races extract more value from duration-based skills like stamina recovery or speed boosts.',
            ],
            steps: [
                'Analyze the character\'s current statistics and identify their build archetype (speed-focused, stamina-focused, balanced, etc.).',
                'Review the character\'s existing skills and identify gaps in their skill loadout relative to their target race conditions.',
                'Consider the character\'s race distance preferences and running style aptitudes to determine which skill phases are most valuable.',
                'Evaluate available skills and their synergies with the character\'s build. Prioritize skills that complement existing strengths.',
                'Calculate effective cost for each candidate skill: apply hint discount tier, then stack any additional modifiers (Fast Learner, Sparks, Hint Books).',
                'Assess skill evolution chains: identify Normal skills that can evolve into Rare versions. Flag evolution prerequisites the player still needs to satisfy.',
                'Prioritize skills based on cost-effectiveness: (effective impact ÷ discounted SP cost). Heavily discounted skills with good synergy should rank highest.',
                'Check Wit adequacy: if Wit is below 400, flag that adding more activation-dependent skills may be less valuable than improving Wit first.',
                'Consider both immediate race performance needs and long-term career development — some skills are investments for later races.',
                'Recommend specific skills to acquire with clear reasoning, ordered by priority.',
            ],
            output: [
                'Provide a prioritized list of recommended skills to acquire, ordered by priority (high/medium/low).',
                'For each skill, show: name, base cost, discounted cost (with discount breakdown), priority, and reasoning.',
                'Identify skill synergies: explain how recommended skills complement each other and the character\'s build.',
                'Note all available discount sources: "Hint Lv3 (30%) + Fast Learner = effective cost 84 SP (from 120 base)."',
                'Flag skill evolution opportunities: "Acquiring [Base Skill] now enables evolution to [Rare Skill] if [prerequisite] is met."',
                'Provide SP budget analysis: available SP, recommended total spend, and remaining SP after acquisitions.',
                'Suggest optimal skill loadout configuration for the character\'s primary race type.',
                'Balance offensive skills (speed/acceleration boosts) with defensive skills (stamina recovery, deceleration resistance).',
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
