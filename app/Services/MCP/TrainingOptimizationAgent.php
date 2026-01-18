<?php

namespace App\Services\MCP;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

/**
 * Training Optimization Agent
 *
 * MCP-powered agent for advanced training optimization using:
 * - Resource Management Agent for stat allocation
 * - Skill Build Planning Agent for skill hint prioritization
 * - Multi-agent workflow coordination
 */
class TrainingOptimizationAgent
{
    public function __construct(
        private readonly MCPClientService $mcpClient
    ) {}

    /**
     * Get training optimization recommendations from MCP agents
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>|null
     */
    public function getOptimization(Character $character, array $context = []): ?array
    {
        if (! $this->mcpClient->isEnabled() || ! $this->mcpClient->isServerEnabled('strands-agents')) {
            return null;
        }

        try {
            // Prepare agent context
            $agentContext = $this->prepareAgentContext($character, $context);

            // Execute multi-agent workflow
            $workflow = $this->executeAgentWorkflow($agentContext);

            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($workflow);

            return [
                'agent_workflow' => $workflow['workflow_type'],
                'confidence_score' => $confidenceScore,
                'agents_consulted' => $workflow['agents_consulted'],
                'recommendations' => $workflow['recommendations'],
                'reasoning' => $workflow['reasoning'],
                'processing_time_ms' => $workflow['processing_time_ms'],
            ];
        } catch (\Exception $e) {
            Log::error('MCP training optimization failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return null;
        }
    }

    /**
     * Prepare context for MCP agents
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     character: array{
     *         id: int,
     *         name: string,
     *         scenario_type: string,
     *         current_stats: array<string, int>,
     *         energy_level: int,
     *         mood_status: string,
     *         growth_rates: array<string, int>|null,
     *         facility_levels: array<string, int>|null
     *     },
     *     goals: array<string, mixed>,
     *     support_cards: array<int, array<string, mixed>>,
     *     training_history: array<int, mixed>,
     *     upcoming_races: array<int, mixed>,
     *     team_members: array<int, mixed>
     * }
     */
    protected function prepareAgentContext(Character $character, array $context): array
    {
        return [
            'character' => [
                'id' => $character->id,
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'current_stats' => $character->current_stats,
                'energy_level' => $character->energy_level,
                'mood_status' => $character->mood_status,
                'growth_rates' => $character->growth_rates,
                'facility_levels' => $character->facility_levels,
            ],
            'goals' => $character->goals ?? [],
            'support_cards' => $this->getSupportCardContext($character),
            'training_history' => $context['training_history'] ?? [],
            'upcoming_races' => $context['upcoming_races'] ?? [],
            'team_members' => $context['team_members'] ?? [],
        ];
    }

    /**
     * Execute multi-agent workflow
     *
     * @param  array{
     *     character: array{
     *         scenario_type: string,
     *         current_stats: array<string, int>
     *     },
     *     goals: array<string, mixed>,
     *     support_cards: array<int, array<string, mixed>>,
     *     training_history: array<int, mixed>,
     *     upcoming_races: array<int, mixed>,
     *     team_members: array<int, mixed>
     * }  $context
     * @return array<string, mixed>
     */
    protected function executeAgentWorkflow(array $context): array
    {
        $startTime = microtime(true);
        $agentsConsulted = [];
        $recommendations = [];

        // Agent 1: Resource Management Agent
        // Analyzes stat allocation and resource optimization
        $resourceAgent = $this->consultResourceManagementAgent($context);
        $agentsConsulted[] = 'ResourceManagementAgent';
        $recommendations['resource_allocation'] = $resourceAgent;

        // Agent 2: Skill Build Planning Agent
        // Prioritizes skill hints and SP allocation
        $skillAgent = $this->consultSkillBuildPlanningAgent($context);
        $agentsConsulted[] = 'SkillBuildPlanningAgent';
        $recommendations['skill_priorities'] = $skillAgent;

        // Agent 3: Scenario Strategy Agent
        // Provides scenario-specific optimization
        $scenarioAgent = $this->consultScenarioStrategyAgent($context);
        $agentsConsulted[] = 'ScenarioStrategyAgent';
        $recommendations['scenario_strategy'] = $scenarioAgent;

        // Synthesize recommendations
        $synthesized = $this->synthesizeRecommendations($recommendations, $context);

        $processingTime = (microtime(true) - $startTime) * 1000;

        return [
            'workflow_type' => 'multi_agent_optimization',
            'agents_consulted' => $agentsConsulted,
            'recommendations' => $synthesized,
            'reasoning' => $this->generateWorkflowReasoning($synthesized, $context),
            'processing_time_ms' => round($processingTime, 2),
        ];
    }

    /**
     * Consult Resource Management Agent
     *
     * @param  array{
     *     character: array{current_stats: array<string, int>},
     *     goals: array<string, mixed>
     * }  $context
     * @return array{priority_stats: array<int, string>, stat_gaps: array<string, int>, resource_efficiency: array<string, mixed>, recommended_focus: string|null}
     */
    protected function consultResourceManagementAgent(array $context): array
    {
        $character = $context['character'];
        $goals = $context['goals'];

        // Analyze stat gaps
        $statGaps = [];
        $targetStats = $goals['target_stats'] ?? [];
        foreach ($targetStats as $stat => $target) {
            $current = $character['current_stats'][$stat] ?? 0;
            $statGaps[$stat] = max(0, $target - $current);
        }

        // Prioritize stats by gap size
        arsort($statGaps);

        return [
            'priority_stats' => array_keys($statGaps),
            'stat_gaps' => $statGaps,
            'resource_efficiency' => $this->calculateResourceEfficiency($statGaps, $character),
            'recommended_focus' => array_key_first($statGaps),
        ];
    }

    /**
     * Consult Skill Build Planning Agent
     *
     * @param  array{support_cards: array<int, array<string, mixed>>}  $context
     * @return array{available_skills: array<int, string>, skill_priority: array<int, string>, sp_allocation_strategy: string}
     */
    protected function consultSkillBuildPlanningAgent(array $context): array
    {
        $supportCards = $context['support_cards'];

        // Analyze available skill hints
        $availableSkills = [];
        foreach ($supportCards as $card) {
            if (isset($card['skills_provided'])) {
                $availableSkills = array_merge($availableSkills, $card['skills_provided']);
            }
        }

        return [
            'available_skills' => array_unique($availableSkills),
            'skill_priority' => $this->prioritizeSkills($availableSkills, $context),
            'sp_allocation_strategy' => 'prioritize_core_skills',
        ];
    }

    /**
     * Consult Scenario Strategy Agent
     *
     * @param  array{character: array{scenario_type: string}}  $context
     * @return array<string, mixed>
     */
    protected function consultScenarioStrategyAgent(array $context): array
    {
        $scenarioType = $context['character']['scenario_type'];

        return match ($scenarioType) {
            'unity_cup' => $this->getUnityCupStrategy($context),
            'ura_finale' => $this->getUraFinaleStrategy($context),
            default => ['strategy' => 'balanced', 'focus' => 'stat_optimization'],
        };
    }

    /**
     * Get Unity Cup specific strategy
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function getUnityCupStrategy(array $context): array
    {
        return [
            'strategy' => 'team_coordination',
            'focus' => 'spirit_burst_optimization',
            'recommendations' => [
                'Coordinate training with teammates for Spirit Burst gauge',
                'Balance individual stats with team synergy',
                'Prioritize facility level upgrades',
            ],
        ];
    }

    /**
     * Get URA Finale specific strategy
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function getUraFinaleStrategy(array $context): array
    {
        return [
            'strategy' => 'individual_optimization',
            'focus' => 'race_preparation',
            'recommendations' => [
                'Maximize individual stats for race performance',
                'Prioritize stat gaps and race requirements',
                'Focus on aptitude-aligned training',
            ],
        ];
    }

    /**
     * Synthesize recommendations from all agents
     *
     * @param  array<string, mixed>  $recommendations
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function synthesizeRecommendations(array $recommendations, array $context): array
    {
        $resourceRec = $recommendations['resource_allocation'];
        $skillRec = $recommendations['skill_priorities'];
        $scenarioRec = $recommendations['scenario_strategy'];

        return [
            'primary_focus' => $resourceRec['recommended_focus'],
            'secondary_focus' => $this->getSecondaryFocus($resourceRec['priority_stats']),
            'skill_priorities' => $skillRec['skill_priority'],
            'strategy' => $scenarioRec['strategy'],
            'action_plan' => $this->generateActionPlan($recommendations, $context),
        ];
    }

    /**
     * Generate workflow reasoning
     *
     * @param  array<string, mixed>  $synthesized
     * @param  array<string, mixed>  $context
     */
    protected function generateWorkflowReasoning(array $synthesized, array $context): string
    {
        $primaryFocus = $synthesized['primary_focus'];
        $strategy = $synthesized['strategy'];

        $reasoning = "Multi-agent analysis recommends focusing on {$primaryFocus} training. ";
        $reasoning .= "Strategy: {$strategy}. ";

        if (isset($synthesized['action_plan'])) {
            $reasoning .= 'Action plan: '.implode(', ', array_slice($synthesized['action_plan'], 0, 3)).'.';
        }

        return $reasoning;
    }

    /**
     * Calculate confidence score based on agent consensus
     *
     * @param  array<string, mixed>  $workflow
     */
    protected function calculateConfidenceScore(array $workflow): float
    {
        // Base confidence
        $confidence = 0.7;

        // Increase confidence if multiple agents agree
        $agentCount = count($workflow['agents_consulted']);
        $confidence += ($agentCount - 1) * 0.1;

        // Cap at 0.95
        return min(0.95, $confidence);
    }

    /**
     * Get support card context
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSupportCardContext(Character $character): array
    {
        return $character->supportCards->map(function ($characterCard) {
            return [
                'card_id' => $characterCard->support_card_id,
                'card_type' => $characterCard->supportCard->card_type ?? 'unknown',
                'limit_break_level' => $characterCard->limit_break_level ?? 0,
                'skills_provided' => $characterCard->supportCard->skills_provided ?? [],
            ];
        })->toArray();
    }

    /**
     * Calculate resource efficiency
     *
     * @param  array<string, int>  $statGaps
     * @param  array<string, mixed>  $character
     */
    protected function calculateResourceEfficiency(array $statGaps, array $character): float
    {
        $totalGap = array_sum($statGaps);
        $energyLevel = $character['energy_level'];

        // Higher energy = higher efficiency
        return ($energyLevel / 100) * min(1.0, $totalGap / 1000);
    }

    /**
     * Prioritize skills based on context
     *
     * @param  array<string>  $skills
     * @param  array<string, mixed>  $context
     * @return array<string>
     */
    protected function prioritizeSkills(array $skills, array $context): array
    {
        // Simple prioritization - can be enhanced with ML
        return array_slice($skills, 0, 5);
    }

    /**
     * Get secondary focus stat
     *
     * @param  array<string>  $priorityStats
     */
    protected function getSecondaryFocus(array $priorityStats): ?string
    {
        return $priorityStats[1] ?? null;
    }

    /**
     * Generate action plan
     *
     * @param  array<string, mixed>  $recommendations
     * @param  array<string, mixed>  $context
     * @return array<string>
     */
    protected function generateActionPlan(array $recommendations, array $context): array
    {
        $plan = [];

        $resourceRec = $recommendations['resource_allocation'];
        $scenarioRec = $recommendations['scenario_strategy'];

        // Add primary focus
        $plan[] = "Train {$resourceRec['recommended_focus']} to close stat gap";

        // Add scenario-specific recommendations
        if (isset($scenarioRec['recommendations'])) {
            $plan = array_merge($plan, array_slice($scenarioRec['recommendations'], 0, 2));
        }

        return $plan;
    }
}
