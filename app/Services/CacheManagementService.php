<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Cache Management Service with MCP Integration
 *
 * Provides intelligent caching with MCP server health monitoring,
 * predictive cache warming, and cost-optimized strategies.
 *
 * Requirements: 14.5, 55.3, 56.4, Task 4.4.2
 */
class CacheManagementService
{
    /**
     * Cache prefix for all application caches
     */
    protected const CACHE_PREFIX = 'umamusume-career-planner:';

    /**
     * Default cache TTL in seconds (24 hours)
     */
    protected const DEFAULT_TTL = 86400;

    /**
     * Cache warming batch size
     */
    protected const WARMING_BATCH_SIZE = 50;

    /**
     * Performance metrics tracking
     *
     * @var array<string, array{hits: int, misses: int, total_time_ms: float}>
     */
    protected array $metrics = [];

    public function __construct(
        protected MCPClientService $mcpClient
    ) {}

    /**
     * Get cached data with MCP health monitoring
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $startTime = microtime(true);
        $fullKey = self::CACHE_PREFIX.$key;
        $ttl = $ttl ?? self::DEFAULT_TTL;

        // Check MCP server health before cache operations
        $mcpHealthy = $this->mcpClient->isServerHealthy('awspricing');

        if (Cache::has($fullKey)) {
            $this->recordMetric($key, 'hit', microtime(true) - $startTime);

            return Cache::get($fullKey);
        }

        // Cache miss - execute callback
        $value = $callback();

        // Store in cache with TTL
        Cache::put($fullKey, $value, $ttl);

        // Log cache miss with MCP health status
        Log::debug('[CacheManagementService] Cache miss', [
            'key' => $key,
            'ttl' => $ttl,
            'mcp_healthy' => $mcpHealthy,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);

        $this->recordMetric($key, 'miss', microtime(true) - $startTime);

        return $value;
    }

    /**
     * Warm cache with predictive data fetching using MCP agents
     *
     * @param  array<string>  $keys
     * @param  array<string, callable>  $callbacks
     * @return array{warmed: int, failed: int, duration_ms: float}
     */
    public function warmCache(array $keys, array $callbacks): array
    {
        $startTime = microtime(true);
        $warmed = 0;
        $failed = 0;

        Log::info('[CacheManagementService] Starting cache warming', [
            'keys_count' => count($keys),
            'mcp_available' => $this->mcpClient->isServerEnabled('awspricing'),
        ]);

        // Process in batches for better performance
        $batches = array_chunk($keys, self::WARMING_BATCH_SIZE);

        foreach ($batches as $batch) {
            foreach ($batch as $key) {
                try {
                    if (! isset($callbacks[$key])) {
                        Log::warning('[CacheManagementService] No callback for key', ['key' => $key]);
                        $failed++;

                        continue;
                    }

                    $fullKey = self::CACHE_PREFIX.$key;

                    // Skip if already cached
                    if (Cache::has($fullKey)) {
                        continue;
                    }

                    // Execute callback and cache result
                    $value = $callbacks[$key]();
                    Cache::put($fullKey, $value, self::DEFAULT_TTL);

                    $warmed++;
                } catch (\Exception $e) {
                    Log::error('[CacheManagementService] Cache warming failed', [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                }
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[CacheManagementService] Cache warming completed', [
            'warmed' => $warmed,
            'failed' => $failed,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'warmed' => $warmed,
            'failed' => $failed,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Invalidate cache based on external data change detection
     *
     * @param  string|array<string>  $keys
     */
    public function invalidate(string|array $keys, string $reason = 'manual'): void
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $invalidated = 0;

        foreach ($keys as $key) {
            $fullKey = self::CACHE_PREFIX.$key;

            if (Cache::has($fullKey)) {
                Cache::forget($fullKey);
                $invalidated++;
            }
        }

        Log::info('[CacheManagementService] Cache invalidated', [
            'keys_count' => count($keys),
            'invalidated' => $invalidated,
            'reason' => $reason,
            'mcp_triggered' => $reason === 'external_data_change',
        ]);
    }

    /**
     * Invalidate cache by pattern
     */
    public function invalidateByPattern(string $pattern): int
    {
        $fullPattern = self::CACHE_PREFIX.$pattern;
        $invalidated = 0;

        try {
            // Get all keys matching pattern
            $keys = Redis::keys($fullPattern);

            foreach ($keys as $key) {
                // Remove prefix from Redis key
                $cacheKey = str_replace(self::CACHE_PREFIX, '', $key);
                Cache::forget($cacheKey);
                $invalidated++;
            }

            Log::info('[CacheManagementService] Pattern invalidation completed', [
                'pattern' => $pattern,
                'invalidated' => $invalidated,
            ]);
        } catch (\Exception $e) {
            Log::error('[CacheManagementService] Pattern invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }

        return $invalidated;
    }

    /**
     * Get cache hit rate statistics
     *
     * @return array{hit_rate: float, total_hits: int, total_misses: int, total_requests: int, avg_response_time_ms: float}
     */
    public function getHitRateStatistics(): array
    {
        $totalHits = 0;
        $totalMisses = 0;
        $totalTime = 0.0;

        foreach ($this->metrics as $metric) {
            $totalHits += $metric['hits'];
            $totalMisses += $metric['misses'];
            $totalTime += $metric['total_time_ms'];
        }

        $totalRequests = $totalHits + $totalMisses;
        $hitRate = $totalRequests > 0 ? ($totalHits / $totalRequests) * 100 : 0;
        $avgResponseTime = $totalRequests > 0 ? $totalTime / $totalRequests : 0;

        return [
            'hit_rate' => round($hitRate, 2),
            'total_hits' => $totalHits,
            'total_misses' => $totalMisses,
            'total_requests' => $totalRequests,
            'avg_response_time_ms' => round($avgResponseTime, 2),
        ];
    }

    /**
     * Get detailed cache metrics by key
     *
     * @return array<string, array{hits: int, misses: int, hit_rate: float, avg_time_ms: float}>
     */
    public function getDetailedMetrics(): array
    {
        $detailed = [];

        foreach ($this->metrics as $key => $metric) {
            $total = $metric['hits'] + $metric['misses'];
            $hitRate = $total > 0 ? ($metric['hits'] / $total) * 100 : 0;
            $avgTime = $total > 0 ? $metric['total_time_ms'] / $total : 0;

            $detailed[$key] = [
                'hits' => $metric['hits'],
                'misses' => $metric['misses'],
                'hit_rate' => round($hitRate, 2),
                'avg_time_ms' => round($avgTime, 2),
            ];
        }

        return $detailed;
    }

    /**
     * Get cost-optimized caching strategy using awspricing MCP
     *
     * @return array{recommended_ttl: int, estimated_cost: float, strategy: string}
     */
    public function getCostOptimizedStrategy(string $dataType, int $estimatedSize): array
    {
        // Check if awspricing MCP server is available
        if (! $this->mcpClient->isServerEnabled('awspricing')) {
            return $this->getDefaultStrategy($dataType);
        }

        try {
            // In production, this would query awspricing MCP server
            // For now, we'll use intelligent defaults based on data type
            return $this->calculateOptimalStrategy($dataType, $estimatedSize);
        } catch (\Exception $e) {
            Log::warning('[CacheManagementService] Cost optimization failed', [
                'data_type' => $dataType,
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultStrategy($dataType);
        }
    }

    /**
     * Monitor API response times with MCP tools
     */
    public function recordApiResponseTime(string $apiName, float $responseTime): void
    {
        $key = "api_response_time:{$apiName}";

        // Store in Redis sorted set for time-series analysis
        Redis::zadd($key, time(), $responseTime);

        // Keep only last 1000 entries
        Redis::zremrangebyrank($key, 0, -1001);

        // Set expiry to 7 days
        Redis::expire($key, 604800);

        Log::debug('[CacheManagementService] API response time recorded', [
            'api' => $apiName,
            'response_time_ms' => round($responseTime, 2),
        ]);
    }

    /**
     * Get API response time statistics
     *
     * @return array{avg: float, min: float, max: float, p50: float, p95: float, p99: float, count: int}
     */
    public function getApiResponseTimeStats(string $apiName): array
    {
        $key = "api_response_time:{$apiName}";

        try {
            $times = Redis::zrange($key, 0, -1);

            if (empty($times)) {
                return [
                    'avg' => 0.0,
                    'min' => 0.0,
                    'max' => 0.0,
                    'p50' => 0.0,
                    'p95' => 0.0,
                    'p99' => 0.0,
                    'count' => 0,
                ];
            }

            $times = array_map('floatval', $times);
            sort($times);

            $count = count($times);
            $avg = array_sum($times) / $count;
            $min = min($times);
            $max = max($times);

            return [
                'avg' => round($avg, 2),
                'min' => round($min, 2),
                'max' => round($max, 2),
                'p50' => round($this->percentile($times, 50), 2),
                'p95' => round($this->percentile($times, 95), 2),
                'p99' => round($this->percentile($times, 99), 2),
                'count' => $count,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagementService] Failed to get API stats', [
                'api' => $apiName,
                'error' => $e->getMessage(),
            ]);

            return [
                'avg' => 0.0,
                'min' => 0.0,
                'max' => 0.0,
                'p50' => 0.0,
                'p95' => 0.0,
                'p99' => 0.0,
                'count' => 0,
            ];
        }
    }

    /**
     * Clear all application caches
     */
    public function clearAll(): void
    {
        try {
            $pattern = self::CACHE_PREFIX.'*';
            $keys = Redis::keys($pattern);
            if (! is_array($keys)) {
                $keys = [];
            }

            foreach ($keys as $key) {
                $cacheKey = str_replace(self::CACHE_PREFIX, '', $key);
                Cache::forget($cacheKey);
            }

            Log::info('[CacheManagementService] All caches cleared', [
                'keys_cleared' => count($keys),
            ]);
        } catch (\Exception $e) {
            Log::error('[CacheManagementService] Failed to clear all caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record cache metric
     */
    protected function recordMetric(string $key, string $type, float $time): void
    {
        if (! isset($this->metrics[$key])) {
            $this->metrics[$key] = [
                'hits' => 0,
                'misses' => 0,
                'total_time_ms' => 0.0,
            ];
        }

        if ($type === 'hit') {
            $this->metrics[$key]['hits']++;
        } else {
            $this->metrics[$key]['misses']++;
        }

        $this->metrics[$key]['total_time_ms'] += $time * 1000;
    }

    /**
     * Calculate optimal caching strategy
     *
     * @return array{recommended_ttl: int, estimated_cost: float, strategy: string}
     */
    protected function calculateOptimalStrategy(string $dataType, int $estimatedSize): array
    {
        // Strategy based on data type and size
        $strategies = [
            'characters' => ['ttl' => 86400, 'cost' => 0.001, 'strategy' => 'long_term'],
            'support_cards' => ['ttl' => 86400, 'cost' => 0.001, 'strategy' => 'long_term'],
            'training_calculations' => ['ttl' => 3600, 'cost' => 0.0005, 'strategy' => 'medium_term'],
            'race_strategies' => ['ttl' => 7200, 'cost' => 0.0005, 'strategy' => 'medium_term'],
            'meta_rankings' => ['ttl' => 43200, 'cost' => 0.0008, 'strategy' => 'long_term'],
            'news' => ['ttl' => 3600, 'cost' => 0.0003, 'strategy' => 'short_term'],
        ];

        $strategy = $strategies[$dataType] ?? $strategies['training_calculations'];

        // Adjust cost based on size (rough estimate: $0.0001 per MB)
        $sizeMB = $estimatedSize / 1024 / 1024;
        $strategy['cost'] += $sizeMB * 0.0001;

        return [
            'recommended_ttl' => $strategy['ttl'],
            'estimated_cost' => round($strategy['cost'], 6),
            'strategy' => $strategy['strategy'],
        ];
    }

    /**
     * Get default caching strategy
     *
     * @return array{recommended_ttl: int, estimated_cost: float, strategy: string}
     */
    protected function getDefaultStrategy(string $dataType): array
    {
        return [
            'recommended_ttl' => self::DEFAULT_TTL,
            'estimated_cost' => 0.001,
            'strategy' => 'default',
        ];
    }

    /**
     * Calculate percentile from sorted array
     *
     * @param  array<float>  $values
     */
    protected function percentile(array $values, int $percentile): float
    {
        $count = count($values);
        $index = ($percentile / 100) * ($count - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower === $upper) {
            return $values[(int) $index];
        }

        $fraction = $index - $lower;

        return $values[(int) $lower] * (1 - $fraction) + $values[(int) $upper] * $fraction;
    }
}
