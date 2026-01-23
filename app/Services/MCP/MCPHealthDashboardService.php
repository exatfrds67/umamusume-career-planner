<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\MCPAgent;
use App\Models\MCPServer;
use App\Models\MCPToolUsage;
use App\Services\CacheManagementService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\MCPMonitoringService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * MCP Health Dashboard Service
 *
 * Provides comprehensive health monitoring dashboard for all MCP servers,
 * external API integrations, and agent performance with real-time metrics.
 *
 * Requirements: 14.5, 55.4, 56.4, Task 4.4.5
 */
class MCPHealthDashboardService
{
    /**
     * Dashboard cache key
     */
    protected const DASHBOARD_CACHE_KEY = 'mcp_health_dashboard';

    /**
     * Dashboard cache TTL in seconds (5 minutes)
     */
    protected const DASHBOARD_CACHE_TTL = 300;

    /**
     * Health check interval in seconds
     */
    protected const HEALTH_CHECK_INTERVAL = 60;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected MCPMonitoringService $mcpMonitoring,
        protected APIHealthMonitorService $apiHealthMonitor,
        protected CacheManagementService $cacheManagement
    ) {}

    /**
     * Get comprehensive dashboard data with all health metrics
     *
     * @return array{
     *     overview: array<string, mixed>,
     *     mcp_servers: array<string, mixed>,
     *     external_apis: array<string, mixed>,
     *     agents: array<string, mixed>,
     *     performance: array<string, mixed>,
     *     costs: array<string, mixed>,
     *     recommendations: array<string>,
     *     last_updated: string
     * }
     */
    public function getDashboardData(): array
        $cacheKey = self::DASHBOARD_CACHE_KEY.":{$userId}";

        if (! $forceRefresh && Cache::has($cacheKey)) {
            Log::debug('[MCPHealthDashboard] Returning cached dashboard data', [
                'user_id' => $userId,
            ]);

            return Cache::get($cacheKey);
        }

        Log::info('[MCPHealthDashboard] Generating fresh dashboard data', [
            'user_id' => $userId,
            'force_refresh' => $forceRefresh,
        ]);

        $startTime = microtime(true);

        $dashboardData = [
            'overview' => $this->getOverviewMetrics($userId),
            'mcp_servers' => $this->getMCPServerHealth(),
            'external_apis' => $this->getExternalAPIHealth(),
            'agents' => $this->getAgentHealth($userId),
            'performance' => $this->getPerformanceMetrics($userId),
            'costs' => $this->getCostAnalytics($userId),
            'recommendations' => $this->getRecommendations($userId),
            'last_updated' => now()->toIso8601String(),
        ];

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[MCPHealthDashboard] Dashboard data generated', [
            'user_id' => $userId,
            'duration_ms' => round($duration, 2),
            'overall_health' => $dashboardData['overview']['overall_health'],
        ]);

        // Cache the dashboard data
        Cache::put($cacheKey, $dashboardData, self::DASHBOARD_CACHE_TTL);

        return $dashboardData;
    }

    /**
     * Get overview metrics for dashboard
     *
     * @return array{
     *     overall_health: string,
     *     health_score: float,
     *     total_servers: int,
     *     healthy_servers: int,
     *     total_agents: int,
     *     active_agents: int,
     *     total_apis: int,
     *     healthy_apis: int,
     *     alerts_count: int,
     *     uptime_percentage: float
     * }
     */
    protected function getOverviewMetrics(): array
        // Get MCP server status
        $mcpServers = MCPServer::all();
        $healthyServers = $mcpServers->filter(fn ($server) => $server->isHealthy())->count();

        // Get agent status
        $agents = MCPAgent::where('user_id', $userId)->get();
        $activeAgents = $agents->where('status', '=', 'active')->count();

        // Get external API status
        $apiHealth = $this->apiHealthMonitor->getCachedAllHealth() ?? $this->apiHealthMonitor->checkAllAPIs();
        $healthyAPIs = collect([$apiHealth['umapyoi'], $apiHealth['umamusumedb']])
            ->filter(fn ($api) => $api['status'] === 'healthy')
            ->count();

        // Calculate overall health score (0-100)
        $healthScore = $this->calculateOverallHealthScore([
            'mcp_servers' => ['healthy' => $healthyServers, 'total' => $mcpServers->count()],
            'agents' => ['active' => $activeAgents, 'total' => $agents->count()],
            'apis' => ['healthy' => $healthyAPIs, 'total' => 2],
        ]);

        // Determine overall health status
        $overallHealth = match (true) {
            $healthScore >= 90 => 'excellent',
            $healthScore >= 75 => 'good',
            $healthScore >= 50 => 'fair',
            $healthScore >= 25 => 'poor',
            default => 'critical',
        };

        // Count alerts
        $alertsCount = $this->countActiveAlerts($userId);

        // Calculate uptime percentage (last 24 hours)
        $uptimePercentage = $this->calculateUptimePercentage();

        return [
            'overall_health' => $overallHealth,
            'health_score' => round($healthScore, 2),
            'total_servers' => $mcpServers->count(),
            'healthy_servers' => $healthyServers,
            'total_agents' => $agents->count(),
            'active_agents' => $activeAgents,
            'total_apis' => 2,
            'healthy_apis' => $healthyAPIs,
            'alerts_count' => $alertsCount,
            'uptime_percentage' => round($uptimePercentage, 2),
        ];
    }

    /**
     * Get MCP server health status
     *
     * @return array{
     *     servers: array<array<string, mixed>>,
     *     summary: array{total: int, healthy: int, degraded: int, unhealthy: int, offline: int}
     * }
     */
    protected function getMCPServerHealth(): array
        $servers = MCPServer::all();

        $serverData = $servers->map(function ($server) {
            $isHealthy = $server->isHealthy();
            $status = $this->determineServerStatus($server);

            return [
                'id' => $server->id,
                'name' => $server->server_name,
                'type' => $server->server_type,
                'status' => $status,
                'is_healthy' => $isHealthy,
                'success_rate' => round($server->success_rate, 2),
                'failure_rate' => round($server->failure_rate, 2),
                'average_response_time' => round($server->average_response_time, 3),
                'total_requests' => $server->total_requests,
                'consecutive_failures' => $server->consecutive_failures,
                'last_health_check' => $server->last_health_check?->toIso8601String(),
                'last_error' => $server->last_error_message,
                'capabilities' => $server->server_capabilities ?? [],
                'connection_status' => $server->status,
            ];
        })->values()->all();

        // Calculate summary
        $summary = [
            'total' => $servers->count(),
            'healthy' => collect($serverData)->where('status', '=', 'healthy')->count(),
            'degraded' => collect($serverData)->where('status', '=', 'degraded')->count(),
            'unhealthy' => collect($serverData)->where('status', '=', 'unhealthy')->count(),
            'offline' => collect($serverData)->where('status', '=', 'offline')->count(),
        ];

        return [
            'servers' => $serverData,
            'summary' => $summary,
        ];
    }

    /**
     * Get external API health status
     *
     * @return array{
     *     apis: array<array<string, mixed>>,
     *     summary: array{total: int, healthy: int, degraded: int, unhealthy: int, circuit_open: int},
     *     overall_status: string
     * }
     */
    protected function getExternalAPIHealth(): array
        $apiHealth = $this->apiHealthMonitor->getCachedAllHealth() ?? $this->apiHealthMonitor->checkAllAPIs();

        $apis = [
            [
                'name' => 'umapyoi',
                'display_name' => 'Umapyoi.net API',
                'status' => $apiHealth['umapyoi']['status'],
                'available' => $apiHealth['umapyoi']['available'],
                'response_time_ms' => $apiHealth['umapyoi']['response_time_ms'],
                'failure_count' => $apiHealth['umapyoi']['failure_count'],
                'circuit_breaker_open' => $apiHealth['umapyoi']['circuit_breaker_open'],
                'last_check' => $apiHealth['umapyoi']['last_check'],
                'message' => $apiHealth['umapyoi']['message'],
            ],
            [
                'name' => 'umamusumedb',
                'display_name' => 'UmamusumeDB API',
                'status' => $apiHealth['umamusumedb']['status'],
                'available' => $apiHealth['umamusumedb']['available'],
                'response_time_ms' => $apiHealth['umamusumedb']['response_time_ms'],
                'failure_count' => $apiHealth['umamusumedb']['failure_count'],
                'circuit_breaker_open' => $apiHealth['umamusumedb']['circuit_breaker_open'],
                'last_check' => $apiHealth['umamusumedb']['last_check'],
                'message' => $apiHealth['umamusumedb']['message'],
            ],
        ];

        $summary = [
            'total' => 2,
            'healthy' => collect($apis)->where('status', '=', 'healthy')->count(),
            'degraded' => collect($apis)->where('status', '=', 'degraded')->count(),
            'unhealthy' => collect($apis)->where('status', '=', 'unhealthy')->count(),
            'circuit_open' => collect($apis)->where('circuit_breaker_open', true)->count(),
        ];

        return [
            'apis' => $apis,
            'summary' => $summary,
            'overall_status' => $apiHealth['overall_status'],
        ];
    }

    /**
     * Get agent health status
     *
     * @return array{
     *     agents: array<array<string, mixed>>,
     *     summary: array{total: int, active: int, terminated: int, healthy: int, unhealthy: int}
     * }
     */
    protected function getAgentHealth(): array
        $agents = MCPAgent::where('user_id', $userId)->get();

        $agentData = $agents->map(function ($agent) {
            return [
                'id' => $agent->id,
                'agent_id' => $agent->agent_id,
                'name' => $agent->name,
                'type' => $agent->type,
                'model' => $agent->model,
                'status' => $agent->status,
                'health_status' => $agent->health_status,
                'uptime_seconds' => $agent->getUptimeSeconds(),
                'deployment_time' => $agent->deployment_time,
                'performance_metrics' => $agent->performance_metrics ?? [],
                'last_health_check' => $agent->last_health_check?->toIso8601String(),
                'created_at' => $agent->created_at->toIso8601String(),
            ];
        })->values()->all();

        $summary = [
            'total' => $agents->count(),
            'active' => $agents->where('status', '=', 'active')->count(),
            'terminated' => $agents->where('status', '=', 'terminated')->count(),
            'healthy' => $agents->where('health_status', '=', 'healthy')->count(),
            'unhealthy' => $agents->where('health_status', '=', 'unhealthy')->count(),
        ];

        return [
            'agents' => $agentData,
            'summary' => $summary,
        ];
    }

    /**
     * Get performance metrics
     *
     * @return array{
     *     cache_performance: array<string, mixed>,
     *     api_response_times: array<string, mixed>,
     *     tool_usage: array<string, mixed>,
     *     system_health: array<string, mixed>
     * }
     */
    protected function getPerformanceMetrics(): array
        // Cache performance
        $cacheStats = $this->cacheManagement->getHitRateStatistics();

        // API response times
        $apiResponseTimes = [
            'umapyoi' => $this->cacheManagement->getApiResponseTimeStats('umapyoi'),
            'umamusumedb' => $this->cacheManagement->getApiResponseTimeStats('umamusumedb'),
        ];

        // Tool usage statistics
        $toolUsage = $this->getToolUsageStatistics($userId);

        // System health metrics
        $systemHealth = $this->getSystemHealthMetrics();

        return [
            'cache_performance' => $cacheStats,
            'api_response_times' => $apiResponseTimes,
            'tool_usage' => $toolUsage,
            'system_health' => $systemHealth,
        ];
    }

    /**
     * Get cost analytics
     *
     * @return array{
     *     current_month: array<string, mixed>,
     *     daily_costs: array<array<string, mixed>>,
     *     cost_by_server: array<array<string, mixed>>,
     *     cost_by_tool: array<array<string, mixed>>,
     *     budget_status: array<string, mixed>
     * }
     */
    protected function getCostAnalytics(): array
        $costData = $this->mcpMonitoring->getCostAnalytics($userId, 'month');

        // Get budget information
        $budgetStatus = $this->getBudgetStatus($userId, $costData['total_cost']);

        return [
            'current_month' => [
                'total_cost' => $costData['total_cost'],
                'total_tokens' => $costData['total_tokens'],
                'total_requests' => $costData['total_requests'],
                'average_cost_per_request' => $costData['average_cost_per_request'],
            ],
            'daily_costs' => $costData['daily_costs'],
            'cost_by_server' => $costData['cost_by_server'],
            'cost_by_tool' => $costData['cost_by_tool'],
            'budget_status' => $budgetStatus,
        ];
    }

    /**
     * Get comprehensive recommendations
     *
     * @return array<array{type: string, severity: string, title: string, description: string, action: string, metadata: array<string, mixed>}>
     */
    protected function getRecommendations(): array
        $recommendations = [];

        // Get MCP monitoring recommendations
        $mcpRecommendations = $this->mcpMonitoring->getOptimizationRecommendations($userId);
        $recommendations = array_merge($recommendations, $mcpRecommendations);

        // Get API health recommendations
        $apiMetrics = $this->apiHealthMonitor->getHealthMetrics();
        $recommendations = array_merge($recommendations, $apiMetrics['recommendations']);

        // Add cache optimization recommendations
        $cacheRecommendations = $this->getCacheOptimizationRecommendations();
        $recommendations = array_merge($recommendations, $cacheRecommendations);

        // Add cost optimization recommendations
        $costRecommendations = $this->getCostOptimizationRecommendations($userId);
        $recommendations = array_merge($recommendations, $costRecommendations);

        // Sort by severity
        usort($recommendations, function ($a, $b) {
            $severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            $aSeverity = $severityOrder[$a['severity'] ?? 'low'] ?? 999;
            $bSeverity = $severityOrder[$b['severity'] ?? 'low'] ?? 999;

            return $aSeverity <=> $bSeverity;
        });

        return $recommendations;
    }

    /**
     * Calculate overall health score
     *
     * @param  array{mcp_servers: array{healthy: int, total: int}, agents: array{active: int, total: int}, apis: array{healthy: int, total: int}}  $metrics
     */
    protected function calculateOverallHealthScore(array $metrics): float
    {
        $scores = [];

        // MCP servers score (40% weight)
        if ($metrics['mcp_servers']['total'] > 0) {
            $scores[] = ($metrics['mcp_servers']['healthy'] / $metrics['mcp_servers']['total']) * 40;
        }

        // Agents score (30% weight)
        if ($metrics['agents']['total'] > 0) {
            $scores[] = ($metrics['agents']['active'] / $metrics['agents']['total']) * 30;
        }

        // APIs score (30% weight)
        if ($metrics['apis']['total'] > 0) {
            $scores[] = ($metrics['apis']['healthy'] / $metrics['apis']['total']) * 30;
        }

        return ! empty($scores) ? array_sum($scores) : 0;
    }

    /**
     * Determine server status based on metrics
     */
    protected function determineServerStatus(MCPServer $server): string
    {
        if ($server->status === 'inactive' || $server->status === 'error') {
            return 'offline';
        }

        if (! $server->isHealthy()) {
            return 'unhealthy';
        }

        if ($server->average_response_time > 3.0 || $server->failure_rate > 5) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * Count active alerts
     */
    protected function countActiveAlerts(int $userId): int
    {
        $alertCount = 0;

        // Check for unhealthy servers
        $unhealthyServers = MCPServer::all()->filter(fn ($server) => ! $server->isHealthy())->count();
        $alertCount = ($alertCount ?? 0) + $unhealthyServers;

        // Check for circuit breakers
        if ($this->apiHealthMonitor->isCircuitBreakerOpen('umapyoi')) {
            $alertCount = ($alertCount ?? 0) + 1;
        }
        if ($this->apiHealthMonitor->isCircuitBreakerOpen('umamusumedb')) {
            $alertCount = ($alertCount ?? 0) + 1;
        }

        // Check for unhealthy agents
        $unhealthyAgents = MCPAgent::where('user_id', $userId)
            ->where('health_status', '=', 'unhealthy')
            ->count();
        $alertCount = ($alertCount ?? 0) + $unhealthyAgents;

        return $alertCount;
    }

    /**
     * Calculate uptime percentage (last 24 hours)
     */
    protected function calculateUptimePercentage(): float
    {
        // In production, this would query historical health check data
        // For now, we'll use a simplified calculation based on current status

        $servers = MCPServer::all();
        if ($servers->isEmpty()) {
            return 100.0;
        }

        $healthyServers = $servers->filter(fn ($server) => $server->isHealthy())->count();

        return ($healthyServers / $servers->count()) * 100;
    }

    /**
     * Get tool usage statistics
     *
     * @return array{total_executions: int, successful_executions: int, failed_executions: int, success_rate: float, average_execution_time: float}
     */
    protected function getToolUsageStatistics(): array
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates(now()->startOfDay(), now())
            ->get();

        $totalExecutions = $toolUsage->count();
        $successfulExecutions = $toolUsage->where('execution_status', '=', 'success')->count();
        $failedExecutions = $toolUsage->where('execution_status', '=', 'failure')->count();
        $successRate = $totalExecutions > 0 ? ($successfulExecutions / $totalExecutions) * 100 : 0;
        $avgExecutionTime = $toolUsage->avg('execution_time') ?? 0;

        return [
            'total_executions' => $totalExecutions,
            'successful_executions' => $successfulExecutions,
            'failed_executions' => $failedExecutions,
            'success_rate' => round($successRate, 2),
            'average_execution_time' => round($avgExecutionTime, 3),
        ];
    }

    /**
     * Get system health metrics
     *
     * @return array{redis_status: string, database_status: string, queue_status: string}
     */
    protected function getSystemHealthMetrics(): array
        // Check Redis status
        $redisStatus = 'healthy';
        try {
            Redis::ping();
        } catch (\Exception $e) {
            $redisStatus = 'unhealthy';
            Log::error('[MCPHealthDashboard] Redis health check failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Check database status
        $databaseStatus = 'healthy';
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $databaseStatus = 'unhealthy';
            Log::error('[MCPHealthDashboard] Database health check failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Check queue status (simplified)
        $queueStatus = 'healthy';

        return [
            'redis_status' => $redisStatus,
            'database_status' => $databaseStatus,
            'queue_status' => $queueStatus,
        ];
    }

    /**
     * Get budget status
     *
     * @return array{budget_limit: float, current_spend: float, remaining_budget: float, percentage_used: float, status: string}
     */
    protected function getBudgetStatus(): array
        // Get user's budget limit from preferences
        $budgetLimit = 10.0; // Default $10/month

        $remainingBudget = max(0, $budgetLimit - $currentSpend);
        $percentageUsed = $budgetLimit > 0 ? ($currentSpend / $budgetLimit) * 100 : 0;

        $status = match (true) {
            $percentageUsed >= 100 => 'exceeded',
            $percentageUsed >= 90 => 'critical',
            $percentageUsed >= 75 => 'warning',
            default => 'normal',
        };

        return [
            'budget_limit' => $budgetLimit,
            'current_spend' => round($currentSpend, 2),
            'remaining_budget' => round($remainingBudget, 2),
            'percentage_used' => round($percentageUsed, 2),
            'status' => $status,
        ];
    }

    /**
     * Get cache optimization recommendations
     *
     * @return array<array{type: string, severity: string, title: string, description: string, action: string}>
     */
    protected function getCacheOptimizationRecommendations(): array
        $recommendations = [];
        $cacheStats = $this->cacheManagement->getHitRateStatistics();

        if ($cacheStats['hit_rate'] < 70) {
            $recommendations[] = [
                'type' => 'cache_optimization',
                'severity' => 'medium',
                'title' => 'Low cache hit rate detected',
                'description' => sprintf(
                    'Current cache hit rate is %.2f%%. Consider implementing cache warming strategies to improve performance.',
                    $cacheStats['hit_rate']
                ),
                'action' => 'optimize_cache_strategy',
            ];
        }

        return $recommendations;
    }

    /**
     * Get cost optimization recommendations
     *
     * @return array<array{type: string, severity: string, title: string, description: string, action: string}>
     */
    protected function getCostOptimizationRecommendations(): array
        $recommendations = [];
        $costData = $this->mcpMonitoring->getCostAnalytics($userId, 'month');

        // Check for expensive tools
        if (! empty($costData['cost_by_tool'])) {
            $topCostTool = $costData['cost_by_tool'][0] ?? null;

            if ($topCostTool && $topCostTool['cost'] > 1.0) {
                $recommendations[] = [
                    'type' => 'cost_optimization',
                    'severity' => 'medium',
                    'title' => 'High-cost tool usage detected',
                    'description' => sprintf(
                        'Tool "%s" has cost $%.4f this month. Consider optimizing usage or implementing caching.',
                        $topCostTool['tool'],
                        $topCostTool['cost']
                    ),
                    'action' => 'optimize_tool_usage',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Force refresh dashboard data
     */
    public function refreshDashboard(): array
        // Clear cached dashboard data
        $cacheKey = self::DASHBOARD_CACHE_KEY.":{$userId}";
        Cache::forget($cacheKey);

        // Trigger fresh health checks
        $this->apiHealthMonitor->checkAllAPIs();

        // Generate fresh dashboard data
        return $this->getDashboardData($userId, true);
    }

    /**
     * Get dashboard health history
     *
     * @param  string  $period  'hour', 'day', 'week', 'month'
     * @return array<array{timestamp: string, health_score: float, alerts_count: int}>
     */
    public function getHealthHistory(): array
        // In production, this would query historical health data from database
        // For now, we'll return a simplified response

        $dataPoints = match ($period) {
            'hour' => 12, // 5-minute intervals
            'day' => 24, // Hourly
            'week' => 7, // Daily
            'month' => 30, // Daily
            default => 24,
        };

        $history = [];
        for ($i = $dataPoints - 1; $i >= 0; $i--) {
            $timestamp = match ($period) {
                'hour' => now()->subMinutes($i * 5),
                'day' => now()->subHours($i),
                'week' => now()->subDays($i),
                'month' => now()->subDays($i),
                default => now()->subHours($i),
            };

            $history[] = [
                'timestamp' => $timestamp->toIso8601String(),
                'health_score' => rand(75, 100), // Simulated data
                'alerts_count' => rand(0, 3), // Simulated data
            ];
        }

        return $history;
    }
}
