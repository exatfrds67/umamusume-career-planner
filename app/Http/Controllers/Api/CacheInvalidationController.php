<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Cache Invalidation Controller
 *
 * Provides API endpoints for manual cache invalidation and management.
 *
 * Endpoints:
 * - POST /api/cache/invalidate/pattern - Invalidate by pattern
 * - POST /api/cache/invalidate/type - Invalidate by data type
 * - POST /api/cache/invalidate/keys - Invalidate specific keys
 * - POST /api/cache/invalidate/stale - Invalidate stale entries
 * - POST /api/cache/flush - Flush all cache
 * - GET /api/cache/info - Get cache information
 * - GET /api/cache/version - Get game version
 * - POST /api/cache/version - Set game version
 * - GET /api/cache/staleness - Check cache staleness
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.3
 */
class CacheInvalidationController extends Controller
{
    public function __construct(
        private CacheManagerService $cacheManager
    ) {}

    /**
     * Invalidate cache by pattern
     *
     * @param  Request  $request  {pattern: string}
     */
    public function invalidateByPattern(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pattern' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->cacheManager->invalidateByPattern($validated['pattern']);

        Log::info('[CacheInvalidationController] Cache invalidated by pattern', [
            'pattern' => $validated['pattern'],
            'result' => $result,
            'user_id' => auth()->id(),
        ]);

        $statusCode = $result['success'] ? 200 : 500;
        $message = $result['success']
            ? "Successfully invalidated {$result['invalidated_count']} cache entries"
            : ($result['error'] ?? 'Failed to invalidate cache');

        return response()->json([
            'success' => $result['success'],
            'message' => $message,
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Invalidate cache by data type
     *
     * @param  Request  $request  {data_type: string}
     */
    public function invalidateByType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data_type' => [
                'required',
                'string',
                'in:character_data,support_cards,meta_rankings,race_data,skills,news,game_mechanics',
            ],
        ]);

        $result = $this->cacheManager->invalidateByType($validated['data_type']);

        Log::info('[CacheInvalidationController] Cache invalidated by type', [
            'data_type' => $validated['data_type'],
            'result' => $result,
            'user_id' => auth()->id(),
        ]);

        $statusCode = $result['success'] ? 200 : 500;
        $message = $result['success']
            ? "Successfully invalidated {$result['invalidated_count']} {$validated['data_type']} entries"
            : ($result['error'] ?? 'Failed to invalidate cache');

        return response()->json([
            'success' => $result['success'],
            'message' => $message,
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Invalidate specific cache keys
     *
     * @param  Request  $request  {keys: array<string>}
     */
    public function invalidateKeys(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keys' => ['required', 'array', 'min:1'],
            'keys.*' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->cacheManager->invalidateKeys($validated['keys']);

        Log::info('[CacheInvalidationController] Cache keys invalidated', [
            'keys_count' => count($validated['keys']),
            'result' => $result,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? "Successfully invalidated {$result['invalidated_count']} cache keys"
                : "Failed to invalidate some cache keys ({$result['invalidated_count']} succeeded)",
            'data' => $result,
        ], $result['success'] ? 200 : 207); // 207 Multi-Status if partial success
    }

    /**
     * Invalidate stale cache entries
     */
    public function invalidateStale(): JsonResponse
    {
        $result = $this->cacheManager->invalidateStaleCache();

        Log::info('[CacheInvalidationController] Stale cache invalidated', [
            'result' => $result,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => "Successfully invalidated {$result['total_invalidated']} stale cache entries",
            'data' => $result,
        ]);
    }

    /**
     * Flush all external API cache
     */
    public function flush(): JsonResponse
    {
        $result = $this->cacheManager->flush();

        Log::warning('[CacheInvalidationController] Cache flushed', [
            'success' => $result,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => $result,
            'message' => $result
                ? 'Successfully flushed all external API cache'
                : 'Failed to flush cache',
        ], $result ? 200 : 500);
    }

    /**
     * Get cache information
     */
    public function info(): JsonResponse
    {
        $info = $this->cacheManager->getCacheInfo();
        $size = $this->cacheManager->getCacheSize();
        $keys = $this->cacheManager->getCachedKeys();
        $warmingStats = $this->cacheManager->getWarmingStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'info' => $info,
                'size' => $size,
                'cached_keys_count' => count($keys),
                'warming_statistics' => $warmingStats,
            ],
        ]);
    }

    /**
     * Get current game version
     */
    public function getVersion(): JsonResponse
    {
        $version = $this->cacheManager->getGameVersion();
        $history = $this->cacheManager->getVersionHistory();

        return response()->json([
            'success' => true,
            'data' => [
                'current_version' => $version,
                'version_history' => $history,
            ],
        ]);
    }

    /**
     * Set game version (triggers automatic cache invalidation if changed)
     *
     * @param  Request  $request  {version: string}
     */
    public function setVersion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:50'],
        ]);

        $result = $this->cacheManager->setGameVersion($validated['version']);

        Log::info('[CacheInvalidationController] Game version updated', [
            'result' => $result,
            'user_id' => auth()->id(),
        ]);

        $message = $result['version_changed']
            ? "Game version updated from {$result['previous_version']} to {$result['new_version']}. Invalidated cache for: ".implode(', ', $result['invalidated_types'])
            : "Game version confirmed as {$result['new_version']} (no changes)";

        return response()->json([
            'success' => $result['success'],
            'message' => $message,
            'data' => $result,
        ]);
    }

    /**
     * Check cache staleness
     */
    public function checkStaleness(): JsonResponse
    {
        $result = $this->cacheManager->checkCacheStaleness();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
