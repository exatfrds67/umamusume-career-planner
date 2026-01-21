<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\WarmCacheJob;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cache Monitoring Controller
 *
 * Provides API endpoints for monitoring and managing cache warming operations.
 *
 * Endpoints:
 * - GET /api/cache/statistics - Get cache statistics
 * - GET /api/cache/warming/statistics - Get warming statistics
 * - POST /api/cache/warm - Trigger cache warming
 * - GET /api/cache/info - Get comprehensive cache information
 *
 * Requirements: 14.2, 14.5 (Intelligent Caching and Performance Monitoring)
 * Task: 2.1.2
 */
class CacheMonitoringController extends Controller
{
    public function __construct(
        private CacheManagerService $cacheManager
    ) {}

    /**
     * Get cache statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->cacheManager->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get cache warming statistics
     */
    public function warmingStatistics(): JsonResponse
    {
        $stats = $this->cacheManager->getWarmingStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Trigger cache warming
     */
    public function warm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'sometimes|string|in:high,medium,low,all',
            'async' => 'sometimes|boolean',
        ]);

        $priority = $validated['priority'] ?? 'all';
        $async = $validated['async'] ?? true;

        if ($async) {
            // Dispatch background job
            WarmCacheJob::dispatch($priority);

            return response()->json([
                'success' => true,
                'message' => 'Cache warming job dispatched',
                'data' => [
                    'priority' => $priority,
                    'mode' => 'async',
                ],
            ]);
        }

        // Run synchronously
        $result = $this->cacheManager->warmCache($priority);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Cache warming completed successfully'
                : 'Cache warming completed with failures',
            'data' => $result,
        ]);
    }

    /**
     * Get comprehensive cache information
     */
    public function info(): JsonResponse
    {
        $info = $this->cacheManager->getCacheInfo();
        $warmingStats = $this->cacheManager->getWarmingStatistics();
        $cacheSize = $this->cacheManager->getCacheSize();

        return response()->json([
            'success' => true,
            'data' => [
                'cache_info' => $info,
                'warming_statistics' => $warmingStats,
                'cache_size' => $cacheSize,
            ],
        ]);
    }

    /**
     * Get cache size information
     */
    public function size(): JsonResponse
    {
        $size = $this->cacheManager->getCacheSize();

        return response()->json([
            'success' => true,
            'data' => $size,
        ]);
    }

    /**
     * Get cached keys
     */
    public function keys(): JsonResponse
    {
        $keys = $this->cacheManager->getCachedKeys();

        return response()->json([
            'success' => true,
            'data' => [
                'keys' => $keys,
                'count' => count($keys),
            ],
        ]);
    }
}
