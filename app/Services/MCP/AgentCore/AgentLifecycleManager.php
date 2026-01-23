<?php

namespace App\Services\MCP\AgentCore;

use App\Models\MCPAgent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Lifecycle Manager
 *
 * Manages the complete lifecycle of MCP agents including creation,
 * monitoring, health checks, and termination with proper resource cleanup.
 *
 * Requirements: 56.3, 59.2
 */
class AgentLifecycleManager
{
    protected AgentCoreService $agentCore;

    protected AgentCommunicationProtocol $communication;

    protected AgentPerformanceAnalytics $analytics;

    public function __construct(
        AgentCoreService $agentCore,
        AgentCommunicationProtocol $communication,
        AgentPerformanceAnalytics $analytics
    ) {
        $this->agentCore = $agentCore;
        $this->communication = $communication;
        $this->analytics = $analytics;
    }

    /**
     * Create and deploy a new agent
     *
     * @param  array{
     *     name: string,
     *     type: string,
     *     model: string,
     *     instructions: string,
     *     tools?: array<string, mixed>,
     *     memory?: array<string, mixed>,
     *     guardrails?: array<string, mixed>,
     *     metadata?: array<string, mixed>
     * }  $config
     * @return array{
     *     agent_id: string,
     *     database_id: int,
     *     status: string,
     *     deployment_time: float,
     *     error?: string
     * }
     */
    public function createAgent(): array
        $startTime = microtime(true);

        try {
            Log::info('[AgentLifecycle] Creating agent', [
                'name' => $config['name'] ?? 'unknown',
                'type' => $config['type'] ?? 'unknown',
            ]);

            // Deploy to AgentCore
            $deployment = $this->agentCore->deployAgent($config);

            if ($deployment['status'] !== 'deployed') {
                throw new \RuntimeException('Agent deployment failed: '.$deployment['error'] ?? 'Unknown error');
            }

            // Store in database
            $agent = MCPAgent::create([
                'agent_id' => $deployment['agent_id'],
                'name' => $config['name'],
                'type' => $config['type'],
                'model' => $config['model'],
                'instructions' => $config['instructions'],
                'tools' => $config['tools'] ?? [],
                'memory_config' => $config['memory'] ?? ['type' => 'ephemeral'],
                'guardrails' => $config['guardrails'] ?? [],
                'metadata' => $config['metadata'] ?? [],
                'status' => 'active',
                'health_status' => 'healthy',
                'deployment_time' => $deployment['deployment_time'],
                'last_health_check' => now(),
            ]);

            // Initialize performance tracking
            $this->analytics->initializeAgent($deployment['agent_id']);

            $creationTime = microtime(true) - $startTime;

            Log::info('[AgentLifecycle] Agent created successfully', [
                'agent_id' => $deployment['agent_id'],
                'database_id' => $agent->id,
                'creation_time' => $creationTime,
            ]);

            return [
                'agent_id' => $deployment['agent_id'],
                'database_id' => $agent->id,
                'status' => 'active',
                'deployment_time' => round($creationTime, 3),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Agent creation failed', [
                'error' => $e->getMessage(),
                'config' => $config,
            ]);

            return [
                'agent_id' => '',
                'database_id' => 0,
                'status' => 'failed',
                'deployment_time' => microtime(true) - $startTime,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Monitor agent health and performance
     *
     * @return array{
     *     agent_id: string,
     *     health_status: string,
     *     performance_metrics: array<string, mixed>,
     *     issues: array<int, string>,
     *     recommendations: array<int, string>
     * }
     */
    public function monitorAgent(): array
        try {
            Log::info('[AgentLifecycle] Monitoring agent', ['agent_id' => $agentId]);

            // Get agent from database
            $agent = MCPAgent::where('agent_id', $agentId)->first();
            if (! $agent) {
                throw new \RuntimeException("Agent not found: {$agentId}");
            }

            // Check health
            $healthStatus = $this->checkAgentHealth($agentId);

            // Get performance metrics
            $performanceMetrics = $this->analytics->getAgentMetrics($agentId);

            // Identify issues
            $issues = $this->identifyIssues($healthStatus, $performanceMetrics);

            // Generate recommendations
            $recommendations = $this->generateRecommendations($issues, $performanceMetrics);

            // Update database
            $agent->update([
                'health_status' => $healthStatus['status'],
                'last_health_check' => now(),
                'performance_metrics' => $performanceMetrics,
            ]);

            return [
                'agent_id' => $agentId,
                'health_status' => $healthStatus['status'],
                'performance_metrics' => $performanceMetrics,
                'issues' => $issues,
                'recommendations' => $recommendations,
            ];
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Agent monitoring failed', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'agent_id' => $agentId,
                'health_status' => 'error',
                'performance_metrics' => [],
                'issues' => ['Monitoring failed: '.$e->getMessage()],
                'recommendations' => [],
            ];
        }
    }

    /**
     * Terminate an agent with proper cleanup
     *
     * @return array{
     *     agent_id: string,
     *     status: string,
     *     cleanup_time: float,
     *     resources_cleaned: array<string, bool>,
     *     error?: string
     * }
     */
    public function terminateAgent(): array
        $startTime = microtime(true);
        $resourcesCleaned = [
            'agentcore' => false,
            'database' => false,
            'cache' => false,
            'communication' => false,
            'analytics' => false,
        ];

        try {
            Log::info('[AgentLifecycle] Terminating agent', ['agent_id' => $agentId]);

            // Get agent from database
            $agent = MCPAgent::where('agent_id', $agentId)->first();
            if (! $agent) {
                throw new \RuntimeException("Agent not found: {$agentId}");
            }

            // Terminate in AgentCore
            $termination = $this->agentCore->terminateAgent($agentId);
            $resourcesCleaned['agentcore'] = $termination['status'] === 'terminated';

            // Clear communication resources
            $this->communication->clearMessageQueue($agentId);
            $this->communication->clearSharedMemory($agentId);
            $resourcesCleaned['communication'] = true;

            // Archive analytics data
            $this->analytics->archiveAgentData($agentId);
            $resourcesCleaned['analytics'] = true;

            // Clear cache
            Cache::forget("agentcore_agent_{$agentId}");
            Cache::forget("agentcore_metrics_{$agentId}");
            $resourcesCleaned['cache'] = true;

            // Update database status
            $agent->update([
                'status' => 'terminated',
                'terminated_at' => now(),
            ]);
            $resourcesCleaned['database'] = true;

            $cleanupTime = microtime(true) - $startTime;

            Log::info('[AgentLifecycle] Agent terminated successfully', [
                'agent_id' => $agentId,
                'cleanup_time' => $cleanupTime,
                'resources_cleaned' => $resourcesCleaned,
            ]);

            return [
                'agent_id' => $agentId,
                'status' => 'terminated',
                'cleanup_time' => round($cleanupTime, 3),
                'resources_cleaned' => $resourcesCleaned,
            ];
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Agent termination failed', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
                'resources_cleaned' => $resourcesCleaned,
            ]);

            return [
                'agent_id' => $agentId,
                'status' => 'failed',
                'cleanup_time' => microtime(true) - $startTime,
                'resources_cleaned' => $resourcesCleaned,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restart an agent
     *
     * @return array{
     *     agent_id: string,
     *     status: string,
     *     restart_time: float,
     *     error?: string
     * }
     */
    public function restartAgent(): array
        $startTime = microtime(true);

        try {
            Log::info('[AgentLifecycle] Restarting agent', ['agent_id' => $agentId]);

            // Get agent configuration
            $agent = MCPAgent::where('agent_id', $agentId)->first();
            if (! $agent) {
                throw new \RuntimeException("Agent not found: {$agentId}");
            }

            // Terminate existing agent
            $termination = $this->terminateAgent($agentId);
            if ($termination['status'] !== 'terminated') {
                throw new \RuntimeException('Failed to terminate agent for restart');
            }

            // Create new agent with same configuration
            $config = [
                'name' => $agent->name,
                'type' => $agent->type,
                'model' => $agent->model,
                'instructions' => $agent->instructions,
                'tools' => $agent->tools,
                'memory' => $agent->memory_config,
                'guardrails' => $agent->guardrails,
                'metadata' => $agent->metadata,
            ];

            $creation = $this->createAgent($config);
            if ($creation['status'] !== 'active') {
                throw new \RuntimeException('Failed to create agent after restart');
            }

            $restartTime = microtime(true) - $startTime;

            Log::info('[AgentLifecycle] Agent restarted successfully', [
                'old_agent_id' => $agentId,
                'new_agent_id' => $creation['agent_id'],
                'restart_time' => $restartTime,
            ]);

            return [
                'agent_id' => $creation['agent_id'],
                'status' => 'restarted',
                'restart_time' => round($restartTime, 3),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Agent restart failed', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'agent_id' => $agentId,
                'status' => 'failed',
                'restart_time' => microtime(true) - $startTime,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List all active agents
     *
     * @return array<int, array{
     *     agent_id: string,
     *     name: string,
     *     type: string,
     *     status: string,
     *     health_status: string,
     *     uptime_seconds: int
     * }>
     */
    public function listActiveAgents(): array
        try {
            $agents = MCPAgent::where('status', 'active')->get();

            return $agents->map(function ($agent) {
                return [
                    'agent_id' => $agent->agent_id,
                    'name' => $agent->name,
                    'type' => $agent->type,
                    'status' => $agent->status,
                    'health_status' => $agent->health_status,
                    'uptime_seconds' => now()->diffInSeconds($agent->created_at),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to list active agents', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Perform health check on agent
     *
     * @return array{
     *     status: string,
     *     response_time: float,
     *     error_rate: float,
     *     last_activity: string
     * }
     */
    protected function checkAgentHealth(): array
        $status = $this->agentCore->getAgentStatus($agentId);

        return [
            'status' => $status['status'] === 'active' ? 'healthy' : 'unhealthy',
            'response_time' => $status['health']['response_time'] ?? 0.0,
            'error_rate' => $status['health']['error_rate'] ?? 0.0,
            'last_activity' => now()->toIso8601String(),
        ];
    }

    /**
     * Identify issues from health and performance data
     *
     * @param  array<string, mixed>  $healthStatus
     * @param  array<string, mixed>  $performanceMetrics
     * @return array<int, string>
     */
    protected function identifyIssues(): array
        $issues = [];

        // Check health status
        if ($healthStatus['status'] === 'unhealthy') {
            $issues[] = 'Agent health status is unhealthy';
        }

        // Check response time
        if (($healthStatus['response_time'] ?? 0.0) > 5.0) {
            $issues[] = 'High response time detected (>5 seconds)';
        }

        // Check error rate
        if (($healthStatus['error_rate'] ?? 0.0) > 0.1) {
            $issues[] = 'High error rate detected (>10%)';
        }

        // Check success rate
        if (($performanceMetrics['success_rate'] ?? 1.0) < 0.9) {
            $issues[] = 'Low success rate (<90%)';
        }

        return $issues;
    }

    /**
     * Generate recommendations based on issues
     *
     * @param  array<int, string>  $issues
     * @param  array<string, mixed>  $performanceMetrics
     * @return array<int, string>
     */
    protected function generateRecommendations(): array
        $recommendations = [];

        foreach ($issues as $issue) {
            if (str_contains($issue, 'unhealthy')) {
                $recommendations[] = 'Consider restarting the agent';
            }

            if (str_contains($issue, 'response time')) {
                $recommendations[] = 'Optimize agent instructions or reduce task complexity';
            }

            if (str_contains($issue, 'error rate')) {
                $recommendations[] = 'Review agent logs and adjust error handling';
            }

            if (str_contains($issue, 'success rate')) {
                $recommendations[] = 'Review agent configuration and training data';
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Agent is performing well - no action needed';
        }

        return $recommendations;
    }

    /**
     * Get lifecycle statistics
     *
     * @return array{
     *     total_agents: int,
     *     active_agents: int,
     *     terminated_agents: int,
     *     average_uptime_hours: float
     * }
     */
    public function getStatistics(): array
        try {
            $totalAgents = MCPAgent::count();
            $activeAgents = MCPAgent::where('status', 'active')->count();
            $terminatedAgents = MCPAgent::where('status', 'terminated')->count();

            $avgUptime = MCPAgent::where('status', 'active')
                ->get()
                ->avg(fn ($agent) => now()->diffInHours($agent->created_at));

            return [
                'total_agents' => $totalAgents,
                'active_agents' => $activeAgents,
                'terminated_agents' => $terminatedAgents,
                'average_uptime_hours' => round($avgUptime ?? 0.0, 2),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to get statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_agents' => 0,
                'active_agents' => 0,
                'terminated_agents' => 0,
                'average_uptime_hours' => 0.0,
            ];
        }
    }
}
