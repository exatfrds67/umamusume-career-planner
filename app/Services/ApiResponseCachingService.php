<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Response Caching Service
 *
 * Provides intelligent API response caching with:
 * - Automatic cache key generation based on request parameters
 * - Tag-based cache invalidation
 * - Conditional caching based on response characteristics
 * - Cache warming for frequently accessed endpoints
 * - Intelligent TTL management based on data volatility
 *
 * @see Requirements: 52.3, 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */
class ApiResponseCachingService
{
    /**
     * Cache prefix for API responses
     */
    protected const CACHE_PREFIX = 'api_response:';

    /**
     * Metrics prefix for cache statistics
     */
    protected const METRICS_PREFIX = 'api_cache_metrics:';

    /**
     * Default TTL in seconds
     */
    protected const DEFAULT_TTL = 300;

    /**
     * In-memory cache for frequently accessed data
     *
     * @var array<string, array{data: mixed, expires: int}>
     */
    protected array $memoryCache = [];

    /**
     * Check if a cached response exists for the request.
     */
    public function has(Request $request): bool
    {
        $cacheKey = $this->generateCacheKey($request);

        // Check memory cache first
        if ($this->hasInMemory($cacheKey)) {
            return true;
        }

        return Cache::has($cacheKey);
    }

    /**
     * Get cached response for the request.
     *
     * @return array{data: mixed, metadata: array{cached_at: string, ttl: int, cache_key: string}}|null
     */
    public function get(Request $request): ?array
    {
        $cacheKey = $this->generateCacheKey($request);

        // Check memory cache first
        if ($this->hasInMemory($cacheKey)) {
            $this->recordCacheHit($cacheKey, 'memory');

            return $this->memoryCache[$cacheKey]['data'];
        }

        // Check Redis cache
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            $this->recordCacheHit($cacheKey, 'redis');

            // Store in memory cache for subsequent requests
            $this->storeInMemory($cacheKey, $cached, 60);

            return $cached;
        }

        $this->recordCacheMiss($cacheKey);

        return null;
    }

    /**
     * Store response in cache.
     *
     * @param  array<string, mixed>  $responseData
     * @param  array<string>  $tags
     */
    public function put(Request $request, array $responseData, ?int $ttl = null, array $tags = []): bool
    {
        $cacheKey = $this->generateCacheKey($request);
        $ttl = $ttl ?? $this->determineTTL($request);

        $cacheData = [
            'data' => $responseData,
            'metadata' => [
                'cached_at' => now()->toIso8601String(),
                'ttl' => $ttl,
                'cache_key' => $cacheKey,
                'endpoint' => $request->path(),
                'method' => $request->method(),
            ],
        ];

        try {
            // Determine tags for this endpoint
            $tags = array_merge($tags, $this->getEndpointTags($request));

            if (! empty($tags)) {
                Cache::tags($tags)->put($cacheKey, $cacheData, $ttl);
            } else {
                Cache::put($cacheKey, $cacheData, $ttl);
            }

            // Store in memory cache for immediate subsequent requests
            $this->storeInMemory($cacheKey, $cacheData, min($ttl, 60));

            $this->recordCacheStore($cacheKey, $ttl, strlen(serialize($cacheData)));

            return true;
        } catch (\Exception $e) {
            Log::error('[ApiResponseCaching] Failed to store cache', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Invalidate cache for specific tags.
     *
     * @param  array<string>  $tags
     * @return array{invalidated: bool, tags: array<string>}
     */
    public function invalidateByTags(array $tags): array
    {
        try {
            Cache::tags($tags)->flush();

            // Clear memory cache entries with matching tags
            $this->clearMemoryCacheByTags($tags);

            Log::info('[ApiResponseCaching] Cache invalidated by tags', [
                'tags' => $tags,
            ]);

            return [
                'invalidated' => true,
                'tags' => $tags,
            ];
        } catch (\Exception $e) {
            Log::error('[ApiResponseCaching] Tag invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return [
                'invalidated' => false,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Invalidate cache for a specific endpoint pattern.
     *
     * @return array{invalidated: int, pattern: string}
     */
    public function invalidateByEndpoint(string $endpointPattern): array
    {
        $invalidated = 0;

        try {
            $pattern = self::CACHE_PREFIX.'*'.str_replace('/', '_', $endpointPattern).'*';

            if ($this->isRedisAvailable()) {
                $cursor = 0;
                do {
                    $keys = Redis::scan($cursor, $pattern, 100);
                    if ($keys === false) {
                        break;
                    }
                    if (is_array($keys) && ! empty($keys)) {
                        Redis::del(...$keys);
                        $invalidated += count($keys);
                    }
                } while ($cursor !== 0);
            }

            // Clear memory cache
            $this->memoryCache = [];

            Log::info('[ApiResponseCaching] Cache invalidated by endpoint', [
                'pattern' => $endpointPattern,
                'invalidated' => $invalidated,
            ]);

            return [
                'invalidated' => $invalidated,
                'pattern' => $endpointPattern,
            ];
        } catch (\Exception $e) {
            Log::error('[ApiResponseCaching] Endpoint invalidation failed', [
                'pattern' => $endpointPattern,
                'error' => $e->getMessage(),
            ]);

            return [
                'invalidated' => $invalidated,
                'pattern' => $endpointPattern,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Invalidate cache with cascade rules.
     *
     * @return array{invalidated: int, cascaded: array<string>}
     */
    public function invalidateWithCascade(string $dataType): array
    {
        $invalidated = 0;
        $cascaded = [];

        // Get cascade rules from config
        $cascadeRules = config('api-performance.cache.cascade_rules', []);

        // Invalidate primary type
        $primaryTags = $this->getTagsForDataType($dataType);
        if (! empty($primaryTags)) {
            $this->invalidateByTags($primaryTags);
            $invalidated++;
        }

        // Apply cascade rules
        if (isset($cascadeRules[$dataType])) {
            foreach ($cascadeRules[$dataType] as $cascadeType) {
                $cascadeTags = $this->getTagsForDataType($cascadeType);
                if (! empty($cascadeTags)) {
                    $this->invalidateByTags($cascadeTags);
                    $cascaded[] = $cascadeType;
                    $invalidated++;
                }
            }
        }

        return [
            'invalidated' => $invalidated,
            'cascaded' => $cascaded,
        ];
    }

    /**
     * Warm cache for frequently accessed endpoints.
     *
     * @param  array<array{method: string, path: string, params?: array<string, mixed>}>  $endpoints
     * @return array{warmed: int, failed: int, details: array<string, array{status: string, ttl?: int, error?: string}>}
     */
    public function warmCache(array $endpoints): array
    {
        $warmed = 0;
        $failed = 0;
        $details = [];

        foreach ($endpoints as $endpoint) {
            $key = $endpoint['method'].':'.$endpoint['path'];

            try {
                // Create a mock request for cache key generation
                $request = Request::create(
                    $endpoint['path'],
                    $endpoint['method'],
                    $endpoint['params'] ?? []
                );

                $cacheKey = $this->generateCacheKey($request);

                // Check if already cached
                if (Cache::has($cacheKey)) {
                    $details[$key] = [
                        'status' => 'skipped',
                        'reason' => 'already_cached',
                    ];

                    continue;
                }

                // Mark as needing warming (actual data will be cached on first request)
                $warmingKey = 'api_cache_warming:'.$cacheKey;
                Cache::put($warmingKey, true, 3600);

                $warmed++;
                $details[$key] = [
                    'status' => 'queued',
                    'cache_key' => $cacheKey,
                ];
            } catch (\Exception $e) {
                $failed++;
                $details[$key] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'warmed' => $warmed,
            'failed' => $failed,
            'details' => $details,
        ];
    }

    /**
     * Get cache statistics.
     *
     * @return array{hit_rate: float, hits: int, misses: int, stores: int, total_size_bytes: int, by_endpoint: array<string, array{hits: int, misses: int, avg_ttl: float}>}
     */
    public function getStatistics(): array
    {
        $stats = [
            'hit_rate' => 0.0,
            'hits' => 0,
            'misses' => 0,
            'stores' => 0,
            'total_size_bytes' => 0,
            'by_endpoint' => [],
        ];

        try {
            // Get metrics from Redis
            $metricsKey = self::METRICS_PREFIX.'totals';
            $totals = Cache::get($metricsKey, [
                'hits' => 0,
                'misses' => 0,
                'stores' => 0,
                'total_size' => 0,
            ]);

            $stats['hits'] = $totals['hits'];
            $stats['misses'] = $totals['misses'];
            $stats['stores'] = $totals['stores'];
            $stats['total_size_bytes'] = $totals['total_size'];

            $total = $stats['hits'] + $stats['misses'];
            $stats['hit_rate'] = $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0.0;

            // Get per-endpoint metrics
            $endpointMetricsKey = self::METRICS_PREFIX.'endpoints';
            $stats['by_endpoint'] = Cache::get($endpointMetricsKey, []);
        } catch (\Exception $e) {
            Log::warning('[ApiResponseCaching] Failed to get statistics', [
                'error' => $e->getMessage(),
            ]);
        }

        return $stats;
    }

    /**
     * Generate cache key for request.
     */
    public function generateCacheKey(Request $request): string
    {
        $components = [
            $request->method(),
            $request->path(),
            $this->normalizeQueryParams($request->query()),
            $request->user()?->id ?? 'guest',
        ];

        return self::CACHE_PREFIX.md5(implode(':', array_filter($components)));
    }

    /**
     * Determine TTL based on endpoint characteristics.
     */
    protected function determineTTL(Request $request): int
    {
        $path = $request->path();

        // Get TTL configuration
        $ttlConfig = config('api-performance.cache.ttl_by_endpoint', []);

        foreach ($ttlConfig as $pattern => $ttl) {
            if (fnmatch($pattern, $path)) {
                return $ttl;
            }
        }

        // Determine based on data volatility
        if (str_contains($path, 'skills') || str_contains($path, 'support-cards')) {
            return 86400; // 24 hours for static game data
        }

        if (str_contains($path, 'training') || str_contains($path, 'predictions')) {
            return 3600; // 1 hour for calculations
        }

        if (str_contains($path, 'characters')) {
            return 1800; // 30 minutes for character data
        }

        return self::DEFAULT_TTL;
    }

    /**
     * Get tags for endpoint.
     *
     * @return array<string>
     */
    protected function getEndpointTags(Request $request): array
    {
        $path = $request->path();
        $tags = ['api_response'];

        // Add endpoint-specific tags
        if (str_contains($path, 'characters')) {
            $tags[] = 'characters';
        }

        if (str_contains($path, 'skills')) {
            $tags[] = 'skills';
        }

        if (str_contains($path, 'training')) {
            $tags[] = 'training';
        }

        if (str_contains($path, 'support-cards') || str_contains($path, 'deck')) {
            $tags[] = 'support_cards';
        }

        if (str_contains($path, 'careers')) {
            $tags[] = 'careers';
        }

        // Add user-specific tag if authenticated
        $userId = $request->user()?->id;
        if ($userId) {
            $tags[] = 'user_'.$userId;
        }

        return $tags;
    }

    /**
     * Get tags for data type.
     *
     * @return array<string>
     */
    protected function getTagsForDataType(string $dataType): array
    {
        $tagMap = config('api-performance.cache.data_type_tags', [
            'characters' => ['api_response', 'characters'],
            'skills' => ['api_response', 'skills'],
            'training' => ['api_response', 'training'],
            'support_cards' => ['api_response', 'support_cards'],
            'careers' => ['api_response', 'careers'],
        ]);

        return $tagMap[$dataType] ?? ['api_response'];
    }

    /**
     * Normalize query parameters for consistent cache keys.
     *
     * @param  array<string, mixed>  $params
     */
    protected function normalizeQueryParams(array $params): string
    {
        // Remove pagination and sorting params that shouldn't affect cache
        $excludeParams = ['_', 'timestamp', 'nocache'];

        $filtered = array_filter(
            $params,
            fn ($key) => ! in_array($key, $excludeParams),
            ARRAY_FILTER_USE_KEY
        );

        ksort($filtered);

        return http_build_query($filtered);
    }

    /**
     * Check if value exists in memory cache.
     */
    protected function hasInMemory(string $key): bool
    {
        if (! isset($this->memoryCache[$key])) {
            return false;
        }

        if ($this->memoryCache[$key]['expires'] < time()) {
            unset($this->memoryCache[$key]);

            return false;
        }

        return true;
    }

    /**
     * Store value in memory cache.
     */
    protected function storeInMemory(string $key, mixed $data, int $ttl): void
    {
        // Limit memory cache size
        if (count($this->memoryCache) > 100) {
            // Remove oldest entries
            $this->memoryCache = array_slice($this->memoryCache, -50, null, true);
        }

        $this->memoryCache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl,
        ];
    }

    /**
     * Clear memory cache entries by tags.
     *
     * @param  array<string>  $tags
     */
    protected function clearMemoryCacheByTags(array $tags): void
    {
        // For simplicity, clear all memory cache when tags are invalidated
        $this->memoryCache = [];
    }

    /**
     * Record cache hit.
     */
    protected function recordCacheHit(string $cacheKey, string $source): void
    {
        $this->updateMetrics('hits', 1);
        $this->updateEndpointMetrics($cacheKey, 'hits');
    }

    /**
     * Record cache miss.
     */
    protected function recordCacheMiss(string $cacheKey): void
    {
        $this->updateMetrics('misses', 1);
        $this->updateEndpointMetrics($cacheKey, 'misses');
    }

    /**
     * Record cache store.
     */
    protected function recordCacheStore(string $cacheKey, int $ttl, int $size): void
    {
        $this->updateMetrics('stores', 1);
        $this->updateMetrics('total_size', $size);
    }

    /**
     * Update metrics.
     */
    protected function updateMetrics(string $field, int $increment): void
    {
        try {
            $metricsKey = self::METRICS_PREFIX.'totals';
            $metrics = Cache::get($metricsKey, [
                'hits' => 0,
                'misses' => 0,
                'stores' => 0,
                'total_size' => 0,
            ]);

            $metrics[$field] = ($metrics[$field] ?? 0) + $increment;
            Cache::put($metricsKey, $metrics, 86400);
        } catch (\Exception $e) {
            // Silently fail metrics update
        }
    }

    /**
     * Update endpoint-specific metrics.
     */
    protected function updateEndpointMetrics(string $cacheKey, string $field): void
    {
        // Extract endpoint from cache key (simplified)
        // In production, you'd want to store endpoint info with the cache
    }

    /**
     * Check if Redis is available.
     */
    protected function isRedisAvailable(): bool
    {
        return extension_loaded('redis') && config('cache.default') === 'redis';
    }
}
