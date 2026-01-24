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
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  Collection<int, \App\Models\SupportCard>  $supportCards
     * @param  Collection<int, \App\Models\Skill>  $currentSkills
     * @param  array<string, mixed>  $goals
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
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeSPBudgetAgent(
        Character $character,
        Collection $targetSkills,
        array $context
    ): array {
        try {
            Log::info('Executing SP Budget Management Agent', ['character_id' => $character->id]);

            // @phpstan-ignore argument.type (Collection covariance: passing Collection<int, Skill> to Collection<int, object>)
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
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  Collection<int, \App\Models\SupportCard>  $supportCards
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeHintFarmingAgent(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        array $context
    ): array {
        try {
            Log::info('Executing Hint Farming Strategy Agent', ['character_id' => $character->id]);

            /** @phpstan-ignore-next-line Collection covariance: passing typed collections to object collections */
            return $this->hintFarmingAgent->analyzeHintFarmingStrategy($character, $targetSkills, $supportCards, $context);
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
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  Collection<int, \App\Models\Skill>  $currentSkills
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeSkillBuildAgent(
        Character $character,
        Collection $targetSkills,
        Collection $currentSkills,
        array $context
    ): array {
        try {
            Log::info('Executing Skill Build Planning Agent', ['character_id' => $character->id]);

            /** @phpstan-ignore-next-line Collection covariance: passing typed collections to object collections */
            return $this->skillBuildAgent->analyzeSkillBuild($character, $targetSkills, $currentSkills, $context);
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
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  array<string, mixed>  $goals
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeLongTermAgent(
        Character $character,
        Collection $targetSkills,
        array $goals,
        array $context
    ): array {
        try {
            Log::info('Executing Long-term Development Agent', ['character_id' => $character->id]);

            // @phpstan-ignore argument.type (Collection covariance: passing Collection<int, Skill> to Collection<int, object>)
            return $this->longTermAgent->analyzeLongTermDevelopment($character, $targetSkills, $goals, $context);
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
        array $context
    ): array {
        // Extract key insights from each agent
        $spBudget = is_array($agentResults['sp_budget'] ?? null) ? $agentResults['sp_budget'] : [];
        $hintFarming = is_array($agentResults['hint_farming'] ?? null) ? $agentResults['hint_farming'] : [];
        $skillBuild = is_array($agentResults['skill_build'] ?? null) ? $agentResults['skill_build'] : [];
        $longTerm = is_array($agentResults['long_term_development'] ?? null) ? $agentResults['long_term_development'] : [];

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
    protected function determinePriorityStrategy(
        array $spBudget,
        array $hintFarming,
        array $longTerm
    ): array {
        // Check SP budget status
        $budgetStatusArray = is_array($spBudget['budget_status'] ?? null) ? $spBudget['budget_status'] : [];
        $budgetStatus = isset($budgetStatusArray['status']) && is_string($budgetStatusArray['status'])
            ? $budgetStatusArray['status']
            : 'adequate';

        // Check hint farming intensity
        $farmingStrategyArray = is_array($hintFarming['farming_strategy'] ?? null) ? $hintFarming['farming_strategy'] : [];
        $farmingIntensity = isset($farmingStrategyArray['farming_intensity']) && is_numeric($farmingStrategyArray['farming_intensity'])
            ? (float) $farmingStrategyArray['farming_intensity']
            : 0.5;

        // Check long-term feasibility
        $developmentRoadmap = is_array($longTerm['development_roadmap'] ?? null) ? $longTerm['development_roadmap'] : [];
        $skillTimeline = is_array($developmentRoadmap['skill_timeline'] ?? null) ? $developmentRoadmap['skill_timeline'] : [];
        $skillFeasibility = isset($skillTimeline['feasibility']) && is_string($skillTimeline['feasibility'])
            ? $skillTimeline['feasibility']
            : 'feasible';

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
        /** @var array<string, array<int, array<string, mixed>>> $plan */
        $plan = [
            'immediate_actions' => [],
            'short_term_actions' => [],
            'long_term_actions' => [],
        ];

        // Immediate actions from SP budget
        $allocationPlan = is_array($spBudget['allocation_plan'] ?? null) ? $spBudget['allocation_plan'] : [];
        $affordableSkills = isset($allocationPlan['affordable_skills']) && is_numeric($allocationPlan['affordable_skills'])
            ? (int) $allocationPlan['affordable_skills']
            : 0;

        if ($affordableSkills > 0) {
            $plan['immediate_actions'][] = [
                'action' => 'acquire_affordable_skills',
                'count' => $affordableSkills,
                'reason' => 'Skills with maximum discount ready for acquisition',
                'source' => 'sp_budget',
            ];
        }

        // Short-term actions from hint farming
        $hintCollectionPlan = is_array($hintFarming['hint_collection_plan'] ?? null) ? $hintFarming['hint_collection_plan'] : [];
        $hintPlan = is_array($hintCollectionPlan['plan'] ?? null) ? $hintCollectionPlan['plan'] : [];

        if (! empty($hintPlan)) {
            $plan['short_term_actions'][] = [
                'action' => 'execute_hint_farming',
                'turns_needed' => count($hintPlan),
                'reason' => 'Collect hints for cost reduction',
                'source' => 'hint_farming',
            ];
        }

        // Long-term actions from development roadmap
        $milestoneTracking = is_array($longTerm['milestone_tracking'] ?? null) ? $longTerm['milestone_tracking'] : [];
        $milestones = is_array($milestoneTracking['milestones'] ?? null) ? $milestoneTracking['milestones'] : [];

        $incompleteMilestones = array_filter($milestones, function ($m): bool {
            return is_array($m) && ! ($m['completed'] ?? false);
        });

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
        /** @var array<int, float|int> $scores */
        $scores = [];

        // SP Budget optimization score
        $spBudget = is_array($agentResults['sp_budget'] ?? null) ? $agentResults['sp_budget'] : [];
        $hintOptimization = is_array($spBudget['hint_optimization'] ?? null) ? $spBudget['hint_optimization'] : [];
        if (isset($hintOptimization['optimization_score']) && is_numeric($hintOptimization['optimization_score'])) {
            $scores[] = (float) $hintOptimization['optimization_score'];
        }

        // Hint Farming deck optimization score
        $hintFarming = is_array($agentResults['hint_farming'] ?? null) ? $agentResults['hint_farming'] : [];
        $supportCardOptimization = is_array($hintFarming['support_card_optimization'] ?? null) ? $hintFarming['support_card_optimization'] : [];
        if (isset($supportCardOptimization['optimization_score']) && is_numeric($supportCardOptimization['optimization_score'])) {
            $scores[] = (float) $supportCardOptimization['optimization_score'];
        }

        // Skill Build synergy score
        $skillBuild = is_array($agentResults['skill_build'] ?? null) ? $agentResults['skill_build'] : [];
        $skillSynergies = is_array($skillBuild['skill_synergies'] ?? null) ? $skillBuild['skill_synergies'] : [];
        if (isset($skillSynergies['synergy_score']) && is_numeric($skillSynergies['synergy_score'])) {
            $scores[] = (float) $skillSynergies['synergy_score'];
        }

        // Long-term development progress
        $longTerm = is_array($agentResults['long_term_development'] ?? null) ? $agentResults['long_term_development'] : [];
        $milestoneTracking = is_array($longTerm['milestone_tracking'] ?? null) ? $longTerm['milestone_tracking'] : [];
        if (isset($milestoneTracking['overall_progress']) && is_numeric($milestoneTracking['overall_progress'])) {
            $scores[] = (float) $milestoneTracking['overall_progress'];
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
    protected function generateIntegratedRecommendations(
        array $agentResults,
        array $priorityStrategy
    ): array {
        /** @var array<string, string> $recommendations */
        $recommendations = [];

        // Priority strategy recommendation
        $strategyReason = isset($priorityStrategy['reason']) && is_string($priorityStrategy['reason'])
            ? $priorityStrategy['reason']
            : 'Continue balanced development';
        $recommendations['strategy'] = $strategyReason;

        // SP Budget recommendations
        $spBudget = is_array($agentResults['sp_budget'] ?? null) ? $agentResults['sp_budget'] : [];
        $spRecs = is_array($spBudget['recommendations'] ?? null) ? $spBudget['recommendations'] : [];
        if (isset($spRecs['budget']) && is_string($spRecs['budget'])) {
            $recommendations['sp_budget'] = $spRecs['budget'];
        }

        // Hint Farming recommendations
        $hintFarming = is_array($agentResults['hint_farming'] ?? null) ? $agentResults['hint_farming'] : [];
        $hintRecs = is_array($hintFarming['recommendations'] ?? null) ? $hintFarming['recommendations'] : [];
        if (isset($hintRecs['strategy']) && is_string($hintRecs['strategy'])) {
            $recommendations['hint_farming'] = $hintRecs['strategy'];
        }

        // Skill Build recommendations
        $skillBuild = is_array($agentResults['skill_build'] ?? null) ? $agentResults['skill_build'] : [];
        $buildRecs = is_array($skillBuild['recommendations'] ?? null) ? $skillBuild['recommendations'] : [];
        if (isset($buildRecs['racing_style']) && is_string($buildRecs['racing_style'])) {
            $recommendations['skill_build'] = $buildRecs['racing_style'];
        }

        // Long-term Development recommendations
        $longTerm = is_array($agentResults['long_term_development'] ?? null) ? $agentResults['long_term_development'] : [];
        $longTermRecs = is_array($longTerm['recommendations'] ?? null) ? $longTerm['recommendations'] : [];
        if (isset($longTermRecs['progress']) && is_string($longTermRecs['progress'])) {
            $recommendations['long_term'] = $longTermRecs['progress'];
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
        /** @var array<int, string> $summaryParts */
        $summaryParts = [];

        // Priority strategy
        $strategyName = isset($priorityStrategy['strategy']) && is_string($priorityStrategy['strategy'])
            ? $priorityStrategy['strategy']
            : 'unknown';
        $summaryParts[] = "Strategy: {$strategyName}";

        // Optimization score
        $scoreGrade = match (true) {
            $optimizationScore >= 80 => 'Excellent',
            $optimizationScore >= 60 => 'Good',
            $optimizationScore >= 40 => 'Moderate',
            default => 'Needs Improvement',
        };
        $summaryParts[] = "Optimization: {$scoreGrade} ({$optimizationScore}/100)";

        // SP Budget status
        $spBudget = is_array($agentResults['sp_budget'] ?? null) ? $agentResults['sp_budget'] : [];
        $budgetStatusArray = is_array($spBudget['budget_status'] ?? null) ? $spBudget['budget_status'] : [];
        if (isset($budgetStatusArray['status']) && is_string($budgetStatusArray['status'])) {
            $budgetStatus = $budgetStatusArray['status'];
            $summaryParts[] = "SP Budget: {$budgetStatus}";
        }

        // Hint Farming intensity
        $hintFarming = is_array($agentResults['hint_farming'] ?? null) ? $agentResults['hint_farming'] : [];
        $farmingStrategy = is_array($hintFarming['farming_strategy'] ?? null) ? $hintFarming['farming_strategy'] : [];
        if (isset($farmingStrategy['farming_intensity']) && is_numeric($farmingStrategy['farming_intensity'])) {
            $intensity = (float) $farmingStrategy['farming_intensity'];
            $intensityPercent = (int) round($intensity * 100, 0);
            $summaryParts[] = "Hint Farming: {$intensityPercent}% intensity";
        }

        // Long-term progress
        $longTerm = is_array($agentResults['long_term_development'] ?? null) ? $agentResults['long_term_development'] : [];
        $milestoneTracking = is_array($longTerm['milestone_tracking'] ?? null) ? $longTerm['milestone_tracking'] : [];
        if (isset($milestoneTracking['overall_progress']) && is_numeric($milestoneTracking['overall_progress'])) {
            $progress = (int) $milestoneTracking['overall_progress'];
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
        /** @var array<int, float> $confidenceScores */
        $confidenceScores = [];

        // Collect confidence scores from each agent
        $spBudget = is_array($agentResults['sp_budget'] ?? null) ? $agentResults['sp_budget'] : [];
        if (isset($spBudget['confidence']) && is_numeric($spBudget['confidence'])) {
            $confidenceScores[] = (float) $spBudget['confidence'];
        }

        $hintFarming = is_array($agentResults['hint_farming'] ?? null) ? $agentResults['hint_farming'] : [];
        if (isset($hintFarming['confidence']) && is_numeric($hintFarming['confidence'])) {
            $confidenceScores[] = (float) $hintFarming['confidence'];
        }

        $skillBuild = is_array($agentResults['skill_build'] ?? null) ? $agentResults['skill_build'] : [];
        if (isset($skillBuild['confidence']) && is_numeric($skillBuild['confidence'])) {
            $confidenceScores[] = (float) $skillBuild['confidence'];
        }

        $longTerm = is_array($agentResults['long_term_development'] ?? null) ? $agentResults['long_term_development'] : [];
        if (isset($longTerm['confidence']) && is_numeric($longTerm['confidence'])) {
            $confidenceScores[] = (float) $longTerm['confidence'];
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
     * @return array{
     *     execution_time_seconds: float,
     *     agents_executed: int,
     *     agent_statuses: array<string, array{status: string, has_data: bool}>,
     *     timestamp: string,
     *     orchestration_type: string
     * }
     */
    protected function generateOrchestrationMetadata(
        array $agentResults,
        float $executionTime
    ): array {
        /** @var array<string, array{status: string, has_data: bool}> $agentStatuses */
        $agentStatuses = [];

        foreach ($agentResults as $agentName => $result) {
            $resultArray = is_array($result) ? $result : [];
            $agentStatuses[$agentName] = [
                'status' => isset($resultArray['error']) ? 'failed' : 'success',
                'has_data' => ! isset($resultArray['error']),
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
     * Optimize skill acquisition for a character (alias for comprehensive optimization)
     *
     * This method provides a simplified interface for skill acquisition optimization,
     * calling the comprehensive optimization workflow internally.
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function optimizeSkillAcquisition(
        Character $character,
        Collection $targetSkills,
        array $context = []
    ): array {
        // Get support cards from character
        $supportCards = $character->supportCards ?? collect();

        // Get current skills
        $currentSkills = $character->skills ?? collect();

        // Extract goals from context if provided
        $goals = is_array($context['goals'] ?? null) ? $context['goals'] : [];

        // Execute comprehensive optimization
        return $this->executeComprehensiveOptimization(
            character: $character,
            targetSkills: $targetSkills,
            supportCards: $supportCards,
            currentSkills: $currentSkills,
            goals: $goals,
            context: $context
        );
    }

    /**
     * Get quick skill optimization recommendation (cached, lightweight)
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getQuickOptimizationRecommendation(
        Character $character,
        Collection $targetSkills,
        array $context = []
    ): array {
        $cacheKey = "quick_skill_optimization_{$character->id}_".md5(json_encode($context) ?: '');

        $result = Cache::remember($cacheKey, 60, function () use ($character, $targetSkills, $context): array {
            // Execute only SP Budget agent for quick response
            $spBudget = $this->executeSPBudgetAgent($character, $targetSkills, $context);

            // Extract key recommendation
            $budgetStatusArray = is_array($spBudget['budget_status'] ?? null) ? $spBudget['budget_status'] : [];
            $budgetStatus = isset($budgetStatusArray['status']) && is_string($budgetStatusArray['status'])
                ? $budgetStatusArray['status']
                : 'adequate';

            $hintOptimization = is_array($spBudget['hint_optimization'] ?? null) ? $spBudget['hint_optimization'] : [];
            $maxDiscountSkills = is_array($hintOptimization['max_discount_skills'] ?? null)
                ? $hintOptimization['max_discount_skills']
                : [];

            $confidence = isset($spBudget['confidence']) && is_numeric($spBudget['confidence'])
                ? (float) $spBudget['confidence']
                : 0.7;

            if (! empty($maxDiscountSkills)) {
                return [
                    'action' => 'acquire_skills',
                    'skills' => array_column($maxDiscountSkills, 'skill_name'),
                    'reason' => 'Skills with maximum discount ready for acquisition',
                    'confidence' => max(0.8, $confidence),
                ];
            }

            if ($budgetStatus === 'insufficient' || $budgetStatus === 'tight') {
                return [
                    'action' => 'collect_hints',
                    'reason' => 'SP budget is tight - focus on hint collection',
                    'confidence' => $confidence,
                ];
            }

            return [
                'action' => 'balanced_development',
                'reason' => 'Continue balanced skill development',
                'confidence' => $confidence,
            ];
        });

        /** @var array<string, mixed> $result */
        return is_array($result) ? $result : [];
    }
}
