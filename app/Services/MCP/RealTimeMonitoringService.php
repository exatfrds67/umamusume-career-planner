<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Real-Time Monitoring Service
 *
 * Provides real-time MCP server communication monitoring, agent progress tracking,
 * tool execution monitoring, and performance metrics for the AI Chat UI.
 *
 * Requirements: 13.4, 47.2, 56.4
 */
class RealTimeMonitoringService
{
    /**
     * Cache TTL for real-time data (seconds)
     */
    private const CACHE_TTL = 60;

    /**
     * Maximum number of recent events to track
     */
    private const MAX_RECENT_EVENTS = 100;

    public function __construct(
        private readonly MCPClientService $mcpClient,
        private readonly MCPMonitoringService $monitoringService,
        private readonly AgentOrchestrationService $orchestrationService
    ) {}

    /**
     * Get real-time MCP server status with health metrics
     *
     * @return array{
     *     timestamp: string,
     *     overall_status: string,
     *     servers: array<string, array{
     *         name: string,
     *         status: string,
     *         is_connected: bool,
     *         response_time: float|null,
     *         uptime_percentage: float,
     *         consecutive_failures: int,
     *         last_success_at: string|null,
     *         last_failure_at: string|null,
     *         capabilities: array<string, mixed>,
     *         health_trend: string
     *     }>,
     *     alerts: array<int, array{
     *         level: string,
     *         server: string,
     *         message: string,
     *         timestamp: string
     *     }>
     * }
     */
    public function getRealTimeServerStatus(): array
    {
        $cacheKey = 'realtime:server_status';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $healthCheck = $this->monitoringService->performHealthCheck();
            $dashboard = $this->monitoringService->getMonitoringDashboard();

            $servers = [];
            foreach ($healthCheck as $serverName => $health) {
                $uptimeStats = $this->monitoringService->getServerUptimeStats($serverName, 1);
                $healthTrend = $this->calculateHealthTrend($serverName);

                $servers[$serverName] = [
                    'name' => $serverName,
                    'status' => $health['status'],
                    'is_connected' => $health['is_connected'],
                    'response_time' => $health['response_time'],
                    'uptime_percentage' => $uptimeStats['uptime_percentage'],
                    'consecutive_failures' => $health['consecutive_failures'],
                    'last_success_at' => $health['last_success_at'],
                    'last_failure_at' => $health['last_failure_at'],
                    'capabilities' => $health['capabilities'],
                    'health_trend' => $healthTrend,
                ];
            }

            return [
                'timestamp' => now()->toIso8601String(),
                'overall_status' => $dashboard['overall_health'],
                'servers' => $servers,
                'alerts' => $this->formatAlerts($dashboard['alerts']),
            ];
        });
    }

    /**
     * Track agent progress for long-running workflows
     *
     * @return array{
     *     workflow_id: string|null,
     *     workflow_name: string|null,
     *     status: string,
     *     progress_percentage: float,
     *     current_step: string|null,
     *     total_steps: int,
     *     completed_steps: int,
     *     agents: array<int, array{
     *         agent_id: string,
     *         agent_type: string,
     *         status: string,
     *         progress: float,
     *         started_at: string|null,
     *         completed_at: string|null,
     *         execution_time: float|null,
     *         error: string|null
     *     }>,
     *     estimated_completion: string|null,
     *     started_at: string|null
     * }
     */
    public function getAgentProgressTracking(?int $userId = null): array
    {
        $cacheKey = $userId ? "realtime:agent_progress:{$userId}" : 'realtime:agent_progress:global';

        return Cache::get($cacheKey, [
            'workflow_id' => null,
            'workflow_name' => null,
            'status' => 'idle',
            'progress_percentage' => 0.0,
            'current_step' => null,
            'total_steps' => 0,
            'completed_steps' => 0,
            'agents' => [],
            'estimated_completion' => null,
            'started_at' => null,
        ]);
    }

    /**
     * Update agent progress in real-time
     */
    public function updateAgentProgress(
        string $workflowId,
        string $agentId,
        string $status,
        float $progress,
        ?int $userId = null
    ): void {
        $cacheKey = $userId ? "realtime:agent_progress:{$userId}" : 'realtime:agent_progress:global';

        $currentProgress = $this->getAgentProgressTracking($userId);

        // Update agent status
        $agentFound = false;
        foreach ($currentProgress['agents'] as &$agent) {
            if ($agent['agent_id'] === $agentId) {
                $agent['status'] = $status;
                $agent['progress'] = $progress;

                if ($status === AgentOrchestrationService::STATE_COMPLETED) {
                    $agent['completed_at'] = now()->toIso8601String();
                    $agent['execution_time'] = $agent['started_at']
                        ? now()->diffInSeconds($agent['started_at'])
                        : null;
                }

                $agentFound = true;
                break;
            }
        }

        // Add new agent if not found
        if (! $agentFound) {
            $currentProgress['agents'][] = [
                'agent_id' => $agentId,
                'agent_type' => 'unknown',
                'status' => $status,
                'progress' => $progress,
                'started_at' => now()->toIso8601String(),
                'completed_at' => null,
                'execution_time' => null,
                'error' => null,
            ];
        }

        // Recalculate overall progress
        $totalAgents = count($currentProgress['agents']);
        $completedAgents = count(array_filter(
            $currentProgress['agents'],
            fn ($a) => $a['status'] === AgentOrchestrationService::STATE_COMPLETED
        ));

        $currentProgress['completed_steps'] = $completedAgents;
        $currentProgress['total_steps'] = $totalAgents;
        $currentProgress['progress_percentage'] = $totalAgents > 0
            ? ($completedAgents / $totalAgents) * 100
            : 0.0;

        // Update workflow status
        if ($completedAgents === $totalAgents && $totalAgents > 0) {
            $currentProgress['status'] = AgentOrchestrationService::STATE_COMPLETED;
        } elseif ($completedAgents > 0) {
            $currentProgress['status'] = AgentOrchestrationService::STATE_RUNNING;
        }

        Cache::put($cacheKey, $currentProgress, 3600);

        // Log progress update
        Log::info('[RealTimeMonitoring] Agent progress updated', [
            'workflow_id' => $workflowId,
            'agent_id' => $agentId,
            'status' => $status,
            'progress' => $progress,
            'overall_progress' => $currentProgress['progress_percentage'],
        ]);
    }

    /**
     * Get tool execution monitoring data
     *
     * @return array{
     *     timestamp: string,
     *     active_tools: array<int, array{
     *         tool_name: string,
     *         server: string,
     *         status: string,
     *         started_at: string,
     *         execution_time: float|null,
     *         input_summary: string,
     *         output_summary: string|null,
     *         error: string|null
     *     }>,
     *     recent_executions: array<int, array{
     *         tool_name: string,
     *         server: string,
     *         status: string,
     *         execution_time: float,
     *         completed_at: string,
     *         success: bool
     *     }>,
     *     tool_statistics: array<string, array{
     *         tool_name: string,
     *         total_executions: int,
     *         successful_executions: int,
     *         failed_executions: int,
     *         average_execution_time: float,
     *         success_rate: float
     *     }>
     * }
     */
    public function getToolExecutionMonitoring(?int $userId = null): array
    {
        $cacheKey = $userId ? "realtime:tool_execution:{$userId}" : 'realtime:tool_execution:global';

        return Cache::get($cacheKey, [
            'timestamp' => now()->toIso8601String(),
            'active_tools' => [],
            'recent_executions' => [],
            'tool_statistics' => [],
        ]);
    }

    /**
     * Record tool execution
     */
    public function recordToolExecution(
        string $toolName,
        string $server,
        string $status,
        float $executionTime,
        bool $success,
        ?string $error = null,
        ?int $userId = null
    ): void {
        $cacheKey = $userId ? "realtime:tool_execution:{$userId}" : 'realtime:tool_execution:global';

        $monitoring = $this->getToolExecutionMonitoring($userId);

        // Add to recent executions
        $monitoring['recent_executions'][] = [
            'tool_name' => $toolName,
            'server' => $server,
            'status' => $status,
            'execution_time' => $executionTime,
            'completed_at' => now()->toIso8601String(),
            'success' => $success,
        ];

        // Keep only last 50 executions
        if (count($monitoring['recent_executions']) > 50) {
            $monitoring['recent_executions'] = array_slice($monitoring['recent_executions'], -50);
        }

        // Update tool statistics
        if (! isset($monitoring['tool_statistics'][$toolName])) {
            $monitoring['tool_statistics'][$toolName] = [
                'tool_name' => $toolName,
                'total_executions' => 0,
                'successful_executions' => 0,
                'failed_executions' => 0,
                'average_execution_time' => 0.0,
                'success_rate' => 0.0,
            ];
        }

        $stats = &$monitoring['tool_statistics'][$toolName];
        $stats['total_executions']++;

        if ($success) {
            $stats['successful_executions']++;
        } else {
            $stats['failed_executions']++;
        }

        // Update average execution time
        $stats['average_execution_time'] = (
            ($stats['average_execution_time'] * ($stats['total_executions'] - 1)) + $executionTime
        ) / $stats['total_executions'];

        // Update success rate
        $stats['success_rate'] = ($stats['successful_executions'] / $stats['total_executions']) * 100;

        $monitoring['timestamp'] = now()->toIso8601String();

        Cache::put($cacheKey, $monitoring, 3600);

        // Log tool execution
        Log::info('[RealTimeMonitoring] Tool execution recorded', [
            'tool_name' => $toolName,
            'server' => $server,
            'status' => $status,
            'execution_time' => $executionTime,
            'success' => $success,
        ]);
    }

    /**
     * Get performance metrics comparing AI providers and agents
     *
     * @return array{
     *     timestamp: string,
     *     providers: array<string, array{
     *         name: string,
     *         total_requests: int,
     *         successful_requests: int,
     *         failed_requests: int,
     *         average_response_time: float,
     *         average_cost: float,
     *         success_rate: float,
     *         uptime_percentage: float
     *     }>,
     *     agents: array<string, array{
     *         type: string,
     *         total_executions: int,
     *         successful_executions: int,
     *         failed_executions: int,
     *         average_execution_time: float,
     *         success_rate: float,
     *         health_status: string
     *     }>,
     *     comparison: array{
     *         fastest_provider: string,
     *         most_reliable_provider: string,
     *         most_cost_effective: string,
     *         best_performing_agent: string
     *     }
     * }
     */
    public function getPerformanceMetrics(?int $userId = null): array
    {
        $cacheKey = $userId ? "realtime:performance_metrics:{$userId}" : 'realtime:performance_metrics:global';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $providers = $this->getProviderMetrics($userId);
            $agents = $this->getAgentMetrics($userId);
            $comparison = $this->generateComparison($providers, $agents);

            return [
                'timestamp' => now()->toIso8601String(),
                'providers' => $providers,
                'agents' => $agents,
                'comparison' => $comparison,
            ];
        });
    }

    /**
     * Handle MCP server disconnection with recovery
     */
    public function handleServerDisconnection(string $serverName, string $error): array
    {
        Log::warning('[RealTimeMonitoring] Server disconnection detected', [
            'server' => $serverName,
            'error' => $error,
        ]);

        // Record disconnection event
        $this->recordServerEvent($serverName, 'disconnection', $error);

        // Attempt automatic reconnection
        $reconnectionResult = $this->attemptServerReconnection($serverName);

        // Update server status cache
        $this->invalidateServerStatusCache();

        return [
            'server' => $serverName,
            'event' => 'disconnection',
            'error' => $error,
            'reconnection_attempted' => true,
            'reconnection_successful' => $reconnectionResult['success'],
            'reconnection_message' => $reconnectionResult['message'],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Attempt server reconnection with exponential backoff
     */
    protected function attemptServerReconnection(string $serverName, int $attempt = 1): array
    {
        $maxAttempts = 3;
        $baseDelay = 2; // seconds

        if ($attempt > $maxAttempts) {
            return [
                'success' => false,
                'message' => "Failed to reconnect after {$maxAttempts} attempts",
                'attempts' => $attempt - 1,
            ];
        }

        try {
            // Wait with exponential backoff
            if ($attempt > 1) {
                $delay = $baseDelay * pow(2, $attempt - 1);
                sleep($delay);
            }

            // Perform health check to test connection
            $healthCheck = $this->mcpClient->healthCheck();

            if (isset($healthCheck[$serverName]) && $healthCheck[$serverName]['status'] === 'healthy') {
                Log::info('[RealTimeMonitoring] Server reconnection successful', [
                    'server' => $serverName,
                    'attempt' => $attempt,
                ]);

                $this->recordServerEvent($serverName, 'reconnection_success', "Reconnected on attempt {$attempt}");

                return [
                    'success' => true,
                    'message' => "Successfully reconnected on attempt {$attempt}",
                    'attempts' => $attempt,
                ];
            }

            // Retry if not successful
            return $this->attemptServerReconnection($serverName, $attempt + 1);
        } catch (\Exception $e) {
            Log::error('[RealTimeMonitoring] Reconnection attempt failed', [
                'server' => $serverName,
                'attempt' => $attempt,
                'error' => $e->getMessage(),
            ]);

            // Retry if not at max attempts
            if ($attempt < $maxAttempts) {
                return $this->attemptServerReconnection($serverName, $attempt + 1);
            }

            return [
                'success' => false,
                'message' => "Reconnection failed: {$e->getMessage()}",
                'attempts' => $attempt,
            ];
        }
    }

    /**
     * Record server event for tracking
     */
    protected function recordServerEvent(string $serverName, string $eventType, string $message): void
    {
        $cacheKey = "realtime:server_events:{$serverName}";

        $events = Cache::get($cacheKey, []);
        $events[] = [
            'event_type' => $eventType,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // Keep only last 100 events
        if (count($events) > self::MAX_RECENT_EVENTS) {
            $events = array_slice($events, -self::MAX_RECENT_EVENTS);
        }

        Cache::put($cacheKey, $events, 3600);
    }

    /**
     * Get provider metrics from database
     */
    protected function getProviderMetrics(?int $userId): array
    {
        $query = DB::table('ucp_ai_conversations')
            ->select(
                DB::raw('JSON_EXTRACT(metadata, "$.provider") as provider'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN message_type = "ai" THEN 1 ELSE 0 END) as successful_requests'),
                DB::raw('AVG(processing_time) as average_response_time'),
                DB::raw('AVG(cost_estimate) as average_cost')
            )
            ->where('created_at', '>=', now()->subHours(24))
            ->groupBy('provider');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $results = $query->get();

        $providers = [];
        foreach ($results as $result) {
            $providerName = trim($result->provider ?? 'unknown', '"');
            $totalRequests = $result->total_requests ?? 0;
            $successfulRequests = $result->successful_requests ?? 0;

            $providers[$providerName] = [
                'name' => $providerName,
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $totalRequests - $successfulRequests,
                'average_response_time' => round($result->average_response_time ?? 0.0, 3),
                'average_cost' => round($result->average_cost ?? 0.0, 6),
                'success_rate' => $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0,
                'uptime_percentage' => 100.0, // TODO: Calculate from health checks
            ];
        }

        return $providers;
    }

    /**
     * Get agent metrics from database
     */
    protected function getAgentMetrics(?int $userId): array
    {
        // TODO: Implement agent metrics from database
        return [];
    }

    /**
     * Generate performance comparison
     */
    protected function generateComparison(array $providers, array $agents): array
    {
        $fastestProvider = null;
        $fastestTime = PHP_FLOAT_MAX;

        $mostReliableProvider = null;
        $highestSuccessRate = 0.0;

        $mostCostEffective = null;
        $lowestCost = PHP_FLOAT_MAX;

        foreach ($providers as $provider) {
            if ($provider['average_response_time'] < $fastestTime) {
                $fastestTime = $provider['average_response_time'];
                $fastestProvider = $provider['name'];
            }

            if ($provider['success_rate'] > $highestSuccessRate) {
                $highestSuccessRate = $provider['success_rate'];
                $mostReliableProvider = $provider['name'];
            }

            if ($provider['average_cost'] < $lowestCost && $provider['average_cost'] > 0) {
                $lowestCost = $provider['average_cost'];
                $mostCostEffective = $provider['name'];
            }
        }

        $bestPerformingAgent = null;
        $highestAgentSuccessRate = 0.0;

        foreach ($agents as $agent) {
            if ($agent['success_rate'] > $highestAgentSuccessRate) {
                $highestAgentSuccessRate = $agent['success_rate'];
                $bestPerformingAgent = $agent['type'];
            }
        }

        return [
            'fastest_provider' => $fastestProvider ?? 'N/A',
            'most_reliable_provider' => $mostReliableProvider ?? 'N/A',
            'most_cost_effective' => $mostCostEffective ?? 'N/A',
            'best_performing_agent' => $bestPerformingAgent ?? 'N/A',
        ];
    }

    /**
     * Calculate health trend for a server
     */
    protected function calculateHealthTrend(string $serverName): string
    {
        $history = $this->monitoringService->getServerHealthHistory($serverName, 1);

        if (count($history) < 2) {
            return 'stable';
        }

        $recentChecks = array_slice($history, 0, 10);
        $successCount = count(array_filter($recentChecks, fn ($h) => $h['status'] === 'healthy'));
        $successRate = $successCount / count($recentChecks);

        if ($successRate >= 0.9) {
            return 'improving';
        } elseif ($successRate >= 0.7) {
            return 'stable';
        } else {
            return 'degrading';
        }
    }

    /**
     * Format alerts for frontend display
     */
    protected function formatAlerts(array $alerts): array
    {
        return array_map(function ($alert) {
            return [
                'level' => $alert['level'],
                'server' => $alert['server'],
                'message' => $alert['message'],
                'timestamp' => now()->toIso8601String(),
            ];
        }, $alerts);
    }

    /**
     * Invalidate server status cache
     */
    protected function invalidateServerStatusCache(): void
    {
        Cache::forget('realtime:server_status');
    }
}
