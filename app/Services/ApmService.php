<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Application Performance Monitoring (APM) Service
 *
 * Provides comprehensive APM capabilities including:
 * - Aggregation of metrics from database, Redis, and API monitoring services
 * - Real-time performance metrics dashboard data
 * - Overall application health score calculation
 * - Performance trend analysis
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
class ApmService
{
    /**
     * APM data prefix for cache storage
     */
    protected const APM_PREFIX = 'apm:';

    /**
     * Create a new ApmService instance.
     */
    public function __construct(
        protected readonly QueryOptimizationService $queryService,
        protected readonly RedisCacheOptimizationService $redisService,
        protected readonly ApiPerformanceMonitoringService $apiService
    ) {}

    /**
     * Get comprehensive APM dashboard data.
     *
     * @return array{
     *     health_score: array{score: float, status: string, components: array<string, array{score: float, status: string}>},
     *     overview: array<string, mixed>,
     *     database: array<string, mixed>,
     *     cache: array<string, mixed>,
     *     api: array<string, mixed>,
     *     system: array<string, mixed>,
     *     trends: array<string, mixed>,
     *     alerts: array<int, array<string, mixed>>,
     *     regressions: array<int, array<string, mixed>>
     * }
     */
    public function getDashboardData(): array
        $cacheKey = self::APM_PREFIX.'dashboard';
        $cacheTtl = (int) config('apm.dashboard.refresh_interval', 30);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return [
                'health_score' => $this->calculateHealthScore(),
                'overview' => $this->getOverviewMetrics(),
                'database' => $this->getDatabaseMetrics(),
                'cache' => $this->getCacheMetrics(),
                'api' => $this->getApiMetrics(),
                'system' => $this->getSystemMetrics(),
                'trends' => $this->getPerformanceTrends(),
                'alerts' => $this->getRecentAlerts(),
                'regressions' => $this->getRecentRegressions(),
            ];
        });
    }

    /**
     * Calculate overall application health score.
     *
     * @return array{score: float, status: string, components: array<string, array{score: float, status: string, value: mixed, threshold: mixed}>}
     */
    public function calculateHealthScore(): array
        $weights = config('apm.health_score.weights', []);
        $components = [];
        $totalScore = 0.0;

        // Response time score
        $responseTimeScore = $this->calculateResponseTimeScore();
        $components['response_time'] = $responseTimeScore;
        $totalScore = ($totalScore ?? 0) + $responseTimeScore['score'] * ($weights['response_time'] ?? 0.25);

        // Error rate score
        $errorRateScore = $this->calculateErrorRateScore();
        $components['error_rate'] = $errorRateScore;
        $totalScore = ($totalScore ?? 0) + $errorRateScore['score'] * ($weights['error_rate'] ?? 0.25);

        // Cache hit rate score
        $cacheScore = $this->calculateCacheScore();
        $components['cache_hit_rate'] = $cacheScore;
        $totalScore = ($totalScore ?? 0) + $cacheScore['score'] * ($weights['cache_hit_rate'] ?? 0.15);

        // Database health score
        $dbScore = $this->calculateDatabaseScore();
        $components['database_health'] = $dbScore;
        $totalScore = ($totalScore ?? 0) + $dbScore['score'] * ($weights['database_health'] ?? 0.15);

        // Memory usage score
        $memoryScore = $this->calculateMemoryScore();
        $components['memory_usage'] = $memoryScore;
        $totalScore = ($totalScore ?? 0) + $memoryScore['score'] * ($weights['memory_usage'] ?? 0.10);

        // Throughput score
        $throughputScore = $this->calculateThroughputScore();
        $components['throughput'] = $throughputScore;
        $totalScore = ($totalScore ?? 0) + $throughputScore['score'] * ($weights['throughput'] ?? 0.10);

        $status = $this->getHealthStatus($totalScore);

        return [
            'score' => round($totalScore, 1),
            'status' => $status,
            'components' => $components,
        ];
    }

    /**
     * Get overview metrics aggregated from all sources.
     *
     * @return array{
     *     total_requests: int,
     *     avg_response_time_ms: float,
     *     error_rate: float,
     *     cache_hit_rate: float,
     *     active_connections: int,
     *     memory_usage_percent: float,
     *     uptime_hours: float
     * }
     */
    public function getOverviewMetrics(): array
        $apiMetrics = $this->apiService->getOverviewMetrics();
        $cacheStats = $this->redisService->getHitRateStatistics();
        $dbMetrics = $this->queryService->getPerformanceMetrics();

        return [
            'total_requests' => $apiMetrics['total_requests'],
            'avg_response_time_ms' => $apiMetrics['avg_response_time_ms'],
            'error_rate' => $apiMetrics['error_rate'],
            'cache_hit_rate' => $cacheStats['overall']['hit_rate'],
            'active_connections' => $this->getDatabaseConnectionCount(),
            'memory_usage_percent' => $this->getMemoryUsagePercent(),
            'uptime_hours' => $this->getUptimeHours(),
        ];
    }

    /**
     * Get database performance metrics.
     *
     * @return array<string, mixed>
     */
    public function getDatabaseMetrics(): array
        $metrics = $this->queryService->getPerformanceMetrics();
        $slowQueries = $this->queryService->getSlowQueries();
        $cacheStats = $this->queryService->getCacheStats();

        return [
            'total_queries' => $metrics['total_queries'],
            'slow_queries' => $metrics['slow_queries'],
            'avg_query_time_ms' => $metrics['avg_query_time'],
            'cache_hit_rate' => $cacheStats['hit_rate'],
            'recommendations_count' => $metrics['recommendations_count'],
            'recent_slow_queries' => \array_slice($slowQueries, -5),
            'connection_count' => $this->getDatabaseConnectionCount(),
            'thresholds' => [
                'warning_ms' => config('query-optimization.slow_query.warning_threshold_ms'),
                'critical_ms' => config('query-optimization.slow_query.critical_threshold_ms'),
            ],
        ];
    }

    /**
     * Get cache performance metrics.
     *
     * @return array<string, mixed>
     */
    public function getCacheMetrics(): array
        $health = $this->redisService->checkHealth();
        $memory = $this->redisService->getMemoryUsage();
        $hitRate = $this->redisService->getHitRateStatistics();
        $recommendations = $this->redisService->getOptimizationRecommendations();

        return [
            'healthy' => $health['healthy'],
            'latency_ms' => $health['latency_ms'],
            'memory' => [
                'used' => $memory['used_memory_human'],
                'peak' => $memory['used_memory_peak_human'],
                'usage_percent' => $memory['usage_percent'],
                'status' => $memory['status'],
            ],
            'hit_rate' => $hitRate['overall'],
            'recommendations' => $recommendations,
            'error' => $health['error'],
        ];
    }

    /**
     * Get API performance metrics.
     *
     * @return array<string, mixed>
     */
    public function getApiMetrics(): array
        $overview = $this->apiService->getOverviewMetrics();
        $endpoints = $this->apiService->getEndpointMetrics();
        $bottlenecks = $this->apiService->identifyBottlenecks();
        $slowRequests = $this->apiService->getSlowRequests(10);

        return [
            'overview' => $overview,
            'top_endpoints' => $this->getTopEndpoints($endpoints, 5),
            'bottlenecks' => $bottlenecks,
            'slow_requests' => $slowRequests,
            'thresholds' => [
                'slow_ms' => config('api-performance.monitoring.slow_request_threshold_ms'),
                'very_slow_ms' => config('api-performance.monitoring.very_slow_request_threshold_ms'),
            ],
        ];
    }

    /**
     * Get system resource metrics.
     *
     * @return array{
     *     memory: array{current_mb: float, peak_mb: float, limit_mb: float|null, usage_percent: float},
     *     cpu: array{load_average: array<float>|null},
     *     disk: array{free_gb: float, total_gb: float, usage_percent: float}|null,
     *     php: array{version: string, memory_limit: string, max_execution_time: int}
     * }
     */
    public function getSystemMetrics(): array
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimitBytes();

        return [
            'memory' => [
                'current_mb' => round($memoryUsage / 1024 / 1024, 2),
                'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
                'limit_mb' => $memoryLimit ? round($memoryLimit / 1024 / 1024, 2) : null,
                'usage_percent' => $memoryLimit ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
            ],
            'cpu' => [
                'load_average' => \function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            ],
            'disk' => $this->getDiskUsage(),
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit') ?: 'unknown',
                'max_execution_time' => (int) ini_get('max_execution_time'),
            ],
        ];
    }

    /**
     * Get performance trends over time.
     *
     * @return array{hourly: array<string, mixed>, daily: array<string, mixed>}
     */
    public function getPerformanceTrends(): array
        $apiTrends = $this->apiService->getPerformanceTrends();

        return [
            'hourly' => $apiTrends['hourly'],
            'daily' => $apiTrends['daily'],
        ];
    }

    /**
     * Get recent alerts.
     *
     * @return array<int, array{id: string, type: string, severity: string, message: string, timestamp: string, acknowledged: bool}>
     */
    public function getRecentAlerts(): array
        $alertsKey = self::APM_PREFIX.'alerts';
        $alerts = Cache::get($alertsKey, []);

        // Sort by timestamp descending
        usort($alerts, fn ($a, $b) => strtotime($b['timestamp']) <=> strtotime($a['timestamp']));

        return \array_slice($alerts, 0, $limit);
    }

    /**
     * Get recent regressions.
     *
     * @return array<int, array{id: string, metric: string, baseline: float, current: float, deviation_percent: float, detected_at: string, status: string}>
     */
    public function getRecentRegressions(): array
        $regressionsKey = self::APM_PREFIX.'regressions';
        $regressions = Cache::get($regressionsKey, []);
        if (! is_array($regressions)) {
            $regressions = [];
        }

        // Sort by detected_at descending
        usort($regressions, function ($a, $b) {
            $timeA = strtotime($a['detected_at'] ?? '') ?: 0;
            $timeB = strtotime($b['detected_at'] ?? '') ?: 0;

            return $timeB <=> $timeA;
        });

        return \array_slice($regressions, 0, $limit);
    }

    /**
     * Record a metric data point.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordMetric(string $metric, array $data): void
    {
        $key = self::APM_PREFIX."metrics:{$metric}:".date('Y-m-d-H');
        $retention = (int) config('apm.retention_period', 604800);

        $existing = Cache::get($key, []);
        if (! is_array($existing)) {
            $existing = [];
        }
        $existing[] = [
            ...$data,
            'recorded_at' => now()->toIso8601String(),
        ];

        // Keep last 1000 data points per hour
        if (\count($existing) > 1000) {
            $existing = \array_slice($existing, -1000);
        }

        Cache::put($key, $existing, $retention);
    }

    /**
     * Get aggregated metrics for a time range.
     *
     * @return array<string, mixed>
     */
    public function getAggregatedMetrics(): array
        $dataPoints = [];

        for ($i = $hours - 1; $i >= 0; $i--) {
            $timestamp = strtotime("-{$i} hours") ?: time();
            $hourKey = self::APM_PREFIX."metrics:{$metric}:".date('Y-m-d-H', $timestamp);
            $hourData = Cache::get($hourKey, []);
            if (! is_array($hourData)) {
                $hourData = [];
            }
            $dataPoints = array_merge($dataPoints, $hourData);
        }

        if (empty($dataPoints)) {
            return [
                'count' => 0,
                'avg' => 0,
                'min' => 0,
                'max' => 0,
                'p50' => 0,
                'p95' => 0,
                'p99' => 0,
            ];
        }

        $values = array_column($dataPoints, 'value');
        sort($values);
        $count = \count($values);

        return [
            'count' => $count,
            'avg' => round(array_sum($values) / $count, 2),
            'min' => round(min($values), 2),
            'max' => round(max($values), 2),
            'p50' => round($values[(int) ($count * 0.50)] ?? 0, 2),
            'p95' => round($values[(int) ($count * 0.95)] ?? 0, 2),
            'p99' => round($values[(int) ($count * 0.99)] ?? 0, 2),
        ];
    }

    /**
     * Clear all APM data.
     */
    public function clearData(): void
    {
        try {
            // Clear cached dashboard data
            Cache::forget(self::APM_PREFIX.'dashboard');
            Cache::forget(self::APM_PREFIX.'alerts');
            Cache::forget(self::APM_PREFIX.'regressions');
            Cache::forget(self::APM_PREFIX.'baselines');

            Log::info('[APM] All APM data cleared');
        } catch (\Exception $e) {
            Log::error('[APM] Failed to clear data', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate response time health score.
     *
     * @return array{score: float, status: string, value: float, threshold: float}
     */
    protected function calculateResponseTimeScore(): array
        $apiMetrics = $this->apiService->getOverviewMetrics();
        $avgResponseTime = $apiMetrics['avg_response_time_ms'];

        $warningThreshold = (float) config('apm.alerting.thresholds.response_time.warning', 1000);
        $criticalThreshold = (float) config('apm.alerting.thresholds.response_time.critical', 3000);

        // Score calculation: 100 at 0ms, 0 at critical threshold
        $score = max(0, min(100, 100 - ($avgResponseTime / $criticalThreshold) * 100));

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $avgResponseTime,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Calculate error rate health score.
     *
     * @return array{score: float, status: string, value: float, threshold: float}
     */
    protected function calculateErrorRateScore(): array
        $apiMetrics = $this->apiService->getOverviewMetrics();
        $errorRate = $apiMetrics['error_rate'];

        $warningThreshold = (float) config('apm.alerting.thresholds.error_rate.warning', 5);
        $criticalThreshold = (float) config('apm.alerting.thresholds.error_rate.critical', 10);

        // Score calculation: 100 at 0%, 0 at critical threshold
        $score = max(0, min(100, 100 - ($errorRate / $criticalThreshold) * 100));

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $errorRate,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Calculate cache health score.
     *
     * @return array{score: float, status: string, value: float, threshold: float}
     */
    protected function calculateCacheScore(): array
        $hitStats = $this->redisService->getHitRateStatistics();
        $hitRate = $hitStats['overall']['hit_rate'];

        $warningThreshold = (float) config('apm.alerting.thresholds.cache_hit_rate.warning', 70);

        // Score is directly the hit rate
        $score = $hitRate;

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $hitRate,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Calculate database health score.
     *
     * @return array{score: float, status: string, value: int, threshold: int}
     */
    protected function calculateDatabaseScore(): array
        $metrics = $this->queryService->getPerformanceMetrics();
        $slowQueries = $metrics['slow_queries'];

        $warningThreshold = (int) config('apm.alerting.thresholds.slow_queries.warning', 10);
        $criticalThreshold = (int) config('apm.alerting.thresholds.slow_queries.critical', 25);

        // Score calculation: 100 at 0 slow queries, 0 at critical threshold
        $score = max(0, min(100, 100 - ($slowQueries / $criticalThreshold) * 100));

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $slowQueries,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Calculate memory health score.
     *
     * @return array{score: float, status: string, value: float, threshold: float}
     */
    protected function calculateMemoryScore(): array
        $memoryUsagePercent = $this->getMemoryUsagePercent();

        $warningThreshold = (float) config('apm.alerting.thresholds.memory_usage.warning', 70);
        $criticalThreshold = (float) config('apm.alerting.thresholds.memory_usage.critical', 90);

        // Score calculation: 100 at 0%, 0 at critical threshold
        $score = max(0, min(100, 100 - ($memoryUsagePercent / $criticalThreshold) * 100));

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $memoryUsagePercent,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Calculate throughput health score.
     *
     * @return array{score: float, status: string, value: float, threshold: float}
     */
    protected function calculateThroughputScore(): array
        $apiMetrics = $this->apiService->getOverviewMetrics();
        $requestsPerMinute = $apiMetrics['requests_per_minute'];

        $warningThreshold = (float) config('apm.alerting.thresholds.throughput.warning', 10);

        // Score based on having reasonable throughput (100 at warning threshold or above)
        $score = min(100, ($requestsPerMinute / $warningThreshold) * 100);

        return [
            'score' => round($score, 1),
            'status' => $this->getComponentStatus($score),
            'value' => $requestsPerMinute,
            'threshold' => $warningThreshold,
        ];
    }

    /**
     * Get health status from score.
     */
    protected function getHealthStatus(float $score): string
    {
        $thresholds = config('apm.health_score.thresholds', []);

        if ($score >= ($thresholds['excellent'] ?? 90)) {
            return 'excellent';
        }
        if ($score >= ($thresholds['good'] ?? 75)) {
            return 'good';
        }
        if ($score >= ($thresholds['fair'] ?? 60)) {
            return 'fair';
        }
        if ($score >= ($thresholds['poor'] ?? 40)) {
            return 'poor';
        }

        return 'critical';
    }

    /**
     * Get component status from score.
     */
    protected function getComponentStatus(float $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        }
        if ($score >= 70) {
            return 'good';
        }
        if ($score >= 50) {
            return 'warning';
        }

        return 'critical';
    }

    /**
     * Get top endpoints by request count.
     *
     * @param  array<string, array<string, mixed>>  $endpoints
     * @return array<string, array<string, mixed>>
     */
    protected function getTopEndpoints(): array
        uasort($endpoints, fn ($a, $b) => ($b['total_requests'] ?? 0) <=> ($a['total_requests'] ?? 0));

        return \array_slice($endpoints, 0, $limit, true);
    }

    /**
     * Get database connection count.
     */
    protected function getDatabaseConnectionCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            return isset($result[0]) ? (isset($result[0]) && is_numeric($result[0]->Value) ? (int) $result[0]->Value : 0) : 0;
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * Get memory usage percentage.
     */
    protected function getMemoryUsagePercent(): float
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->getMemoryLimitBytes();

        if ($memoryLimit === null || $memoryLimit === 0) {
            return 0.0;
        }

        return round(($memoryUsage / $memoryLimit) * 100, 2);
    }

    /**
     * Get memory limit in bytes.
     */
    protected function getMemoryLimitBytes(): ?int
    {
        $limit = ini_get('memory_limit');

        if ($limit === '-1' || $limit === false) {
            return null;
        }

        $value = (is_numeric($limit) ? (int) $limit : 0);
        $unit = strtoupper(substr($limit, -1));

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Get disk usage information.
     *
     * @return array{free_gb: float, total_gb: float, usage_percent: float}|null
     */
    protected function getDiskUsage(): ?array
    {
        try {
            $path = base_path();
            $free = disk_free_space($path);
            $total = disk_total_space($path);

            if ($free === false || $total === false) {
                return null;
            }

            return [
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
                'usage_percent' => round((($total - $free) / $total) * 100, 2),
            ];
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get application uptime in hours.
     */
    protected function getUptimeHours(): float
    {
        $startTime = Cache::get(self::APM_PREFIX.'start_time');

        if ($startTime === null) {
            Cache::forever(self::APM_PREFIX.'start_time', now()->timestamp);

            return 0.0;
        }

        return round((now()->timestamp - $startTime) / 3600, 2);
    }
}
