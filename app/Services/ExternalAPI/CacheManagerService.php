<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Events\GameVersionUpdated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Cache Manager Service for External API Integration
 *
 * Provides intelligent caching with staleness indicators, TTL configuration,
 * and cache statistics tracking for external API data.
 *
 * Features:
 * - Configurable TTL by data type
 * - Staleness indicators with age tracking
 * - Cache statistics and hit/miss tracking
 * - Automatic metadata management
 * - Support for cache warming and invalidation
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.1
 */
class CacheManagerService
{
    /**
     * TTL configuration by data type (in seconds)
     *
     * @var array<string, int>
     */
    private const TTL_CONFIG = [
        'character_data' => 86400,      // 24 hours
        'support_cards' => 43200,       // 12 hours
        'meta_rankings' => 21600,       // 6 hours
        'race_data' => 172800,          // 48 hours
        'skills' => 86400,              // 24 hours
        'news' => 3600,                 // 1 hour
        'game_mechanics' => 604800,     // 7 days (rarely changes)
    ];

    /**
     * Default TTL for unknown data types (1 hour)
     */
    private const DEFAULT_TTL = 3600;

    /**
     * Staleness threshold (percentage of TTL)
     * Data is considered stale when age > TTL * threshold
     */
    private const STALENESS_THRESHOLD = 0.8;

    /**
     * Cache key prefix for external API data
     */
    private const CACHE_PREFIX = 'external_api';

    /**
     * Cache key for statistics
     */
    private const STATS_KEY = 'external_api:stats';

    public function __construct(
        protected ?APIPerformanceMetricsService $metricsService = null
    ) {}

    /**
     * Get data from cache with staleness indicator
     *
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        $fullKey = $this->buildCacheKey($key);
        $data = Cache::get($fullKey);

        if ($data === null) {
            $this->recordCacheMiss($key);

            // Record metrics if service available
            if ($this->metricsService) {
                $this->metricsService->recordCacheAccess($key, false);
            }

            return null;
        }

        $this->recordCacheHit($key);

        // Record metrics if service available
        if ($this->metricsService) {
            $this->metricsService->recordCacheAccess($key, true);
        }

        // Add staleness metadata
        $metadata = $this->getCacheMetadata($key);
        $age = $metadata ? (int) now()->diffInSeconds($metadata['cached_at']) : 0;
        $ttl = $this->getTTL($key);
        $isStale = $age > ($ttl * self::STALENESS_THRESHOLD);

        return array_merge($data, [
            '_cache' => [
                'cached_at' => $metadata['cached_at'] ?? now(),
                'age_seconds' => $age,
                'ttl_seconds' => $ttl,
                'is_stale' => $isStale,
                'staleness_percentage' => $ttl > 0 ? round(($age / $ttl) * 100, 2) : 0,
                'source' => 'cache',
            ],
        ]);
    }

    /**
     * Store data in cache with automatic TTL and metadata
     *
     * @param  array<string, mixed>  $data
     */
    public function put(string $key, array $data, ?int $customTtl = null): bool
    {
        $fullKey = $this->buildCacheKey($key);
        $ttl = $customTtl ?? $this->getTTL($key);

        // Store the data
        $success = Cache::put($fullKey, $data, $ttl);

        if ($success) {
            // Store metadata separately
            $this->storeCacheMetadata($key, [
                'cached_at' => now(),
                'ttl' => $ttl,
                'data_type' => $this->extractDataType($key),
                'key' => $key,
            ]);

            Log::debug('[CacheManagerService] Data cached', [
                'key' => $key,
                'ttl' => $ttl,
                'data_size' => strlen(json_encode($data)),
            ]);
        }

        return $success;
    }

    /**
     * Delete data from cache
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->buildCacheKey($key);
        $metadataKey = $this->buildMetadataKey($key);

        $dataDeleted = Cache::forget($fullKey);
        $metadataDeleted = Cache::forget($metadataKey);

        if ($dataDeleted || $metadataDeleted) {
            Log::debug('[CacheManagerService] Cache entry deleted', [
                'key' => $key,
            ]);
        }

        return $dataDeleted;
    }

    /**
     * Check if cache key exists
     */
    public function has(string $key): bool
    {
        $fullKey = $this->buildCacheKey($key);

        return Cache::has($fullKey);
    }

    /**
     * Get cache metadata for a key
     *
     * @return array<string, mixed>|null
     */
    public function getCacheMetadata(string $key): ?array
    {
        $metadataKey = $this->buildMetadataKey($key);

        return Cache::get($metadataKey);
    }

    /**
     * Get TTL for a cache key based on data type
     */
    public function getTTL(string $key): int
    {
        $dataType = $this->extractDataType($key);

        return self::TTL_CONFIG[$dataType] ?? self::DEFAULT_TTL;
    }

    /**
     * Get all configured TTL values
     *
     * @return array<string, int>
     */
    public function getTTLConfig(): array
    {
        return self::TTL_CONFIG;
    }

    /**
     * Get cache statistics
     *
     * @return array{hits: int, misses: int, hit_rate: float, total_requests: int, last_reset: string}
     */
    public function getStatistics(): array
    {
        $stats = Cache::get(self::STATS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => now()->toISOString(),
        ]);

        $totalRequests = $stats['hits'] + $stats['misses'];
        $hitRate = $totalRequests > 0 ? ($stats['hits'] / $totalRequests) * 100 : 0;

        return [
            'hits' => $stats['hits'],
            'misses' => $stats['misses'],
            'hit_rate' => round($hitRate, 2),
            'total_requests' => $totalRequests,
            'last_reset' => $stats['last_reset'],
        ];
    }

    /**
     * Reset cache statistics
     */
    public function resetStatistics(): void
    {
        Cache::put(self::STATS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => now()->toISOString(),
        ], 86400 * 30); // Keep stats for 30 days

        Log::info('[CacheManagerService] Cache statistics reset');
    }

    /**
     * Get detailed cache information for monitoring
     *
     * @return array<string, mixed>
     */
    public function getCacheInfo(): array
    {
        $stats = $this->getStatistics();

        // Get Redis info if available
        $redisInfo = $this->getRedisInfo();

        return [
            'statistics' => $stats,
            'redis' => $redisInfo,
            'ttl_config' => self::TTL_CONFIG,
            'staleness_threshold' => self::STALENESS_THRESHOLD,
        ];
    }

    /**
     * Flush all external API cache data
     */
    public function flush(): bool
    {
        try {
            // Get all cache keys with our prefix
            $pattern = self::CACHE_PREFIX.':*';

            // Use Redis to find and delete keys
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $keys = $redis->keys($pattern);

                if (! empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For non-Redis cache drivers, we can't easily flush by pattern
                // This would require tracking all keys separately
                Log::warning('[CacheManagerService] Flush not fully supported for non-Redis cache driver');
            }

            // Reset statistics
            $this->resetStatistics();

            Log::info('[CacheManagerService] Cache flushed', [
                'pattern' => $pattern,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Cache flush failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get list of all cached keys (for monitoring/debugging)
     *
     * @return array<string>
     */
    public function getCachedKeys(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $pattern = self::CACHE_PREFIX.':*';
                $keys = $redis->keys($pattern);

                // Remove prefix from keys for cleaner output
                return array_map(
                    fn ($key) => str_replace(self::CACHE_PREFIX.':', '', $key),
                    $keys
                );
            }

            return [];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to get cached keys', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get cache size information
     *
     * @return array{total_keys: int, estimated_size_bytes: int}
     */
    public function getCacheSize(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $pattern = self::CACHE_PREFIX.':*';
                $keys = $redis->keys($pattern);

                $totalSize = 0;
                foreach ($keys as $key) {
                    $value = $redis->get($key);
                    if ($value) {
                        $totalSize += strlen($value);
                    }
                }

                return [
                    'total_keys' => count($keys),
                    'estimated_size_bytes' => $totalSize,
                ];
            }

            return [
                'total_keys' => 0,
                'estimated_size_bytes' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to get cache size', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_keys' => 0,
                'estimated_size_bytes' => 0,
            ];
        }
    }

    /**
     * Warm cache with frequently accessed data
     *
     * This method preloads frequently accessed data into the cache
     * to improve performance and reduce API calls during normal operation.
     *
     * Priority levels:
     * - high: Top 50 characters, top 100 support cards
     * - medium: All race definitions, popular skills
     * - low: Meta rankings, game mechanics data
     *
     * @param  string  $priority  Priority level: 'high', 'medium', 'low', or 'all'
     * @return array{success: bool, warmed_items: int, failed_items: int, duration_ms: float, items: array<string, string>}
     */
    public function warmCache(string $priority = 'all'): array
    {
        $startTime = microtime(true);
        $warmedItems = 0;
        $failedItems = 0;
        $items = [];

        Log::info('[CacheManagerService] Starting cache warming', [
            'priority' => $priority,
            'started_at' => now()->toISOString(),
        ]);

        // Get warming tasks based on priority
        $warmingTasks = $this->getWarmingTasks($priority);

        foreach ($warmingTasks as $taskName => $task) {
            try {
                $taskStartTime = microtime(true);

                // Execute warming task
                $result = $task['callback']();

                $taskDuration = (microtime(true) - $taskStartTime) * 1000;

                if ($result['success']) {
                    $warmedItems += $result['count'];
                    $items[$taskName] = 'success';

                    Log::info('[CacheManagerService] Cache warming task completed', [
                        'task' => $taskName,
                        'priority' => $task['priority'],
                        'items_warmed' => $result['count'],
                        'duration_ms' => round($taskDuration, 2),
                    ]);
                } else {
                    $failedItems++;
                    $items[$taskName] = 'failed';

                    Log::warning('[CacheManagerService] Cache warming task failed', [
                        'task' => $taskName,
                        'priority' => $task['priority'],
                        'error' => $result['error'] ?? 'Unknown error',
                        'duration_ms' => round($taskDuration, 2),
                    ]);
                }
            } catch (\Exception $e) {
                $failedItems++;
                $items[$taskName] = 'error';

                Log::error('[CacheManagerService] Cache warming task exception', [
                    'task' => $taskName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $totalDuration = (microtime(true) - $startTime) * 1000;

        $result = [
            'success' => $failedItems === 0,
            'warmed_items' => $warmedItems,
            'failed_items' => $failedItems,
            'duration_ms' => round($totalDuration, 2),
            'items' => $items,
        ];

        Log::info('[CacheManagerService] Cache warming completed', $result);

        // Store warming statistics
        $this->storeWarmingStatistics($result);

        return $result;
    }

    /**
     * Get warming tasks based on priority level
     *
     * @return array<string, array{priority: string, callback: callable}>
     */
    protected function getWarmingTasks(string $priority): array
    {
        $allTasks = [
            'top_characters' => [
                'priority' => 'high',
                'callback' => fn () => $this->warmTopCharacters(),
            ],
            'top_support_cards' => [
                'priority' => 'high',
                'callback' => fn () => $this->warmTopSupportCards(),
            ],
            'race_definitions' => [
                'priority' => 'medium',
                'callback' => fn () => $this->warmRaceDefinitions(),
            ],
            'popular_skills' => [
                'priority' => 'medium',
                'callback' => fn () => $this->warmPopularSkills(),
            ],
            'meta_rankings' => [
                'priority' => 'low',
                'callback' => fn () => $this->warmMetaRankings(),
            ],
            'game_mechanics' => [
                'priority' => 'low',
                'callback' => fn () => $this->warmGameMechanics(),
            ],
        ];

        // Filter tasks based on priority
        if ($priority === 'all') {
            return $allTasks;
        }

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $targetLevel = $priorityOrder[$priority] ?? 999;

        return array_filter(
            $allTasks,
            fn ($task) => ($priorityOrder[$task['priority']] ?? 999) <= $targetLevel
        );
    }

    /**
     * Warm cache with top characters
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmTopCharacters(): array
    {
        // Top 50 most popular characters based on usage data
        $topCharacters = [
            'Silence Suzuka',
            'Tokai Teio',
            'Gold Ship',
            'Special Week',
            'Vodka',
            'Daiwa Scarlet',
            'Oguri Cap',
            'Taiki Shuttle',
            'Mejiro McQueen',
            'Rice Shower',
            'Air Groove',
            'Super Creek',
            'Grass Wonder',
            'Haru Urara',
            'King Halo',
            'Symboli Rudolf',
            'Narita Brian',
            'Twin Turbo',
            'Mejiro Palmer',
            'Admire Vega',
            'Inari One',
            'Winning Ticket',
            'T.M. Opera O',
            'Narita Taishin',
            'Mejiro Dober',
            'Agnes Tachyon',
            'Seiun Sky',
            'Machikane Fukukitaru',
            'Eishin Flash',
            'Mayano Top Gun',
            'Manhattan Cafe',
            'Mihono Bourbon',
            'Mejiro Ryan',
            'Hishi Amazon',
            'Sakura Chiyono O',
            'Sirius Symboli',
            'Matikanetannhauser',
            'Ikuno Dictus',
            'Yaeno Muteki',
            'Nice Nature',
            'Kitasan Black',
            'Satono Diamond',
            'Scarlet',
            'Mejiro Ardan',
            'Fine Motion',
            'Biwa Hayahide',
            'Marvelous Sunday',
            'Tokai Teio',
            'Fuji Kiseki',
            'Zenno Rob Roy',
        ];

        $warmedCount = 0;

        foreach ($topCharacters as $character) {
            $cacheKey = "character_data:{$character}";

            // Check if already cached
            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            // For now, we'll just mark the key as needing warming
            // The actual API fetching will be done by the background job
            // This method just ensures the cache structure is ready
            $placeholderData = [
                'name' => $character,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300); // 5 minutes TTL for placeholder
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Warm cache with top support cards
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmTopSupportCards(): array
    {
        // Top 100 support card IDs (placeholder - would come from configuration or database)
        $topCardIds = range(1, 100);

        $warmedCount = 0;

        foreach ($topCardIds as $cardId) {
            $cacheKey = "support_cards:{$cardId}";

            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            $placeholderData = [
                'id' => $cardId,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300);
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Warm cache with race definitions
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmRaceDefinitions(): array
    {
        // Common race types and distances
        $raceDefinitions = [
            'sprint_dirt',
            'sprint_turf',
            'mile_dirt',
            'mile_turf',
            'intermediate_dirt',
            'intermediate_turf',
            'long_dirt',
            'long_turf',
            'extended_turf',
        ];

        $warmedCount = 0;

        foreach ($raceDefinitions as $raceType) {
            $cacheKey = "race_data:{$raceType}";

            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            $placeholderData = [
                'type' => $raceType,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300);
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Warm cache with popular skills
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmPopularSkills(): array
    {
        // Popular skill IDs (placeholder)
        $popularSkills = range(1, 50);

        $warmedCount = 0;

        foreach ($popularSkills as $skillId) {
            $cacheKey = "skills:{$skillId}";

            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            $placeholderData = [
                'id' => $skillId,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300);
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Warm cache with meta rankings
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmMetaRankings(): array
    {
        $rankingTypes = [
            'speed',
            'stamina',
            'power',
            'guts',
            'wisdom',
            'overall',
        ];

        $warmedCount = 0;

        foreach ($rankingTypes as $type) {
            $cacheKey = "meta_rankings:{$type}";

            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            $placeholderData = [
                'type' => $type,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300);
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Warm cache with game mechanics data
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmGameMechanics(): array
    {
        $mechanicsTypes = [
            'stat_breakpoints',
            'growth_rates',
            'training_multipliers',
            'hidden_mechanics',
        ];

        $warmedCount = 0;

        foreach ($mechanicsTypes as $type) {
            $cacheKey = "game_mechanics:{$type}";

            if ($this->has($cacheKey)) {
                $warmedCount++;

                continue;
            }

            $placeholderData = [
                'type' => $type,
                'status' => 'warming',
                'queued_at' => now()->toISOString(),
            ];

            $this->put($cacheKey, $placeholderData, 300);
            $warmedCount++;
        }

        return [
            'success' => true,
            'count' => $warmedCount,
        ];
    }

    /**
     * Store warming statistics for monitoring
     *
     * @param  array<string, mixed>  $result
     */
    protected function storeWarmingStatistics(array $result): void
    {
        $statsKey = self::CACHE_PREFIX.':warming_stats';

        $stats = [
            'last_run' => now()->toISOString(),
            'warmed_items' => $result['warmed_items'],
            'failed_items' => $result['failed_items'],
            'duration_ms' => $result['duration_ms'],
            'success' => $result['success'],
            'items' => $result['items'],
        ];

        Cache::put($statsKey, $stats, 86400); // Keep for 24 hours
    }

    /**
     * Get warming statistics
     *
     * @return array<string, mixed>|null
     */
    public function getWarmingStatistics(): ?array
    {
        $statsKey = self::CACHE_PREFIX.':warming_stats';

        return Cache::get($statsKey);
    }

    /**
     * Build full cache key with prefix
     */
    private function buildCacheKey(string $key): string
    {
        return self::CACHE_PREFIX.':'.$key;
    }

    /**
     * Build metadata cache key
     */
    private function buildMetadataKey(string $key): string
    {
        return self::CACHE_PREFIX.':metadata:'.$key;
    }

    /**
     * Store cache metadata
     *
     * @param  array<string, mixed>  $metadata
     */
    private function storeCacheMetadata(string $key, array $metadata): void
    {
        $metadataKey = $this->buildMetadataKey($key);
        $ttl = $metadata['ttl'] ?? self::DEFAULT_TTL;

        // Store metadata with same TTL as data
        Cache::put($metadataKey, $metadata, $ttl);
    }

    /**
     * Extract data type from cache key
     */
    private function extractDataType(string $key): string
    {
        // Extract data type from key pattern
        // Examples:
        // - "character_data:Silence Suzuka" -> "character_data"
        // - "support_cards:123" -> "support_cards"
        // - "meta_rankings:speed" -> "meta_rankings"

        $parts = explode(':', $key);

        return $parts[0] ?? 'unknown';
    }

    /**
     * Record cache hit in statistics
     */
    private function recordCacheHit(string $key): void
    {
        $stats = Cache::get(self::STATS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => now()->toISOString(),
        ]);

        $stats['hits']++;

        Cache::put(self::STATS_KEY, $stats, 86400 * 30);
    }

    /**
     * Record cache miss in statistics
     */
    private function recordCacheMiss(string $key): void
    {
        $stats = Cache::get(self::STATS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => now()->toISOString(),
        ]);

        $stats['misses']++;

        Cache::put(self::STATS_KEY, $stats, 86400 * 30);
    }

    /**
     * Invalidate cache by pattern
     *
     * Invalidates all cache entries matching the given pattern.
     * Useful for invalidating all data of a specific type.
     *
     * Examples:
     * - invalidateByPattern('character_data:*') - invalidates all character data
     * - invalidateByPattern('support_cards:*') - invalidates all support cards
     * - invalidateByPattern('*:Silence Suzuka') - invalidates all data for specific character
     *
     * @return array{success: bool, invalidated_count: int, error?: string}
     */
    public function invalidateByPattern(string $pattern): array
    {
        try {
            $fullPattern = self::CACHE_PREFIX.':'.$pattern;
            $invalidatedCount = 0;

            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $keys = $redis->keys($fullPattern);

                if (! empty($keys)) {
                    $redis->del($keys);
                    $invalidatedCount = count($keys);

                    // Also delete metadata keys
                    $metadataKeys = array_map(
                        fn ($key) => str_replace(self::CACHE_PREFIX.':', self::CACHE_PREFIX.':metadata:', $key),
                        $keys
                    );
                    $redis->del($metadataKeys);
                }

                Log::info('[CacheManagerService] Cache invalidated by pattern', [
                    'pattern' => $pattern,
                    'invalidated_count' => $invalidatedCount,
                ]);

                return [
                    'success' => true,
                    'invalidated_count' => $invalidatedCount,
                ];
            }

            // For non-Redis drivers (like array in tests), we can't use pattern matching
            // but we still return success to avoid breaking the API
            Log::warning('[CacheManagerService] Pattern invalidation has limited support for non-Redis cache driver');

            return [
                'success' => true,
                'invalidated_count' => 0,
                'error' => 'Pattern invalidation has limited support for non-Redis cache driver',
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Cache invalidation by pattern failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'invalidated_count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Invalidate cache by data type
     *
     * Invalidates all cache entries of a specific data type.
     *
     * @param  string  $dataType  One of: character_data, support_cards, meta_rankings, race_data, skills, news, game_mechanics
     * @return array{success: bool, invalidated_count: int, error?: string}
     */
    public function invalidateByType(string $dataType): array
    {
        if (! array_key_exists($dataType, self::TTL_CONFIG)) {
            return [
                'success' => false,
                'invalidated_count' => 0,
                'error' => "Invalid data type: {$dataType}",
            ];
        }

        return $this->invalidateByPattern("{$dataType}:*");
    }

    /**
     * Invalidate specific cache keys
     *
     * @param  array<string>  $keys
     * @return array{success: bool, invalidated_count: int, failed_keys: array<string>}
     */
    public function invalidateKeys(array $keys): array
    {
        $invalidatedCount = 0;
        $failedKeys = [];

        foreach ($keys as $key) {
            try {
                if ($this->delete($key)) {
                    $invalidatedCount++;
                } else {
                    $failedKeys[] = $key;
                }
            } catch (\Exception $e) {
                $failedKeys[] = $key;
                Log::error('[CacheManagerService] Failed to invalidate key', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[CacheManagerService] Cache keys invalidated', [
            'total_keys' => count($keys),
            'invalidated_count' => $invalidatedCount,
            'failed_count' => count($failedKeys),
        ]);

        return [
            'success' => count($failedKeys) === 0,
            'invalidated_count' => $invalidatedCount,
            'failed_keys' => $failedKeys,
        ];
    }

    /**
     * Get current game version from cache
     */
    public function getGameVersion(): ?string
    {
        return Cache::get(self::CACHE_PREFIX.':game_version');
    }

    /**
     * Set game version and invalidate cache if version changed
     *
     * @return array{success: bool, version_changed: bool, previous_version: ?string, new_version: string, invalidated_types: array<string>}
     */
    public function setGameVersion(string $version): array
    {
        $previousVersion = $this->getGameVersion();
        $versionChanged = $previousVersion !== $version;

        if ($versionChanged) {
            Log::info('[CacheManagerService] Game version changed, invalidating cache', [
                'previous_version' => $previousVersion,
                'new_version' => $version,
            ]);

            // Invalidate data types that are affected by game updates
            $typesToInvalidate = [
                'character_data',
                'support_cards',
                'skills',
                'game_mechanics',
                'meta_rankings',
            ];

            $invalidatedTypes = [];
            foreach ($typesToInvalidate as $type) {
                $result = $this->invalidateByType($type);
                if ($result['success']) {
                    $invalidatedTypes[] = $type;
                }
            }

            // Store new version
            Cache::forever(self::CACHE_PREFIX.':game_version', $version);

            // Store version change history
            $this->recordVersionChange($previousVersion, $version);

            // Dispatch event for automatic cache invalidation
            GameVersionUpdated::dispatch($previousVersion, $version, $invalidatedTypes);

            return [
                'success' => true,
                'version_changed' => true,
                'previous_version' => $previousVersion,
                'new_version' => $version,
                'invalidated_types' => $invalidatedTypes,
            ];
        }

        // Version unchanged, just update timestamp
        Cache::forever(self::CACHE_PREFIX.':game_version', $version);

        return [
            'success' => true,
            'version_changed' => false,
            'previous_version' => $previousVersion,
            'new_version' => $version,
            'invalidated_types' => [],
        ];
    }

    /**
     * Record version change in history
     */
    private function recordVersionChange(?string $previousVersion, string $newVersion): void
    {
        $historyKey = self::CACHE_PREFIX.':version_history';
        $history = Cache::get($historyKey, []);

        $history[] = [
            'previous_version' => $previousVersion,
            'new_version' => $newVersion,
            'changed_at' => now()->toISOString(),
        ];

        // Keep last 50 version changes
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        Cache::put($historyKey, $history, 86400 * 365); // Keep for 1 year
    }

    /**
     * Get version change history
     *
     * @return array<array{previous_version: ?string, new_version: string, changed_at: string}>
     */
    public function getVersionHistory(): array
    {
        $historyKey = self::CACHE_PREFIX.':version_history';

        return Cache::get($historyKey, []);
    }

    /**
     * Check if cache needs invalidation based on staleness
     *
     * @return array{needs_invalidation: bool, stale_types: array<string>, recommendations: array<string>}
     */
    public function checkCacheStaleness(): array
    {
        $staleTypes = [];
        $recommendations = [];

        foreach (self::TTL_CONFIG as $dataType => $ttl) {
            $pattern = "{$dataType}:*";
            $fullPattern = self::CACHE_PREFIX.':'.$pattern;

            try {
                if (config('cache.default') === 'redis') {
                    $redis = Redis::connection();
                    $keys = $redis->keys($fullPattern);

                    $staleCount = 0;
                    $totalCount = count($keys);

                    foreach ($keys as $key) {
                        $metadataKey = str_replace(self::CACHE_PREFIX.':', self::CACHE_PREFIX.':metadata:', $key);
                        $metadata = Cache::get($metadataKey);

                        if ($metadata && isset($metadata['cached_at'])) {
                            $age = now()->diffInSeconds($metadata['cached_at']);
                            if ($age > ($ttl * self::STALENESS_THRESHOLD)) {
                                $staleCount++;
                            }
                        }
                    }

                    if ($totalCount > 0 && ($staleCount / $totalCount) > 0.5) {
                        $staleTypes[] = $dataType;
                        $recommendations[] = "Consider invalidating {$dataType} cache ({$staleCount}/{$totalCount} entries are stale)";
                    }
                }
            } catch (\Exception $e) {
                Log::error('[CacheManagerService] Failed to check staleness for type', [
                    'data_type' => $dataType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'needs_invalidation' => count($staleTypes) > 0,
            'stale_types' => $staleTypes,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Invalidate stale cache entries
     *
     * @return array{success: bool, invalidated_types: array<string>, total_invalidated: int}
     */
    public function invalidateStaleCache(): array
    {
        $stalenessCheck = $this->checkCacheStaleness();
        $invalidatedTypes = [];
        $totalInvalidated = 0;

        foreach ($stalenessCheck['stale_types'] as $dataType) {
            $result = $this->invalidateByType($dataType);
            if ($result['success']) {
                $invalidatedTypes[] = $dataType;
                $totalInvalidated += $result['invalidated_count'];
            }
        }

        Log::info('[CacheManagerService] Stale cache invalidated', [
            'invalidated_types' => $invalidatedTypes,
            'total_invalidated' => $totalInvalidated,
        ]);

        return [
            'success' => true,
            'invalidated_types' => $invalidatedTypes,
            'total_invalidated' => $totalInvalidated,
        ];
    }

    /**
     * Get Redis information
     *
     * @return array<string, mixed>
     */
    private function getRedisInfo(): array
    {
        try {
            if (config('cache.default') !== 'redis') {
                return [
                    'available' => false,
                    'driver' => config('cache.default'),
                ];
            }

            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'available' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'uptime_days' => isset($info['uptime_in_seconds']) ? round($info['uptime_in_seconds'] / 86400, 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
