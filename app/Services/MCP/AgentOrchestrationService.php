<?php

declare(strict_types=1);

namespace App\Services\MCP;

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
        private readonly MCPClientService $mcpClient
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
        if ($workflow) {
            $workflow['state'] = $state;
            $workflow['updated_at'] = now()->toIso8601String();
            Cache::put("workflow:{$workflowId}", $workflow, 3600);
        }
    }

    protected function updateAgentState(string $agentId, string $state): void
    {
        $agent = $this->getAgent($agentId);
        if ($agent) {
            $agent['state'] = $state;
            $agent['updated_at'] = now()->toIso8601String();
            Cache::put("agent:{$agentId}", $agent, 3600);
        }
    }

    protected function recordAgentMetrics(string $agentId, array $metrics): void
    {
        $allMetrics = Cache::get("agent_metrics:{$agentId}", []);
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
        return Cache::get("agent_metrics:{$agentId}", []);
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
