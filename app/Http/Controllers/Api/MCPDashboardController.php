<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MCP\AgentLifecycleManager;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\CostManagementService;
use App\Services\MCP\MCPMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MCP Dashboard Controller
 *
 * Provides API endpoints for the MCP Management Dashboard.
 * Handles MCP server status, agent activity, cost transparency, and performance metrics.
 *
 * Requirements: 13.5, 56.4, 57.5
 */
class MCPDashboardController extends Controller
{
    public function __construct(
        protected MCPMonitoringService $mcpMonitoring,
        protected AgentLifecycleManager $agentLifecycle,
        protected AgentOrchestrationService $agentOrchestration,
        protected CostManagementService $costManagement
    ) {}

    /**
     * Get comprehensive MCP dashboard overview
     */
    public function overview(): JsonResponse
    {
        try {
            $overview = [
                'overview' => $this->getOverviewMetrics(),
                'servers' => $this->getServerStatus(),
                'agents' => $this->getActiveAgents(),
                'costs' => $this->getCostSummary(),
                'performance' => $this->getPerformanceMetrics(),
                'settings' => $this->getUserSettings(),
            ];

            return response()->json([
                'success' => true,
                'data' => $overview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch MCP dashboard overview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get MCP server status
     */
    public function servers(): JsonResponse
    {
        try {
            $servers = $this->getServerStatus();

            return response()->json([
                'success' => true,
                'data' => $servers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch server status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active agents
     */
    public function agents(): JsonResponse
    {
        try {
            $agents = $this->getActiveAgents();

            return response()->json([
                'success' => true,
                'data' => $agents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cost summary and transparency data
     */
    public function costs(): JsonResponse
    {
        try {
            $costs = $this->getCostSummary();

            return response()->json([
                'success' => true,
                'data' => $costs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cost data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function performance(Request $request): JsonResponse
    {
        try {
            $range = $request->query('range', '24h');
            $performance = $this->getPerformanceMetrics($range);

            return response()->json([
                'success' => true,
                'data' => $performance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch performance metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user settings
     */
    public function settings(): JsonResponse
    {
        try {
            $settings = $this->getUserSettings();

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'servers' => 'sometimes|array',
                'agents' => 'sometimes|array',
                'budget' => 'sometimes|array',
                'performance' => 'sometimes|array',
            ]);

            // Update user preferences
            $user = $request->user();
            if ($user) {
                $user->userPreferences()->updateOrCreate(
                    [
                        'user_id' => $user->id ?? throw new \Exception('User required'),
                        'preference_category' => 'mcp',
                        'preference_key' => 'dashboard_settings',
                        'scope' => 'global',
                        'context_id' => null,
                    ],
                    [
                        'preference_value' => $validated,
                        'value_type' => 'object',
                        'last_modified_at' => now(),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get overview metrics
     *
     * @return array<string, mixed>
     */
    protected function getOverviewMetrics(): array
    {
        $healthCheck = $this->mcpMonitoring->performHealthCheck();
        $healthyServers = collect($healthCheck)->where('status', 'healthy')->count();

        $agents = $this->agentLifecycle->getActiveAgents();
        $activeAgents = \count($agents);

        $costs = $this->costManagement->getUsageAnalytics(1);

        return [
            'total_servers' => \count($healthCheck),
            'healthy_servers' => $healthyServers,
            'active_agents' => $activeAgents,
            'active_workflows' => collect($agents)->sum('workflow_steps'),
            'cost_24h' => $costs['total_cost'] ?? 0,
            'requests_24h' => $costs['total_requests'] ?? 0,
            'avg_response_time' => $this->calculateAverageResponseTime(),
            'p95_response_time' => $this->calculateP95ResponseTime(),
        ];
    }

    /**
     * Get server status for all MCP servers
     *
     * @return array<string, mixed>
     */
    protected function getServerStatus(): array
    {
        return $this->mcpMonitoring->performHealthCheck();
    }

    /**
     * Get active agents with their current status
     *
     * @return array<int, mixed>
     */
    protected function getActiveAgents(): array
    {
        return $this->agentLifecycle->getActiveAgents();
    }

    /**
     * Get cost summary with transparency data
     *
     * @return array<string, mixed>
     */
    protected function getCostSummary(): array
    {
        $dailyCosts = $this->costManagement->getUsageAnalytics(1);
        $weeklyCosts = $this->costManagement->getUsageAnalytics(7);
        $monthlyCosts = $this->costManagement->getUsageAnalytics(30);

        $budgetStatus = $this->costManagement->checkBudgetStatus();
        $costsByProvider = $this->costManagement->getCostBreakdownByProvider(30);
        $recommendations = $this->costManagement->getOptimizationRecommendations();

        return [
            'daily_cost' => $dailyCosts['total_cost'] ?? 0,
            'daily_requests' => $dailyCosts['total_requests'] ?? 0,
            'weekly_cost' => $weeklyCosts['total_cost'] ?? 0,
            'weekly_requests' => $weeklyCosts['total_requests'] ?? 0,
            'monthly_cost' => $monthlyCosts['total_cost'] ?? 0,
            'monthly_requests' => $monthlyCosts['total_requests'] ?? 0,
            'budget_status' => $budgetStatus,
            'by_provider' => $costsByProvider,
            'top_tools' => [],
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get performance metrics for all providers
     *
     * @return array<string, mixed>
     */
    protected function getPerformanceMetrics(string $range = '24h'): array
    {
        // Get cost breakdown by provider as a proxy for performance data
        $days = match ($range) {
            '1h' => 1,
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 1,
        };

        $providerBreakdown = $this->costManagement->getCostBreakdownByProvider($days);

        // Transform to performance metrics format
        $providers = [];
        foreach ($providerBreakdown as $name => $data) {
            $providers[$name] = [
                'name' => $name,
                'avg_response_time' => 0.0,
                'success_rate' => 100.0,
                'cost_per_request' => $data['avg_cost_per_request'] ?? 0.0,
                'total_requests' => $data['request_count'] ?? 0,
            ];
        }

        // Find best performers
        $fastest = collect($providers)->sortBy('avg_response_time')->first();
        $mostReliable = collect($providers)->sortByDesc('success_rate')->first();
        $mostCostEffective = collect($providers)->sortBy('cost_per_request')->first();

        $recommendations = $this->generatePerformanceRecommendations($providers);

        return [
            'providers' => $providers,
            'fastest' => $fastest,
            'most_reliable' => $mostReliable,
            'most_cost_effective' => $mostCostEffective,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get user settings
     *
     * @return array<string, mixed>
     */
    protected function getUserSettings(): array
    {
        $user = auth()->user();
        $preferences = $user?->preferences()->where('category', 'mcp')->first();

        $defaultSettings = [
            'servers' => $this->getDefaultServerSettings(),
            'agents' => [
                'training' => 'auto',
                'career' => 'auto',
                'race' => 'auto',
                'skill' => 'auto',
            ],
            'budget' => [
                'daily' => 1.00,
                'monthly' => 30.00,
                'alert_threshold' => 90,
            ],
            'performance' => [
                'auto_fallback' => true,
                'parallel_processing' => true,
                'cache_responses' => true,
            ],
        ];

        if ($preferences && isset($preferences->settings) && is_string($preferences->settings)) {
            $decoded = json_decode($preferences->settings, true);
            if (is_array($decoded)) {
                return array_merge($defaultSettings, $decoded);
            }
        }

        return $defaultSettings;
    }

    /**
     * Get default server settings
     *
     * @return array<string, mixed>
     */
    protected function getDefaultServerSettings(): array
    {
        $servers = $this->mcpMonitoring->performHealthCheck();
        $settings = [];

        foreach ($servers as $name => $server) {
            $settings[$name] = [
                'name' => $server['server_name'] ?? $name,
                'description' => $server['description'] ?? '',
                'enabled' => $server['is_connected'] ?? false,
            ];
        }

        return $settings;
    }

    /**
     * Calculate average response time across all providers
     */
    protected function calculateAverageResponseTime(): float
    {
        // Use monitoring dashboard data for response time
        $dashboard = $this->mcpMonitoring->getMonitoringDashboard();
        $servers = $dashboard['servers'] ?? [];

        if (empty($servers)) {
            return 0.0;
        }

        $responseTimes = collect($servers)->pluck('response_time')->filter();

        if ($responseTimes->isEmpty()) {
            return 0.0;
        }

        $average = $responseTimes->avg();

        return is_numeric($average) ? (float) $average : 0.0;
    }

    /**
     * Calculate P95 response time across all providers
     */
    protected function calculateP95ResponseTime(): float
    {
        // Use monitoring dashboard data for response time
        $dashboard = $this->mcpMonitoring->getMonitoringDashboard();
        $servers = $dashboard['servers'] ?? [];

        if (empty($servers)) {
            return 0.0;
        }

        $responseTimes = collect($servers)->pluck('response_time')->filter()->sort()->values();

        if ($responseTimes->isEmpty()) {
            return 0.0;
        }

        $p95Index = (int) ceil($responseTimes->count() * 0.95) - 1;
        $value = $responseTimes->get(max(0, $p95Index), 0.0);

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Generate performance recommendations
     *
     * @param  array<string, mixed>  $providers
     * @return array<int, array<string, string>>
     */
    protected function generatePerformanceRecommendations(array $providers): array
    {
        $recommendations = [];

        foreach ($providers as $name => $provider) {
            if (! is_array($provider)) {
                continue;
            }

            // Check for slow response times
            $avgResponseTime = $provider['avg_response_time'] ?? 0;
            if (is_numeric($avgResponseTime) && $avgResponseTime > 5.0) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => "{$name} has slow average response time ({$avgResponseTime}s). Consider using a faster provider for time-sensitive tasks.",
                ];
            }

            // Check for low success rates
            $successRate = $provider['success_rate'] ?? 100;
            if (is_numeric($successRate) && $successRate < 90) {
                $recommendations[] = [
                    'type' => 'error',
                    'message' => "{$name} has low success rate ({$successRate}%). Investigate connection issues or consider disabling this provider.",
                ];
            }

            // Check for high costs
            $costPerRequest = $provider['cost_per_request'] ?? 0;
            if (is_numeric($costPerRequest) && $costPerRequest > 0.01) {
                $recommendations[] = [
                    'type' => 'info',
                    'message' => "{$name} has high cost per request (\${$costPerRequest}). Consider using a more cost-effective provider for routine tasks.",
                ];
            }
        }

        return $recommendations;
    }
}
