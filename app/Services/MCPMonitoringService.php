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
     */
    public function getDashboardData(int $userId): array
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
     */
    public function getServerStatus(): array
    {
        $servers = MCPServer::all();

        return [
            'total' => $servers->count(),
            'active' => $servers->where('status', 'active')->count(),
            'inactive' => $servers->where('status', 'inactive')->count(),
            'error' => $servers->where('status', 'error')->count(),
            'servers' => $servers->map(function ($server) {
                return [
                    'id' => $server->id,
                    'name' => $server->server_name,
                    'type' => $server->server_type,
                    'status' => $server->status,
                    'health' => $server->isHealthy() ? 'healthy' : 'unhealthy',
                    'success_rate' => $server->success_rate,
                    'average_response_time' => $server->average_response_time,
                    'last_health_check' => $server->last_health_check?->toIso8601String(),
                    'consecutive_failures' => $server->consecutive_failures,
                    'total_requests' => $server->total_requests,
                ];
            })->values(),
        ];
    }

    /**
     * Get agent status information.
     */
    public function getAgentStatus(?int $userId = null): array
    {
        $query = MCPAgent::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $agents = $query->get();

        return [
            'total' => $agents->count(),
            'active' => $agents->where('status', 'active')->count(),
            'terminated' => $agents->where('status', 'terminated')->count(),
            'healthy' => $agents->where('health_status', 'healthy')->count(),
            'agents' => $agents->map(function ($agent) {
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
                    'last_health_check' => $agent->last_health_check?->toIso8601String(),
                ];
            })->values(),
        ];
    }

    /**
     * Get cost analytics.
     */
    public function getCostAnalytics(int $userId, ?string $period = 'month'): array
    {
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($startDate, now())
            ->get();

        $totalCost = $toolUsage->sum('cost_estimate');
        $totalTokens = $toolUsage->sum('tokens_used');

        // Cost by server
        $costByServer = $toolUsage->groupBy('server_name')->map(function ($items, $serverName) {
            return [
                'server' => $serverName,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
                'tokens' => $items->sum('tokens_used'),
            ];
        })->values();

        // Cost by tool
        $costByTool = $toolUsage->groupBy('tool_name')->map(function ($items, $toolName) {
            return [
                'tool' => $toolName,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
                'average_cost' => $items->avg('cost_estimate'),
            ];
        })->sortByDesc('cost')->take(10)->values();

        // Daily cost trend
        $dailyCosts = $toolUsage->groupBy(function ($item) {
            return $item->executed_at->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'date' => $date,
                'cost' => $items->sum('cost_estimate'),
                'requests' => $items->count(),
            ];
        })->values();

        return [
            'period' => $period,
            'total_cost' => round($totalCost, 6),
            'total_tokens' => $totalTokens,
            'total_requests' => $toolUsage->count(),
            'average_cost_per_request' => $toolUsage->count() > 0 ? round($totalCost / $toolUsage->count(), 6) : 0,
            'cost_by_server' => $costByServer,
            'cost_by_tool' => $costByTool,
            'daily_costs' => $dailyCosts,
        ];
    }

    /**
     * Get performance metrics.
     */
    public function getPerformanceMetrics(int $userId, ?string $period = 'day'): array
    {
        $startDate = match ($period) {
            'hour' => now()->subHour(),
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            default => now()->startOfDay(),
        };

        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($startDate, now())
            ->get();

        $successfulRequests = $toolUsage->where('execution_status', 'success')->count();
        $failedRequests = $toolUsage->where('execution_status', 'failure')->count();
        $totalRequests = $toolUsage->count();

        // Performance by server
        $performanceByServer = $toolUsage->groupBy('server_name')->map(function ($items, $serverName) {
            $successful = $items->where('execution_status', 'success')->count();
            $total = $items->count();

            return [
                'server' => $serverName,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'average_execution_time' => round($items->avg('execution_time'), 3),
                'total_requests' => $total,
            ];
        })->values();

        // Slowest tools
        $slowestTools = $toolUsage->sortByDesc('execution_time')->take(10)->map(function ($item) {
            return [
                'tool' => $item->tool_name,
                'server' => $item->server_name,
                'execution_time' => $item->execution_time,
                'executed_at' => $item->executed_at->toIso8601String(),
            ];
        })->values();

        return [
            'period' => $period,
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'average_execution_time' => round($toolUsage->avg('execution_time') ?? 0, 3),
            'performance_by_server' => $performanceByServer,
            'slowest_tools' => $slowestTools,
        ];
    }

    /**
     * Get optimization recommendations.
     */
    public function getOptimizationRecommendations(int $userId): array
    {
        $recommendations = [];

        // Check for underperforming servers
        $servers = MCPServer::all();
        foreach ($servers as $server) {
            if ($server->failure_rate > 10) {
                $recommendations[] = [
                    'type' => 'server_health',
                    'severity' => 'high',
                    'title' => "High failure rate on {$server->server_name}",
                    'description' => "Server {$server->server_name} has a failure rate of {$server->failure_rate}%. Consider investigating or restarting the server.",
                    'action' => 'restart_server',
                    'server_id' => $server->id,
                ];
            }

            if ($server->average_response_time > 5.0) {
                $recommendations[] = [
                    'type' => 'performance',
                    'severity' => 'medium',
                    'title' => "Slow response time on {$server->server_name}",
                    'description' => "Server {$server->server_name} has an average response time of {$server->average_response_time}s. Consider optimizing or scaling the server.",
                    'action' => 'optimize_server',
                    'server_id' => $server->id,
                ];
            }
        }

        // Check for cost optimization opportunities
        $costAnalytics = $this->getCostAnalytics($userId, 'month');
        if ($costAnalytics['total_cost'] > 10.0) {
            $topCostTools = collect($costAnalytics['cost_by_tool'])->take(3);
            $recommendations[] = [
                'type' => 'cost_optimization',
                'severity' => 'medium',
                'title' => 'High monthly costs detected',
                'description' => 'Your monthly MCP costs are $'.$costAnalytics['total_cost'].'. Top cost drivers: '.$topCostTools->pluck('tool')->implode(', '),
                'action' => 'review_usage',
            ];
        }

        // Check for inactive agents
        $inactiveAgents = MCPAgent::where('user_id', $userId)
            ->where('status', 'active')
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

        return $recommendations;
    }

    /**
     * Connect to an MCP server.
     */
    public function connectServer(int $serverId): array
    {
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
     */
    public function disconnectServer(int $serverId): array
    {
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
     */
    public function updateServerConfig(int $serverId, array $config): array
    {
        $server = MCPServer::findOrFail($serverId);

        $server->server_config = array_merge($server->server_config ?? [], $config);
        $server->save();

        return [
            'success' => true,
            'message' => "Successfully updated configuration for {$server->server_name}",
            'server' => $server,
        ];
    }

    /**
     * Create a new agent.
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
     */
    public function terminateAgent(int $agentId): array
    {
        $agent = MCPAgent::findOrFail($agentId);

        $agent->status = 'terminated';
        $agent->terminated_at = now();
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
