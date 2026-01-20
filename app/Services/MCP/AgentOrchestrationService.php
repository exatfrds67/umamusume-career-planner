<?php

declare(strict_types=1);

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
 * Manages multi-agent workflows, agent lifecycle, and inter-agent communication
 * using AgentCore MCP server and Strands Agent SDK integration.
 */
class AgentOrchestrationService
{
    /**
     * Agent orchestration patterns
     */
    public const PATTERN_SEQUENTIAL = 'sequential';

    public const PATTERN_PARALLEL = 'parallel';

    public const PATTERN_HIERARCHICAL = 'hierarchical';

    public const PATTERN_COLLABORATIVE = 'collaborative';

    /**
     * Agent states
     */
    public const STATE_IDLE = 'idle';

    public const STATE_INITIALIZING = 'initializing';

    public const STATE_RUNNING = 'running';

    public const STATE_WAITING = 'waiting';

    public const STATE_COMPLETED = 'completed';

    public const STATE_FAILED = 'failed';

    public const STATE_TERMINATED = 'terminated';

    public function __construct(
        private readonly MCPClientService $mcpClient,
        private readonly AgentContextService $contextService,
        private readonly CareerStateSyncService $syncService,
        private readonly AgentMemoryService $memoryService
    ) {}

    /**
     * Create a new agent workflow
     */
    public function createWorkflow(
        string $name,
        string $pattern,
        array $agents,
        array $config = []
    ): array {
        try {
            $workflowId = $this->generateWorkflowId($name);

            $workflow = [
                'id' => $workflowId,
                'name' => $name,
                'pattern' => $pattern,
                'agents' => $agents,
                'config' => $config,
                'state' => self::STATE_IDLE,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ];

            // Store workflow in cache
            Cache::put("workflow:{$workflowId}", $workflow, 3600);

            Log::info('[AgentOrchestration] Workflow created', [
                'workflow_id' => $workflowId,
                'name' => $name,
                'pattern' => $pattern,
                'agent_count' => count($agents),
            ]);

            return $workflow;
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Failed to create workflow', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute a workflow
     */
    public function executeWorkflow(string $workflowId, array $input = []): array
    {
        $workflow = $this->getWorkflow($workflowId);

        if (! $workflow) {
            throw new \RuntimeException("Workflow not found: {$workflowId}");
        }

        try {
            $this->updateWorkflowState($workflowId, self::STATE_RUNNING);

            $result = match ($workflow['pattern']) {
                self::PATTERN_SEQUENTIAL => $this->executeSequential($workflow, $input),
                self::PATTERN_PARALLEL => $this->executeParallel($workflow, $input),
                self::PATTERN_HIERARCHICAL => $this->executeHierarchical($workflow, $input),
                self::PATTERN_COLLABORATIVE => $this->executeCollaborative($workflow, $input),
                default => throw new \InvalidArgumentException("Unknown pattern: {$workflow['pattern']}"),
            };

            $this->updateWorkflowState($workflowId, self::STATE_COMPLETED);

            return $result;
        } catch (\Exception $e) {
            $this->updateWorkflowState($workflowId, self::STATE_FAILED);

            Log::error('[AgentOrchestration] Workflow execution failed', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get current workflow for user context
     */
    public function getCurrentWorkflow(?int $userId = null): ?array
    {
        return null;
    }

    /**
     * Create context-aware workflow with character and career state
     */
    public function createContextAwareWorkflow(
        string $name,
        string $pattern,
        array $agents,
        \App\Models\Character $character,
        ?\App\Models\Career $career = null,
        array $config = []
    ): array {
        try {
            // Build unified context
            $context = $this->contextService->buildUnifiedContext($character, $career);

            // Create workflow with context
            $workflow = $this->createWorkflow($name, $pattern, $agents, array_merge($config, [
                'context_aware' => true,
                'character_id' => $character->id,
                'career_id' => $career?->id,
            ]));

            // Store context reference
            Cache::put("workflow_context:{$workflow['id']}", $context, 3600);

            // Synchronize state with agents
            if ($career) {
                $this->syncService->synchronizeCareerState($career);
            }

            Log::info('[AgentOrchestration] Context-aware workflow created', [
                'workflow_id' => $workflow['id'],
                'character_id' => $character->id,
                'career_id' => $career?->id,
            ]);

            return $workflow;
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Failed to create context-aware workflow', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute workflow with memory persistence
     */
    public function executeWorkflowWithMemory(
        string $workflowId,
        string $agentId,
        array $input = []
    ): array {
        try {
            // Retrieve agent memories
            $memories = $this->memoryService->getAgentMemories($agentId);

            // Add memories to input context
            $input['agent_memories'] = $memories;

            // Execute workflow
            $result = $this->executeWorkflow($workflowId, $input);

            // Store episodic memory of this execution
            $this->memoryService->storeEpisode($agentId, uniqid('episode_'), [
                'workflow_id' => $workflowId,
                'input' => $input,
                'result' => $result,
                'timestamp' => now()->toIso8601String(),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Failed to execute workflow with memory', [
                'workflow_id' => $workflowId,
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute a comprehensive analysis across core MCP agents.
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

        $careerStrategy = app(CareerStrategyAgent::class)->analyzeCareerStrategy($character, $context);
        $resourceManagement = app(ResourceManagementAgent::class)->analyzeResourceManagement($character, $context);
        $performanceAnalytics = app(PerformanceAnalyticsAgent::class)->analyzePerformance($character, $context);
        $summerCamp = app(SummerCampOptimizationAgent::class)->analyzeSummerCampOptimization($character, $context);

        $integratedRecommendations = $this->buildIntegratedRecommendations(
            $character,
            $careerStrategy,
            $resourceManagement,
            $performanceAnalytics,
            $summerCamp
        );

        return [
            'career_strategy' => $careerStrategy,
            'resource_management' => $resourceManagement,
            'performance_analytics' => $performanceAnalytics,
            'summer_camp' => $summerCamp,
            'integrated_recommendations' => $integratedRecommendations,
            'orchestration_metadata' => [
                'execution_time_seconds' => microtime(true) - $startTime,
                'agents_executed' => 4,
                'agent_statuses' => [
                    'career_strategy' => self::STATE_COMPLETED,
                    'resource_management' => self::STATE_COMPLETED,
                    'performance_analytics' => self::STATE_COMPLETED,
                    'summer_camp' => self::STATE_COMPLETED,
                ],
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get a fast recommendation based on current character state.
     *
     * @return array{action: string, reason: string, confidence: float}
     */
    public function getQuickRecommendation(Character $character): array
    {
        $cacheKey = "mcp_quick_recommendation:{$character->id}";
        $cached = Cache::get($cacheKey);

        if (is_array($cached)
            && isset($cached['action'], $cached['reason'], $cached['confidence'])
            && is_string($cached['action'])
            && is_string($cached['reason'])
        ) {
            return [
                'action' => $cached['action'],
                'reason' => $cached['reason'],
                'confidence' => (float) $cached['confidence'],
            ];
        }

        $performanceAnalytics = app(PerformanceAnalyticsAgent::class)->analyzePerformance($character, []);
        $energy = (int) ($performanceAnalytics['energy_analysis']['current_energy'] ?? 100);

        $action = $energy <= 25 ? 'rest' : 'training';
        $reason = $energy <= 25
            ? 'Energy critically low; prioritize recovery.'
            : 'Energy stable; proceed with training.';

        $confidence = max(0.0, min(1.0, (float) ($performanceAnalytics['optimization_score'] ?? 0.7)));

        $result = [
            'action' => $action,
            'reason' => $reason,
            'confidence' => $confidence,
        ];

        Cache::put($cacheKey, $result, 300);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $careerStrategy
     * @param  array<string, mixed>  $resourceManagement
     * @param  array<string, mixed>  $performanceAnalytics
     * @param  array<string, mixed>  $summerCamp
     * @return array<string, mixed>
     */
    protected function buildIntegratedRecommendations(
        Character $character,
        array $careerStrategy,
        array $resourceManagement,
        array $performanceAnalytics,
        array $summerCamp
    ): array {
        $energy = (int) ($performanceAnalytics['energy_analysis']['current_energy'] ?? $character->energy_level ?? 100);
        $isInCamp = (bool) ($summerCamp['summer_camp_status']['is_in_camp'] ?? false);

        $priorityRecommendation = $this->determinePriorityRecommendation($energy, $isInCamp);

        return [
            'priority_recommendation' => $priorityRecommendation,
            'action_plan' => [
                'primary_action' => $priorityRecommendation['action'],
                'secondary_actions' => array_values($careerStrategy['recommendations'] ?? []),
            ],
            'consensus_score' => $this->calculateConsensusScore([
                $careerStrategy['confidence'] ?? 0.8,
                $resourceManagement['efficiency_score'] ?? 0.8,
                $performanceAnalytics['optimization_score'] ?? 0.8,
                $summerCamp['efficiency_score'] ?? 0.8,
            ]),
            'summary' => 'Integrated recommendations generated from core MCP agents.',
            'all_recommendations' => [
                'career_strategy' => $careerStrategy['recommendations'] ?? [],
                'resource_management' => $resourceManagement['optimization_recommendations'] ?? [],
                'performance_analytics' => $performanceAnalytics['recommendations'] ?? [],
                'summer_camp' => $summerCamp['recommendations'] ?? [],
            ],
        ];
    }

    /**
     * @return array{priority: string, action: string, reason: string, source: string}
     */
    protected function determinePriorityRecommendation(int $energy, bool $isInCamp): array
    {
        if ($energy <= 25) {
            return [
                'priority' => 'critical',
                'action' => 'rest',
                'reason' => 'Energy critically low; recovery required.',
                'source' => 'performance_analytics',
            ];
        }

        if ($isInCamp) {
            return [
                'priority' => 'critical',
                'action' => 'training',
                'reason' => 'Summer Camp active; prioritize training for bonus gains.',
                'source' => 'summer_camp',
            ];
        }

        return [
            'priority' => 'high',
            'action' => 'training',
            'reason' => 'Conditions stable; maintain training progression.',
            'source' => 'career_strategy',
        ];
    }

    /**
     * @param  array<int, float|int>  $scores
     */
    protected function calculateConsensusScore(array $scores): float
    {
        $filtered = array_values(array_filter($scores, static fn ($score) => is_numeric($score)));

        if ($filtered === []) {
            return 0.0;
        }

        $total = array_sum($filtered);

        return max(0.0, min(1.0, $total / count($filtered)));
    }

    /**
     * Execute agents sequentially
     */
    protected function executeSequential(array $workflow, array $input): array
    {
        $results = [];
        $currentInput = $input;

        foreach ($workflow['agents'] as $agentConfig) {
            $agentResult = $this->executeAgent($agentConfig, $currentInput);
            $results[] = $agentResult;

            // Pass output to next agent
            $currentInput = $agentResult['output'] ?? [];
        }

        return [
            'pattern' => self::PATTERN_SEQUENTIAL,
            'results' => $results,
            'final_output' => end($results)['output'] ?? [],
        ];
    }

    /**
     * Execute agents in parallel
     */
    protected function executeParallel(array $workflow, array $input): array
    {
        $results = [];

        foreach ($workflow['agents'] as $agentConfig) {
            $results[] = $this->executeAgent($agentConfig, $input);
        }

        return [
            'pattern' => self::PATTERN_PARALLEL,
            'results' => $results,
            'combined_output' => $this->combineOutputs($results),
        ];
    }

    /**
     * Execute agents hierarchically
     */
    protected function executeHierarchical(array $workflow, array $input): array
    {
        $results = [];

        // Execute coordinator agent first
        $coordinator = $workflow['agents'][0] ?? null;
        if (! $coordinator) {
            throw new \RuntimeException('Hierarchical workflow requires coordinator agent');
        }

        $coordinatorResult = $this->executeAgent($coordinator, $input);
        $results['coordinator'] = $coordinatorResult;

        // Execute subordinate agents based on coordinator output
        $subordinates = array_slice($workflow['agents'], 1);
        $subordinateResults = [];

        foreach ($subordinates as $agentConfig) {
            $subordinateResults[] = $this->executeAgent(
                $agentConfig,
                $coordinatorResult['output'] ?? []
            );
        }

        $results['subordinates'] = $subordinateResults;

        return [
            'pattern' => self::PATTERN_HIERARCHICAL,
            'results' => $results,
            'final_output' => $this->aggregateHierarchicalResults($results),
        ];
    }

    /**
     * Execute agents collaboratively
     */
    protected function executeCollaborative(array $workflow, array $input): array
    {
        $results = [];
        $sharedContext = $input;

        foreach ($workflow['agents'] as $agentConfig) {
            $agentResult = $this->executeAgent($agentConfig, $sharedContext);
            $results[] = $agentResult;

            // Update shared context with agent output
            $sharedContext = array_merge($sharedContext, $agentResult['output'] ?? []);
        }

        return [
            'pattern' => self::PATTERN_COLLABORATIVE,
            'results' => $results,
            'shared_context' => $sharedContext,
        ];
    }

    /**
     * Execute a single agent
     */
    protected function executeAgent(array $agentConfig, array $input): array
    {
        $agentId = $agentConfig['id'] ?? uniqid('agent_');
        $agentType = $agentConfig['type'] ?? 'generic';

        try {
            $startTime = microtime(true);

            // Update agent state
            $this->updateAgentState($agentId, self::STATE_RUNNING);

            // Execute agent via MCP
            $output = $this->mcpClient->executeAgent($agentType, $input);

            $executionTime = microtime(true) - $startTime;

            // Update agent state
            $this->updateAgentState($agentId, self::STATE_COMPLETED);

            // Record performance metrics
            $this->recordAgentMetrics($agentId, [
                'execution_time' => $executionTime,
                'input_size' => strlen(json_encode($input)),
                'output_size' => strlen(json_encode($output)),
            ]);

            return [
                'agent_id' => $agentId,
                'agent_type' => $agentType,
                'state' => self::STATE_COMPLETED,
                'output' => $output,
                'execution_time' => $executionTime,
            ];
        } catch (\Exception $e) {
            $this->updateAgentState($agentId, self::STATE_FAILED);

            Log::error('[AgentOrchestration] Agent execution failed', [
                'agent_id' => $agentId,
                'agent_type' => $agentType,
                'error' => $e->getMessage(),
            ]);

            return [
                'agent_id' => $agentId,
                'agent_type' => $agentType,
                'state' => self::STATE_FAILED,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new agent
     */
    public function createAgent(string $type, array $config = []): array
    {
        try {
            $agentId = $this->generateAgentId($type);

            $agent = [
                'id' => $agentId,
                'type' => $type,
                'config' => $config,
                'state' => self::STATE_IDLE,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ];

            // Store agent in cache
            Cache::put("agent:{$agentId}", $agent, 3600);

            Log::info('[AgentOrchestration] Agent created', [
                'agent_id' => $agentId,
                'type' => $type,
            ]);

            return $agent;
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Failed to create agent', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Monitor agent performance
     */
    public function monitorAgent(string $agentId): array
    {
        $agent = $this->getAgent($agentId);

        if (! $agent) {
            throw new \RuntimeException("Agent not found: {$agentId}");
        }

        $metrics = $this->getAgentMetrics($agentId);

        return [
            'agent_id' => $agentId,
            'type' => $agent['type'],
            'state' => $agent['state'],
            'metrics' => $metrics,
            'health' => $this->calculateAgentHealth($metrics),
        ];
    }

    /**
     * Terminate an agent
     */
    public function terminateAgent(string $agentId): bool
    {
        try {
            $this->updateAgentState($agentId, self::STATE_TERMINATED);

            // Clean up agent resources
            Cache::forget("agent:{$agentId}");
            Cache::forget("agent_metrics:{$agentId}");

            Log::info('[AgentOrchestration] Agent terminated', [
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Failed to terminate agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get agent performance analytics
     */
    public function getAgentAnalytics(string $agentId): array
    {
        $metrics = $this->getAgentMetrics($agentId);

        if (empty($metrics)) {
            return [
                'agent_id' => $agentId,
                'total_executions' => 0,
                'average_execution_time' => 0,
                'success_rate' => 0,
                'recommendations' => [],
            ];
        }

        $totalExecutions = count($metrics);
        $successfulExecutions = count(array_filter($metrics, fn ($m) => $m['success'] ?? false));
        $executionTimes = array_column($metrics, 'execution_time');

        $analytics = [
            'agent_id' => $agentId,
            'total_executions' => $totalExecutions,
            'successful_executions' => $successfulExecutions,
            'failed_executions' => $totalExecutions - $successfulExecutions,
            'success_rate' => $totalExecutions > 0 ? ($successfulExecutions / $totalExecutions) * 100 : 0,
            'average_execution_time' => ! empty($executionTimes) ? array_sum($executionTimes) / count($executionTimes) : 0,
            'min_execution_time' => ! empty($executionTimes) ? min($executionTimes) : 0,
            'max_execution_time' => ! empty($executionTimes) ? max($executionTimes) : 0,
            'recommendations' => $this->generateOptimizationRecommendations($metrics),
        ];

        return $analytics;
    }

    /**
     * Generate optimization recommendations
     */
    protected function generateOptimizationRecommendations(array $metrics): array
    {
        $recommendations = [];

        if (empty($metrics)) {
            return $recommendations;
        }

        $executionTimes = array_column($metrics, 'execution_time');
        $avgTime = array_sum($executionTimes) / count($executionTimes);

        // Performance recommendations
        if ($avgTime > 5.0) {
            $recommendations[] = [
                'type' => 'performance',
                'severity' => 'high',
                'message' => 'Average execution time is high. Consider optimizing agent logic or using parallel execution.',
            ];
        }

        // Success rate recommendations
        $successRate = count(array_filter($metrics, fn ($m) => $m['success'] ?? false)) / count($metrics);
        if ($successRate < 0.8) {
            $recommendations[] = [
                'type' => 'reliability',
                'severity' => 'high',
                'message' => 'Success rate is below 80%. Review error logs and improve error handling.',
            ];
        }

        return $recommendations;
    }

    /**
     * Helper methods
     */
    protected function generateWorkflowId(string $name): string
    {
        return 'workflow_'.md5($name.microtime(true));
    }

    protected function generateAgentId(string $type): string
    {
        return 'agent_'.$type.'_'.uniqid();
    }

    protected function getWorkflow(string $workflowId): ?array
    {
        return Cache::get("workflow:{$workflowId}");
    }

    protected function getAgent(string $agentId): ?array
    {
        return Cache::get("agent:{$agentId}");
    }

    protected function updateWorkflowState(string $workflowId, string $state): void
    {
        $workflow = $this->getWorkflow($workflowId);
        if (is_array($workflow)) {
            $workflow['state'] = $state;
            $workflow['updated_at'] = now()->toIso8601String();
            Cache::put("workflow:{$workflowId}", $workflow, 3600);
        }
    }

    protected function updateAgentState(string $agentId, string $state): void
    {
        $agent = $this->getAgent($agentId);
        if (is_array($agent)) {
            $agent['state'] = $state;
            $agent['updated_at'] = now()->toIso8601String();
            Cache::put("agent:{$agentId}", $agent, 3600);
        }
    }

    protected function recordAgentMetrics(string $agentId, array $metrics): void
    {
        $allMetrics = Cache::get("agent_metrics:{$agentId}", []);
        if (! is_array($allMetrics)) {
            $allMetrics = [];
        }
        $allMetrics[] = array_merge($metrics, [
            'timestamp' => now()->toIso8601String(),
            'success' => ! isset($metrics['error']),
        ]);

        // Keep only last 100 metrics
        if (count($allMetrics) > 100) {
            $allMetrics = array_slice($allMetrics, -100);
        }

        Cache::put("agent_metrics:{$agentId}", $allMetrics, 3600);
    }

    protected function getAgentMetrics(string $agentId): array
    {
        $metrics = Cache::get("agent_metrics:{$agentId}", []);

        return is_array($metrics) ? $metrics : [];
    }

    protected function calculateAgentHealth(array $metrics): string
    {
        if (empty($metrics)) {
            return 'unknown';
        }

        $recentMetrics = array_slice($metrics, -10);
        $successRate = count(array_filter($recentMetrics, fn ($m) => $m['success'] ?? false)) / count($recentMetrics);

        if ($successRate >= 0.9) {
            return 'healthy';
        } elseif ($successRate >= 0.7) {
            return 'degraded';
        } else {
            return 'unhealthy';
        }
    }

    protected function combineOutputs(array $results): array
    {
        $combined = [];
        foreach ($results as $result) {
            if (isset($result['output']) && is_array($result['output'])) {
                $combined = array_merge($combined, $result['output']);
            }
        }

        return $combined;
    }

    protected function aggregateHierarchicalResults(array $results): array
    {
        $aggregated = $results['coordinator']['output'] ?? [];

        foreach ($results['subordinates'] as $subordinateResult) {
            if (isset($subordinateResult['output']) && is_array($subordinateResult['output'])) {
                $aggregated = array_merge($aggregated, $subordinateResult['output']);
            }
        }

        return $aggregated;
    }
}
