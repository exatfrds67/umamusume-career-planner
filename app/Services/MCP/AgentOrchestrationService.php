<?php

namespace App\Services\MCP;

use App\Models\Character;
use App\Services\MCP\Agents\CareerStrategyAgent;
use App\Services\MCP\Agents\PerformanceAnalyticsAgent;
use App\Services\MCP\Agents\ResourceManagementAgent;
use App\Services\MCP\Agents\SummerCampOptimizationAgent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Orchestration Service
 *
 * Coordinates multiple MCP agents for collaborative recommendations.
 * Implements agent workflows for comprehensive training optimization
 * by combining insights from specialized agents.
 */
class AgentOrchestrationService
{
    protected MCPClientService $mcpClient;

    protected CareerStrategyAgent $careerStrategyAgent;

    protected ResourceManagementAgent $resourceManagementAgent;

    protected PerformanceAnalyticsAgent $performanceAnalyticsAgent;

    protected SummerCampOptimizationAgent $summerCampAgent;

    public function __construct(
        MCPClientService $mcpClient,
        CareerStrategyAgent $careerStrategyAgent,
        ResourceManagementAgent $resourceManagementAgent,
        PerformanceAnalyticsAgent $performanceAnalyticsAgent,
        SummerCampOptimizationAgent $summerCampAgent
    ) {
        $this->mcpClient = $mcpClient;
        $this->careerStrategyAgent = $careerStrategyAgent;
        $this->resourceManagementAgent = $resourceManagementAgent;
        $this->performanceAnalyticsAgent = $performanceAnalyticsAgent;
        $this->summerCampAgent = $summerCampAgent;
    }

    /**
     * Execute comprehensive multi-agent analysis
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     career_strategy: array<string, mixed>,
     *     resource_management: array<string, mixed>,
     *     performance_analytics: array<string, mixed>,
     *     summer_camp: array<string, mixed>,
     *     integrated_recommendations: array<string, mixed>,
     *     orchestration_metadata: array<string, mixed>
     * }
     */
    public function executeComprehensiveAnalysis(Character $character, array $context = []): array
    {
        $startTime = microtime(true);

        // Execute all agents in parallel (conceptually - actual parallel execution would require async)
        $results = [
            'career_strategy' => $this->executeCareerStrategyAgent($character, $context),
            'resource_management' => $this->executeResourceManagementAgent($character, $context),
            'performance_analytics' => $this->executePerformanceAnalyticsAgent($character, $context),
            'summer_camp' => $this->executeSummerCampAgent($character, $context),
        ];

        // Integrate recommendations from all agents
        $integratedRecommendations = $this->integrateRecommendations($character, $results, $context);

        // Calculate orchestration metadata
        $executionTime = microtime(true) - $startTime;
        $orchestrationMetadata = $this->generateOrchestrationMetadata($results, $executionTime);

        return [
            ...$results,
            'integrated_recommendations' => $integratedRecommendations,
            'orchestration_metadata' => $orchestrationMetadata,
        ];
    }

    /**
     * Execute Career Strategy Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeCareerStrategyAgent(Character $character, array $context = []): array
    {
        try {
            Log::info('Executing Career Strategy Agent', ['character_id' => $character->id]);

            return $this->careerStrategyAgent->analyzeCareerStrategy($character, $context);
        } catch (\Exception $e) {
            Log::error('Career Strategy Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Career Strategy Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Resource Management Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeResourceManagementAgent(Character $character, array $context = []): array
    {
        try {
            Log::info('Executing Resource Management Agent', ['character_id' => $character->id]);

            return $this->resourceManagementAgent->analyzeResourceManagement($character, $context);
        } catch (\Exception $e) {
            Log::error('Resource Management Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Resource Management Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Performance Analytics Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executePerformanceAnalyticsAgent(Character $character, array $context = []): array
    {
        try {
            Log::info('Executing Performance Analytics Agent', ['character_id' => $character->id]);

            return $this->performanceAnalyticsAgent->analyzePerformance($character, $context);
        } catch (\Exception $e) {
            Log::error('Performance Analytics Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Performance Analytics Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Summer Camp Optimization Agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function executeSummerCampAgent(Character $character, array $context = []): array
    {
        try {
            Log::info('Executing Summer Camp Optimization Agent', ['character_id' => $character->id]);

            return $this->summerCampAgent->analyzeSummerCampOptimization($character, $context);
        } catch (\Exception $e) {
            Log::error('Summer Camp Optimization Agent failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'error' => 'Summer Camp Optimization Agent execution failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Integrate recommendations from all agents
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function integrateRecommendations(
        Character $character,
        array $agentResults,
        array $context = []
    ): array {
        // Extract recommendations from each agent
        $careerRecs = $agentResults['career_strategy']['recommendations'] ?? [];
        $resourceRecs = $agentResults['resource_management']['optimization_recommendations'] ?? [];
        $performanceRecs = $agentResults['performance_analytics']['recommendations'] ?? [];
        $summerCampRecs = $agentResults['summer_camp']['recommendations'] ?? [];

        // Determine priority recommendation
        $priorityRecommendation = $this->determinePriorityRecommendation(
            $character,
            $agentResults,
            $context
        );

        // Synthesize action plan
        $actionPlan = $this->synthesizeActionPlan(
            $character,
            $agentResults,
            $priorityRecommendation
        );

        // Calculate consensus score
        $consensusScore = $this->calculateConsensusScore($agentResults);

        // Generate integrated summary
        $summary = $this->generateIntegratedSummary(
            $character,
            $agentResults,
            $priorityRecommendation
        );

        return [
            'priority_recommendation' => $priorityRecommendation,
            'action_plan' => $actionPlan,
            'consensus_score' => $consensusScore,
            'summary' => $summary,
            'all_recommendations' => [
                'career_strategy' => $careerRecs,
                'resource_management' => $resourceRecs,
                'performance_analytics' => $performanceRecs,
                'summer_camp' => $summerCampRecs,
            ],
        ];
    }

    /**
     * Determine priority recommendation from all agents
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function determinePriorityRecommendation(
        Character $character,
        array $agentResults,
        array $context = []
    ): array {
        // Check for critical situations first
        $performanceAnalysis = $agentResults['performance_analytics'];
        $energyAnalysis = $performanceAnalysis['energy_analysis'] ?? [];
        $summerCampStatus = $agentResults['summer_camp']['summer_camp_status'] ?? [];

        // Critical energy situation
        if (isset($energyAnalysis['status']) && $energyAnalysis['status'] === 'critical') {
            return [
                'priority' => 'critical',
                'action' => 'rest',
                'reason' => 'Energy critically low - immediate rest required',
                'source' => 'performance_analytics',
            ];
        }

        // Summer Camp active - highest priority
        if (isset($summerCampStatus['is_in_camp']) && $summerCampStatus['is_in_camp']) {
            $careerStrategy = $agentResults['career_strategy'];
            $priorityStats = $careerStrategy['priority_stats'] ?? [];
            $topStat = array_key_first($priorityStats);

            return [
                'priority' => 'critical',
                'action' => 'training',
                'focus' => $topStat ?? 'speed',
                'reason' => 'Summer Camp active - maximize high-priority training',
                'source' => 'summer_camp',
            ];
        }

        // Summer Camp preparation
        if (isset($summerCampStatus['camp_phase']) && $summerCampStatus['camp_phase'] === 'preparation') {
            return [
                'priority' => 'high',
                'action' => 'prepare_for_summer_camp',
                'reason' => 'Summer Camp starting soon - optimize energy and mood',
                'source' => 'summer_camp',
            ];
        }

        // Normal priority - follow career strategy
        $careerStrategy = $agentResults['career_strategy'];
        $priorityStats = $careerStrategy['priority_stats'] ?? [];
        $topStat = array_key_first($priorityStats);

        return [
            'priority' => 'normal',
            'action' => 'training',
            'focus' => $topStat ?? 'speed',
            'reason' => 'Continue goal-based training optimization',
            'source' => 'career_strategy',
        ];
    }

    /**
     * Synthesize action plan from agent recommendations
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $priorityRecommendation
     * @return array<string, mixed>
     */
    protected function synthesizeActionPlan(
        Character $character,
        array $agentResults,
        array $priorityRecommendation
    ): array {
        $plan = [
            'immediate_action' => $priorityRecommendation,
            'short_term' => [],
            'medium_term' => [],
            'long_term' => [],
        ];

        // Add short-term actions (next 1-3 turns)
        $resourceManagement = $agentResults['resource_management'];
        $turnEconomy = $resourceManagement['turn_economy'] ?? [];

        if (isset($turnEconomy['turns_remaining']) && $turnEconomy['turns_remaining'] < 10) {
            $plan['short_term'][] = [
                'action' => 'focus_critical_gaps',
                'reason' => 'Final turns approaching - prioritize critical stat gaps',
            ];
        }

        // Add medium-term actions (next 4-10 turns)
        $careerStrategy = $agentResults['career_strategy'];
        $trainingFocus = $careerStrategy['training_focus'] ?? [];

        if (isset($trainingFocus['approach'])) {
            $plan['medium_term'][] = [
                'action' => 'follow_training_distribution',
                'distribution' => $trainingFocus['training_distribution'] ?? [],
                'reason' => 'Maintain balanced progress toward goals',
            ];
        }

        // Add long-term actions (overall career strategy)
        if (isset($careerStrategy['strategy'])) {
            $plan['long_term'][] = [
                'action' => 'execute_career_strategy',
                'strategy' => $careerStrategy['strategy'],
                'reason' => 'Long-term career optimization',
            ];
        }

        return $plan;
    }

    /**
     * Calculate consensus score across agents
     *
     * @param  array<string, mixed>  $agentResults
     */
    protected function calculateConsensusScore(array $agentResults): float
    {
        $scores = [];

        // Collect confidence/efficiency scores from each agent
        if (isset($agentResults['career_strategy']['confidence'])) {
            $scores[] = $agentResults['career_strategy']['confidence'];
        }

        if (isset($agentResults['resource_management']['efficiency_score'])) {
            $scores[] = $agentResults['resource_management']['efficiency_score'];
        }

        if (isset($agentResults['performance_analytics']['optimization_score'])) {
            $scores[] = $agentResults['performance_analytics']['optimization_score'];
        }

        if (isset($agentResults['summer_camp']['efficiency_score'])) {
            $scores[] = $agentResults['summer_camp']['efficiency_score'];
        }

        if (empty($scores)) {
            return 0.5;
        }

        // Calculate average score
        $averageScore = array_sum($scores) / count($scores);

        // Calculate variance (low variance = high consensus)
        $mean = $averageScore;
        $variance = array_sum(array_map(fn ($s) => ($s - $mean) ** 2, $scores)) / count($scores);

        // Consensus score: high average + low variance = high consensus
        $consensusScore = $averageScore * (1 - min(1.0, $variance * 2));

        return round($consensusScore, 2);
    }

    /**
     * Generate integrated summary
     *
     * @param  array<string, mixed>  $agentResults
     * @param  array<string, mixed>  $priorityRecommendation
     */
    protected function generateIntegratedSummary(
        Character $character,
        array $agentResults,
        array $priorityRecommendation
    ): string {
        $summaryParts = [];

        // Priority action
        $action = $priorityRecommendation['action'] ?? 'training';
        $reason = $priorityRecommendation['reason'] ?? 'Continue development';
        $summaryParts[] = "Priority: {$action} - {$reason}";

        // Career strategy insight
        $careerStrategy = $agentResults['career_strategy'];
        if (isset($careerStrategy['strategy'])) {
            $summaryParts[] = "Strategy: {$careerStrategy['strategy']}";
        }

        // Resource management insight
        $resourceManagement = $agentResults['resource_management'];
        if (isset($resourceManagement['turn_economy']['turn_efficiency'])) {
            $efficiency = $resourceManagement['turn_economy']['turn_efficiency'];
            $efficiencyPercent = round($efficiency * 100, 1);
            $summaryParts[] = "Turn efficiency: {$efficiencyPercent}%";
        }

        // Performance insight
        $performanceAnalytics = $agentResults['performance_analytics'];
        if (isset($performanceAnalytics['performance_metrics']['performance_rating'])) {
            $rating = $performanceAnalytics['performance_metrics']['performance_rating'];
            $summaryParts[] = "Performance: {$rating}";
        }

        // Summer Camp insight
        $summerCamp = $agentResults['summer_camp'];
        if (isset($summerCamp['summer_camp_status']['camp_phase'])) {
            $phase = $summerCamp['summer_camp_status']['camp_phase'];
            if ($phase === 'active') {
                $summaryParts[] = 'Summer Camp ACTIVE - maximize gains!';
            } elseif ($phase === 'preparation') {
                $turnsUntil = $summerCamp['summer_camp_status']['turns_until_camp'] ?? 0;
                $summaryParts[] = "Summer Camp in {$turnsUntil} turns - prepare now";
            }
        }

        return implode('. ', $summaryParts).'.';
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
        ];
    }

    /**
     * Get quick recommendation (cached, lightweight)
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getQuickRecommendation(Character $character, array $context = []): array
    {
        $cacheKey = "quick_recommendation_{$character->id}_".md5(json_encode($context) ?: '');

        return Cache::remember($cacheKey, 60, function () use ($character, $context) {
            // Execute only priority agents for quick response
            $careerStrategy = $this->executeCareerStrategyAgent($character, $context);
            $performanceAnalytics = $this->executePerformanceAnalyticsAgent($character, $context);

            // Determine quick recommendation
            $priorityStats = $careerStrategy['priority_stats'] ?? [];
            $topStat = array_key_first($priorityStats);

            $energyAnalysis = $performanceAnalytics['energy_analysis'] ?? [];
            $energyStatus = $energyAnalysis['status'] ?? 'good';

            if ($energyStatus === 'critical' || $energyStatus === 'low') {
                return [
                    'action' => 'rest',
                    'reason' => 'Energy too low for effective training',
                    'confidence' => 0.9,
                ];
            }

            return [
                'action' => 'training',
                'focus' => $topStat ?? 'speed',
                'reason' => 'Continue goal-based optimization',
                'confidence' => $careerStrategy['confidence'] ?? 0.7,
            ];
        });
    }
}
