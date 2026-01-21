<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Performance Metrics Service
 *
 * Tracks and aggregates performance metrics for external API integration including:
 * - Response time tracking (p50, p95, p99)
 * - Cache hit/miss rates
 * - Error rates by source
 * - Request volume and throughput
 * - Real-time performance indicators
 *
 * Requirements: 14.5 (Performance Optimization and Monitoring)
 * Task: 5.1.1
 */
class APIPerformanceMetricsService
{
    /**
     * Redis key prefix for metrics
     */
    protected const METRICS_PREFIX = 'api_metrics:';

    /**
     * Time window for metrics aggregation (seconds)
     */
    protected const METRICS_WINDOW = 3600; // 1 hour

    /**
     * Maximum number of response time samples to keep
     */
    protected const MAX_RESPONSE_TIME_SAMPLES = 1000;

    /**
     * Record API response time
     */
    public function recordResponseTime(string $source, string $endpoint, float $responseTimeMs, bool $success): void
    {
        $timestamp = now()->timestamp;

        // Store response time in sorted set for percentile calculations
        $responseTimeKey = self::METRICS_PREFIX."response_times:{$source}";
        Redis::zadd($responseTimeKey, $timestamp, "{$timestamp}:{$responseTimeMs}");

        // Trim old entries
        $cutoff = $timestamp - self::METRICS_WINDOW;
        Redis::zremrangebyscore($responseTimeKey, '-inf', $cutoff);

        // Limit total samples
        $count = Redis::zcard($responseTimeKey);
        if ($count > self::MAX_RESPONSE_TIME_SAMPLES) {
            $removeCount = $count - self::MAX_RESPONSE_TIME_SAMPLES;
            Redis::zpopmin($responseTimeKey, $removeCount);
        }

        // Update success/error counters
        if ($success) {
            $this->incrementCounter("{$source}:success");
        } else {
            $this->incrementCounter("{$source}:error");
        }

        // Update endpoint-specific metrics
        $endpointKey = $this->sanitizeEndpoint($endpoint);
        $this->incrementCounter("{$source}:endpoints:{$endpointKey}");

        Log::debug('[APIPerformanceMetrics] Response time recorded', [
            'source' => $source,
            'endpoint' => $endpoint,
            'response_time_ms' => $responseTimeMs,
            'success' => $success,
        ]);
    }

    /**
     * Record cache hit or miss
     */
    public function recordCacheAccess(string $key, bool $hit): void
    {
        $dataType = $this->extractDataType($key);

        if ($hit) {
            $this->incrementCounter("cache:hits:{$dataType}");
            $this->incrementCounter('cache:hits:total');
        } else {
            $this->incrementCounter("cache:misses:{$dataType}");
            $this->incrementCounter('cache:misses:total');
        }
    }

    /**
     * Record API error
     */
    public function recordError(string $source, string $errorType, string $message): void
    {
        $timestamp = now()->timestamp;

        // Store error in list
        $errorKey = self::METRICS_PREFIX."errors:{$source}";
        $errorData = json_encode([
            'timestamp' => $timestamp,
            'type' => $errorType,
            'message' => $message,
        ]);

        Redis::lpush($errorKey, $errorData);
        Redis::ltrim($errorKey, 0, 99); // Keep last 100 errors

        // Increment error counter
        $this->incrementCounter("{$source}:errors:{$errorType}");

        Log::warning('[APIPerformanceMetrics] Error recorded', [
            'source' => $source,
            'error_type' => $errorType,
            'message' => $message,
        ]);
    }

    /**
     * Get response time statistics for a source
     *
     * @return array{p50: float, p95: float, p99: float, avg: float, min: float, max: float, count: int}
     */
    public function getResponseTimeStats(string $source): array
    {
        $responseTimeKey = self::METRICS_PREFIX."response_times:{$source}";
        $samples = Redis::zrange($responseTimeKey, 0, -1);

        if (empty($samples)) {
            return [
                'p50' => 0.0,
                'p95' => 0.0,
                'p99' => 0.0,
                'avg' => 0.0,
                'min' => 0.0,
                'max' => 0.0,
                'count' => 0,
            ];
        }

        // Extract response times from samples
        $responseTimes = array_map(function ($sample) {
            $parts = explode(':', $sample);

            return (float) ($parts[1] ?? 0);
        }, $samples);

        sort($responseTimes);

        $count = count($responseTimes);
        $sum = array_sum($responseTimes);

        return [
            'p50' => $this->calculatePercentile($responseTimes, 50),
            'p95' => $this->calculatePercentile($responseTimes, 95),
            'p99' => $this->calculatePercentile($responseTimes, 99),
            'avg' => $count > 0 ? round($sum / $count, 2) : 0.0,
            'min' => round(min($responseTimes), 2),
            'max' => round(max($responseTimes), 2),
            'count' => $count,
        ];
    }

    /**
     * Get cache hit rate statistics
     *
     * @return array{hit_rate: float, hits: int, misses: int, total: int, by_type: array<string, array{hit_rate: float, hits: int, misses: int}>}
     */
    public function getCacheHitRateStats(): array
    {
        $totalHits = $this->getCounter('cache:hits:total');
        $totalMisses = $this->getCounter('cache:misses:total');
        $total = $totalHits + $totalMisses;
        $hitRate = $total > 0 ? ($totalHits / $total) * 100 : 0;

        // Get stats by data type
        $dataTypes = ['character_data', 'support_cards', 'meta_rankings', 'race_data', 'skills', 'news', 'game_mechanics'];
        $byType = [];

        foreach ($dataTypes as $type) {
            $hits = $this->getCounter("cache:hits:{$type}");
            $misses = $this->getCounter("cache:misses:{$type}");
            $typeTotal = $hits + $misses;

            if ($typeTotal > 0) {
                $byType[$type] = [
                    'hit_rate' => round(($hits / $typeTotal) * 100, 2),
                    'hits' => $hits,
                    'misses' => $misses,
                ];
            }
        }

        return [
            'hit_rate' => round($hitRate, 2),
            'hits' => $totalHits,
            'misses' => $totalMisses,
            'total' => $total,
            'by_type' => $byType,
        ];
    }

    /**
     * Get error rate statistics
     *
     * @return array{error_rate: float, total_requests: int, total_errors: int, by_source: array<string, array{error_rate: float, success: int, errors: int}>, recent_errors: array<int, array<string, mixed>>}
     */
    public function getErrorRateStats(): array
    {
        $sources = ['umapyoi', 'umamusumedb'];
        $bySource = [];
        $totalRequests = 0;
        $totalErrors = 0;
        $recentErrors = [];

        foreach ($sources as $source) {
            $success = $this->getCounter("{$source}:success");
            $errors = $this->getCounter("{$source}:error");
            $sourceTotal = $success + $errors;

            $totalRequests += $sourceTotal;
            $totalErrors += $errors;

            if ($sourceTotal > 0) {
                $bySource[$source] = [
                    'error_rate' => round(($errors / $sourceTotal) * 100, 2),
                    'success' => $success,
                    'errors' => $errors,
                ];
            }

            // Get recent errors for this source
            $errorKey = self::METRICS_PREFIX."errors:{$source}";
            $sourceErrors = Redis::lrange($errorKey, 0, 9); // Last 10 errors

            foreach ($sourceErrors as $errorJson) {
                $error = json_decode($errorJson, true);
                if ($error) {
                    $error['source'] = $source;
                    $recentErrors[] = $error;
                }
            }
        }

        // Sort recent errors by timestamp
        usort($recentErrors, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        $recentErrors = array_slice($recentErrors, 0, 20); // Keep top 20

        $errorRate = $totalRequests > 0 ? ($totalErrors / $totalRequests) * 100 : 0;

        return [
            'error_rate' => round($errorRate, 2),
            'total_requests' => $totalRequests,
            'total_errors' => $totalErrors,
            'by_source' => $bySource,
            'recent_errors' => $recentErrors,
        ];
    }

    /**
     * Get comprehensive dashboard metrics
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        $sources = ['umapyoi', 'umamusumedb'];
        $responseTimeStats = [];

        foreach ($sources as $source) {
            $responseTimeStats[$source] = $this->getResponseTimeStats($source);
        }

        return [
            'response_times' => $responseTimeStats,
            'cache_performance' => $this->getCacheHitRateStats(),
            'error_rates' => $this->getErrorRateStats(),
            'request_volume' => $this->getRequestVolumeStats(),
            'health_status' => $this->getHealthStatusSummary(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get request volume statistics
     *
     * @return array{total: int, by_source: array<string, int>, by_endpoint: array<string, array<string, int>>}
     */
    public function getRequestVolumeStats(): array
    {
        $sources = ['umapyoi', 'umamusumedb'];
        $bySource = [];
        $byEndpoint = [];
        $total = 0;

        foreach ($sources as $source) {
            $success = $this->getCounter("{$source}:success");
            $errors = $this->getCounter("{$source}:error");
            $sourceTotal = $success + $errors;

            $bySource[$source] = $sourceTotal;
            $total += $sourceTotal;

            // Get endpoint breakdown
            $endpointPattern = self::METRICS_PREFIX."{$source}:endpoints:*";
            $endpointKeys = Redis::keys($endpointPattern);

            $sourceEndpoints = [];
            foreach ($endpointKeys as $key) {
                $endpoint = str_replace(self::METRICS_PREFIX."{$source}:endpoints:", '', $key);
                $count = (int) Redis::get($key);
                $sourceEndpoints[$endpoint] = $count;
            }

            if (! empty($sourceEndpoints)) {
                arsort($sourceEndpoints);
                $byEndpoint[$source] = array_slice($sourceEndpoints, 0, 10); // Top 10 endpoints
            }
        }

        return [
            'total' => $total,
            'by_source' => $bySource,
            'by_endpoint' => $byEndpoint,
        ];
    }

    /**
     * Get health status summary
     *
     * @return array{overall: string, sources: array<string, string>, alerts: array<int, string>}
     */
    public function getHealthStatusSummary(): array
    {
        $sources = ['umapyoi', 'umamusumedb'];
        $sourceStatuses = [];
        $alerts = [];

        foreach ($sources as $source) {
            $stats = $this->getResponseTimeStats($source);
            $errorRate = $this->getSourceErrorRate($source);

            // Determine status based on metrics
            if ($stats['count'] === 0) {
                $status = 'unknown';
            } elseif ($errorRate > 10) {
                $status = 'critical';
                $alerts[] = "{$source}: High error rate ({$errorRate}%)";
            } elseif ($stats['p95'] > 5000) {
                $status = 'degraded';
                $alerts[] = "{$source}: Slow response times (p95: {$stats['p95']}ms)";
            } elseif ($errorRate > 5) {
                $status = 'warning';
                $alerts[] = "{$source}: Elevated error rate ({$errorRate}%)";
            } elseif ($stats['p95'] > 2000) {
                $status = 'warning';
                $alerts[] = "{$source}: Elevated response times (p95: {$stats['p95']}ms)";
            } else {
                $status = 'healthy';
            }

            $sourceStatuses[$source] = $status;
        }

        // Determine overall status
        $statuses = array_values($sourceStatuses);
        if (in_array('critical', $statuses)) {
            $overall = 'critical';
        } elseif (in_array('degraded', $statuses)) {
            $overall = 'degraded';
        } elseif (in_array('warning', $statuses)) {
            $overall = 'warning';
        } elseif (in_array('unknown', $statuses)) {
            $overall = 'unknown';
        } else {
            $overall = 'healthy';
        }

        return [
            'overall' => $overall,
            'sources' => $sourceStatuses,
            'alerts' => $alerts,
        ];
    }

    /**
     * Get error rate for a specific source
     */
    protected function getSourceErrorRate(string $source): float
    {
        $success = $this->getCounter("{$source}:success");
        $errors = $this->getCounter("{$source}:error");
        $total = $success + $errors;

        return $total > 0 ? round(($errors / $total) * 100, 2) : 0.0;
    }

    /**
     * Reset all metrics
     */
    public function resetMetrics(): void
    {
        $pattern = self::METRICS_PREFIX.'*';
        $keys = Redis::keys($pattern);

        if (! empty($keys)) {
            Redis::del($keys);
        }

        Log::info('[APIPerformanceMetrics] All metrics reset');
    }

    /**
     * Reset metrics for a specific source
     */
    public function resetSourceMetrics(string $source): void
    {
        $patterns = [
            self::METRICS_PREFIX."response_times:{$source}",
            self::METRICS_PREFIX."{$source}:*",
        ];

        foreach ($patterns as $pattern) {
            $keys = Redis::keys($pattern);
            if (! empty($keys)) {
                Redis::del($keys);
            }
        }

        Log::info('[APIPerformanceMetrics] Metrics reset for source', [
            'source' => $source,
        ]);
    }

    /**
     * Get counter value
     */
    protected function getCounter(string $key): int
    {
        $fullKey = self::METRICS_PREFIX.$key;
        $value = Redis::get($fullKey);

        return $value ? (int) $value : 0;
    }

    /**
     * Calculate percentile from sorted array
     */
    protected function calculatePercentile(array $sortedValues, int $percentile): float
    {
        $count = count($sortedValues);
        if ($count === 0) {
            return 0.0;
        }

        $index = (int) ceil(($percentile / 100) * $count) - 1;
        $index = max(0, min($index, $count - 1));

        return round($sortedValues[$index], 2);
    }

    /**
     * Sanitize endpoint for use as key
     */
    protected function sanitizeEndpoint(string $endpoint): string
    {
        // Remove leading slash and replace special characters
        $sanitized = ltrim($endpoint, '/');
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $sanitized);

        return $sanitized ?: 'unknown';
    }

    /**
     * Extract data type from cache key
     */
    protected function extractDataType(string $key): string
    {
        $parts = explode(':', $key);

        return $parts[0] ?? 'unknown';
    }

    /**
     * Record batch execution metrics
     */
    public function recordBatchExecution(string $batchId, int $requestCount, float $durationMs): void
    {
        $this->incrementCounter('batch:executions');
        $this->incrementCounter('batch:total_requests', $requestCount);

        // Store batch execution time
        $timestamp = now()->timestamp;
        $batchKey = self::METRICS_PREFIX.'batch:execution_times';
        Redis::zadd($batchKey, $timestamp, "{$timestamp}:{$durationMs}:{$requestCount}");

        // Trim old entries
        $cutoff = $timestamp - self::METRICS_WINDOW;
        Redis::zremrangebyscore($batchKey, '-inf', $cutoff);

        Log::debug('[APIPerformanceMetrics] Batch execution recorded', [
            'batch_id' => $batchId,
            'request_count' => $requestCount,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Record parallel fetch metrics
     */
    public function recordParallelFetch(int $requestCount, float $durationMs): void
    {
        $this->incrementCounter('parallel:fetches');
        $this->incrementCounter('parallel:total_requests', $requestCount);

        // Store parallel fetch time
        $timestamp = now()->timestamp;
        $parallelKey = self::METRICS_PREFIX.'parallel:execution_times';
        Redis::zadd($parallelKey, $timestamp, "{$timestamp}:{$durationMs}:{$requestCount}");

        // Trim old entries
        $cutoff = $timestamp - self::METRICS_WINDOW;
        Redis::zremrangebyscore($parallelKey, '-inf', $cutoff);

        Log::debug('[APIPerformanceMetrics] Parallel fetch recorded', [
            'request_count' => $requestCount,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Record compression metrics
     */
    public function recordCompression(int $originalSize, int $compressedSize): void
    {
        $this->incrementCounter('compression:operations');
        $this->incrementCounter('compression:bytes_saved', $originalSize - $compressedSize);
        $this->incrementCounter('compression:original_bytes', $originalSize);
        $this->incrementCounter('compression:compressed_bytes', $compressedSize);

        Log::debug('[APIPerformanceMetrics] Compression recorded', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'savings' => $originalSize - $compressedSize,
        ]);
    }

    /**
     * Get batch execution statistics
     *
     * @return array{total_executions: int, total_requests: int, avg_batch_size: float, avg_duration_ms: float}
     */
    public function getBatchExecutionStats(): array
    {
        $executions = $this->getCounter('batch:executions');
        $totalRequests = $this->getCounter('batch:total_requests');

        $batchKey = self::METRICS_PREFIX.'batch:execution_times';
        $samples = Redis::zrange($batchKey, 0, -1);

        $durations = [];
        foreach ($samples as $sample) {
            $parts = explode(':', $sample);
            if (isset($parts[1])) {
                $durations[] = (float) $parts[1];
            }
        }

        $avgDuration = ! empty($durations) ? round(array_sum($durations) / count($durations), 2) : 0.0;
        $avgBatchSize = $executions > 0 ? round($totalRequests / $executions, 2) : 0.0;

        return [
            'total_executions' => $executions,
            'total_requests' => $totalRequests,
            'avg_batch_size' => $avgBatchSize,
            'avg_duration_ms' => $avgDuration,
        ];
    }

    /**
     * Get parallel fetch statistics
     *
     * @return array{total_fetches: int, total_requests: int, avg_parallel_size: float, avg_duration_ms: float}
     */
    public function getParallelFetchStats(): array
    {
        $fetches = $this->getCounter('parallel:fetches');
        $totalRequests = $this->getCounter('parallel:total_requests');

        $parallelKey = self::METRICS_PREFIX.'parallel:execution_times';
        $samples = Redis::zrange($parallelKey, 0, -1);

        $durations = [];
        foreach ($samples as $sample) {
            $parts = explode(':', $sample);
            if (isset($parts[1])) {
                $durations[] = (float) $parts[1];
            }
        }

        $avgDuration = ! empty($durations) ? round(array_sum($durations) / count($durations), 2) : 0.0;
        $avgParallelSize = $fetches > 0 ? round($totalRequests / $fetches, 2) : 0.0;

        return [
            'total_fetches' => $fetches,
            'total_requests' => $totalRequests,
            'avg_parallel_size' => $avgParallelSize,
            'avg_duration_ms' => $avgDuration,
        ];
    }

    /**
     * Get compression statistics
     *
     * @return array{total_operations: int, bytes_saved: int, original_bytes: int, compressed_bytes: int, compression_ratio: float, savings_percent: float}
     */
    public function getCompressionStats(): array
    {
        $operations = $this->getCounter('compression:operations');
        $bytesSaved = $this->getCounter('compression:bytes_saved');
        $originalBytes = $this->getCounter('compression:original_bytes');
        $compressedBytes = $this->getCounter('compression:compressed_bytes');

        $compressionRatio = $originalBytes > 0 ? round($compressedBytes / $originalBytes, 4) : 1.0;
        $savingsPercent = $originalBytes > 0 ? round(($bytesSaved / $originalBytes) * 100, 2) : 0.0;

        return [
            'total_operations' => $operations,
            'bytes_saved' => $bytesSaved,
            'original_bytes' => $originalBytes,
            'compressed_bytes' => $compressedBytes,
            'compression_ratio' => $compressionRatio,
            'savings_percent' => $savingsPercent,
        ];
    }

    /**
     * Increment a counter by a specific amount
     */
    protected function incrementCounter(string $key, int $amount = 1): void
    {
        $fullKey = self::METRICS_PREFIX.$key;
        Redis::incrby($fullKey, $amount);
        Redis::expire($fullKey, self::METRICS_WINDOW);
    }
}
