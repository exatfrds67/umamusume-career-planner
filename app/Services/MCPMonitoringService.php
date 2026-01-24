<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MCPAgent;
use App\Models\MCPServer;
use App\Models\MCPToolUsage;
use App\Models\UserPreference;
use Illuminate\Support\Collection;

class MCPMonitoringService
{
    /**
     * Get comprehensive MCP dashboard data.
     *
     * @param  int|null  $userId  The user ID to filter by (optional for server-level data)
     * @return array<string, mixed>
     */
    public function getDashboardData(?int $userId = null): array
    {
        return [
            'servers' => $this->getServerStatus(),
            'agents' => $this->getAgentStatus($userId),
            'costs' => $this->getCostAnalytics($userId),
            'performance' => $this->getPerformanceMetrics($userId),
            'recommendations' => $this->getOptimizationRecommendations($userId),
        ];
    }

    /**
     * Get server status information.
     *
     * @return array<string, mixed>
     */
    public function getServerStatus(): array
    {
        $servers = MCPServer::all();

        return [
            'total' => $servers->count(),
            'active' => $servers->where('status', '=', 'active')->count(),
            'inactive' => $servers->where('status', '=', 'inactive')->count(),
            'error' => $servers->where('status', '=', 'error')->count(),
            'servers' => $servers->map(function (MCPServer $server): array {
                $lastHealthCheck = $server->last_health_check;

                return [
                    'id' => $server->id,
                    'name' => $server->server_name,
                    'type' => $server->server_type,
                    'status' => $server->status,
                    'health' => $server->isHealthy() ? 'healthy' : 'unhealthy',
                    'success_rate' => $server->success_rate,
                    'average_response_time' => $server->average_response_time,
                    'last_health_check' => $lastHealthCheck instanceof \Carbon\Carbon ? $lastHealthCheck->toIso8601String() : null,
                    'consecutive_failures' => $server->consecutive_failures,
                    'total_requests' => $server->total_requests,
                ];
            })->values(),
        ];
    }

    /**
     * Get agent status information.
     *
     * @param  int|null  $userId  The user ID to filter by
     * @return array<string, mixed>
     */
    public function getAgentStatus(?int $userId = null): array
    {
        $query = MCPAgent::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $agents = $query->get();

        return [
            'total' => $agents->count(),
            'active' => $agents->where('status', '=', 'active')->count(),
            'terminated' => $agents->where('status', '=', 'terminated')->count(),
            'healthy' => $agents->where('health_status', '=', 'healthy')->count(),
            'agents' => $agents->map(function (MCPAgent $agent): array {
                $lastHealthCheck = $agent->last_health_check;

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
                    'performance_metrics' => $agent->performance_metrics,
                    'last_health_check' => $lastHealthCheck instanceof \Carbon\Carbon ? $lastHealthCheck->toIso8601String() : null,
                ];
            })->values(),
        ];
    }

    /**
     * Get cost analytics.
     *
     * @param  int|null  $userId  The user ID to filter by
     * @param  string  $period  The period for analytics (day, week, month, year)
     * @return array<string, mixed>
     */
    public function getCostAnalytics(?int $userId = null, string $period = 'month'): array
    {
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $query = MCPToolUsage::query();
        if ($userId !== null) {
            $query->forUser($userId);
        }
        $toolUsage = $query->betweenDates($startDate, now())->get();

        $totalCost = $toolUsage->sum('cost_estimate');
        $totalTokens = $toolUsage->sum('tokens_used');
        $floatTotalCost = is_numeric($totalCost) ? (float) $totalCost : 0.0;

        // Cost by server
        $costByServer = $toolUsage->groupBy('server_name')->map(function (Collection $items, int|string $serverName): array {
            return [
                'server' => (string) $serverName,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
                'tokens' => $items->sum('tokens_used'),
            ];
        })->values();

        // Cost by tool
        $costByTool = $toolUsage->groupBy('tool_name')->map(function (Collection $items, int|string $toolName): array {
            return [
                'tool' => (string) $toolName,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
                'average_cost' => $items->avg('cost_estimate'),
            ];
        })->sortByDesc('cost')->take(10)->values();

        // Daily cost trend
        $dailyCosts = $toolUsage->groupBy(function (MCPToolUsage $item): string {
            return $item->executed_at->format('Y-m-d');
        })->map(function (Collection $items, string $date): array {
            return [
                'date' => $date,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
            ];
        })->values();

        $totalRequestCount = $toolUsage->count();

        return [
            'period' => $period,
            'total_cost' => round($floatTotalCost, 6),
            'total_tokens' => $totalTokens,
            'total_requests' => $totalRequestCount,
            'average_cost_per_request' => $totalRequestCount > 0 ? round($floatTotalCost / $totalRequestCount, 6) : 0,
            'cost_by_server' => $costByServer,
            'cost_by_tool' => $costByTool,
            'daily_costs' => $dailyCosts,
        ];
    }

    /**
     * Get performance metrics.
     *
     * @param  int|null  $userId  The user ID to filter by
     * @param  string  $period  The period for metrics (hour, day, week)
     * @return array<string, mixed>
     */
    public function getPerformanceMetrics(?int $userId = null, string $period = 'day'): array
    {
        $startDate = match ($period) {
            'hour' => now()->subHour(),
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            default => now()->startOfDay(),
        };

        $query = MCPToolUsage::query();
        if ($userId !== null) {
            $query->forUser($userId);
        }
        $toolUsage = $query->betweenDates($startDate, now())->get();

        $successfulRequests = $toolUsage->where('execution_status', '=', 'success')->count();
        $failedRequests = $toolUsage->where('execution_status', '=', 'failure')->count();
        $totalRequests = $toolUsage->count();

        // Performance by server
        $performanceByServer = $toolUsage->groupBy('server_name')->map(function (Collection $items, int|string $serverName): array {
            $successful = $items->where('execution_status', '=', 'success')->count();
            $total = $items->count();
            $avgExecutionTime = $items->avg('execution_time');

            return [
                'server' => (string) $serverName,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'average_execution_time' => round(is_numeric($avgExecutionTime) ? (float) $avgExecutionTime : 0.0, 3),
                'total_requests' => $total,
            ];
        })->values();

        // Slowest tools
        $slowestTools = $toolUsage->sortByDesc('execution_time')->take(10)->map(function (MCPToolUsage $item): array {
            return [
                'tool' => $item->tool_name,
                'server' => $item->server_name,
                'execution_time' => $item->execution_time,
                'executed_at' => $item->executed_at->toIso8601String(),
            ];
        })->values();

        $avgExecutionTime = $toolUsage->avg('execution_time');

        return [
            'period' => $period,
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'average_execution_time' => round(is_numeric($avgExecutionTime) ? (float) $avgExecutionTime : 0.0, 3),
            'performance_by_server' => $performanceByServer,
            'slowest_tools' => $slowestTools,
        ];
    }

    /**
     * Get optimization recommendations.
     *
     * @param  int|null  $userId  The user ID to filter by
     * @return array<int, array<string, mixed>>
     */
    public function getOptimizationRecommendations(?int $userId = null): array
    {
        /** @var array<int, array<string, mixed>> $recommendations */
        $recommendations = [];

        // Check for underperforming servers
        $servers = MCPServer::all();
        foreach ($servers as $server) {
            $failureRate = $server->failure_rate;
            $serverName = (string) $server->server_name;
            $avgResponseTime = $server->average_response_time;

            if (is_numeric($failureRate) && $failureRate > 10) {
                $recommendations[] = [
                    'type' => 'server_health',
                    'severity' => 'high',
                    'title' => "High failure rate on {$serverName}",
                    'description' => "Server {$serverName} has a failure rate of {$failureRate}%. Consider investigating or restarting the server.",
                    'action' => 'restart_server',
                    'server_id' => $server->id,
                ];
            }

            if (is_numeric($avgResponseTime) && $avgResponseTime > 5.0) {
                $recommendations[] = [
                    'type' => 'performance',
                    'severity' => 'medium',
                    'title' => "Slow response time on {$serverName}",
                    'description' => "Server {$serverName} has an average response time of {$avgResponseTime}s. Consider optimizing or scaling the server.",
                    'action' => 'optimize_server',
                    'server_id' => $server->id,
                ];
            }
        }

        // Check for cost optimization opportunities
        $costAnalytics = $this->getCostAnalytics($userId, 'month');
        $totalCost = $costAnalytics['total_cost'];
        if (is_numeric($totalCost) && $totalCost > 10.0) {
            $costByTool = $costAnalytics['cost_by_tool'];
            /** @var Collection<int, array<string, mixed>> $topCostTools */
            $topCostTools = is_array($costByTool) ? collect($costByTool)->take(3) : collect([]);
            $recommendations[] = [
                'type' => 'cost_optimization',
                'severity' => 'medium',
                'title' => 'High monthly costs detected',
                'description' => 'Your monthly MCP costs are $'.$totalCost.'. Top cost drivers: '.$topCostTools->pluck('tool')->implode(', '),
                'action' => 'review_usage',
            ];
        }

        // Check for inactive agents
        if ($userId !== null) {
            $inactiveAgents = MCPAgent::where('user_id', $userId)
                ->where('status', '=', 'active')
                ->where('last_health_check', '<', now()->subHours(24)->toDateTimeString())
                ->count();

            if ($inactiveAgents > 0) {
                $recommendations[] = [
                    'type' => 'agent_lifecycle',
                    'severity' => 'low',
                    'title' => 'Inactive agents detected',
                    'description' => "You have {$inactiveAgents} agents that haven't been used in 24 hours. Consider terminating them to save resources.",
                    'action' => 'cleanup_agents',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Connect to an MCP server.
     *
     * @param  int  $serverId  The server ID to connect to
     * @return array<string, mixed>
     */
    public function connectServer(int $serverId): array
    {
        /** @var MCPServer $server */
        $server = MCPServer::findOrFail($serverId);

        try {
            // Simulate connection (in real implementation, this would connect to actual MCP server)
            $server->status = 'active';
            $server->last_health_check = now();
            $server->consecutive_failures = 0;
            $server->save();

            return [
                'success' => true,
                'message' => "Successfully connected to {$server->server_name}",
                'server' => $server,
            ];
        } catch (\Exception $e) {
            $server->recordFailure($e->getMessage());

            return [
                'success' => false,
                'message' => "Failed to connect to {$server->server_name}: {$e->getMessage()}",
                'server' => $server,
            ];
        }
    }

    /**
     * Disconnect from an MCP server.
     *
     * @param  int  $serverId  The server ID to disconnect from
     * @return array<string, mixed>
     */
    public function disconnectServer(int $serverId): array
    {
        /** @var MCPServer $server */
        $server = MCPServer::findOrFail($serverId);

        $server->status = 'inactive';
        $server->save();

        return [
            'success' => true,
            'message' => "Successfully disconnected from {$server->server_name}",
            'server' => $server,
        ];
    }

    /**
     * Update server configuration.
     *
     * @param  int  $serverId  The server ID to update
     * @param  array<string, mixed>  $config  The configuration to merge
     * @return array<string, mixed>
     */
    public function updateServerConfig(int $serverId, array $config): array
    {
        /** @var MCPServer $server */
        $server = MCPServer::findOrFail($serverId);

        $existingConfig = $server->server_config;
        $server->server_config = array_merge(is_array($existingConfig) ? $existingConfig : [], $config);
        $server->save();

        return [
            'success' => true,
            'message' => "Successfully updated configuration for {$server->server_name}",
            'server' => $server,
        ];
    }

    /**
     * Create a new agent.
     *
     * @param  int  $userId  The user ID to create the agent for
     * @param  array<string, mixed>  $agentData  The agent data
     */
    public function createAgent(int $userId, array $agentData): MCPAgent
    {
        return MCPAgent::create([
            'user_id' => $userId,
            'agent_id' => $agentData['agent_id'] ?? uniqid('agent_'),
            'name' => $agentData['name'],
            'type' => $agentData['type'],
            'model' => $agentData['model'],
            'instructions' => $agentData['instructions'],
            'tools' => $agentData['tools'] ?? [],
            'memory_config' => $agentData['memory_config'] ?? [],
            'guardrails' => $agentData['guardrails'] ?? [],
            'metadata' => $agentData['metadata'] ?? [],
            'status' => 'active',
            'health_status' => 'healthy',
            'deployment_time' => microtime(true),
        ]);
    }

    /**
     * Terminate an agent.
     *
     * @param  int  $agentId  The agent ID to terminate
     * @return array<string, mixed>
     */
    public function terminateAgent(int $agentId): array
    {
        /** @var MCPAgent $agent */
        $agent = MCPAgent::findOrFail($agentId);

        /** @phpstan-ignore assign.propertyType */
        $agent->status = 'terminated';
        $agent->terminated_at = now()->toDateTimeString();
        $agent->save();

        return [
            'success' => true,
            'message' => "Successfully terminated agent {$agent->name}",
            'agent' => $agent,
        ];
    }

    /**
     * Get user preferences for MCP configuration.
     */
    /**
     * @return Collection<int, UserPreference>
     */
    public function getUserPreferences(int $userId, string $category = 'mcp'): Collection
    {
        return UserPreference::forUser($userId)
            ->category($category)
            ->get();
    }

    /**
     * Update user preference.
     */
    public function updateUserPreference(int $userId, string $key, mixed $value, string $category = 'mcp'): UserPreference
    {
        $preference = UserPreference::forUser($userId)
            ->category($category)
            ->byKey($key)
            ->first();

        if ($preference) {
            $preference->setValue($value);
        } else {
            $preference = UserPreference::create([
                'user_id' => $userId,
                'preference_category' => $category,
                'preference_key' => $key,
                'preference_value' => $value,
                'value_type' => $this->detectValueType($value),
                'last_modified_at' => now(),
            ]);
        }

        return $preference;
    }

    /**
     * Detect value type for preference.
     */
    protected function detectValueType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => array_keys($value) !== range(0, count($value) - 1) ? 'object' : 'array',
            default => 'string',
        };
    }

    /**
     * Record tool usage.
     *
     * @param  array<string, mixed>  $data  The tool usage data to record
     */
    public function recordToolUsage(array $data): MCPToolUsage
    {
        return MCPToolUsage::create($data);
    }

    /**
     * Get tool usage history.
     */
    /**
     * @return Collection<int, MCPToolUsage>
     */
    public function getToolUsageHistory(int $userId, ?int $limit = 100): Collection
    {
        return MCPToolUsage::forUser($userId)
            ->orderBy('executed_at', 'desc')
            ->limit($limit ?? 100)
            ->get();
    }
}
