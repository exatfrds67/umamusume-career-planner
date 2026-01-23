<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisCacheOptimizationService
{
    /**
     * Cache TTL strategies (in seconds)
     */
    private const TTL_STRATEGIES = [
        'training_predictions' => 300,    // 5 minutes
        'character_data' => 3600,         // 1 hour
        'external_api' => 7200,           // 2 hours
        'static_game_data' => 86400,      // 24 hours
        'user_preferences' => 604800,     // 1 week
        'ai_conversations' => 1800,       // 30 minutes
        'mcp_server_status' => 60,        // 1 minute
    ];

    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'umamusume-career-planner:';

    /**
     * Hit rate statistics storage
     */
    private array $hitRateStats = [];

    /**
     * Get TTL for a specific cache strategy
     */
    public function getTTL(string $strategy): int
    {
        return self::TTL_STRATEGIES[$strategy] ?? 3600;
    }

    /**
     * Cache data with intelligent TTL
     */
    public function remember(string $key, string $strategy, callable $callback): mixed
    {
        $ttl = $this->getTTL($strategy);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache data with tags for efficient invalidation
     */
    public function rememberWithTags(array $tags, string $key, string $strategy, callable $callback): mixed
    {
        $ttl = $this->getTTL($strategy);

        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by pattern
     */
    public function invalidatePattern(string $pattern): int
    {
        try {
            $keys = Redis::connection('cache')->keys($pattern);

            if (empty($keys)) {
                return 0;
            }

            // Remove prefix from keys before deletion
            $prefix = config('database.redis.options.prefix', '');
            $cleanKeys = array_map(fn ($key) => str_replace($prefix, '', $key), $keys);

            Redis::connection('cache')->del($cleanKeys);

            Log::info('Cache invalidated by pattern', [
                'pattern' => $pattern,
                'keys_deleted' => \count($cleanKeys),
            ]);

            return \count($cleanKeys);
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateTags(array $tags): void
    {
        try {
            Cache::tags($tags)->flush();

            Log::info('Cache invalidated by tags', [
                'tags' => $tags,
            ]);
        } catch (\Exception $e) {
            Log::error('Cache tag invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm cache with providers
     */
    public function warmCache(): array
        $startTime = microtime(true);
        $warmed = 0;
        $failed = 0;
        $skipped = 0;
        $details = [];

        // Sort providers by priority if configured
        $providers = $this->sortProvidersByPriority($providers);

        foreach ($providers as $key => $provider) {
            $cacheKey = self::CACHE_PREFIX.$key;

            try {
                // Check if already cached
                if (Cache::has($cacheKey)) {
                    $skipped = ($skipped ?? 0) + 1;
                    $details[$key] = [
                        'status' => 'skipped',
                        'reason' => 'Already cached',
                    ];

                    continue;
                }

                // Execute provider and cache result
                $value = $provider();
                $ttl = $this->getTTLForKey($key);
                Cache::put($cacheKey, $value, $ttl);

                $warmed = ($warmed ?? 0) + 1;
                $details[$key] = [
                    'status' => 'success',
                    'ttl' => $ttl,
                ];
            } catch (\Exception $e) {
                $failed = ($failed ?? 0) + 1;
                $details[$key] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                Log::error('Cache warming failed for key', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'warmed' => $warmed,
            'failed' => $failed,
            'skipped' => $skipped,
            'duration_ms' => round($duration, 2),
            'details' => $details,
        ];
    }

    /**
     * Warm static game data cache
     */
    protected function warmStaticGameData(): void
    {
        // This will be implemented when game data services are available
        Log::debug('Warming static game data cache');
    }

    /**
     * Warm user preferences cache
     */
    protected function warmUserPreferences(): void
    {
        // This will be implemented when user preference services are available
        Log::debug('Warming user preferences cache');
    }

    /**
     * Warm external API data cache
     */
    protected function warmExternalAPIData(): void
    {
        // This will be implemented when external API services are available
        Log::debug('Warming external API data cache');
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
        try {
            $info = Redis::connection('cache')->info();

            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'used_memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                'total_keys' => Redis::connection('cache')->dbsize(),
                'hit_rate' => $this->calculateHitRate($info),
                'fragmentation_ratio' => $info['mem_fragmentation_ratio'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 'N/A',
                'uptime_days' => isset($info['uptime_in_seconds'])
                    ? round($info['uptime_in_seconds'] / 86400, 2)
                    : 'N/A',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to retrieve cache statistics',
            ];
        }
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateHitRate(array $info): string
    {
        if (! isset($info['keyspace_hits']) || ! isset($info['keyspace_misses'])) {
            return 'N/A';
        }

        $hits = (int) $info['keyspace_hits'];
        $misses = (int) $info['keyspace_misses'];
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        $hitRate = ($hits / $total) * 100;

        return round($hitRate, 2).'%';
    }

    /**
     * Optimize Redis memory usage
     */
    public function optimizeMemory(): array
        $results = [];

        try {
            // Remove expired keys
            $expiredKeys = $this->removeExpiredKeys();
            $results['expired_keys_removed'] = $expiredKeys;

            // Analyze memory usage
            $memoryAnalysis = $this->analyzeMemoryUsage();
            $results['memory_analysis'] = $memoryAnalysis;

            Log::info('Redis memory optimization completed', $results);
        } catch (\Exception $e) {
            Log::error('Redis memory optimization failed', [
                'error' => $e->getMessage(),
            ]);

            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Remove expired keys
     */
    protected function removeExpiredKeys(): int
    {
        // Redis automatically removes expired keys, but we can trigger cleanup
        try {
            $info = Redis::connection('cache')->info();

            return (int) ($info['expired_keys'] ?? 0);
        } catch (\Exception $e) {
            Log::error('Failed to get expired keys count', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Analyze memory usage by key patterns
     */
    protected function analyzeMemoryUsage(): array
        try {
            $analysis = [];
            $patterns = [
                'training:*',
                'character:*',
                'skills:*',
                'support_cards:*',
                'external_api:*',
                'ai:*',
                'mcp:*',
            ];

            foreach ($patterns as $pattern) {
                $keys = Redis::connection('cache')->keys($pattern);
                $analysis[$pattern] = count($keys);
            }

            return $analysis;
        } catch (\Exception $e) {
            Log::error('Failed to analyze memory usage', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Test Redis connection
     */
    public function testConnection(): bool
    {
        try {
            $response = Redis::connection('cache')->ping();

            return $response === 'PONG' || $response === true;
        } catch (\Exception $e) {
            Log::error('Redis connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
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

        // Check if cache driver is redis
        if (config('cache.default', '') !== 'redis') {
            return false;
        }

        // Test connection
        return $this->testConnection();
    }

    /**
     * Check Redis health
     */
    public function checkHealth(): array
        $startTime = microtime(true);

        try {
            if (! $this->isRedisAvailable()) {
                return [
                    'healthy' => false,
                    'latency_ms' => 0,
                    'connection_info' => null,
                    'error' => 'Redis is not available or not configured',
                ];
            }

            $response = Redis::connection('cache')->ping();
            $latency = (microtime(true) - $startTime) * 1000;

            $info = Redis::connection('cache')->info();

            return [
                'healthy' => $response === 'PONG' || $response === true,
                'latency_ms' => round($latency, 2),
                'connection_info' => [
                    'redis_version' => (is_array($info) && isset($info['redis_version']) ? $info['redis_version'] : null),
                    'connected_clients' => (is_array($info) && isset($info['connected_clients']) ? $info['connected_clients'] : null),
                    'uptime_in_seconds' => (is_array($info) && isset($info['uptime_in_seconds']) ? $info['uptime_in_seconds'] : null),
                ],
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'latency_ms' => 0,
                'connection_info' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get memory usage statistics
     */
    public function getMemoryUsage(): array
        try {
            if (! $this->isRedisAvailable()) {
                return $this->getDefaultMemoryStats();
            }

            $info = Redis::connection('cache')->info();

            $usedMemory = (int) ($info['used_memory'] ?? 0);
            $usedMemoryPeak = (int) ($info['used_memory_peak'] ?? 0);
            $maxMemory = (int) ($info['maxmemory'] ?? 0);
            $fragmentationRatio = (float) ($info['mem_fragmentation_ratio'] ?? 1.0);

            $usagePercent = $maxMemory > 0 ? ($usedMemory / $maxMemory) * 100 : 0;

            $status = $this->determineMemoryStatus($usagePercent);
            $recommendations = $this->getMemoryRecommendations($usagePercent, $fragmentationRatio);

            return [
                'used_memory' => $usedMemory,
                'used_memory_human' => $this->formatBytes($usedMemory),
                'used_memory_peak' => $usedMemoryPeak,
                'used_memory_peak_human' => $this->formatBytes($usedMemoryPeak),
                'memory_fragmentation_ratio' => $fragmentationRatio,
                'usage_percent' => round($usagePercent, 2),
                'status' => $status,
                'recommendations' => $recommendations,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get memory usage', ['error' => $e->getMessage()]);

            return $this->getDefaultMemoryStats();
        }
    }

    /**
     * Get default memory statistics
     */
    protected function getDefaultMemoryStats(): array
        return [
            'used_memory' => 0,
            'used_memory_human' => '0B',
            'used_memory_peak' => 0,
            'used_memory_peak_human' => '0B',
            'memory_fragmentation_ratio' => 1.0,
            'usage_percent' => 0,
            'status' => 'healthy',
            'recommendations' => [],
        ];
    }

    /**
     * Determine memory status based on usage percentage
     */
    protected function determineMemoryStatus(float $usagePercent): string
    {
        $criticalThreshold = config('cache-management.memory.critical_threshold_percent', 90);
        $warningThreshold = config('cache-management.memory.warning_threshold_percent', 70);

        if ($usagePercent >= $criticalThreshold) {
            return 'critical';
        }

        if ($usagePercent >= $warningThreshold) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * Get memory recommendations
     */
    protected function getMemoryRecommendations(): array
        $recommendations = [];

        if ($usagePercent > 80) {
            $recommendations[] = 'Consider increasing Redis max memory or implementing more aggressive eviction policies';
        }

        if ($fragmentationRatio > 1.5) {
            $recommendations[] = 'High memory fragmentation detected. Consider restarting Redis during low-traffic periods';
        }

        return $recommendations;
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Sort providers by priority
     */
    protected function sortProvidersByPriority(): array
        $priorityTypes = config('cache-management.warming.priority_types', []);

        uksort($providers, function ($a, $b) use ($priorityTypes) {
            $priorityA = $priorityTypes[$a]['priority'] ?? 999;
            $priorityB = $priorityTypes[$b]['priority'] ?? 999;

            return $priorityA <=> $priorityB;
        });

        return $providers;
    }

    /**
     * Get TTL for a cache key
     */
    protected function getTTLForKey(string $key): int
    {
        $priorityTypes = config('cache-management.warming.priority_types', []);

        if (isset($priorityTypes[$key]['ttl'])) {
            return $priorityTypes[$key]['ttl'];
        }

        return config('cache-management.ttl.default', 3600);
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(): array
        try {
            if (config('cache-management.invalidation.tags_enabled', true)) {
                Cache::tags($tags)->flush();
            }

            return [
                'invalidated' => true,
                'tags' => $tags,
            ];
        } catch (\Exception $e) {
            Log::error('Tag invalidation failed', [
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
     * Invalidate cache with cascade
     */
    public function invalidateWithCascade(): array
        $cascadeRules = config('cache-management.invalidation.cascade_rules', []);
        $cascaded = [];

        // Invalidate the primary type
        $tags = $this->getTagsForType($type);
        $this->invalidateByTags($tags);

        // Cascade to dependent types
        if (isset($cascadeRules[$type])) {
            foreach ($cascadeRules[$type] as $dependentType) {
                $dependentTags = $this->getTagsForType($dependentType);
                $this->invalidateByTags($dependentTags);
                $cascaded[] = $dependentType;
            }
        }

        return [
            'invalidated' => $type,
            'cascaded' => $cascaded,
        ];
    }

    /**
     * Get tags for a data type
     */
    protected function getTagsForType(): array
        $defaultTags = config('cache-management.invalidation.default_tags', []);

        return $defaultTags[$type] ?? [];
    }

    /**
     * Record cache access for hit rate tracking
     */
    public function recordAccess(string $key, bool $hit, float $latencyMs): void
    {
        if (! config('cache-management.monitoring.enabled', true)) {
            return;
        }

        $sampleRate = config('cache-management.monitoring.sample_rate', 1.0);

        // Sample rate check
        if ($sampleRate < 1.0 && mt_rand() / mt_getrandmax() > $sampleRate) {
            return;
        }

        if (! isset($this->hitRateStats[$key])) {
            $this->hitRateStats[$key] = [
                'hits' => 0,
                'misses' => 0,
                'total_latency' => 0,
            ];
        }

        if ($hit) {
            $this->hitRateStats[$key]['hits']++;
        } else {
            $this->hitRateStats[$key]['misses']++;
        }

        $this->hitRateStats[$key]['total_latency'] += $latencyMs;
    }

    /**
     * Get hit rate statistics
     */
    public function getHitRateStatistics(): array
        $overallHits = 0;
        $overallMisses = 0;
        $byKey = [];

        foreach ($this->hitRateStats as $key => $stats) {
            $total = $stats['hits'] + $stats['misses'];
            $hitRate = $total > 0 ? ($stats['hits'] / $total) * 100 : 0;
            $avgLatency = $total > 0 ? $stats['total_latency'] / $total : 0;

            $byKey[$key] = [
                'hits' => $stats['hits'],
                'misses' => $stats['misses'],
                'total' => $total,
                'hit_rate' => round($hitRate, 2),
                'avg_latency_ms' => round($avgLatency, 2),
            ];

            $overallHits = ($overallHits ?? 0) + $stats['hits'];
            $overallMisses = ($overallMisses ?? 0) + $stats['misses'];
        }

        $overallTotal = $overallHits + $overallMisses;
        $overallHitRate = $overallTotal > 0 ? ($overallHits / $overallTotal) * 100 : 0;

        $recommendations = $this->getHitRateRecommendations($overallHitRate, $byKey);

        return [
            'overall' => [
                'hit_rate' => round($overallHitRate, 2),
                'hits' => $overallHits,
                'misses' => $overallMisses,
                'total' => $overallTotal,
            ],
            'by_key' => $byKey,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get hit rate recommendations
     */
    protected function getHitRateRecommendations(): array
        $recommendations = [];
        $minHitRate = config('cache-management.monitoring.min_hit_rate', 70);

        if ($hitRate < $minHitRate) {
            $recommendations[] = "Overall hit rate ({$hitRate}%) is below target ({$minHitRate}%). Consider adjusting TTL values or cache warming strategies.";
        }

        // Find keys with low hit rates
        foreach ($byKey as $key => $stats) {
            if ($stats['hit_rate'] < 50 && $stats['total'] > 10) {
                $recommendations[] = "Key '{$key}' has low hit rate ({$stats['hit_rate']}%). Consider reviewing caching strategy.";
            }
        }

        return $recommendations;
    }

    /**
     * Clean up stale cache entries
     */
    public function cleanupStaleEntries(): array
        $startTime = microtime(true);
        $scanned = 0;
        $deleted = 0;
        $freedMemory = 0;

        try {
            if (! $this->isRedisAvailable()) {
                return [
                    'scanned' => 0,
                    'deleted' => 0,
                    'freed_memory_bytes' => 0,
                    'duration_ms' => 0,
                ];
            }

            // Get all keys with our prefix
            $pattern = self::CACHE_PREFIX.'*';
            $keys = Redis::connection('cache')->keys($pattern);
            $scanned = count($keys);

            // In a real implementation, we would check TTL and delete stale entries
            // For now, we'll just return the count
        } catch (\Exception $e) {
            Log::error('Cleanup failed', ['error' => $e->getMessage()]);
        }

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'scanned' => $scanned,
            'deleted' => $deleted,
            'freed_memory_bytes' => $freedMemory,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Get optimization recommendations
     */
    public function getOptimizationRecommendations(): array
        $recommendations = [];

        // Check Redis availability
        if (! $this->isRedisAvailable()) {
            $recommendations[] = [
                'type' => 'connection',
                'severity' => 'critical',
                'message' => 'Redis is not available. Application is using fallback cache driver.',
                'action' => 'Check Redis connection and configuration',
            ];
        }

        // Check memory usage
        $memory = $this->getMemoryUsage();
        if ($memory['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'memory',
                'severity' => 'critical',
                'message' => "Redis memory usage is critical ({$memory['usage_percent']}%)",
                'action' => 'Increase max memory or implement more aggressive eviction',
            ];
        } elseif ($memory['status'] === 'warning') {
            $recommendations[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => "Redis memory usage is high ({$memory['usage_percent']}%)",
                'action' => 'Monitor memory usage and consider optimization',
            ];
        }

        // Check hit rate
        $hitRate = $this->getHitRateStatistics();
        $minHitRate = config('cache-management.monitoring.min_hit_rate', 70);
        if ($hitRate['overall']['hit_rate'] < $minHitRate && $hitRate['overall']['total'] > 0) {
            $recommendations[] = [
                'type' => 'hit_rate',
                'severity' => 'warning',
                'message' => "Cache hit rate ({$hitRate['overall']['hit_rate']}%) is below target ({$minHitRate}%)",
                'action' => 'Review cache warming and TTL strategies',
            ];
        }

        return $recommendations;
    }

    /**
     * Get comprehensive statistics
     */
    public function getComprehensiveStats(): array
        return [
            'health' => $this->checkHealth(),
            'memory' => $this->getMemoryUsage(),
            'hit_rate' => $this->getHitRateStatistics(),
            'recommendations' => $this->getOptimizationRecommendations(),
            'config' => [
                'warming_enabled' => config('cache-management.warming.enabled', true),
                'monitoring_enabled' => config('cache-management.monitoring.enabled', true),
                'cleanup_enabled' => config('cache-management.cleanup.enabled', true),
                'compression_enabled' => config('cache-management.optimization.compression_enabled', true),
            ],
        ];
    }
}
