<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Redis Cache Optimization Service
 *
 * Provides advanced Redis caching optimization including:
 * - Redis connection management and health checks
 * - Intelligent cache warming strategies
 * - Tag-based cache invalidation
 * - Memory usage monitoring and optimization
 * - Cache hit rate tracking and analysis
 * - Automatic cache cleanup for expired/stale data
 *
 * @see Requirements: 17.4, 59.2
 * @see Task: 6.1.2 Redis caching optimization and strategy
 */
class RedisCacheOptimizationService
{
    /**
     * Cache prefix for all application caches
     */
    protected const CACHE_PREFIX = 'umamusume-career-planner:';

    /**
     * Metrics key prefix
     */
    protected const METRICS_PREFIX = 'cache_metrics:';

    /**
     * Health check key
     */
    protected const HEALTH_KEY = 'redis_health_check';

    /**
     * In-memory metrics buffer
     *
     * @var array<string, array{hits: int, misses: int, total_time_ms: float, last_access: int}>
     */
    protected array $metricsBuffer = [];

    /**
     * Check Redis connection health
     *
     * @return array{healthy: bool, latency_ms: float, connection_info: array<string, mixed>, error: string|null}
     */
    public function checkHealth(): array
    {
        $startTime = microtime(true);

        try {
            // Check if Redis is available
            if (! $this->isRedisAvailable()) {
                return [
                    'healthy' => false,
                    'latency_ms' => 0,
                    'connection_info' => [],
                    'error' => 'Redis extension not available or not configured',
                ];
            }

            // Ping Redis
            $pong = Redis::ping();
            $latency = (microtime(true) - $startTime) * 1000;

            // Get connection info
            $info = $this->getRedisInfo();

            return [
                'healthy' => $pong === true || $pong === 'PONG' || $pong === '+PONG',
                'latency_ms' => round($latency, 2),
                'connection_info' => [
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? 'N/A',
                    'redis_version' => $info['redis_version'] ?? 'unknown',
                    'uptime_in_days' => $info['uptime_in_days'] ?? 0,
                ],
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('[RedisCacheOptimization] Health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'healthy' => false,
                'latency_ms' => (microtime(true) - $startTime) * 1000,
                'connection_info' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if Redis is available
     */
    public function isRedisAvailable(): bool
    {
        // Check if phpredis extension is loaded
        if (! extension_loaded('redis')) {
            return false;
        }

        // Check if Redis is configured as cache driver
        return config('cache.default') === 'redis' || config('database.redis.default.host') !== null;
    }

    /**
     * Get Redis server information
     *
     * @return array<string, mixed>
     */
    public function getRedisInfo(?string $section = null): array
    {
        if (! $this->isRedisAvailable()) {
            return [];
        }

        try {
            $info = $section ? Redis::info($section) : Redis::info();

            return \is_array($info) ? $info : [];
        } catch (\Exception $e) {
            Log::warning('[RedisCacheOptimization] Failed to get Redis info', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get memory usage statistics
     *
     * @return array{used_memory: int, used_memory_human: string, used_memory_peak: int, used_memory_peak_human: string, memory_fragmentation_ratio: float, usage_percent: float, status: string, recommendations: array<string>}
     */
    public function getMemoryUsage(): array
    {
        $info = $this->getRedisInfo('memory');

        $usedMemory = (int) ($info['used_memory'] ?? 0);
        $usedMemoryPeak = (int) ($info['used_memory_peak'] ?? 0);
        $maxMemory = (int) ($info['maxmemory'] ?? 0);
        $fragRatio = (float) ($info['mem_fragmentation_ratio'] ?? 1.0);

        // Calculate usage percentage
        $usagePercent = $maxMemory > 0 ? ($usedMemory / $maxMemory) * 100 : 0;

        // Determine status
        $warningThreshold = (float) config('cache-management.memory.warning_threshold_percent', 70);
        $criticalThreshold = (float) config('cache-management.memory.critical_threshold_percent', 90);

        $status = 'healthy';
        if ($usagePercent >= $criticalThreshold) {
            $status = 'critical';
        } elseif ($usagePercent >= $warningThreshold) {
            $status = 'warning';
        }

        // Generate recommendations
        $recommendations = $this->generateMemoryRecommendations($usagePercent, $fragRatio);

        return [
            'used_memory' => $usedMemory,
            'used_memory_human' => $info['used_memory_human'] ?? $this->formatBytes($usedMemory),
            'used_memory_peak' => $usedMemoryPeak,
            'used_memory_peak_human' => $info['used_memory_peak_human'] ?? $this->formatBytes($usedMemoryPeak),
            'max_memory' => $maxMemory,
            'max_memory_human' => $maxMemory > 0 ? $this->formatBytes($maxMemory) : 'unlimited',
            'memory_fragmentation_ratio' => round($fragRatio, 2),
            'usage_percent' => round($usagePercent, 2),
            'status' => $status,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Warm cache with frequently accessed data
     *
     * @param  array<string, callable>  $dataProviders
     * @return array{warmed: int, failed: int, skipped: int, duration_ms: float, details: array<string, array{status: string, ttl: int, size_bytes: int|null}>}
     */
    public function warmCache(array $dataProviders): array
    {
        $startTime = microtime(true);
        $warmed = 0;
        $failed = 0;
        $skipped = 0;
        $details = [];

        $batchSize = (int) config('cache-management.warming.batch_size', 50);
        $priorityTypes = config('cache-management.warming.priority_types', []);

        Log::info('[RedisCacheOptimization] Starting cache warming', [
            'providers_count' => \count($dataProviders),
        ]);

        // Sort by priority
        $sortedProviders = $this->sortByPriority($dataProviders, $priorityTypes);

        foreach ($sortedProviders as $key => $provider) {
            try {
                $fullKey = self::CACHE_PREFIX.$key;
                $typeConfig = $priorityTypes[$key] ?? [];
                $ttl = $typeConfig['ttl'] ?? (int) config('cache-management.ttl.default', 3600);

                // Check if already cached and not expired
                if (Cache::has($fullKey)) {
                    $skipped++;
                    $details[$key] = [
                        'status' => 'skipped',
                        'reason' => 'already_cached',
                        'ttl' => $ttl,
                        'size_bytes' => null,
                    ];

                    continue;
                }

                // Execute provider and cache result
                $data = $provider();
                $serialized = serialize($data);
                $sizeBytes = \strlen($serialized);

                // Apply compression if enabled and data is large enough
                if ($this->shouldCompress($sizeBytes)) {
                    $data = $this->compress($data);
                }

                // Get tags for this data type
                $tags = $this->getTagsForType($key);

                // Store with tags if available
                if (! empty($tags)) {
                    Cache::tags($tags)->put($fullKey, $data, $ttl);
                } else {
                    Cache::put($fullKey, $data, $ttl);
                }

                $warmed++;
                $details[$key] = [
                    'status' => 'warmed',
                    'ttl' => $ttl,
                    'size_bytes' => $sizeBytes,
                    'compressed' => $this->shouldCompress($sizeBytes),
                ];
            } catch (\Exception $e) {
                $failed++;
                $details[$key] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'ttl' => 0,
                    'size_bytes' => null,
                ];

                Log::error('[RedisCacheOptimization] Cache warming failed for key', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[RedisCacheOptimization] Cache warming completed', [
            'warmed' => $warmed,
            'failed' => $failed,
            'skipped' => $skipped,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'warmed' => $warmed,
            'failed' => $failed,
            'skipped' => $skipped,
            'duration_ms' => round($duration, 2),
            'details' => $details,
        ];
    }

    /**
     * Invalidate cache by tags
     *
     * @param  array<string>  $tags
     * @return array{invalidated: bool, tags: array<string>}
     */
    public function invalidateByTags(array $tags): array
    {
        try {
            Cache::tags($tags)->flush();

            Log::info('[RedisCacheOptimization] Cache invalidated by tags', [
                'tags' => $tags,
            ]);

            return [
                'invalidated' => true,
                'tags' => $tags,
            ];
        } catch (\Exception $e) {
            Log::error('[RedisCacheOptimization] Tag invalidation failed', [
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
     * Invalidate cache with cascade rules
     *
     * @return array{invalidated: int, cascaded: array<string>}
     */
    public function invalidateWithCascade(string $dataType): array
    {
        $invalidated = 0;
        $cascaded = [];

        // Get cascade rules
        $cascadeRules = config('cache-management.invalidation.cascade_rules', []);

        // Invalidate primary type
        $tags = $this->getTagsForType($dataType);
        if (! empty($tags)) {
            $this->invalidateByTags($tags);
            $invalidated++;
        }

        // Apply cascade rules
        if (isset($cascadeRules[$dataType])) {
            foreach ($cascadeRules[$dataType] as $cascadeType) {
                $cascadeTags = $this->getTagsForType($cascadeType);
                if (! empty($cascadeTags)) {
                    $this->invalidateByTags($cascadeTags);
                    $cascaded[] = $cascadeType;
                    $invalidated++;
                }
            }
        }

        Log::info('[RedisCacheOptimization] Cascade invalidation completed', [
            'primary_type' => $dataType,
            'cascaded' => $cascaded,
            'total_invalidated' => $invalidated,
        ]);

        return [
            'invalidated' => $invalidated,
            'cascaded' => $cascaded,
        ];
    }

    /**
     * Get cache hit rate statistics
     *
     * @return array{overall: array{hit_rate: float, hits: int, misses: int, total: int}, by_key: array<string, array{hit_rate: float, hits: int, misses: int, avg_time_ms: float}>, recommendations: array<string>}
     */
    public function getHitRateStatistics(): array
    {
        $info = $this->getRedisInfo('stats');

        $keyspaceHits = (int) ($info['keyspace_hits'] ?? 0);
        $keyspaceMisses = (int) ($info['keyspace_misses'] ?? 0);
        $total = $keyspaceHits + $keyspaceMisses;

        $hitRate = $total > 0 ? ($keyspaceHits / $total) * 100 : 0;

        // Get per-key metrics from buffer
        $byKey = $this->getPerKeyMetrics();

        // Generate recommendations
        $recommendations = $this->generateHitRateRecommendations($hitRate, $byKey);

        return [
            'overall' => [
                'hit_rate' => round($hitRate, 2),
                'hits' => $keyspaceHits,
                'misses' => $keyspaceMisses,
                'total' => $total,
            ],
            'by_key' => $byKey,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Record cache access for metrics
     */
    public function recordAccess(string $key, bool $hit, float $responseTimeMs): void
    {
        if (! config('cache-management.monitoring.enabled', true)) {
            return;
        }

        // Sample rate check
        $sampleRate = (float) config('cache-management.monitoring.sample_rate', 1.0);
        if ($sampleRate < 1.0 && mt_rand() / mt_getrandmax() > $sampleRate) {
            return;
        }

        if (! isset($this->metricsBuffer[$key])) {
            $this->metricsBuffer[$key] = [
                'hits' => 0,
                'misses' => 0,
                'total_time_ms' => 0.0,
                'last_access' => time(),
            ];
        }

        if ($hit) {
            $this->metricsBuffer[$key]['hits']++;
        } else {
            $this->metricsBuffer[$key]['misses']++;
        }

        $this->metricsBuffer[$key]['total_time_ms'] += $responseTimeMs;
        $this->metricsBuffer[$key]['last_access'] = time();

        // Persist metrics periodically
        $maxTracked = (int) config('cache-management.monitoring.max_tracked_keys', 1000);
        if (\count($this->metricsBuffer) >= $maxTracked) {
            $this->persistMetrics();
        }
    }

    /**
     * Cleanup stale and expired cache entries
     *
     * @return array{scanned: int, deleted: int, freed_memory_bytes: int, duration_ms: float}
     */
    public function cleanupStaleEntries(): array
    {
        $startTime = microtime(true);
        $scanned = 0;
        $deleted = 0;
        $freedMemory = 0;

        // Check if Redis is available
        if (! $this->isRedisAvailable()) {
            return [
                'scanned' => 0,
                'deleted' => 0,
                'freed_memory_bytes' => 0,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }

        $maxKeys = (int) config('cache-management.cleanup.max_keys_per_run', 10000);
        $batchSize = (int) config('cache-management.cleanup.batch_size', 100);
        $staleThreshold = (int) config('cache-management.cleanup.stale_threshold', 604800);

        try {
            $cursor = 0;
            $pattern = self::CACHE_PREFIX.'*';

            do {
                // Use SCAN for non-blocking iteration
                $result = Redis::scan($cursor, ['match' => $pattern, 'count' => $batchSize]);

                if ($result === false) {
                    break;
                }

                [$cursor, $keys] = $result;

                foreach ($keys as $key) {
                    $scanned++;

                    if ($scanned > $maxKeys) {
                        break 2;
                    }

                    // Check if key is stale (no TTL and old)
                    $ttl = Redis::ttl($key);

                    if ($ttl === -1) {
                        // Key has no expiry - check if it's stale
                        $idleTime = Redis::object('idletime', $key);

                        if ($idleTime !== false && $idleTime > $staleThreshold) {
                            $memoryBefore = Redis::memory('usage', $key) ?? 0;
                            Redis::del($key);
                            $freedMemory += $memoryBefore;
                            $deleted++;
                        }
                    }
                }
            } while ($cursor !== 0 && $scanned < $maxKeys);
        } catch (\Exception $e) {
            Log::error('[RedisCacheOptimization] Cleanup failed', [
                'error' => $e->getMessage(),
                'scanned' => $scanned,
                'deleted' => $deleted,
            ]);
        }

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[RedisCacheOptimization] Cleanup completed', [
            'scanned' => $scanned,
            'deleted' => $deleted,
            'freed_memory_bytes' => $freedMemory,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'scanned' => $scanned,
            'deleted' => $deleted,
            'freed_memory_bytes' => $freedMemory,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Get optimization recommendations
     *
     * @return array<array{type: string, severity: string, message: string, action: string}>
     */
    public function getOptimizationRecommendations(): array
    {
        $recommendations = [];

        // Check memory usage
        $memory = $this->getMemoryUsage();
        if ($memory['status'] === 'warning') {
            $recommendations[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => "Memory usage at {$memory['usage_percent']}%",
                'action' => 'Consider increasing maxmemory or enabling eviction',
            ];
        } elseif ($memory['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'memory',
                'severity' => 'critical',
                'message' => "Critical memory usage at {$memory['usage_percent']}%",
                'action' => 'Immediately increase maxmemory or clear unused caches',
            ];
        }

        // Check hit rate
        $hitStats = $this->getHitRateStatistics();
        $minHitRate = (float) config('cache-management.monitoring.min_hit_rate', 70);
        if ($hitStats['overall']['hit_rate'] < $minHitRate) {
            $recommendations[] = [
                'type' => 'hit_rate',
                'severity' => 'warning',
                'message' => "Cache hit rate ({$hitStats['overall']['hit_rate']}%) below threshold ({$minHitRate}%)",
                'action' => 'Review cache warming strategy and TTL configurations',
            ];
        }

        // Check fragmentation
        if ($memory['memory_fragmentation_ratio'] > 1.5) {
            $recommendations[] = [
                'type' => 'fragmentation',
                'severity' => 'info',
                'message' => "Memory fragmentation ratio is {$memory['memory_fragmentation_ratio']}",
                'action' => 'Consider restarting Redis during low-traffic period',
            ];
        }

        // Check connection health
        $health = $this->checkHealth();
        if (! $health['healthy']) {
            $recommendations[] = [
                'type' => 'connection',
                'severity' => 'critical',
                'message' => 'Redis connection unhealthy',
                'action' => 'Check Redis server status and network connectivity',
            ];
        } elseif ($health['latency_ms'] > 10) {
            $recommendations[] = [
                'type' => 'latency',
                'severity' => 'warning',
                'message' => "Redis latency ({$health['latency_ms']}ms) is high",
                'action' => 'Check network latency and Redis server load',
            ];
        }

        return $recommendations;
    }

    /**
     * Get comprehensive cache statistics
     *
     * @return array<string, mixed>
     */
    public function getComprehensiveStats(): array
    {
        return [
            'health' => $this->checkHealth(),
            'memory' => $this->getMemoryUsage(),
            'hit_rate' => $this->getHitRateStatistics(),
            'recommendations' => $this->getOptimizationRecommendations(),
            'config' => [
                'warming_enabled' => config('cache-management.warming.enabled'),
                'monitoring_enabled' => config('cache-management.monitoring.enabled'),
                'cleanup_enabled' => config('cache-management.cleanup.enabled'),
                'compression_enabled' => config('cache-management.optimization.compression_enabled'),
            ],
        ];
    }

    /**
     * Get tags for a data type
     *
     * @return array<string>
     */
    protected function getTagsForType(string $dataType): array
    {
        $defaultTags = config('cache-management.invalidation.default_tags', []);

        return $defaultTags[$dataType] ?? [];
    }

    /**
     * Sort data providers by priority
     *
     * @param  array<string, callable>  $providers
     * @param  array<string, array{priority?: int}>  $priorityConfig
     * @return array<string, callable>
     */
    protected function sortByPriority(array $providers, array $priorityConfig): array
    {
        $sorted = [];
        $priorities = [];

        foreach ($providers as $key => $provider) {
            $priorities[$key] = $priorityConfig[$key]['priority'] ?? 999;
        }

        asort($priorities);

        foreach (array_keys($priorities) as $key) {
            if (isset($providers[$key])) {
                $sorted[$key] = $providers[$key];
            }
        }

        return $sorted;
    }

    /**
     * Check if data should be compressed
     */
    protected function shouldCompress(int $sizeBytes): bool
    {
        if (! config('cache-management.optimization.compression_enabled', true)) {
            return false;
        }

        $threshold = (int) config('cache-management.optimization.compression_threshold', 1024);

        return $sizeBytes >= $threshold;
    }

    /**
     * Compress data
     */
    protected function compress(mixed $data): string
    {
        $level = (int) config('cache-management.optimization.compression_level', 6);
        $serialized = serialize($data);

        return gzcompress($serialized, $level);
    }

    /**
     * Decompress data
     */
    protected function decompress(string $compressed): mixed
    {
        $decompressed = gzuncompress($compressed);

        return $decompressed !== false ? unserialize($decompressed) : null;
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < \count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Generate memory recommendations
     *
     * @return array<string>
     */
    protected function generateMemoryRecommendations(float $usagePercent, float $fragRatio): array
    {
        $recommendations = [];

        if ($usagePercent > 90) {
            $recommendations[] = 'Critical: Memory usage exceeds 90%. Consider increasing maxmemory or clearing unused caches.';
        } elseif ($usagePercent > 70) {
            $recommendations[] = 'Warning: Memory usage exceeds 70%. Monitor closely and plan for capacity increase.';
        }

        if ($fragRatio > 1.5) {
            $recommendations[] = 'Memory fragmentation is high. Consider restarting Redis during maintenance window.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Memory usage is healthy.';
        }

        return $recommendations;
    }

    /**
     * Generate hit rate recommendations
     *
     * @param  array<string, array{hit_rate: float, hits: int, misses: int, avg_time_ms: float}>  $byKey
     * @return array<string>
     */
    protected function generateHitRateRecommendations(float $hitRate, array $byKey): array
    {
        $recommendations = [];

        if ($hitRate < 50) {
            $recommendations[] = 'Critical: Hit rate below 50%. Review caching strategy and TTL settings.';
        } elseif ($hitRate < 70) {
            $recommendations[] = 'Warning: Hit rate below 70%. Consider implementing cache warming.';
        }

        // Find keys with low hit rates
        foreach ($byKey as $key => $metrics) {
            if ($metrics['hit_rate'] < 30 && ($metrics['hits'] + $metrics['misses']) > 10) {
                $recommendations[] = "Key '{$key}' has low hit rate ({$metrics['hit_rate']}%). Consider adjusting TTL.";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Cache hit rate is healthy.';
        }

        return $recommendations;
    }

    /**
     * Get per-key metrics from buffer
     *
     * @return array<string, array{hit_rate: float, hits: int, misses: int, avg_time_ms: float}>
     */
    protected function getPerKeyMetrics(): array
    {
        $metrics = [];

        foreach ($this->metricsBuffer as $key => $data) {
            $total = $data['hits'] + $data['misses'];
            $hitRate = $total > 0 ? ($data['hits'] / $total) * 100 : 0;
            $avgTime = $total > 0 ? $data['total_time_ms'] / $total : 0;

            $metrics[$key] = [
                'hit_rate' => round($hitRate, 2),
                'hits' => $data['hits'],
                'misses' => $data['misses'],
                'avg_time_ms' => round($avgTime, 2),
            ];
        }

        return $metrics;
    }

    /**
     * Persist metrics to Redis
     */
    protected function persistMetrics(): void
    {
        try {
            $key = self::METRICS_PREFIX.'buffer:'.date('Y-m-d-H');
            $retention = (int) config('cache-management.monitoring.retention_period', 86400);

            Redis::setex($key, $retention, serialize($this->metricsBuffer));

            // Clear buffer after persisting
            $this->metricsBuffer = [];
        } catch (\Exception $e) {
            Log::warning('[RedisCacheOptimization] Failed to persist metrics', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
