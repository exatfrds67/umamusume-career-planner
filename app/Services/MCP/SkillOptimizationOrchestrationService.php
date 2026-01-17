<?php

namespace App\Services\MCP;

use App\Models\Character;
use App\Services\MCP\Agents\HintFarmingStrategyAgent;
use App\Services\MCP\Agents\LongTermDevelopmentAgent;
use App\Services\MCP\Agents\SkillBuildPlanningAgent;
use App\Services\MCP\Agents\SPBudgetManagementAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Skill Optimization Orchestration Service
 *
 * Coordinates multiple MCP agents for comprehensive skill optimization.
 * Implements agent collaboration workflows that combine SP budget management,
 * hint farming strategies, skill build planning, and long-term development
 * to provide holistic skill optimization recommendations.
 */
class SkillOptimizationOrchestrationService
{
    protected MCPClientService $mcpClient;

    protected SPBudgetManagementAgent $spBudgetAgent;

    protected HintFarmingStrategyAgent $hintFarmingAgent;

    protected SkillBuildPlanningAgent $skillBuildAgent;

    protected LongTermDevelopmentAgent $longTermAgent;

    public function __construct(
        MCPClientService $mcpClient,
        SPBudgetManagementAgent $spBudgetAgent,
        HintFarmingStrategyAgent $hintFarmingAgent,
        SkillBuildPlanningAgent $skillBuildAgent,
        LongTermDevelopmentAgent $longTermAgent
    ) {
        $this->mcpClient = $mcpClient;
        $this->spBudgetAgent = $spBudgetAgent;
        $this->hintFarmingAgent = $hintFarmingAgent;
        $this->skillBuildAgent = $skillBuildAgent;
        $this->longTermAgent = $longTermAgent;
    }

    /**
     * Execute comprehensive skill optimization analysis
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     sp_budget: array<string, mixed>,
     *     hint_farming: array<string, mixed>,
     *     skill_build: array<string, mixed>,
     *     long_term_development: array<string, mixed>,
     *     integrated_strategy: array<string, mixed>,
     *     orchestration_metadata: array<string, mixed>
     * }
     */
    public function executeComprehensiveOptimization(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        Collection $currentSkills,
        array $goals = [],
        array $context = []
    ): array {
        $startTime = microtime(true);

        Log::info('Starting comprehensive skill optimization', [
            'character_id' => $character->id,
            'target_skills' => $targetSkills->count(),
            'support_cards' => $supportCards->count(),
        ]);

        // Execute all skill optimization agents
        $results = [
            'sp_budget' => $this->executeSPBudgetAgent($character, $targetSkills, $context),
            'hint_farming' => $this->executeHintFarmingAgent($character, $targetSkills, $supportCards, $context),
            'skill_build' => $this->executeSkillBuildAgent($character, $targetSkills, $currentSkills, $context),
            'long_term_development' => $this->executeLongTermAgent($character, $targetSkills, $goals, $context),
        ];

        // Integrate recommendations from all agents
        $integratedStrategy = $this->integrateSkillOptimizationStrategy($character, $results, $context);

        // Calculate orchestration metadata
        $executionTime = microtime(true) - $startTime;
        $orchestrationMetadata = $this->generateOrchestrationMetadata($results, $executionTime);

        Log::info('Skill optimization completed', [
            'character_id' => $character->id,
            'execution_time' => $executionTime,
            'agents_executed' => count($results),
        ]);

        return [
            ...$results,
            'integrated_strategy' => $integratedStrategy,
            'orchestration_metadata' => $orchestrationMetadata,
        ];
    }

    /**
     * Execute SP Budget Management Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeSPBudgetAgent(Character $character, Collection $targetSkills, array $context = []): array
    {
        try {
            Log::info('Executing SP Budget Management Agent', ['character_id' => $character->id]);

            return $this->spBudgetAgent->analyzeSPBudget($character, $targetSkills, $context);
        } catch (\Exception $e) {
            Log::error('SP Budget Management Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'SP Budget Management Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Hint Farming Strategy Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeHintFarmingAgent(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        array $context = []
    ): array {
        try {
            Log::info('Executing Hint Farming Strategy Agent', ['character_id' => $character->id]);

            return $this->hintFarmingAgent->analyzeHintFarmingStrategy(
                $character,
                $targetSkills,
                $supportCards,
                $context
            );
        } catch (\Exception $e) {
            Log::error('Hint Farming Strategy Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Hint Farming Strategy Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Skill Build Planning Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeSkillBuildAgent(
        Character $character,
        Collection $targetSkills,
        Collection $currentSkills,
        array $context = []
    ): array {
        try {
            Log::info('Executing Skill Build Planning Agent', ['character_id' => $character->id]);

            return $this->skillBuildAgent->analyzeSkillBuild(
                $character,
                $targetSkills,
                $currentSkills,
                $context
            );
        } catch (\Exception $e) {
            Log::error('Skill Build Planning Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Skill Build Planning Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Long-term Development Agent
     *
     * @param  array<string, mixed>  $goals
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeLongTermAgent(
        Character $character,
        Collection $targetSkills,
        array $goals = [],
        array $context = []
    ): array {
        try {
            Log::info('Executing Long-term Development Agent', ['character_id' => $character->id]);

            return $this->longTermAgent->analyzeLongTermDevelopment(
                $character,
                $targetSkills,
                $goals,
                $context
            );
        } catch (\Exception $e) {
            Log::error('Long-term Development Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Long-term Development Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Integrate skill optimization strategy from all agents
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function integrateSkillOptimizationStrategy(
        Character $character,
        array $agentResults,
        array $context = []
    ): array {
        // Extract key insights from each agent
        $spBudget = $agentResults['sp_budget'];
        $hintFarming = $agentResults['hint_farming'];
        $skillBuild = $agentResults['skill_build'];
        $longTerm = $agentResults['long_term_development'];

        // Determine priority strategy
        $priorityStrategy = $this->determinePriorityStrategy($spBudget, $hintFarming, $longTerm);

        // Create integrated action plan
        $actionPlan = $this->createIntegratedActionPlan($spBudget, $hintFarming, $skillBuild, $longTerm);

        // Calculate optimization score
        $optimizationScore = $this->calculateOptimizationScore($agentResults);

        // Generate integrated recommendations
        $recommendations = $this->generateIntegratedRecommendations($agentResults, $priorityStrategy);

        // Create summary
        $summary = $this->generateIntegratedSummary($agentResults, $priorityStrategy, $optimizationScore);

        return [
            'priority_strategy' => $priorityStrategy,
            'action_plan' => $actionPlan,
            'optimization_score' => $optimizationScore,
            'recommendations' => $recommendations,
            'summary' => $summary,
            'consensus_score' => $this->calculateConsensusScore($agentResults),
        ];
    }

    /**
     * Determine priority strategy from agent results
     *
     * @param  array<string, mixed>  $spBudget
     * @param  array<string, mixed>  $hintFarming
     * @param  array<string, mixed>  $longTerm
     * @return array<string, mixed>
     */
    protected function determinePriorityStrategy(array $spBudget, array $hintFarming, array $longTerm): array
    {
        // Check SP budget status
        $budgetStatus = $spBudget['budget_status']['status'] ?? 'adequate';

        // Check hint farming intensity
        $farmingIntensity = $hintFarming['farming_strategy']['farming_intensity'] ?? 0.5;

        // Check long-term feasibility
        $skillFeasibility = $longTerm['development_roadmap']['skill_timeline']['feasibility'] ?? 'feasible';

        // Determine strategy based on conditions
        if ($budgetStatus === 'insufficient' || $budgetStatus === 'tight') {
            return [
                'strategy' => 'aggressive_hint_farming',
                'reason' => 'SP budget is tight - maximize hint collection before skill acquisition',
                'priority' => 'critical',
                'focus_agent' => 'hint_farming',
            ];
        }

        if ($farmingIntensity > 0.7) {
            return [
                'strategy' => 'focused_hint_completion',
                'reason' => 'Many skills need hints - prioritize completing partial hints',
                'priority' => 'high',
                'focus_agent' => 'hint_farming',
            ];
        }

        if ($skillFeasibility !== 'feasible') {
            return [
                'strategy' => 'timeline_optimization',
                'reason' => 'Development timeline is tight - optimize skill acquisition timing',
                'priority' => 'high',
                'focus_agent' => 'long_term_development',
            ];
        }

        return [
            'strategy' => 'balanced_optimization',
            'reason' => 'Good conditions for balanced skill development',
            'priority' => 'normal',
            'focus_agent' => 'skill_build',
        ];
    }

    /**
     * Create integrated action plan
     *
     * @param  array<string, mixed>  $spBudget
     * @param  array<string, mixed>  $hintFarming
     * @param  array<string, mixed>  $skillBuild
     * @param  array<string, mixed>  $longTerm
     * @return array<string, mixed>
     */
    protected function createIntegratedActionPlan(
        array $spBudget,
        array $hintFarming,
        array $skillBuild,
        array $longTerm
    ): array {
        $plan = [
            'immediate_actions' => [],
            'short_term_actions' => [],
            'long_term_actions' => [],
        ];

        // Immediate actions from SP budget
        $affordableSkills = $spBudget['allocation_plan']['affordable_skills'] ?? 0;
        if ($affordableSkills > 0) {
            $plan['immediate_actions'][] = [
                'action' => 'acquire_affordable_skills',
                'count' => $affordableSkills,
                'reason' => 'Skills with maximum discount ready for acquisition',
                'source' => 'sp_budget',
            ];
        }

        // Short-term actions from hint farming
        $hintPlan = $hintFarming['hint_collection_plan']['plan'] ?? [];
        if (! empty($hintPlan)) {
            $plan['short_term_actions'][] = [
                'action' => 'execute_hint_farming',
                'turns_needed' => count($hintPlan),
                'reason' => 'Collect hints for cost reduction',
                'source' => 'hint_farming',
            ];
        }

        // Long-term actions from development roadmap
        $milestones = $longTerm['milestone_tracking']['milestones'] ?? [];
        $incompleteMilestones = array_filter($milestones, fn ($m) => ! $m['completed']);
        if (! empty($incompleteMilestones)) {
            $plan['long_term_actions'][] = [
                'action' => 'complete_milestones',
                'milestone_count' => count($incompleteMilestones),
                'reason' => 'Achieve long-term development goals',
                'source' => 'long_term_development',
            ];
        }

        return $plan;
    }

    /**
     * Calculate overall optimization score
     *
     * @param  array<string, mixed>  $agentResults
     */
    protected function calculateOptimizationScore(array $agentResults): int
    {
        $scores = [];

        // SP Budget optimization score
        if (isset($agentResults['sp_budget']['hint_optimization']['optimization_score'])) {
            $scores[] = $agentResults['sp_budget']['hint_optimization']['optimization_score'];
        }

        // Hint Farming deck optimization score
        if (isset($agentResults['hint_farming']['support_card_optimization']['optimization_score'])) {
            $scores[] = $agentResults['hint_farming']['support_card_optimization']['optimization_score'];
        }

        // Skill Build synergy score
        if (isset($agentResults['skill_build']['skill_synergies']['synergy_score'])) {
            $scores[] = $agentResults['skill_build']['skill_synergies']['synergy_score'];
        }

        // Long-term development progress
        if (isset($agentResults['long_term_development']['milestone_tracking']['overall_progress'])) {
            $scores[] = $agentResults['long_term_development']['milestone_tracking']['overall_progress'];
        }

        if (empty($scores)) {
            return 50; // Default moderate score
        }

        return (int) (array_sum($scores) / count($scores));
    }

    /**
     * Generate integrated recommendations
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $priorityStrategy
     * @return array<string, string>
     */
    protected function generateIntegratedRecommendations(array $agentResults, array $priorityStrategy): array
    {
        $recommendations = [];

        // Priority strategy recommendation
        $recommendations['strategy'] = $priorityStrategy['reason'];

        // SP Budget recommendations
        if (isset($agentResults['sp_budget']['recommendations'])) {
            $spRecs = $agentResults['sp_budget']['recommendations'];
            if (isset($spRecs['budget'])) {
                $recommendations['sp_budget'] = $spRecs['budget'];
            }
        }

        // Hint Farming recommendations
        if (isset($agentResults['hint_farming']['recommendations'])) {
            $hintRecs = $agentResults['hint_farming']['recommendations'];
            if (isset($hintRecs['strategy'])) {
                $recommendations['hint_farming'] = $hintRecs['strategy'];
            }
        }

        // Skill Build recommendations
        if (isset($agentResults['skill_build']['recommendations'])) {
            $buildRecs = $agentResults['skill_build']['recommendations'];
            if (isset($buildRecs['racing_style'])) {
                $recommendations['skill_build'] = $buildRecs['racing_style'];
            }
        }

        // Long-term Development recommendations
        if (isset($agentResults['long_term_development']['recommendations'])) {
            $longTermRecs = $agentResults['long_term_development']['recommendations'];
            if (isset($longTermRecs['progress'])) {
                $recommendations['long_term'] = $longTermRecs['progress'];
            }
        }

        return $recommendations;
    }

    /**
     * Generate integrated summary
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $priorityStrategy
     */
    protected function generateIntegratedSummary(
        array $agentResults,
        array $priorityStrategy,
        int $optimizationScore
    ): string {
        $summaryParts = [];

        // Priority strategy
        $summaryParts[] = "Strategy: {$priorityStrategy['strategy']}";

        // Optimization score
        $scoreGrade = match (true) {
            $optimizationScore >= 80 => 'Excellent',
            $optimizationScore >= 60 => 'Good',
            $optimizationScore >= 40 => 'Moderate',
            default => 'Needs Improvement',
        };
        $summaryParts[] = "Optimization: {$scoreGrade} ({$optimizationScore}/100)";

        // SP Budget status
        if (isset($agentResults['sp_budget']['budget_status']['status'])) {
            $budgetStatus = $agentResults['sp_budget']['budget_status']['status'];
            $summaryParts[] = "SP Budget: {$budgetStatus}";
        }

        // Hint Farming intensity
        if (isset($agentResults['hint_farming']['farming_strategy']['farming_intensity'])) {
            $intensity = $agentResults['hint_farming']['farming_strategy']['farming_intensity'];
            $intensityPercent = round($intensity * 100, 0);
            $summaryParts[] = "Hint Farming: {$intensityPercent}% intensity";
        }

        // Long-term progress
        if (isset($agentResults['long_term_development']['milestone_tracking']['overall_progress'])) {
            $progress = $agentResults['long_term_development']['milestone_tracking']['overall_progress'];
            $summaryParts[] = "Progress: {$progress}%";
        }

        return implode('. ', $summaryParts).'.';
    }

    /**
     * Calculate consensus score across agents
     *
     * @param  array<string, mixed>  $agentResults
     */
    protected function calculateConsensusScore(array $agentResults): float
    {
        $confidenceScores = [];

        // Collect confidence scores from each agent
        if (isset($agentResults['sp_budget']['confidence'])) {
            $confidenceScores[] = $agentResults['sp_budget']['confidence'];
        }

        if (isset($agentResults['hint_farming']['confidence'])) {
            $confidenceScores[] = $agentResults['hint_farming']['confidence'];
        }

        if (isset($agentResults['skill_build']['confidence'])) {
            $confidenceScores[] = $agentResults['skill_build']['confidence'];
        }

        if (isset($agentResults['long_term_development']['confidence'])) {
            $confidenceScores[] = $agentResults['long_term_development']['confidence'];
        }

        if (empty($confidenceScores)) {
            return 0.5;
        }

        // Calculate average confidence
        $avgConfidence = array_sum($confidenceScores) / count($confidenceScores);

        // Calculate variance (low variance = high consensus)
        $mean = $avgConfidence;
        $variance = array_sum(array_map(fn ($s) => ($s - $mean) ** 2, $confidenceScores)) / count($confidenceScores);

        // Consensus score: high average + low variance = high consensus
        $consensusScore = $avgConfidence * (1 - min(1.0, $variance * 2));

        return round($consensusScore, 2);
    }

    /**
     * Generate orchestration metadata
     *
     * @param  array<string, mixed>  $agentResults
     */
    protected function generateOrchestrationMetadata(array $agentResults, float $executionTime): array
    {
        $agentStatuses = [];

        foreach ($agentResults as $agentName => $result) {
            $agentStatuses[$agentName] = [
                'status' => isset($result['error']) ? 'failed' : 'success',
                'has_data' => ! isset($result['error']),
            ];
        }

        return [
            'execution_time_seconds' => round($executionTime, 3),
            'agents_executed' => count($agentResults),
            'agent_statuses' => $agentStatuses,
            'timestamp' => now()->toIso8601String(),
            'orchestration_type' => 'skill_optimization',
        ];
    }

    /**
     * Get quick skill optimization recommendation (cached, lightweight)
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getQuickOptimizationRecommendation(
        Character $character,
        Collection $targetSkills,
        array $context = []
    ): array {
        $cacheKey = "quick_skill_optimization_{$character->id}_".md5(json_encode($context) ?: '');

        return Cache::remember($cacheKey, 60, function () use ($character, $targetSkills, $context) {
            // Execute only SP Budget agent for quick response
            $spBudget = $this->executeSPBudgetAgent($character, $targetSkills, $context);

            // Extract key recommendation
            $budgetStatus = $spBudget['budget_status']['status'] ?? 'adequate';
            $maxDiscountSkills = $spBudget['hint_optimization']['max_discount_skills'] ?? [];

            if (! empty($maxDiscountSkills)) {
                return [
                    'action' => 'acquire_skills',
                    'skills' => array_column($maxDiscountSkills, 'skill_name'),
                    'reason' => 'Skills with maximum discount ready for acquisition',
                    'confidence' => $spBudget['confidence'] ?? 0.8,
                ];
            }

            if ($budgetStatus === 'insufficient' || $budgetStatus === 'tight') {
                return [
                    'action' => 'collect_hints',
                    'reason' => 'SP budget is tight - focus on hint collection',
                    'confidence' => $spBudget['confidence'] ?? 0.7,
                ];
            }

            return [
                'action' => 'balanced_development',
                'reason' => 'Continue balanced skill development',
                'confidence' => $spBudget['confidence'] ?? 0.7,
            ];
        });
    }
}
