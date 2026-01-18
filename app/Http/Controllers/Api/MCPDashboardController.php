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
                $user->preferences()->updateOrCreate(
                    ['user_id' => $user->id, 'category' => 'mcp'],
                    ['settings' => json_encode($validated)]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
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
     */
    protected function getOverviewMetrics(): array
    {
        $servers = $this->mcpMonitoring->getAllServersStatus();
        $healthyServers = collect($servers)->where('status', 'healthy')->count();

        $agents = $this->agentLifecycle->getActiveAgents();
        $activeAgents = collect($agents)->where('status', 'active')->count();

        $costs = $this->costManagement->getCostSummary('24h');

        return [
            'total_servers' => count($servers),
            'healthy_servers' => $healthyServers,
            'active_agents' => $activeAgents,
            'active_workflows' => collect($agents)->where('status', 'active')->sum('workflow_steps'),
            'cost_24h' => $costs['daily_cost'] ?? 0,
            'requests_24h' => $costs['daily_requests'] ?? 0,
            'avg_response_time' => $this->calculateAverageResponseTime(),
            'p95_response_time' => $this->calculateP95ResponseTime(),
        ];
    }

    /**
     * Get server status for all MCP servers
     */
    protected function getServerStatus(): array
    {
        return $this->mcpMonitoring->getAllServersStatus();
    }

    /**
     * Get active agents with their current status
     */
    protected function getActiveAgents(): array
    {
        return $this->agentLifecycle->getActiveAgents();
    }

    /**
     * Get cost summary with transparency data
     */
    protected function getCostSummary(): array
    {
        $dailyCosts = $this->costManagement->getCostSummary('24h');
        $weeklyCosts = $this->costManagement->getCostSummary('7d');
        $monthlyCosts = $this->costManagement->getCostSummary('30d');

        $budgetStatus = $this->costManagement->getBudgetStatus();
        $costsByProvider = $this->costManagement->getCostsByProvider('30d');
        $topTools = $this->costManagement->getTopToolsByCost(10);
        $recommendations = $this->costManagement->getCostOptimizationRecommendations();

        return [
            'daily_cost' => $dailyCosts['total_cost'] ?? 0,
            'daily_requests' => $dailyCosts['total_requests'] ?? 0,
            'weekly_cost' => $weeklyCosts['total_cost'] ?? 0,
            'weekly_requests' => $weeklyCosts['total_requests'] ?? 0,
            'monthly_cost' => $monthlyCosts['total_cost'] ?? 0,
            'monthly_requests' => $monthlyCosts['total_requests'] ?? 0,
            'budget_status' => $budgetStatus,
            'by_provider' => $costsByProvider,
            'top_tools' => $topTools,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get performance metrics for all providers
     */
    protected function getPerformanceMetrics(string $range = '24h'): array
    {
        $providers = $this->mcpMonitoring->getProviderPerformanceComparison($range);

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

        if ($preferences && $preferences->settings) {
            return array_merge($defaultSettings, json_decode($preferences->settings, true));
        }

        return $defaultSettings;
    }

    /**
     * Get default server settings
     */
    protected function getDefaultServerSettings(): array
    {
        $servers = $this->mcpMonitoring->getAllServersStatus();
        $settings = [];

        foreach ($servers as $name => $server) {
            $settings[$name] = [
                'name' => $server['name'],
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
        $providers = $this->mcpMonitoring->getProviderPerformanceComparison('24h');

        if (empty($providers)) {
            return 0.0;
        }

        $totalTime = collect($providers)->sum('avg_response_time');
        $count = count($providers);

        return $count > 0 ? $totalTime / $count : 0.0;
    }

    /**
     * Calculate P95 response time across all providers
     */
    protected function calculateP95ResponseTime(): float
    {
        $providers = $this->mcpMonitoring->getProviderPerformanceComparison('24h');

        if (empty($providers)) {
            return 0.0;
        }

        $p95Times = collect($providers)->pluck('p95_response_time')->filter();

        return $p95Times->isNotEmpty() ? $p95Times->avg() : 0.0;
    }

    /**
     * Generate performance recommendations
     */
    protected function generatePerformanceRecommendations(array $providers): array
    {
        $recommendations = [];

        foreach ($providers as $name => $provider) {
            // Check for slow response times
            if ($provider['avg_response_time'] > 5.0) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => "{$provider['name']} has slow average response time ({$provider['avg_response_time']}s). Consider using a faster provider for time-sensitive tasks.",
                ];
            }

            // Check for low success rates
            if ($provider['success_rate'] < 90) {
                $recommendations[] = [
                    'type' => 'error',
                    'message' => "{$provider['name']} has low success rate ({$provider['success_rate']}%). Investigate connection issues or consider disabling this provider.",
                ];
            }

            // Check for high costs
            if ($provider['cost_per_request'] > 0.01) {
                $recommendations[] = [
                    'type' => 'info',
                    'message' => "{$provider['name']} has high cost per request (\${$provider['cost_per_request']}). Consider using a more cost-effective provider for routine tasks.",
                ];
            }
        }

        return $recommendations;
    }
}
