<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Advisory Performance Monitor
 *
 * Tracks and reports performance metrics for the AI Training Advisory System.
 * Monitors response times, cache hit rates, AI provider usage, and fallback frequency.
 *
 * Metrics tracked:
 * - Response time percentiles (P50, P95, P99)
 * - Cache hit rates by cache type
 * - AI provider response times
 * - Fallback frequency
 * - Query execution times
 *
 * @see \App\Services\TrainingAdvisoryService
 */
class AdvisoryPerformanceMonitor
{
    /**
     * Cache key prefix for performance metrics
     */
    protected const METRICS_PREFIX = 'advisory:metrics';

    /**
     * Metrics retention period (7 days)
     */
    protected const METRICS_TTL = 604800;

    /**
     * Record a recommendation generation event.
     *
     * @param  array{
     *   response_time_ms: int,
     *   cache_hit: bool,
     *   ai_provider: string,
     *   fallback_used: bool,
     *   turn_number: int,
     *   career_run_id: string
     * }  $metrics  Performance metrics for the event
     */
    public function recordRecommendationGeneration(array $metrics): void
    {
        $timestamp = (int) now()->timestamp;

        // Record response time
        $this->recordResponseTime($metrics['response_time_ms'], $timestamp);

        // Record cache hit/miss
        $this->recordCacheHit('recommendations', $metrics['cache_hit'], $timestamp);

        // Record AI provider usage
        $this->recordAIProviderUsage($metrics['ai_provider'], $metrics['response_time_ms'], $timestamp);

        // Record fallback usage
        if ($metrics['fallback_used']) {
            $this->recordFallback($timestamp);
        }

        // Log detailed metrics
        Log::debug('[AdvisoryPerformance] Recommendation generation recorded', $metrics);
    }

    /**
     * Record a cache operation.
     *
     * @param  string  $cacheType  Type of cache (recommendations, race_requirements, skill_catalog)
     * @param  bool  $hit  Whether the cache was hit
     * @param  int|null  $timestamp  Optional timestamp (defaults to now)
     */
    public function recordCacheHit(string $cacheType, bool $hit, ?int $timestamp = null): void
    {
        $timestamp ??= now()->timestamp;
        $key = $this->getCacheMetricsKey($cacheType);

        // Get current metrics
        /** @var array{hits: int, misses: int, total: int} $metrics */
        $metrics = Cache::get($key, ['hits' => 0, 'misses' => 0, 'total' => 0]);

        // Update metrics
        if ($hit) {
            $metrics['hits']++;
        } else {
            $metrics['misses']++;
        }
        $metrics['total']++;

        // Store updated metrics
        Cache::put($key, $metrics, self::METRICS_TTL);
    }

    /**
     * Record response time for percentile calculations.
     *
     * @param  int  $responseTimeMs  Response time in milliseconds
     * @param  int|null  $timestamp  Optional timestamp (defaults to now)
     */
    protected function recordResponseTime(int $responseTimeMs, ?int $timestamp = null): void
    {
        $timestamp ??= now()->timestamp;
        $key = $this->getResponseTimeKey();

        // Get current response times (keep last 1000 for percentile calculation)
        /** @var array<int, array{time_ms: int, timestamp: int}> $responseTimes */
        $responseTimes = Cache::get($key, []);

        // Add new response time
        $responseTimes[] = [
            'time_ms' => $responseTimeMs,
            'timestamp' => $timestamp,
        ];

        // Keep only last 1000 entries
        if (\count($responseTimes) > 1000) {
            $responseTimes = \array_slice($responseTimes, -1000);
        }

        // Store updated response times
        Cache::put($key, $responseTimes, self::METRICS_TTL);
    }

    /**
     * Record AI provider usage and response time.
     *
     * @param  string  $provider  AI provider (ollama, bedrock, rule-based)
     * @param  int  $responseTimeMs  Response time in milliseconds
     * @param  int|null  $timestamp  Optional timestamp (defaults to now)
     */
    protected function recordAIProviderUsage(string $provider, int $responseTimeMs, ?int $timestamp = null): void
    {
        $timestamp ??= now()->timestamp;
        $key = $this->getProviderMetricsKey($provider);

        // Get current metrics
        /** @var array{count: int, total_time_ms: int, min_time_ms: int, max_time_ms: int, avg_time_ms?: int} $metrics */
        $metrics = Cache::get($key, [
            'count' => 0,
            'total_time_ms' => 0,
            'min_time_ms' => PHP_INT_MAX,
            'max_time_ms' => 0,
        ]);

        // Update metrics
        $metrics['count']++;
        $metrics['total_time_ms'] += $responseTimeMs;
        $metrics['min_time_ms'] = min($metrics['min_time_ms'], $responseTimeMs);
        $metrics['max_time_ms'] = max($metrics['max_time_ms'], $responseTimeMs);
        $metrics['avg_time_ms'] = (int) round($metrics['total_time_ms'] / $metrics['count']);

        // Store updated metrics
        Cache::put($key, $metrics, self::METRICS_TTL);
    }

    /**
     * Record a fallback event (AI → rule-based).
     *
     * @param  int|null  $timestamp  Optional timestamp (defaults to now)
     */
    protected function recordFallback(?int $timestamp = null): void
    {
        $timestamp ??= now()->timestamp;
        $key = $this->getFallbackMetricsKey();

        // Get current count
        /** @var int $count */
        $count = Cache::get($key, 0);

        // Increment count
        Cache::put($key, $count + 1, self::METRICS_TTL);
    }

    /**
     * Get cache hit rate for a specific cache type.
     *
     * @param  string  $cacheType  Type of cache (recommendations, race_requirements, skill_catalog)
     * @return array{hit_rate: float, hits: int, misses: int, total: int}
     */
    public function getCacheHitRate(string $cacheType): array
    {
        $key = $this->getCacheMetricsKey($cacheType);
        /** @var array{hits: int, misses: int, total: int} $metrics */
        $metrics = Cache::get($key, ['hits' => 0, 'misses' => 0, 'total' => 0]);

        $hitRate = $metrics['total'] > 0
            ? round(($metrics['hits'] / $metrics['total']) * 100, 2)
            : 0.0;

        return [
            'hit_rate' => $hitRate,
            'hits' => $metrics['hits'],
            'misses' => $metrics['misses'],
            'total' => $metrics['total'],
        ];
    }

    /**
     * Get response time percentiles.
     *
     * @return array{p50: int, p95: int, p99: int, min: int, max: int, avg: int, count: int}
     */
    public function getResponseTimePercentiles(): array
    {
        $key = $this->getResponseTimeKey();
        /** @var array<int, array{time_ms: int, timestamp: int}> $responseTimes */
        $responseTimes = Cache::get($key, []);

        if (empty($responseTimes)) {
            return [
                'p50' => 0,
                'p95' => 0,
                'p99' => 0,
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'count' => 0,
            ];
        }

        // Extract time values and sort
        /** @var array<int> $times */
        $times = array_column($responseTimes, 'time_ms');
        sort($times);

        $count = \count($times);

        return [
            'p50' => $this->calculatePercentile($times, 50),
            'p95' => $this->calculatePercentile($times, 95),
            'p99' => $this->calculatePercentile($times, 99),
            'min' => $count > 0 ? min($times) : 0,
            'max' => $count > 0 ? max($times) : 0,
            'avg' => (int) round(array_sum($times) / $count),
            'count' => $count,
        ];
    }

    /**
     * Calculate percentile from sorted array.
     *
     * @param  array<int>  $sortedValues  Sorted array of values
     * @param  int  $percentile  Percentile to calculate (0-100)
     * @return int Percentile value
     */
    protected function calculatePercentile(array $sortedValues, int $percentile): int
    {
        $count = \count($sortedValues);
        if ($count === 0) {
            return 0;
        }

        $index = (int) ceil(($percentile / 100) * $count) - 1;
        $index = max(0, min($index, $count - 1));

        return $sortedValues[$index];
    }

    /**
     * Get AI provider usage statistics.
     *
     * @return array<string, array{count: int, avg_time_ms: int, min_time_ms: int, max_time_ms: int}>
     */
    public function getAIProviderStats(): array
    {
        $providers = ['ollama', 'bedrock', 'rule-based'];
        $stats = [];

        foreach ($providers as $provider) {
            $key = $this->getProviderMetricsKey($provider);
            /** @var array{count: int, avg_time_ms: int, min_time_ms: int, max_time_ms: int} $metrics */
            $metrics = Cache::get($key, [
                'count' => 0,
                'avg_time_ms' => 0,
                'min_time_ms' => 0,
                'max_time_ms' => 0,
            ]);

            $stats[$provider] = $metrics;
        }

        return $stats;
    }

    /**
     * Get fallback frequency.
     *
     * @return int Number of fallback events
     */
    public function getFallbackCount(): int
    {
        $key = $this->getFallbackMetricsKey();
        /** @var int $count */
        $count = Cache::get($key, 0);

        return $count;
    }

    /**
     * Get comprehensive performance report.
     *
     * @return array{
     *   response_times: array{p50: int, p95: int, p99: int, min: int, max: int, avg: int, count: int},
     *   cache_hit_rates: array<string, array{hit_rate: float, hits: int, misses: int, total: int}>,
     *   ai_provider_stats: array<string, array{count: int, avg_time_ms: int, min_time_ms: int, max_time_ms: int}>,
     *   fallback_count: int,
     *   performance_status: string
     * }
     */
    public function getPerformanceReport(): array
    {
        $responseTimes = $this->getResponseTimePercentiles();
        $cacheHitRates = [
            'recommendations' => $this->getCacheHitRate('recommendations'),
            'race_requirements' => $this->getCacheHitRate('race_requirements'),
            'skill_catalog' => $this->getCacheHitRate('skill_catalog'),
        ];
        $aiProviderStats = $this->getAIProviderStats();
        $fallbackCount = $this->getFallbackCount();

        // Determine performance status
        $status = $this->determinePerformanceStatus($responseTimes, $cacheHitRates, $aiProviderStats);

        return [
            'response_times' => $responseTimes,
            'cache_hit_rates' => $cacheHitRates,
            'ai_provider_stats' => $aiProviderStats,
            'fallback_count' => $fallbackCount,
            'performance_status' => $status,
        ];
    }

    /**
     * Determine overall performance status.
     *
     * @param  array{p50: int, p95: int, p99: int}  $responseTimes  Response time percentiles
     * @param  array<string, array{hit_rate: float}>  $cacheHitRates  Cache hit rates
     * @param  array<string, array{avg_time_ms: int}>  $aiProviderStats  AI provider stats
     * @return string Performance status (excellent, good, degraded, poor)
     */
    protected function determinePerformanceStatus(
        array $responseTimes,
        array $cacheHitRates,
        array $aiProviderStats
    ): string {
        $issues = 0;

        // Check response times (P95 should be <2000ms for local, <5000ms for cloud)
        if ($responseTimes['p95'] > 5000) {
            $issues += 2; // Critical issue
        } elseif ($responseTimes['p95'] > 2000) {
            $issues += 1; // Minor issue
        }

        // Check cache hit rates (should be >60%)
        foreach ($cacheHitRates as $type => $metrics) {
            if ($metrics['hit_rate'] < 40) {
                $issues += 2; // Critical issue
            } elseif ($metrics['hit_rate'] < 60) {
                $issues += 1; // Minor issue
            }
        }

        // Check AI provider response times
        if (isset($aiProviderStats['ollama']) && $aiProviderStats['ollama']['avg_time_ms'] > 2000) {
            $issues += 1;
        }
        if (isset($aiProviderStats['bedrock']) && $aiProviderStats['bedrock']['avg_time_ms'] > 5000) {
            $issues += 1;
        }

        // Determine status based on issues
        if ($issues === 0) {
            return 'excellent';
        } elseif ($issues <= 2) {
            return 'good';
        } elseif ($issues <= 4) {
            return 'degraded';
        } else {
            return 'poor';
        }
    }

    /**
     * Reset all performance metrics.
     */
    public function resetMetrics(): void
    {
        $patterns = [
            self::METRICS_PREFIX.':cache:*',
            self::METRICS_PREFIX.':response_times',
            self::METRICS_PREFIX.':provider:*',
            self::METRICS_PREFIX.':fallbacks',
        ];

        foreach ($patterns as $pattern) {
            if (config('cache.default') === 'redis') {
                $keys = \Illuminate\Support\Facades\Redis::keys($pattern);
                if (! empty($keys)) {
                    \Illuminate\Support\Facades\Redis::del($keys);
                }
            }
        }

        Log::info('[AdvisoryPerformance] Metrics reset');
    }

    /**
     * Get cache metrics key for a specific cache type.
     *
     * @param  string  $cacheType  Cache type
     * @return string Cache key
     */
    protected function getCacheMetricsKey(string $cacheType): string
    {
        return self::METRICS_PREFIX.':cache:'.$cacheType;
    }

    /**
     * Get response time metrics key.
     *
     * @return string Cache key
     */
    protected function getResponseTimeKey(): string
    {
        return self::METRICS_PREFIX.':response_times';
    }

    /**
     * Get provider metrics key for a specific provider.
     *
     * @param  string  $provider  AI provider
     * @return string Cache key
     */
    protected function getProviderMetricsKey(string $provider): string
    {
        return self::METRICS_PREFIX.':provider:'.$provider;
    }

    /**
     * Get fallback metrics key.
     *
     * @return string Cache key
     */
    protected function getFallbackMetricsKey(): string
    {
        return self::METRICS_PREFIX.':fallbacks';
    }
}
