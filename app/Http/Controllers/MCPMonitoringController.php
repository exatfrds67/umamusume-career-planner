<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MCPMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MCPMonitoringController extends Controller
{
    public function __construct(
        private readonly MCPMonitoringService $monitoringService
    ) {}

    /**
     * Get comprehensive MCP dashboard data.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $data = $this->monitoringService->getDashboardData($userId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get server status.
     */
    public function serverStatus(): JsonResponse
    {
        $data = $this->monitoringService->getServerStatus();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get agent status.
     */
    public function agentStatus(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $data = $this->monitoringService->getAgentStatus($userId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get cost analytics.
     */
    public function costAnalytics(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $period = $request->input('period', 'month');

        $data = $this->monitoringService->getCostAnalytics($userId, is_string($period) ? $period : null);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get performance metrics.
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $period = $request->input('period', 'day');

        $data = $this->monitoringService->getPerformanceMetrics($userId, is_string($period) ? $period : null);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get optimization recommendations.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $data = $this->monitoringService->getOptimizationRecommendations($userId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Connect to an MCP server.
     */
    public function connectServer(Request $request, int $serverId): JsonResponse
    {
        $result = $this->monitoringService->connectServer($serverId);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Disconnect from an MCP server.
     */
    public function disconnectServer(Request $request, int $serverId): JsonResponse
    {
        $result = $this->monitoringService->disconnectServer($serverId);

        return response()->json($result);
    }

    /**
     * Update server configuration.
     */
    public function updateServerConfig(Request $request, int $serverId): JsonResponse
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        $result = $this->monitoringService->updateServerConfig($serverId, $validated['config']);

        return response()->json($result);
    }

    /**
     * Create a new agent.
     */
    public function createAgent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'instructions' => 'required|string',
            'tools' => 'nullable|array',
            'memory_config' => 'nullable|array',
            'guardrails' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $agent = $this->monitoringService->createAgent($userId !== null ? (int) $userId : 0, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Agent created successfully',
            'data' => $agent,
        ], 201);
    }

    /**
     * Terminate an agent.
     */
    public function terminateAgent(Request $request, int $agentId): JsonResponse
    {
        $result = $this->monitoringService->terminateAgent($agentId);

        return response()->json($result);
    }

    /**
     * Get user preferences.
     */
    public function getUserPreferences(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $category = $request->input('category', 'mcp');

        $preferences = $this->monitoringService->getUserPreferences(
            $userId !== null ? (int) $userId : 0,
            is_string($category) ? $category : 'mcp'
        );

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Update user preference.
     */
    public function updateUserPreference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
            'category' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $preference = $this->monitoringService->updateUserPreference(
            $userId !== null ? (int) $userId : 0,
            $validated['key'],
            $validated['value'],
            $validated['category'] ?? 'mcp'
        );

        return response()->json([
            'success' => true,
            'message' => 'Preference updated successfully',
            'data' => $preference,
        ]);
    }

    /**
     * Get tool usage history.
     */
    public function toolUsageHistory(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $limitInput = $request->input('limit', 100);
        $limit = is_numeric($limitInput) ? (is_numeric($limit) ? (int) $limit : 0)Input : 100;

        $history = $this->monitoringService->getToolUsageHistory(
            $userId !== null ? (int) $userId : 0,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
