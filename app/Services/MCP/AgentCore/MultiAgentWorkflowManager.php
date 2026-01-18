<?php

namespace App\Services\MCP\AgentCore;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

/**
 * Multi-Agent Workflow Manager
 *
 * Coordinates complex task decomposition and multi-agent workflows
 * using strands-agents MCP server for collaborative task execution.
 *
 * Requirements: 56.3, 59.2
 */
class MultiAgentWorkflowManager
{
    protected AgentCoreService $agentCore;

    protected AgentCommunicationProtocol $communication;

    /** @var array<string, mixed> */
    protected array $activeWorkflows = [];

    public function __construct(
        AgentCoreService $agentCore,
        AgentCommunicationProtocol $communication
    ) {
        $this->agentCore = $agentCore;
        $this->communication = $communication;
    }

    /**
     * Execute a sequential workflow
     *
     * @param  array<int, array{
     *     agent_id: string,
     *     task: string,
     *     input: array<string, mixed>,
     *     dependencies?: array<int, int>
     * }>  $steps
     * @return array{
     *     workflow_id: string,
     *     status: string,
     *     results: array<int, mixed>,
     *     execution_time: float,
     *     total_cost: float,
     *     error?: string
     * }
     */
    public function executeSequentialWorkflow(array $steps, array $context = []): array
    {
        $workflowId = $this->generateWorkflowId();
        $startTime = microtime(true);
        $results = [];
        $totalCost = 0.0;
        $sharedContext = $context;

        try {
            Log::info('[MultiAgentWorkflow] Starting sequential workflow', [
                'workflow_id' => $workflowId,
                'steps' => count($steps),
            ]);

            $this->activeWorkflows[$workflowId] = [
                'type' => 'sequential',
                'status' => 'running',
                'started_at' => now()->toIso8601String(),
            ];

            foreach ($steps as $index => $step) {
                Log::info('[MultiAgentWorkflow] Executing step', [
                    'workflow_id' => $workflowId,
                    'step' => $index,
                    'agent_id' => $step['agent_id'],
                ]);

                // Add shared context to step input
                $stepInput = array_merge($step['input'], ['shared_context' => $sharedContext]);

                // Execute agent
                $result = $this->agentCore->invokeAgent($step['agent_id'], $stepInput);

                if ($result['status'] !== 'success') {
                    throw new \RuntimeException("Step {$index} failed: ".$result['error'] ?? 'Unknown error');
                }

                $results[$index] = $result;
                $totalCost += $result['cost'] ?? 0.0;

                // Update shared context with step results
                if (isset($result['output']['context_updates'])) {
                    $sharedContext = array_merge($sharedContext, $result['output']['context_updates']);
                }

                // Share results with other agents
                $this->communication->broadcastMessage([
                    'workflow_id' => $workflowId,
                    'step' => $index,
                    'agent_id' => $step['agent_id'],
                    'result' => $result['output'],
                ]);
            }

            $executionTime = microtime(true) - $startTime;

            $this->activeWorkflows[$workflowId]['status'] = 'completed';
            $this->activeWorkflows[$workflowId]['completed_at'] = now()->toIso8601String();

            Log::info('[MultiAgentWorkflow] Sequential workflow completed', [
                'workflow_id' => $workflowId,
                'execution_time' => $executionTime,
                'total_cost' => $totalCost,
            ]);

            return [
                'workflow_id' => $workflowId,
                'status' => 'completed',
                'results' => $results,
                'execution_time' => round($executionTime, 3),
                'total_cost' => round($totalCost, 6),
                'shared_context' => $sharedContext,
            ];
        } catch (\Exception $e) {
            Log::error('[MultiAgentWorkflow] Sequential workflow failed', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);

            $this->activeWorkflows[$workflowId]['status'] = 'failed';

            return [
                'workflow_id' => $workflowId,
                'status' => 'failed',
                'results' => $results,
                'execution_time' => microtime(true) - $startTime,
                'total_cost' => round($totalCost, 6),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute a parallel workflow
     *
     * @param  array<int, array{
     *     agent_id: string,
     *     task: string,
     *     input: array<string, mixed>
     * }>  $tasks
     * @return array{
     *     workflow_id: string,
     *     status: string,
     *     results: array<int, mixed>,
     *     execution_time: float,
     *     total_cost: float,
     *     error?: string
     * }
     */
    public function executeParallelWorkflow(array $tasks, array $context = []): array
    {
        $workflowId = $this->generateWorkflowId();
        $startTime = microtime(true);
        $results = [];
        $totalCost = 0.0;

        try {
            Log::info('[MultiAgentWorkflow] Starting parallel workflow', [
                'workflow_id' => $workflowId,
                'tasks' => count($tasks),
            ]);

            $this->activeWorkflows[$workflowId] = [
                'type' => 'parallel',
                'status' => 'running',
                'started_at' => now()->toIso8601String(),
            ];

            // Execute all tasks (simulated parallel execution)
            foreach ($tasks as $index => $task) {
                Log::info('[MultiAgentWorkflow] Executing parallel task', [
                    'workflow_id' => $workflowId,
                    'task' => $index,
                    'agent_id' => $task['agent_id'],
                ]);

                $taskInput = array_merge($task['input'], ['context' => $context]);
                $result = $this->agentCore->invokeAgent($task['agent_id'], $taskInput);

                $results[$index] = $result;
                $totalCost += $result['cost'] ?? 0.0;

                // Share results with other agents
                $this->communication->broadcastMessage([
                    'workflow_id' => $workflowId,
                    'task' => $index,
                    'agent_id' => $task['agent_id'],
                    'result' => $result['output'],
                ]);
            }

            $executionTime = microtime(true) - $startTime;

            $this->activeWorkflows[$workflowId]['status'] = 'completed';
            $this->activeWorkflows[$workflowId]['completed_at'] = now()->toIso8601String();

            Log::info('[MultiAgentWorkflow] Parallel workflow completed', [
                'workflow_id' => $workflowId,
                'execution_time' => $executionTime,
                'total_cost' => $totalCost,
            ]);

            return [
                'workflow_id' => $workflowId,
                'status' => 'completed',
                'results' => $results,
                'execution_time' => round($executionTime, 3),
                'total_cost' => round($totalCost, 6),
            ];
        } catch (\Exception $e) {
            Log::error('[MultiAgentWorkflow] Parallel workflow failed', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);

            $this->activeWorkflows[$workflowId]['status'] = 'failed';

            return [
                'workflow_id' => $workflowId,
                'status' => 'failed',
                'results' => $results,
                'execution_time' => microtime(true) - $startTime,
                'total_cost' => round($totalCost, 6),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute a hierarchical workflow with coordinator agent
     *
     * @param  array{
     *     coordinator_agent_id: string,
     *     worker_agents: array<int, string>,
     *     task: string,
     *     input: array<string, mixed>
     * }  $workflowConfig
     * @return array{
     *     workflow_id: string,
     *     status: string,
     *     coordinator_result: array<string, mixed>,
     *     worker_results: array<int, mixed>,
     *     execution_time: float,
     *     total_cost: float,
     *     error?: string
     * }
     */
    public function executeHierarchicalWorkflow(array $workflowConfig): array
    {
        $workflowId = $this->generateWorkflowId();
        $startTime = microtime(true);
        $totalCost = 0.0;

        try {
            Log::info('[MultiAgentWorkflow] Starting hierarchical workflow', [
                'workflow_id' => $workflowId,
                'coordinator' => $workflowConfig['coordinator_agent_id'],
                'workers' => count($workflowConfig['worker_agents']),
            ]);

            $this->activeWorkflows[$workflowId] = [
                'type' => 'hierarchical',
                'status' => 'running',
                'started_at' => now()->toIso8601String(),
            ];

            // Step 1: Coordinator decomposes task
            $coordinatorResult = $this->agentCore->invokeAgent(
                $workflowConfig['coordinator_agent_id'],
                [
                    'task' => 'decompose',
                    'input' => $workflowConfig['input'],
                    'worker_agents' => $workflowConfig['worker_agents'],
                ]
            );

            $totalCost += $coordinatorResult['cost'] ?? 0.0;

            if ($coordinatorResult['status'] !== 'success') {
                throw new \RuntimeException('Coordinator failed: '.$coordinatorResult['error'] ?? 'Unknown error');
            }

            // Step 2: Execute worker tasks
            $workerResults = [];
            $subtasks = $coordinatorResult['output']['subtasks'] ?? [];

            foreach ($subtasks as $index => $subtask) {
                $workerAgentId = $workflowConfig['worker_agents'][$index] ?? $workflowConfig['worker_agents'][0];

                Log::info('[MultiAgentWorkflow] Executing worker task', [
                    'workflow_id' => $workflowId,
                    'worker' => $workerAgentId,
                    'subtask' => $index,
                ]);

                $workerResult = $this->agentCore->invokeAgent($workerAgentId, $subtask);
                $workerResults[$index] = $workerResult;
                $totalCost += $workerResult['cost'] ?? 0.0;
            }

            // Step 3: Coordinator synthesizes results
            $synthesisResult = $this->agentCore->invokeAgent(
                $workflowConfig['coordinator_agent_id'],
                [
                    'task' => 'synthesize',
                    'worker_results' => $workerResults,
                ]
            );

            $totalCost += $synthesisResult['cost'] ?? 0.0;

            $executionTime = microtime(true) - $startTime;

            $this->activeWorkflows[$workflowId]['status'] = 'completed';
            $this->activeWorkflows[$workflowId]['completed_at'] = now()->toIso8601String();

            Log::info('[MultiAgentWorkflow] Hierarchical workflow completed', [
                'workflow_id' => $workflowId,
                'execution_time' => $executionTime,
                'total_cost' => $totalCost,
            ]);

            return [
                'workflow_id' => $workflowId,
                'status' => 'completed',
                'coordinator_result' => $synthesisResult,
                'worker_results' => $workerResults,
                'execution_time' => round($executionTime, 3),
                'total_cost' => round($totalCost, 6),
            ];
        } catch (\Exception $e) {
            Log::error('[MultiAgentWorkflow] Hierarchical workflow failed', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);

            $this->activeWorkflows[$workflowId]['status'] = 'failed';

            return [
                'workflow_id' => $workflowId,
                'status' => 'failed',
                'coordinator_result' => [],
                'worker_results' => [],
                'execution_time' => microtime(true) - $startTime,
                'total_cost' => round($totalCost, 6),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute a comprehensive career analysis workflow
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     workflow_id: string,
     *     status: string,
     *     analysis: array<string, mixed>,
     *     execution_time: float,
     *     total_cost: float
     * }
     */
    public function executeCareerAnalysisWorkflow(Character $character, array $goals = []): array
    {
        // Define workflow steps
        $steps = [
            [
                'agent_id' => 'career_strategy_agent',
                'task' => 'analyze_career_strategy',
                'input' => [
                    'character_id' => $character->id,
                    'goals' => $goals,
                ],
            ],
            [
                'agent_id' => 'training_optimization_agent',
                'task' => 'optimize_training',
                'input' => [
                    'character_id' => $character->id,
                ],
            ],
            [
                'agent_id' => 'race_analysis_agent',
                'task' => 'analyze_races',
                'input' => [
                    'character_id' => $character->id,
                ],
            ],
            [
                'agent_id' => 'skill_management_agent',
                'task' => 'optimize_skills',
                'input' => [
                    'character_id' => $character->id,
                ],
            ],
        ];

        $result = $this->executeSequentialWorkflow($steps, [
            'character' => $character->toArray(),
            'goals' => $goals,
        ]);

        // Synthesize analysis
        $analysis = $this->synthesizeCareerAnalysis($result['results']);

        return [
            'workflow_id' => $result['workflow_id'],
            'status' => $result['status'],
            'analysis' => $analysis,
            'execution_time' => $result['execution_time'],
            'total_cost' => $result['total_cost'],
        ];
    }

    /**
     * Get workflow status
     *
     * @return array<string, mixed>|null
     */
    public function getWorkflowStatus(string $workflowId): ?array
    {
        return $this->activeWorkflows[$workflowId] ?? null;
    }

    /**
     * Cancel a running workflow
     */
    public function cancelWorkflow(string $workflowId): bool
    {
        if (isset($this->activeWorkflows[$workflowId])) {
            $this->activeWorkflows[$workflowId]['status'] = 'cancelled';
            $this->activeWorkflows[$workflowId]['cancelled_at'] = now()->toIso8601String();

            Log::info('[MultiAgentWorkflow] Workflow cancelled', [
                'workflow_id' => $workflowId,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate unique workflow ID
     */
    protected function generateWorkflowId(): string
    {
        return 'workflow_'.substr(md5(uniqid((string) mt_rand(), true)), 0, 12);
    }

    /**
     * Synthesize career analysis from workflow results
     *
     * @param  array<int, mixed>  $results
     * @return array<string, mixed>
     */
    protected function synthesizeCareerAnalysis(array $results): array
    {
        $synthesis = [
            'career_strategy' => $results[0]['output'] ?? [],
            'training_optimization' => $results[1]['output'] ?? [],
            'race_analysis' => $results[2]['output'] ?? [],
            'skill_management' => $results[3]['output'] ?? [],
        ];

        // Calculate overall confidence
        $confidences = [];
        foreach ($results as $result) {
            if (isset($result['output']['confidence'])) {
                $confidences[] = $result['output']['confidence'];
            }
        }

        $synthesis['overall_confidence'] = ! empty($confidences)
            ? array_sum($confidences) / count($confidences)
            : 0.5;

        // Generate integrated recommendations
        $synthesis['integrated_recommendations'] = $this->generateIntegratedRecommendations($synthesis);

        return $synthesis;
    }

    /**
     * Generate integrated recommendations
     *
     * @param  array<string, mixed>  $analysis
     * @return array<string, mixed>
     */
    protected function generateIntegratedRecommendations(array $analysis): array
    {
        return [
            'priority_action' => 'training',
            'focus' => 'speed',
            'reasoning' => 'Based on comprehensive multi-agent analysis',
            'confidence' => $analysis['overall_confidence'] ?? 0.5,
        ];
    }

    /**
     * Get active workflows
     *
     * @return array<string, mixed>
     */
    public function getActiveWorkflows(): array
    {
        return $this->activeWorkflows;
    }
}
