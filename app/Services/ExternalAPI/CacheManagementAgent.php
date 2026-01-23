<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Management Agent for External API Integration
 *
 * Coordinates intelligent cache optimization using MCP strands-agents server for
 * predictive prefetching, eviction policy management, and performance analytics.
 *
 * Features:
 * - Cache optimization strategies (LRU, LFU, TTL-based)
 * - Predictive prefetching based on access patterns
 * - Eviction policy management with configurable strategies
 * - Performance analytics (hit rates, latency, memory usage)
 * - MCP agent coordination for complex cache operations
 * - Integration with CacheManagerService
 *
 * Requirements: 14.4 (MCP Subagent Coordination for API Management)
 * Task: 3.3.1
 */
class CacheManagementAgent
{
    /**
     * Eviction strategies
     */
    public const EVICTION_LRU = 'lru';

    public const EVICTION_LFU = 'lfu';

    public const EVICTION_TTL = 'ttl';

    public const EVICTION_FIFO = 'fifo';

    public const EVICTION_RANDOM = 'random';

    /**
     * Optimization strategies
     */
    public const OPTIMIZATION_AGGRESSIVE = 'aggressive';

    public const OPTIMIZATION_BALANCED = 'balanced';

    public const OPTIMIZATION_CONSERVATIVE = 'conservative';

    /**
     * Prefetch strategies
     */
    public const PREFETCH_PREDICTIVE = 'predictive';

    public const PREFETCH_RELATED = 'related';

    public const PREFETCH_POPULAR = 'popular';

    public const PREFETCH_SCHEDULED = 'scheduled';

    /**
     * Default configuration values
     */
    private const DEFAULT_EVICTION_STRATEGY = self::EVICTION_LRU;

    private const DEFAULT_OPTIMIZATION_STRATEGY = self::OPTIMIZATION_BALANCED;

    private const DEFAULT_PREFETCH_THRESHOLD = 0.7;

    private const DEFAULT_MEMORY_THRESHOLD_PERCENT = 80;

    private const MAX_ACCESS_HISTORY = 1000;

    private const ANALYTICS_RETENTION_HOURS = 24;

    /**
     * Cache key prefixes
     */
    private const ACCESS_PATTERN_KEY = 'cache_agent:access_patterns';

    private const ANALYTICS_KEY = 'cache_agent:analytics';

    private const PREFETCH_QUEUE_KEY = 'cache_agent:prefetch_queue';

    /**
     * Current eviction strategy
     */
    private string $evictionStrategy = self::DEFAULT_EVICTION_STRATEGY;

    /**
     * Current optimization strategy
     */
    private string $optimizationStrategy = self::DEFAULT_OPTIMIZATION_STRATEGY;

    /**
     * Access pattern tracking
     *
     * @var array<string, array{count: int, last_access: int, first_access: int}>
     */
    private array $accessPatterns = [];

    /**
     * Performance analytics data
     *
     * @var array<string, mixed>
     */
    private array $analyticsData = [];

    public function __construct(
        private MCPClientService $mcpClient,
        private CacheManagerService $cacheManager
    ) {
        $this->loadAccessPatterns();
        $this->loadAnalyticsData();
    }

    /**
     * Optimize cache based on current strategy
     *
     * @param  array{strategy?: string, memory_threshold?: int, eviction_count?: int}  $options
     * @return array{success: bool, optimizations: array<string, mixed>, metrics: array<string, mixed>}
     */
    public function optimizeCache(): array
        $startTime = microtime(true);
        $strategy = $options['strategy'] ?? $this->optimizationStrategy;
        $memoryThreshold = $options['memory_threshold'] ?? self::DEFAULT_MEMORY_THRESHOLD_PERCENT;

        Log::info('[CacheManagementAgent] Starting cache optimization', [
            'strategy' => $strategy,
            'memory_threshold' => $memoryThreshold,
        ]);

        $optimizations = [];
        $metrics = $this->collectMetrics();

        // Check memory usage and evict if necessary
        if ($metrics['memory_usage_percent'] > $memoryThreshold) {
            $evictionResult = $this->performEviction($options['eviction_count'] ?? 10);
            $optimizations['eviction'] = $evictionResult;
        }

        // Analyze access patterns and suggest prefetching
        $prefetchSuggestions = $this->analyzePrefetchOpportunities();
        $optimizations['prefetch_suggestions'] = $prefetchSuggestions;

        // Identify stale entries for refresh
        $staleEntries = $this->identifyStaleEntries();
        $optimizations['stale_entries'] = $staleEntries;

        // Apply strategy-specific optimizations
        $strategyOptimizations = $this->applyStrategyOptimizations($strategy, $metrics);
        $optimizations['strategy_optimizations'] = $strategyOptimizations;

        $duration = (microtime(true) - $startTime) * 1000;

        $result = [
            'success' => true,
            'optimizations' => $optimizations,
            'metrics' => array_merge($metrics, [
                'optimization_duration_ms' => round($duration, 2),
                'strategy_used' => $strategy,
                'timestamp' => now()->toIso8601String(),
            ]),
        ];

        $this->recordAnalytics('optimization', $result);

        Log::info('[CacheManagementAgent] Cache optimization completed', [
            'duration_ms' => round($duration, 2),
            'evictions' => $optimizations['eviction']['evicted_count'] ?? 0,
            'prefetch_suggestions' => count($prefetchSuggestions),
        ]);

        return $result;
    }

    /**
     * Perform predictive prefetching based on access patterns
     *
     * @param  array{strategy?: string, max_items?: int, threshold?: float}  $options
     * @return array{success: bool, prefetched: array<string>, skipped: array<string>, metadata: array<string, mixed>}
     */
    public function performPrefetch(): array
        $startTime = microtime(true);
        $strategy = $options['strategy'] ?? self::PREFETCH_PREDICTIVE;
        $maxItems = $options['max_items'] ?? 10;
        $threshold = $options['threshold'] ?? self::DEFAULT_PREFETCH_THRESHOLD;

        Log::info('[CacheManagementAgent] Starting prefetch operation', [
            'strategy' => $strategy,
            'max_items' => $maxItems,
            'threshold' => $threshold,
        ]);

        $prefetched = [];
        $skipped = [];

        // Get items to prefetch based on strategy
        $itemsToPrefetch = match ($strategy) {
            self::PREFETCH_PREDICTIVE => $this->getPredictivePrefetchItems($maxItems, $threshold),
            self::PREFETCH_RELATED => $this->getRelatedPrefetchItems($maxItems),
            self::PREFETCH_POPULAR => $this->getPopularPrefetchItems($maxItems),
            self::PREFETCH_SCHEDULED => $this->getScheduledPrefetchItems($maxItems),
            default => $this->getPredictivePrefetchItems($maxItems, $threshold),
        };

        foreach ($itemsToPrefetch as $item) {
            $cacheKey = $item['key'];

            // Skip if already cached and not stale
            if ($this->cacheManager->has($cacheKey)) {
                $cached = $this->cacheManager->get($cacheKey);
                if ($cached && ! ($cached['_cache']['is_stale'] ?? false)) {
                    $skipped[] = $cacheKey;

                    continue;
                }
            }

            // Queue for prefetch (actual fetching would be done by DataFetchingAgent)
            $this->queueForPrefetch($cacheKey, $item);
            $prefetched[] = $cacheKey;
        }

        $duration = (microtime(true) - $startTime) * 1000;

        $result = [
            'success' => true,
            'prefetched' => $prefetched,
            'skipped' => $skipped,
            'metadata' => [
                'strategy' => $strategy,
                'items_requested' => count($itemsToPrefetch),
                'items_prefetched' => count($prefetched),
                'items_skipped' => count($skipped),
                'duration_ms' => round($duration, 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $this->recordAnalytics('prefetch', $result);

        Log::info('[CacheManagementAgent] Prefetch operation completed', [
            'prefetched' => count($prefetched),
            'skipped' => count($skipped),
            'duration_ms' => round($duration, 2),
        ]);

        return $result;
    }

    /**
     * Perform cache eviction based on current strategy
     *
     * @return array{success: bool, evicted_count: int, evicted_keys: array<string>, freed_bytes: int, strategy: string}
     */
    public function performEviction(): array
        $startTime = microtime(true);
        $strategy = $this->evictionStrategy;

        Log::info('[CacheManagementAgent] Starting eviction', [
            'strategy' => $strategy,
            'target_count' => $count,
        ]);

        $evictedKeys = [];
        $freedBytes = 0;

        // Get candidates for eviction based on strategy
        $candidates = match ($strategy) {
            self::EVICTION_LRU => $this->getLRUCandidates($count),
            self::EVICTION_LFU => $this->getLFUCandidates($count),
            self::EVICTION_TTL => $this->getTTLCandidates($count),
            self::EVICTION_FIFO => $this->getFIFOCandidates($count),
            self::EVICTION_RANDOM => $this->getRandomCandidates($count),
            default => $this->getLRUCandidates($count),
        };

        foreach ($candidates as $candidate) {
            $key = $candidate['key'];
            $size = $candidate['size'] ?? 0;

            if ($this->cacheManager->delete($key)) {
                $evictedKeys[] = $key;
                $freedBytes = ($freedBytes ?? 0) + $size;

                // Remove from access patterns
                unset($this->accessPatterns[$key]);
            }
        }

        $this->saveAccessPatterns();

        $duration = (microtime(true) - $startTime) * 1000;

        $result = [
            'success' => true,
            'evicted_count' => count($evictedKeys),
            'evicted_keys' => $evictedKeys,
            'freed_bytes' => $freedBytes,
            'strategy' => $strategy,
            'duration_ms' => round($duration, 2),
        ];

        $this->recordAnalytics('eviction', $result);

        Log::info('[CacheManagementAgent] Eviction completed', [
            'evicted_count' => count($evictedKeys),
            'freed_bytes' => $freedBytes,
            'duration_ms' => round($duration, 2),
        ]);

        return $result;
    }

    /**
     * Get performance analytics
     *
     * @return array{hit_rate: float, miss_rate: float, latency: array<string, float>, memory: array<string, mixed>, throughput: array<string, mixed>, trends: array<string, mixed>}
     */
    public function getPerformanceAnalytics(): array
        $cacheStats = $this->cacheManager->getStatistics();
        $cacheInfo = $this->cacheManager->getCacheInfo();
        $cacheSize = $this->cacheManager->getCacheSize();

        $hitRate = $cacheStats['hit_rate'] ?? 0.0;
        $missRate = 100.0 - $hitRate;

        // Calculate latency metrics from analytics history
        $latencyMetrics = $this->calculateLatencyMetrics();

        // Calculate throughput metrics
        $throughputMetrics = $this->calculateThroughputMetrics();

        // Analyze trends
        $trends = $this->analyzeTrends();

        return [
            'hit_rate' => round($hitRate, 2),
            'miss_rate' => round($missRate, 2),
            'latency' => $latencyMetrics,
            'memory' => [
                'total_keys' => $cacheSize['total_keys'],
                'estimated_size_bytes' => $cacheSize['estimated_size_bytes'],
                'estimated_size_mb' => round($cacheSize['estimated_size_bytes'] / 1024 / 1024, 2),
            ],
            'throughput' => $throughputMetrics,
            'trends' => $trends,
            'cache_statistics' => $cacheStats,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Record cache access for pattern analysis
     */
    public function recordAccess(string $key, bool $isHit): void
    {
        $now = time();

        if (! isset($this->accessPatterns[$key])) {
            $this->accessPatterns[$key] = [
                'count' => 0,
                'last_access' => $now,
                'first_access' => $now,
                'hits' => 0,
                'misses' => 0,
            ];
        }

        $this->accessPatterns[$key]['count']++;
        $this->accessPatterns[$key]['last_access'] = $now;

        if ($isHit) {
            $this->accessPatterns[$key]['hits']++;
        } else {
            $this->accessPatterns[$key]['misses']++;
        }

        // Trim access patterns if too large
        if (count($this->accessPatterns) > self::MAX_ACCESS_HISTORY) {
            $this->trimAccessPatterns();
        }

        // Periodically save access patterns
        if (count($this->accessPatterns) % 100 === 0) {
            $this->saveAccessPatterns();
        }
    }

    /**
     * Set eviction strategy
     */
    public function setEvictionStrategy(string $strategy): void
    {
        $validStrategies = [
            self::EVICTION_LRU,
            self::EVICTION_LFU,
            self::EVICTION_TTL,
            self::EVICTION_FIFO,
            self::EVICTION_RANDOM,
        ];

        if (! in_array($strategy, $validStrategies, true)) {
            throw new \InvalidArgumentException("Invalid eviction strategy: {$strategy}");
        }

        $this->evictionStrategy = $strategy;

        Log::debug('[CacheManagementAgent] Eviction strategy changed', [
            'strategy' => $strategy,
        ]);
    }

    /**
     * Get current eviction strategy
     */
    public function getEvictionStrategy(): string
    {
        return $this->evictionStrategy;
    }

    /**
     * Set optimization strategy
     */
    public function setOptimizationStrategy(string $strategy): void
    {
        $validStrategies = [
            self::OPTIMIZATION_AGGRESSIVE,
            self::OPTIMIZATION_BALANCED,
            self::OPTIMIZATION_CONSERVATIVE,
        ];

        if (! in_array($strategy, $validStrategies, true)) {
            throw new \InvalidArgumentException("Invalid optimization strategy: {$strategy}");
        }

        $this->optimizationStrategy = $strategy;

        Log::debug('[CacheManagementAgent] Optimization strategy changed', [
            'strategy' => $strategy,
        ]);
    }

    /**
     * Get current optimization strategy
     */
    public function getOptimizationStrategy(): string
    {
        return $this->optimizationStrategy;
    }

    /**
     * Get access patterns for analysis
     *
     * @return array<string, array{count: int, last_access: int, first_access: int, hits: int, misses: int}>
     */
    public function getAccessPatterns(): array
        return $this->accessPatterns;
    }

    /**
     * Check if agent is healthy
     */
    public function isHealthy(): bool
    {
        return $this->mcpClient->isEnabled();
    }

    /**
     * Get agent status
     *
     * @return array{healthy: bool, mcp_enabled: bool, cache_available: bool, eviction_strategy: string, optimization_strategy: string, access_patterns_count: int}
     */
    public function getStatus(): array
        return [
            'healthy' => $this->isHealthy(),
            'mcp_enabled' => $this->mcpClient->isEnabled(),
            'cache_available' => true, // CacheManagerService is always available
            'eviction_strategy' => $this->evictionStrategy,
            'optimization_strategy' => $this->optimizationStrategy,
            'access_patterns_count' => count($this->accessPatterns),
        ];
    }

    /**
     * Reset analytics data
     */
    public function resetAnalytics(): void
    {
        $this->analyticsData = [
            'optimizations' => [],
            'prefetches' => [],
            'evictions' => [],
            'reset_at' => now()->toIso8601String(),
        ];

        Cache::forget(self::ANALYTICS_KEY);

        Log::info('[CacheManagementAgent] Analytics data reset');
    }

    /**
     * Reset access patterns
     */
    public function resetAccessPatterns(): void
    {
        $this->accessPatterns = [];
        Cache::forget(self::ACCESS_PATTERN_KEY);

        Log::info('[CacheManagementAgent] Access patterns reset');
    }

    /**
     * Collect current cache metrics
     *
     * @return array<string, mixed>
     */
    protected function collectMetrics(): array
        $cacheStats = $this->cacheManager->getStatistics();
        $cacheSize = $this->cacheManager->getCacheSize();

        // Estimate memory usage percentage (assuming 100MB max for external API cache)
        $maxMemoryBytes = 100 * 1024 * 1024; // 100MB
        $memoryUsagePercent = ($cacheSize['estimated_size_bytes'] / $maxMemoryBytes) * 100;

        return [
            'total_keys' => $cacheSize['total_keys'],
            'estimated_size_bytes' => $cacheSize['estimated_size_bytes'],
            'memory_usage_percent' => round($memoryUsagePercent, 2),
            'hit_rate' => $cacheStats['hit_rate'],
            'total_requests' => $cacheStats['total_requests'],
            'access_patterns_tracked' => count($this->accessPatterns),
        ];
    }

    /**
     * Analyze prefetch opportunities based on access patterns
     *
     * @return array<array{key: string, score: float, reason: string}>
     */
    protected function analyzePrefetchOpportunities(): array
        $opportunities = [];
        $now = time();

        foreach ($this->accessPatterns as $key => $pattern) {
            // Skip recently accessed items
            $timeSinceAccess = $now - $pattern['last_access'];
            if ($timeSinceAccess < 300) { // 5 minutes
                continue;
            }

            // Calculate prefetch score based on access frequency and recency
            $accessFrequency = $pattern['count'] / max(1, ($now - $pattern['first_access']) / 3600);
            $hitRatio = $pattern['count'] > 0 ? $pattern['hits'] / $pattern['count'] : 0;

            $score = ($accessFrequency * 0.6) + ($hitRatio * 0.4);

            if ($score > self::DEFAULT_PREFETCH_THRESHOLD) {
                $opportunities[] = [
                    'key' => $key,
                    'score' => round($score, 3),
                    'reason' => $this->determinePrefetchReason($pattern, $score),
                ];
            }
        }

        // Sort by score descending
        usort($opportunities, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($opportunities, 0, 20);
    }

    /**
     * Identify stale cache entries
     *
     * @return array<array{key: string, age_seconds: int, staleness_percent: float}>
     */
    protected function identifyStaleEntries(): array
        $staleEntries = [];
        $cachedKeys = $this->cacheManager->getCachedKeys();

        foreach ($cachedKeys as $key) {
            $metadata = $this->cacheManager->getCacheMetadata($key);
            if (! $metadata) {
                continue;
            }

            $cachedAt = $metadata['cached_at'] ?? now();
            $ttl = $metadata['ttl'] ?? 3600;
            $age = now()->diffInSeconds($cachedAt);
            $stalenessPercent = ($age / $ttl) * 100;

            if ($stalenessPercent > 80) {
                $staleEntries[] = [
                    'key' => $key,
                    'age_seconds' => $age,
                    'staleness_percent' => round($stalenessPercent, 2),
                ];
            }
        }

        // Sort by staleness descending
        usort($staleEntries, fn ($a, $b) => $b['staleness_percent'] <=> $a['staleness_percent']);

        return array_slice($staleEntries, 0, 50);
    }

    /**
     * Apply strategy-specific optimizations
     *
     * @param  array<string, mixed>  $metrics
     * @return array<string, mixed>
     */
    protected function applyStrategyOptimizations(): array
        $optimizations = [];

        switch ($strategy) {
            case self::OPTIMIZATION_AGGRESSIVE:
                // Aggressive: Lower thresholds, more eviction, more prefetching
                if ($metrics['memory_usage_percent'] > 60) {
                    $optimizations['memory_action'] = 'evict_20_percent';
                }
                if ($metrics['hit_rate'] < 90) {
                    $optimizations['prefetch_action'] = 'increase_prefetch_rate';
                }
                $optimizations['ttl_adjustment'] = 'reduce_by_20_percent';
                break;

            case self::OPTIMIZATION_BALANCED:
                // Balanced: Standard thresholds
                if ($metrics['memory_usage_percent'] > 80) {
                    $optimizations['memory_action'] = 'evict_10_percent';
                }
                if ($metrics['hit_rate'] < 80) {
                    $optimizations['prefetch_action'] = 'moderate_prefetch';
                }
                $optimizations['ttl_adjustment'] = 'maintain_current';
                break;

            case self::OPTIMIZATION_CONSERVATIVE:
                // Conservative: Higher thresholds, less eviction
                if ($metrics['memory_usage_percent'] > 95) {
                    $optimizations['memory_action'] = 'evict_5_percent';
                }
                if ($metrics['hit_rate'] < 70) {
                    $optimizations['prefetch_action'] = 'minimal_prefetch';
                }
                $optimizations['ttl_adjustment'] = 'extend_by_20_percent';
                break;
        }

        $optimizations['strategy'] = $strategy;
        $optimizations['applied_at'] = now()->toIso8601String();

        return $optimizations;
    }

    /**
     * Get LRU (Least Recently Used) eviction candidates
     *
     * @return array<array{key: string, size: int, last_access: int}>
     */
    protected function getLRUCandidates(): array
        $candidates = [];

        foreach ($this->accessPatterns as $key => $pattern) {
            $candidates[] = [
                'key' => $key,
                'size' => $this->estimateKeySize($key),
                'last_access' => $pattern['last_access'],
            ];
        }

        // Sort by last_access ascending (oldest first)
        usort($candidates, fn ($a, $b) => $a['last_access'] <=> $b['last_access']);

        return array_slice($candidates, 0, $count);
    }

    /**
     * Get LFU (Least Frequently Used) eviction candidates
     *
     * @return array<array{key: string, size: int, access_count: int}>
     */
    protected function getLFUCandidates(): array
        $candidates = [];

        foreach ($this->accessPatterns as $key => $pattern) {
            $candidates[] = [
                'key' => $key,
                'size' => $this->estimateKeySize($key),
                'access_count' => $pattern['count'],
            ];
        }

        // Sort by access_count ascending (least accessed first)
        usort($candidates, fn ($a, $b) => $a['access_count'] <=> $b['access_count']);

        return array_slice($candidates, 0, $count);
    }

    /**
     * Get TTL-based eviction candidates (closest to expiration)
     *
     * @return array<array{key: string, size: int, ttl_remaining: int}>
     */
    protected function getTTLCandidates(): array
        $candidates = [];
        $cachedKeys = $this->cacheManager->getCachedKeys();

        foreach ($cachedKeys as $key) {
            $metadata = $this->cacheManager->getCacheMetadata($key);
            if (! $metadata) {
                continue;
            }

            $cachedAt = $metadata['cached_at'] ?? now();
            $ttl = $metadata['ttl'] ?? 3600;
            $age = now()->diffInSeconds($cachedAt);
            $ttlRemaining = max(0, $ttl - $age);

            $candidates[] = [
                'key' => $key,
                'size' => $this->estimateKeySize($key),
                'ttl_remaining' => $ttlRemaining,
            ];
        }

        // Sort by ttl_remaining ascending (closest to expiration first)
        usort($candidates, fn ($a, $b) => $a['ttl_remaining'] <=> $b['ttl_remaining']);

        return array_slice($candidates, 0, $count);
    }

    /**
     * Get FIFO (First In First Out) eviction candidates
     *
     * @return array<array{key: string, size: int, first_access: int}>
     */
    protected function getFIFOCandidates(): array
        $candidates = [];

        foreach ($this->accessPatterns as $key => $pattern) {
            $candidates[] = [
                'key' => $key,
                'size' => $this->estimateKeySize($key),
                'first_access' => $pattern['first_access'],
            ];
        }

        // Sort by first_access ascending (oldest first)
        usort($candidates, fn ($a, $b) => $a['first_access'] <=> $b['first_access']);

        return array_slice($candidates, 0, $count);
    }

    /**
     * Get random eviction candidates
     *
     * @return array<array{key: string, size: int}>
     */
    protected function getRandomCandidates(): array
        $keys = array_keys($this->accessPatterns);

        if (empty($keys)) {
            return [];
        }

        shuffle($keys);
        $selectedKeys = array_slice($keys, 0, $count);

        $candidates = [];
        foreach ($selectedKeys as $key) {
            $candidates[] = [
                'key' => $key,
                'size' => $this->estimateKeySize($key),
            ];
        }

        return $candidates;
    }

    /**
     * Estimate size of a cache key's value
     */
    protected function estimateKeySize(string $key): int
    {
        $data = $this->cacheManager->get($key);
        if ($data === null) {
            return 0;
        }

        return strlen(json_encode($data) ?: '');
    }

    /**
     * Get predictive prefetch items based on access patterns
     *
     * @return array<array{key: string, score: float, type: string}>
     */
    protected function getPredictivePrefetchItems(): array
        $items = [];
        $now = time();

        foreach ($this->accessPatterns as $key => $pattern) {
            // Calculate prediction score
            $accessFrequency = $pattern['count'] / max(1, ($now - $pattern['first_access']) / 3600);
            $recencyScore = 1 / max(1, ($now - $pattern['last_access']) / 3600);
            $hitRatio = $pattern['count'] > 0 ? $pattern['hits'] / $pattern['count'] : 0;

            $score = ($accessFrequency * 0.4) + ($recencyScore * 0.3) + ($hitRatio * 0.3);

            if ($score >= $threshold) {
                $items[] = [
                    'key' => $key,
                    'score' => round($score, 3),
                    'type' => $this->extractTypeFromKey($key),
                ];
            }
        }

        // Sort by score descending
        usort($items, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($items, 0, $maxItems);
    }

    /**
     * Get related prefetch items based on data relationships
     *
     * @return array<array{key: string, score: float, type: string}>
     */
    protected function getRelatedPrefetchItems(): array
        $items = [];

        // Get recently accessed items and find related data
        $recentKeys = $this->getRecentlyAccessedKeys(10);

        foreach ($recentKeys as $key) {
            $relatedKeys = $this->findRelatedKeys($key);
            foreach ($relatedKeys as $relatedKey) {
                if (! $this->cacheManager->has($relatedKey)) {
                    $items[] = [
                        'key' => $relatedKey,
                        'score' => 0.8, // High score for related items
                        'type' => $this->extractTypeFromKey($relatedKey),
                    ];
                }
            }
        }

        return array_slice($items, 0, $maxItems);
    }

    /**
     * Get popular prefetch items based on overall access frequency
     *
     * @return array<array{key: string, score: float, type: string}>
     */
    protected function getPopularPrefetchItems(): array
        $items = [];

        foreach ($this->accessPatterns as $key => $pattern) {
            $items[] = [
                'key' => $key,
                'score' => (float) $pattern['count'],
                'type' => $this->extractTypeFromKey($key),
            ];
        }

        // Sort by access count descending
        usort($items, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($items, 0, $maxItems);
    }

    /**
     * Get scheduled prefetch items from the prefetch queue
     *
     * @return array<array{key: string, score: float, type: string}>
     */
    protected function getScheduledPrefetchItems(): array
        $queue = Cache::get(self::PREFETCH_QUEUE_KEY, []);

        return array_slice($queue, 0, $maxItems);
    }

    /**
     * Queue an item for prefetching
     *
     * @param  array<string, mixed>  $item
     */
    protected function queueForPrefetch(string $key, array $item): void
    {
        $queue = Cache::get(self::PREFETCH_QUEUE_KEY, []);

        $queue[] = [
            'key' => $key,
            'score' => $item['score'] ?? 0.5,
            'type' => $item['type'] ?? 'unknown',
            'queued_at' => now()->toIso8601String(),
        ];

        // Keep queue size manageable
        if (count($queue) > 100) {
            $queue = array_slice($queue, -100);
        }

        Cache::put(self::PREFETCH_QUEUE_KEY, $queue, 3600);
    }

    /**
     * Get recently accessed keys
     *
     * @return array<string>
     */
    protected function getRecentlyAccessedKeys(): array
        $patterns = $this->accessPatterns;

        // Sort by last_access descending
        uasort($patterns, fn ($a, $b) => $b['last_access'] <=> $a['last_access']);

        return array_slice(array_keys($patterns), 0, $count);
    }

    /**
     * Find related keys based on data type and naming patterns
     *
     * @return array<string>
     */
    protected function findRelatedKeys(): array
        $relatedKeys = [];
        $type = $this->extractTypeFromKey($key);
        $name = $this->extractNameFromKey($key);

        // Define relationships between data types
        $relationships = [
            'character_data' => ['support_cards', 'skills', 'race_data'],
            'support_cards' => ['character_data', 'skills'],
            'skills' => ['character_data'],
            'race_data' => ['character_data'],
        ];

        $relatedTypes = $relationships[$type] ?? [];

        foreach ($relatedTypes as $relatedType) {
            // Generate potential related keys
            $relatedKeys[] = "{$relatedType}:{$name}";
        }

        return $relatedKeys;
    }

    /**
     * Extract data type from cache key
     */
    protected function extractTypeFromKey(string $key): string
    {
        $parts = explode(':', $key);

        return $parts[0] ?? 'unknown';
    }

    /**
     * Extract name/identifier from cache key
     */
    protected function extractNameFromKey(string $key): string
    {
        $parts = explode(':', $key);

        return $parts[1] ?? '';
    }

    /**
     * Determine reason for prefetch suggestion
     *
     * @param  array{count: int, last_access: int, first_access: int, hits: int, misses: int}  $pattern
     */
    protected function determinePrefetchReason(array $pattern, float $score): string
    {
        $hitRatio = $pattern['count'] > 0 ? $pattern['hits'] / $pattern['count'] : 0;

        if ($hitRatio > 0.9) {
            return 'High hit ratio indicates frequent access';
        }

        if ($pattern['count'] > 50) {
            return 'High access frequency';
        }

        if ($score > 0.9) {
            return 'Strong access pattern detected';
        }

        return 'Moderate access pattern suggests prefetching';
    }

    /**
     * Calculate latency metrics from analytics history
     *
     * @return array{avg_ms: float, p50_ms: float, p95_ms: float, p99_ms: float}
     */
    protected function calculateLatencyMetrics(): array
        $latencies = [];

        foreach ($this->analyticsData['optimizations'] ?? [] as $optimization) {
            if (isset($optimization['metrics']['optimization_duration_ms'])) {
                $latencies[] = $optimization['metrics']['optimization_duration_ms'];
            }
        }

        if (empty($latencies)) {
            return [
                'avg_ms' => 0.0,
                'p50_ms' => 0.0,
                'p95_ms' => 0.0,
                'p99_ms' => 0.0,
            ];
        }

        sort($latencies);
        $count = count($latencies);

        return [
            'avg_ms' => round(array_sum($latencies) / $count, 2),
            'p50_ms' => round($latencies[(int) ($count * 0.5)] ?? 0, 2),
            'p95_ms' => round($latencies[(int) ($count * 0.95)] ?? 0, 2),
            'p99_ms' => round($latencies[(int) ($count * 0.99)] ?? 0, 2),
        ];
    }

    /**
     * Calculate throughput metrics
     *
     * @return array{operations_per_minute: float, evictions_per_hour: int, prefetches_per_hour: int}
     */
    protected function calculateThroughputMetrics(): array
        $now = time();
        $oneHourAgo = $now - 3600;

        $recentEvictions = 0;
        $recentPrefetches = 0;

        foreach ($this->analyticsData['evictions'] ?? [] as $eviction) {
            $timestamp = strtotime($eviction['timestamp'] ?? '');
            if ($timestamp > $oneHourAgo) {
                $recentEvictions = ($recentEvictions ?? 0) + $eviction['evicted_count'] ?? 0;
            }
        }

        foreach ($this->analyticsData['prefetches'] ?? [] as $prefetch) {
            $timestamp = strtotime($prefetch['metadata']['timestamp'] ?? '');
            if ($timestamp > $oneHourAgo) {
                $recentPrefetches = ($recentPrefetches ?? 0) + $prefetch['metadata']['items_prefetched'] ?? 0;
            }
        }

        $totalOperations = count($this->analyticsData['optimizations'] ?? []) +
            count($this->analyticsData['evictions'] ?? []) +
            count($this->analyticsData['prefetches'] ?? []);

        return [
            'operations_per_minute' => round($totalOperations / 60, 2),
            'evictions_per_hour' => $recentEvictions,
            'prefetches_per_hour' => $recentPrefetches,
        ];
    }

    /**
     * Analyze trends in cache performance
     *
     * @return array{hit_rate_trend: string, memory_trend: string, efficiency_score: float}
     */
    protected function analyzeTrends(): array
        $recentOptimizations = array_slice($this->analyticsData['optimizations'] ?? [], -10);

        if (count($recentOptimizations) < 2) {
            return [
                'hit_rate_trend' => 'insufficient_data',
                'memory_trend' => 'insufficient_data',
                'efficiency_score' => 0.0,
            ];
        }

        // Analyze hit rate trend
        $hitRates = array_map(
            fn ($opt) => $opt['metrics']['hit_rate'] ?? 0,
            $recentOptimizations
        );
        $hitRateTrend = $this->calculateTrend($hitRates);

        // Analyze memory usage trend
        $memoryUsages = array_map(
            fn ($opt) => $opt['metrics']['memory_usage_percent'] ?? 0,
            $recentOptimizations
        );
        $memoryTrend = $this->calculateTrend($memoryUsages);

        // Calculate efficiency score (higher hit rate + lower memory = better)
        $avgHitRate = array_sum($hitRates) / count($hitRates);
        $avgMemory = array_sum($memoryUsages) / count($memoryUsages);
        $efficiencyScore = ($avgHitRate * 0.7) + ((100 - $avgMemory) * 0.3);

        return [
            'hit_rate_trend' => $hitRateTrend,
            'memory_trend' => $memoryTrend,
            'efficiency_score' => round($efficiencyScore, 2),
        ];
    }

    /**
     * Calculate trend direction from a series of values
     *
     * @param  array<float>  $values
     */
    protected function calculateTrend(array $values): string
    {
        if (count($values) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($values, 0, (int) (count($values) / 2));
        $secondHalf = array_slice($values, (int) (count($values) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $change = $secondAvg - $firstAvg;
        $threshold = 5; // 5% change threshold

        if ($change > $threshold) {
            return 'increasing';
        }

        if ($change < -$threshold) {
            return 'decreasing';
        }

        return 'stable';
    }

    /**
     * Record analytics data
     *
     * @param  array<string, mixed>  $data
     */
    protected function recordAnalytics(string $type, array $data): void
    {
        (is_array($data) && isset($data['timestamp']) ? $data['timestamp'] : null) = now()->toIso8601String();

        switch ($type) {
            case 'optimization':
                $this->analyticsData['optimizations'][] = $data;
                break;
            case 'prefetch':
                $this->analyticsData['prefetches'][] = $data;
                break;
            case 'eviction':
                $this->analyticsData['evictions'][] = $data;
                break;
        }

        // Trim old analytics data
        $this->trimAnalyticsData();

        // Save to cache
        $this->saveAnalyticsData();
    }

    /**
     * Trim analytics data to retention period
     */
    protected function trimAnalyticsData(): void
    {
        $cutoff = now()->subHours(self::ANALYTICS_RETENTION_HOURS)->toIso8601String();

        foreach (['optimizations', 'prefetches', 'evictions'] as $type) {
            if (! isset($this->analyticsData[$type])) {
                continue;
            }

            $this->analyticsData[$type] = array_filter(
                $this->analyticsData[$type],
                fn ($item) => ($item['timestamp'] ?? '') > $cutoff
            );

            // Re-index array
            $this->analyticsData[$type] = array_values($this->analyticsData[$type]);
        }
    }

    /**
     * Trim access patterns to max size
     */
    protected function trimAccessPatterns(): void
    {
        if (count($this->accessPatterns) <= self::MAX_ACCESS_HISTORY) {
            return;
        }

        // Sort by last_access and keep most recent
        uasort($this->accessPatterns, fn ($a, $b) => $b['last_access'] <=> $a['last_access']);

        $this->accessPatterns = array_slice($this->accessPatterns, 0, self::MAX_ACCESS_HISTORY, true);
    }

    /**
     * Load access patterns from cache
     */
    protected function loadAccessPatterns(): void
    {
        $patterns = Cache::get(self::ACCESS_PATTERN_KEY, []);
        $this->accessPatterns = is_array($patterns) ? $patterns : [];
    }

    /**
     * Save access patterns to cache
     */
    protected function saveAccessPatterns(): void
    {
        Cache::put(self::ACCESS_PATTERN_KEY, $this->accessPatterns, 86400); // 24 hours
    }

    /**
     * Load analytics data from cache
     */
    protected function loadAnalyticsData(): void
    {
        $data = Cache::get(self::ANALYTICS_KEY, []);
        $this->analyticsData = is_array($data) ? $data : [
            'optimizations' => [],
            'prefetches' => [],
            'evictions' => [],
        ];
    }

    /**
     * Save analytics data to cache
     */
    protected function saveAnalyticsData(): void
    {
        Cache::put(self::ANALYTICS_KEY, $this->analyticsData, 86400); // 24 hours
    }
}
