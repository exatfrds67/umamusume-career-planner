<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Events\GameVersionUpdated;
use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCard;
use Carbon\Carbon;
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

        /** @var array<string, mixed>|null $data */
        $data = Cache::get($fullKey);

        if ($data === null || ! is_array($data)) {
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
        $cachedAt = isset($metadata['cached_at']) && ($metadata['cached_at'] instanceof \DateTimeInterface || is_string($metadata['cached_at']))
            ? $metadata['cached_at']
            : now();
        $age = (int) now()->diffInSeconds($cachedAt);
        $ttl = $this->getTTL($key);
        $isStale = $age > ($ttl * self::STALENESS_THRESHOLD);

        return array_merge($data, [
            '_cache' => [
                'cached_at' => $cachedAt,
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
                'data_size' => strlen((string) json_encode($data)),
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

        /** @var array<string, mixed>|null $metadata */
        $metadata = Cache::get($metadataKey);

        return is_array($metadata) ? $metadata : null;
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
        /** @var array{hits?: int, misses?: int, last_reset?: string}|null $rawStats */
        $rawStats = Cache::get(self::STATS_KEY);

        $stats = [
            'hits' => isset($rawStats['hits']) && is_int($rawStats['hits']) ? $rawStats['hits'] : 0,
            'misses' => isset($rawStats['misses']) && is_int($rawStats['misses']) ? $rawStats['misses'] : 0,
            'last_reset' => isset($rawStats['last_reset']) && is_string($rawStats['last_reset']) ? $rawStats['last_reset'] : now()->toIso8601String(),
        ];

        $totalRequests = $stats['hits'] + $stats['misses'];
        $hitRate = $totalRequests > 0 ? ($stats['hits'] / $totalRequests) * 100 : 0.0;

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
            if (config('cache.default', '') === 'redis') {
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
            if (config('cache.default', '') === 'redis') {
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
            if (config('cache.default', '') === 'redis') {
                $redis = Redis::connection();
                $pattern = self::CACHE_PREFIX.':*';
                $keys = $redis->keys($pattern);

                if (! is_array($keys)) {
                    return [
                        'total_keys' => 0,
                        'estimated_size_bytes' => 0,
                    ];
                }

                $totalSize = 0;

                foreach ($keys as $key) {
                    if (is_string($key)) {
                        $value = $redis->get($key);

                        if (is_string($value)) {
                            $totalSize += strlen($value);
                        }
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
    public function warmCache(string $priority = 'high'): array
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
                    $warmedItems = $warmedItems + $result['count'];
                    $items[$taskName] = 'success';

                    Log::info('[CacheManagerService] Cache warming task completed', [
                        'task' => $taskName,
                        'priority' => $task['priority'],
                        'items_warmed' => $result['count'],
                        'duration_ms' => round($taskDuration, 2),
                    ]);
                } else {
                    $failedItems = $failedItems + 1;
                    $items[$taskName] = 'failed';

                    Log::warning('[CacheManagerService] Cache warming task failed', [
                        'task' => $taskName,
                        'priority' => $task['priority'],
                        'error' => $result['error'] ?? 'Unknown error',
                        'duration_ms' => round($taskDuration, 2),
                    ]);
                }
            } catch (\Exception $e) {
                $failedItems = $failedItems + 1;
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
        $targetLevel = array_key_exists($priority, $priorityOrder) ? $priorityOrder[$priority] : 999;

        return array_filter(
            $allTasks,
            fn (array $task): bool => (array_key_exists($task['priority'], $priorityOrder) ? $priorityOrder[$task['priority']] : 999) <= $targetLevel
        );
    }

    /**
     * Warm cache with top characters
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmTopCharacters(): array
    {
        try {
            $characters = Character::query()
                ->select(['id', 'name', 'scenario_type', 'current_stats', 'status', 'career_stage'])
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();

            $warmedCount = 0;

            foreach ($characters as $character) {
                $cacheKey = "character_data:{$character->name}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $characterData = [
                    'id' => $character->id,
                    'name' => $character->name,
                    'scenario_type' => $character->scenario_type,
                    'current_stats' => $character->current_stats,
                    'status' => $character->status,
                    'career_stage' => $character->career_stage,
                    'warmed_at' => now()->toISOString(),
                ];

                $this->put($cacheKey, $characterData);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm top characters', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm cache with top support cards
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmTopSupportCards(): array
    {
        try {
            $cards = SupportCard::query()
                ->select(['id', 'name', 'card_type', 'rarity', 'meta_tier', 'is_active'])
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->limit(100)
                ->get();

            $warmedCount = 0;

            foreach ($cards as $card) {
                $cacheKey = "support_cards:{$card->id}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $cardData = [
                    'id' => $card->id,
                    'name' => $card->name,
                    'card_type' => $card->card_type,
                    'rarity' => $card->rarity,
                    'meta_tier' => $card->meta_tier,
                    'is_active' => $card->is_active,
                    'warmed_at' => now()->toISOString(),
                ];

                $this->put($cacheKey, $cardData);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm top support cards', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm cache with race definitions
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmRaceDefinitions(): array
    {
        try {
            $races = Race::query()
                ->select(['id', 'race_name', 'race_grade', 'distance_category', 'distance_meters', 'surface', 'track_type'])
                ->distinct('race_name')
                ->orderBy('race_name')
                ->limit(100)
                ->get();

            $warmedCount = 0;

            foreach ($races as $race) {
                $cacheKey = "race_data:{$race->id}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $raceData = [
                    'id' => $race->id,
                    'race_name' => $race->race_name,
                    'race_grade' => $race->race_grade,
                    'distance_category' => $race->distance_category,
                    'distance_meters' => $race->distance_meters,
                    'surface' => $race->surface,
                    'track_type' => $race->track_type,
                    'warmed_at' => now()->toISOString(),
                ];

                $this->put($cacheKey, $raceData);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm race definitions', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm cache with popular skills
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmPopularSkills(): array
    {
        try {
            $skills = Skill::query()
                ->select(['id', 'name', 'skill_type', 'rarity', 'base_sp_cost', 'meta_tier', 'is_active'])
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();

            $warmedCount = 0;

            foreach ($skills as $skill) {
                $cacheKey = "skills:{$skill->id}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $skillData = [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'skill_type' => $skill->skill_type,
                    'rarity' => $skill->rarity,
                    'base_sp_cost' => $skill->base_sp_cost,
                    'meta_tier' => $skill->meta_tier,
                    'is_active' => $skill->is_active,
                    'warmed_at' => now()->toISOString(),
                ];

                $this->put($cacheKey, $skillData);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm popular skills', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm cache with meta rankings
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmMetaRankings(): array
    {
        try {
            $rankingTypes = ['speed', 'stamina', 'power', 'guts', 'wisdom', 'overall'];
            $warmedCount = 0;

            foreach ($rankingTypes as $type) {
                $cacheKey = "meta_rankings:{$type}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $statColumn = match ($type) {
                    'speed' => 'speed_bonus',
                    'stamina' => 'stamina_bonus',
                    'power' => 'power_bonus',
                    'guts' => 'guts_bonus',
                    'wisdom' => 'wit_bonus',
                    default => null,
                };

                if ($type === 'overall') {
                    $topCards = SupportCard::query()
                        ->select(['id', 'name', 'card_type', 'meta_tier', 'rarity'])
                        ->where('is_active', true)
                        ->whereNotNull('meta_tier')
                        ->orderBy('meta_tier')
                        ->limit(20)
                        ->get()
                        ->toArray();
                } else {
                    if ($statColumn === null) {
                        continue;
                    }

                    $topCards = SupportCard::query()
                        ->select(['id', 'name', 'card_type', 'meta_tier', $statColumn])
                        ->where('is_active', true)
                        ->whereNotNull($statColumn)
                        ->orderByDesc($statColumn)
                        ->limit(20)
                        ->get()
                        ->toArray();
                }

                $rankingData = [
                    'type' => $type,
                    'rankings' => $topCards,
                    'total_cards' => count($topCards),
                    'warmed_at' => now()->toISOString(),
                ];

                $this->put($cacheKey, $rankingData);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm meta rankings', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm cache with game mechanics data
     *
     * @return array{success: bool, count: int, error?: string}
     */
    protected function warmGameMechanics(): array
    {
        try {
            $warmedCount = 0;

            $mechanicsData = [
                'stat_breakpoints' => [
                    'type' => 'stat_breakpoints',
                    'breakpoints' => [
                        ['value' => 901, 'label' => 'A-rank threshold'],
                        ['value' => 1200, 'label' => 'S-rank threshold / soft cap'],
                        ['value' => 1600, 'label' => 'SS-rank threshold'],
                    ],
                    'diminishing_returns' => 'Half value above 1200',
                    'stamina_contest_threshold' => 1200,
                ],
                'growth_rates' => [
                    'type' => 'growth_rates',
                    'multipliers' => ['+10%', '+20%', '+30%'],
                    'description' => 'Inherited bonuses multiplying training effectiveness',
                ],
                'training_multipliers' => [
                    'type' => 'training_multipliers',
                    'mood_modifiers' => [
                        'great' => 0.04,
                        'good' => 0.02,
                        'normal' => 0.0,
                        'bad' => -0.02,
                        'awful' => -0.04,
                    ],
                ],
                'hidden_mechanics' => [
                    'type' => 'hidden_mechanics',
                    'aptitude_grades' => ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'],
                    'aptitude_baseline' => 'A (0%)',
                    'aptitude_max' => 'S (+5%)',
                    'hint_discount_levels' => [10, 20, 30, 35, 40],
                ],
            ];

            foreach ($mechanicsData as $type => $data) {
                $cacheKey = "game_mechanics:{$type}";

                if ($this->has($cacheKey)) {
                    $warmedCount++;

                    continue;
                }

                $data['warmed_at'] = now()->toISOString();
                $this->put($cacheKey, $data);
                $warmedCount++;
            }

            return [
                'success' => true,
                'count' => $warmedCount,
            ];
        } catch (\Exception $e) {
            Log::error('[CacheManagerService] Failed to warm game mechanics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
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

        /** @var array<string, mixed>|null $stats */
        $stats = Cache::get($statsKey);

        return is_array($stats) ? $stats : null;
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
        $ttlValue = $metadata['ttl'] ?? self::DEFAULT_TTL;
        $ttl = is_int($ttlValue) ? $ttlValue : self::DEFAULT_TTL;

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
        /** @var array{hits?: int, misses?: int, last_reset?: string}|null $rawStats */
        $rawStats = Cache::get(self::STATS_KEY);

        $stats = [
            'hits' => isset($rawStats['hits']) && is_int($rawStats['hits']) ? $rawStats['hits'] : 0,
            'misses' => isset($rawStats['misses']) && is_int($rawStats['misses']) ? $rawStats['misses'] : 0,
            'last_reset' => isset($rawStats['last_reset']) && is_string($rawStats['last_reset']) ? $rawStats['last_reset'] : now()->toIso8601String(),
        ];

        $stats['hits']++;

        Cache::put(self::STATS_KEY, $stats, 86400 * 30);
    }

    /**
     * Record cache miss in statistics
     */
    private function recordCacheMiss(string $key): void
    {
        /** @var array{hits?: int, misses?: int, last_reset?: string}|null $rawStats */
        $rawStats = Cache::get(self::STATS_KEY);

        $stats = [
            'hits' => isset($rawStats['hits']) && is_int($rawStats['hits']) ? $rawStats['hits'] : 0,
            'misses' => isset($rawStats['misses']) && is_int($rawStats['misses']) ? $rawStats['misses'] : 0,
            'last_reset' => isset($rawStats['last_reset']) && is_string($rawStats['last_reset']) ? $rawStats['last_reset'] : now()->toIso8601String(),
        ];

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

            if (config('cache.default', '') === 'redis') {
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
                    $invalidatedCount = $invalidatedCount + 1;
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
        $version = Cache::get(self::CACHE_PREFIX.':game_version');

        return is_string($version) ? $version : null;
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

        /** @var array<int, array<string, mixed>> $history */
        $history = Cache::get($historyKey, []);

        if (! is_array($history)) {
            $history = [];
        }

        $history[] = [
            'previous_version' => $previousVersion,
            'new_version' => $newVersion,
            'changed_at' => now()->toIso8601String(),
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

        /** @var array<array{previous_version: ?string, new_version: string, changed_at: string}> $history */
        $history = Cache::get($historyKey, []);

        return is_array($history) ? $history : [];
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
                if (config('cache.default', '') === 'redis') {
                    $redis = Redis::connection();
                    $keys = $redis->keys($fullPattern);

                    $staleCount = 0;
                    $totalCount = count($keys);

                    foreach ($keys as $key) {
                        $metadataKey = str_replace(self::CACHE_PREFIX.':', self::CACHE_PREFIX.':metadata:', $key);
                        /** @var array<string, mixed>|null $metadata */
                        $metadata = Cache::get($metadataKey);

                        if (is_array($metadata) && isset($metadata['cached_at']) && is_string($metadata['cached_at'])) {
                            $age = now()->diffInSeconds(Carbon::parse($metadata['cached_at']));
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
            if (config('cache.default', '') !== 'redis') {
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
