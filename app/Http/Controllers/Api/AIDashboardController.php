<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\AIDashboardService;
use App\Services\AI\ConversationHistoryService;
use App\Services\AI\CostTrackingService;
use App\Services\MCP\MCPMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AI Dashboard Controller
 *
 * Provides API endpoints for the AI Management Dashboard.
 * Handles requests for metrics, server status, performance comparison, costs, and conversations.
 *
 * Requirements: 56.4, 57.5, 13.5
 */
class AIDashboardController extends Controller
{
    protected AIDashboardService $dashboardService;

    protected MCPMonitoringService $mcpMonitoring;

    protected CostTrackingService $costTracking;

    protected ConversationHistoryService $conversationHistory;

    public function __construct(
        AIDashboardService $dashboardService,
        MCPMonitoringService $mcpMonitoring,
        CostTrackingService $costTracking,
        ConversationHistoryService $conversationHistory
    ) {
        $this->dashboardService = $dashboardService;
        $this->mcpMonitoring = $mcpMonitoring;
        $this->costTracking = $costTracking;
        $this->conversationHistory = $conversationHistory;
    }

    /**
     * Get comprehensive dashboard overview
     */
    public function overview(): JsonResponse
    {
        try {
            $overview = $this->dashboardService->getDashboardOverview();

            return response()->json([
                'success' => true,
                'data' => $overview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard overview',
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
            $servers = $this->dashboardService->getServerStatus();

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
     * Get AI provider performance comparison
     */
    public function performance(): JsonResponse
    {
        try {
            $performance = $this->dashboardService->getPerformanceComparison();

            return response()->json([
                'success' => true,
                'data' => $performance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch performance comparison',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cost tracking and budget status
     */
    public function costs(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', '30d');
            $userId = $request->user()?->id;

            $costs = $this->dashboardService->getCostSummary();

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
     * Get agents summary
     */
    public function agents(): JsonResponse
    {
        try {
            $agents = $this->dashboardService->getAgentsSummary();

            return response()->json([
                'success' => true,
                'data' => $agents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agents data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function conversations(Request $request): JsonResponse
    {
        try {
            $filters = [];
            $characterId = $request->query('character_id');
            if (is_string($characterId) && ctype_digit($characterId)) {
                $filters['character_id'] = (int) $characterId;
            }

            $conversationId = $request->query('conversation_id');
            if (is_string($conversationId) && $conversationId !== '') {
                $filters['conversation_id'] = $conversationId;
            }

            $provider = $request->query('provider');
            if (is_string($provider) && $provider !== '') {
                $filters['provider'] = $provider;
            }

            $model = $request->query('model');
            if (is_string($model) && $model !== '') {
                $filters['model'] = $model;
            }

            $dateFrom = $request->query('date_from');
            if (is_string($dateFrom) && $dateFrom !== '') {
                $filters['date_from'] = $dateFrom;
            }

            $dateTo = $request->query('date_to');
            if (is_string($dateTo) && $dateTo !== '') {
                $filters['date_to'] = $dateTo;
            }

            $search = $request->query('search');
            if (is_string($search) && $search !== '') {
                $filters['search'] = $search;
            }

            $limit = $request->query('limit');
            if (is_string($limit) && ctype_digit($limit)) {
                $filters['limit'] = (int) $limit;
            }

            $offset = $request->query('offset');
            if (is_string($offset) && ctype_digit($offset)) {
                $filters['offset'] = (int) $offset;
            }

            $conversations = $this->conversationHistory->getConversations($filters);

            return response()->json([
                'success' => true,
                'data' => $conversations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation analytics
     */
    public function conversationAnalytics(Request $request): JsonResponse
    {
        try {
            $filters = [
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ];

            // Remove null values
            $filters = array_filter($filters, fn ($value) => $value !== null);

            $analytics = $this->conversationHistory->getConversationAnalytics($filters);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversation analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get server health history
     */
    public function serverHealth(Request $request, string $serverName): JsonResponse
    {
        try {
            $hours = (int) $request->query('hours', 24);

            $history = $this->mcpMonitoring->getServerHealthHistory($serverName, $hours);
            $stats = $this->mcpMonitoring->getServerUptimeStats($serverName, $hours);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                    'stats' => $stats,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch server health history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cost optimization recommendations
     */
    public function costOptimization(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id;

            $recommendations = $this->costTracking->getCostOptimizationRecommendations($userId);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cost optimization recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get daily cost trend
     */
    public function costTrend(Request $request): JsonResponse
    {
        try {
            $days = (int) $request->query('days', 30);
            $userId = $request->user()?->id;

            $trend = $this->costTracking->getDailyCostTrend($days, $userId);

            return response()->json([
                'success' => true,
                'data' => $trend,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cost trend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export conversations
     */
    public function exportConversations(Request $request): JsonResponse
    {
        try {
            $format = $request->query('format', 'json');

            $filters = [
                'character_id' => $request->query('character_id'),
                'conversation_id' => $request->query('conversation_id'),
                'provider' => $request->query('provider'),
                'model' => $request->query('model'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->query('search'),
            ];

            // Remove null values
            $filters = array_filter($filters, fn ($value) => $value !== null);

            $export = match ($format) {
                'csv' => $this->conversationHistory->exportToCsv($filters),
                default => $this->conversationHistory->exportToJson($filters),
            };

            $filename = 'conversations_export_'.now()->format('Y-m-d_His').".{$format}";

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $export,
                    'filename' => $filename,
                    'format' => $format,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export conversations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
