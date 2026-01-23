<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CacheManagementService;
use App\Services\ExternalAPI\ExternalAPIFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cache Management API Controller
 *
 * Provides endpoints for cache management, performance monitoring,
 * and cost optimization using MCP integration.
 *
 * Requirements: 14.5, 55.3, 56.4, Task 4.4.2
 */
class CacheManagementController extends Controller
{
    public function __construct(
        protected CacheManagementService $cacheManager,
        protected ExternalAPIFacade $apiFacade
    ) {}

    /**
     * Get cache statistics and hit rates
     *
     * GET /api/cache/statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = $this->cacheManager->getHitRateStatistics();
        $detailed = $this->cacheManager->getDetailedMetrics();

        return response()->json([
            'success' => true,
            'data' => [
                'overall' => $stats,
                'by_key' => $detailed,
            ],
        ]);
    }

    /**
     * Get API response time statistics
     *
     * GET /api/cache/api-performance
     */
    public function getApiPerformance(Request $request): JsonResponse
    {
        $apiName = $request->query('api', 'umapyoi_characters');

        $stats = $this->cacheManager->getApiResponseTimeStats($apiName);

        return response()->json([
            'success' => true,
            'data' => [
                'api' => $apiName,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Get cost-optimized caching strategy
     *
     * POST /api/cache/optimize-strategy
     */
    public function getOptimizedStrategy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data_type' => 'required|string',
            'estimated_size' => 'required|integer|min:0',
        ]);

        $strategy = $this->cacheManager->getCostOptimizedStrategy(
            $validated['data_type'],
            $validated['estimated_size']
        );

        return response()->json([
            'success' => true,
            'data' => $strategy,
        ]);
    }

    /**
     * Warm specific caches
     *
     * POST /api/cache/warm
     */
    public function warmCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'types' => 'required|array',
            'types.*' => 'string|in:characters,support_cards,meta',
            'force' => 'boolean',
        ]);

        $types = $validated['types'];
        $force = $validated['force'] ?? false;
        $results = [];

        foreach ($types as $type) {
            $result = match ($type) {
                'characters' => $this->apiFacade->getCharacters($force),
                'support_cards' => $this->apiFacade->getSupportCards($force),
                'meta' => $this->apiFacade->getMetaTierRankings($force),
                default => ['success' => false, 'error' => 'Unknown type'],
            };

            $results[$type] = [
                'success' => $result['success'],
                'count' => count($result['data'] ?? []),
                'source' => $result['source'] ?? 'unknown',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Invalidate specific caches
     *
     * POST /api/cache/invalidate
     */
    public function invalidateCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keys' => 'array',
            'keys.*' => 'string',
            'pattern' => 'string',
            'reason' => 'string',
        ]);

        if (isset($validated['pattern'])) {
            $invalidated = $this->cacheManager->invalidateByPattern($validated['pattern']);

            return response()->json([
                'success' => true,
                'data' => [
                    'invalidated' => $invalidated,
                    'pattern' => $validated['pattern'],
                ],
            ]);
        }

        if (isset($validated['keys'])) {
            $this->cacheManager->invalidate(
                $validated['keys'],
                $validated['reason'] ?? 'manual'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'invalidated' => count($validated['keys']),
                    'keys' => $validated['keys'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Either keys or pattern must be provided',
        ], 400);
    }

    /**
     * Clear all caches
     *
     * POST /api/cache/clear-all
     */
    public function clearAll(): JsonResponse
    {
        $this->cacheManager->clearAll();
        $this->apiFacade->clearAllCaches();

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully',
        ]);
    }

    /**
     * Get comprehensive cache health status
     *
     * GET /api/cache/health
     */
    public function getHealth(): JsonResponse
    {
        $stats = $this->cacheManager->getHitRateStatistics();
        $apiHealth = $this->apiFacade->getHealthStatus();

        $health = [
            'cache_healthy' => $stats['hit_rate'] >= 70, // 70% hit rate threshold
            'hit_rate' => $stats['hit_rate'],
            'avg_response_time_ms' => $stats['avg_response_time_ms'],
            'api_status' => $apiHealth,
        ];

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }
}
