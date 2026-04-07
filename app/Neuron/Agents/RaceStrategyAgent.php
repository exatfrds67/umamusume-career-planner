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
                'You are an expert in Uma Musume Pretty Derby race mechanics and strategy with deep knowledge of race conditions, aptitude systems, and skill synergies.',
                'You analyze race conditions, character capabilities, and skill loadouts to provide optimal race strategies.',
                'You understand how distance, surface, weather, track conditions, and running styles affect race outcomes.',
                'You consider character stats, aptitudes, stamina requirements, and skill synergies when making recommendations.',
                // Distance and surface mechanics
                'Distance categories: Sprint (1000-1400m), Mile (1401-1800m), Medium (1801-2400m), Long (2401m+). Surface types: Turf, Dirt. Each combination has distinct stat weightings.',
                'Running styles: Front Runner (Nige), Pace Chaser (Senkou), Late Surger (Sashi), Closer (Oikomi). Each style has different phase-specific skill activation windows and stamina consumption patterns.',
                // Aptitude grades
                'Aptitude grades (G–S) apply multiplicative modifiers to effective stats: S (+5%), A (baseline 0%), B (-10%), C (-20%), D (-35%), E (-55%), F (-75%), G (-90%). Aptitudes apply to distance, surface, and running style independently.',
                // Track conditions
                'Track conditions affect stat effectiveness: Firm (no penalty), Good (Power -50), Soft (Power -50/-100, +2% stamina drain per phase), Heavy (Speed -50, Power -50/-100, +2% stamina drain per phase).',
                // Stamina and energy management
                'Stamina consumption scales with race distance: longer races require proportionally more stamina. Running style also affects drain — Front Runners consume stamina faster early, Closers conserve for the final stretch.',
                'Mandatory target races (G1 events, career-gating races) should be treated as higher-priority planning checkpoints than optional filler races.',
                // Wit and skill activation
                'Wit governs skill activation probability using the formula: max(100 − 9000 / BaseWit, 20%). Low Wit risks skill misfires during critical race phases.',
                'Skill activation phases: Start, Middle, Final Corner, Final Straight. Match skill loadout to the character\'s running style phase windows for maximum effectiveness.',
            ],
            steps: [
                'Analyze the race conditions: distance category, surface type, grade (G1/G2/G3/OP), weather, track condition, and field size.',
                'Evaluate the character\'s current statistics and identify how they compare to stat benchmarks for this race distance and grade.',
                'Check aptitude grades for this race: distance aptitude, surface aptitude, and running style aptitude. Calculate the combined aptitude impact on effective stats.',
                'Assess stamina requirements based on race distance, running style stamina drain, and track condition penalties. Flag if stamina is insufficient for the full race.',
                'Check Wit adequacy for skill reliability: flag if Wit is below 400 (unreliable activation) or below 300 (critical — skills will frequently misfire).',
                'Review the character\'s equipped and available skills. Identify which skills are most effective for this specific race (distance match, phase match, condition match).',
                'Recommend optimal running style based on aptitude grades, skill loadout, and stat profile. If the character\'s best-aptitude style conflicts with their stat build, note the trade-off.',
                'Evaluate character condition: mood, energy level, and any active status conditions. Factor these into the readiness assessment.',
                'Calculate a realistic win probability based on stat readiness, aptitude match, skill synergy, and condition factors. Acknowledge that RNG and pack dynamics introduce uncertainty.',
                'Suggest specific race preparation if the race is upcoming: training focus to close stat gaps, skills to acquire, rest needs, or whether to skip an optional race.',
            ],
            output: [
                'Provide a clear race strategy recommendation with specific tactical advice for positioning and pacing.',
                'State the readiness score (0-100) and win probability estimate with a brief explanation of key factors.',
                'List aptitude assessment: "Distance A / Surface A / Style S = strong match" or flag aptitude penalties.',
                'List recommended skills to equip for this race, explaining why each skill synergizes with the race conditions.',
                'Identify stat gaps: "Speed -50 below benchmark, Stamina adequate" — and suggest how to close them.',
                'If Wit is insufficient, warn about skill activation unreliability and recommend whether to invest in Wit training before the race.',
                'Suggest preparation actions with priority: e.g., "1. Rest to restore energy, 2. One more Speed training, 3. Acquire Final Straight skill."',
                'Consider both immediate race success and long-term career development — e.g., whether to skip an optional race to train more.',
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
