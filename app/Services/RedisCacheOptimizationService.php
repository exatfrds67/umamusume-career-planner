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
            $prefix = config('database.redis.options.prefix');
            $cleanKeys = array_map(function ($key) use ($prefix) {
                return str_replace($prefix, '', $key);
            }, $keys);

            Redis::connection('cache')->del($cleanKeys);

            Log::info('Cache invalidated by pattern', [
                'pattern' => $pattern,
                'keys_deleted' => count($cleanKeys),
            ]);

            return count($cleanKeys);
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
     * Warm cache with frequently accessed data
     */
    public function warmCache(): void
    {
        Log::info('Starting cache warming process');

        try {
            // Warm static game data
            $this->warmStaticGameData();

            // Warm user preferences
            $this->warmUserPreferences();

            // Warm external API data
            $this->warmExternalAPIData();

            Log::info('Cache warming completed successfully');
        } catch (\Exception $e) {
            Log::error('Cache warming failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
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
    {
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
    {
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
    {
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
}
