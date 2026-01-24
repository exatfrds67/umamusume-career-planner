<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Performance Monitoring Service
 *
 * Provides comprehensive API performance monitoring including:
 * - Request/response timing and metrics collection
 * - Bottleneck identification and analysis
 * - Performance trend tracking
 * - Slow endpoint detection
 * - Resource utilization monitoring
 *
 * @see Requirements: 52.3, 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */
class ApiPerformanceMonitoringService
{
    /**
     * Metrics prefix for Redis storage
     */
    protected const METRICS_PREFIX = 'api_perf:';

    /**
     * Request tracking prefix
     */
    protected const REQUEST_PREFIX = 'api_request:';

    /**
     * Slow request threshold in milliseconds
     */
    protected const SLOW_REQUEST_THRESHOLD_MS = 1000;

    /**
     * Very slow request threshold in milliseconds
     */
    protected const VERY_SLOW_REQUEST_THRESHOLD_MS = 3000;

    /**
     * Maximum requests to track in memory
     */
    protected const MAX_TRACKED_REQUESTS = 1000;

    /**
     * In-memory request tracking buffer
     *
     * @var array<string, array{start_time: float, metadata: array<string, mixed>}>
     */
    protected array $activeRequests = [];

    /**
     * Record the start of a request.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordRequestStart(string $requestId, array $metadata): void
    {
        $this->activeRequests[$requestId] = [
            'start_time' => microtime(true),
            'metadata' => $metadata,
        ];

        // Limit memory usage
        if (count($this->activeRequests) > self::MAX_TRACKED_REQUESTS) {
            $this->activeRequests = array_slice($this->activeRequests, -500, null, true);
        }
    }

    /**
     * Record the end of a request.
     *
     * @param  array<string, mixed>  $metrics
     */
    public function recordRequestEnd(string $requestId, array $metrics): void
    {
        $requestData = $this->activeRequests[$requestId] ?? null;
        unset($this->activeRequests[$requestId]);

        if ($requestData === null) {
            return;
        }

        $metadata = $requestData['metadata'];
        $method = isset($metadata['method']) && is_string($metadata['method']) ? $metadata['method'] : '';
        $path = isset($metadata['path']) && is_string($metadata['path']) ? $metadata['path'] : '';
        $endpoint = $method.':'.$path;

        // Store metrics
        $this->storeRequestMetrics($endpoint, $metrics);

        // Track slow requests
        if ($metrics['duration_ms'] >= self::SLOW_REQUEST_THRESHOLD_MS) {
            $this->trackSlowRequest($requestId, $endpoint, $metrics, $metadata);
        }

        // Update endpoint statistics
        $this->updateEndpointStats($endpoint, $metrics);
    }

    /**
     * Get performance dashboard data.
     *
     * @return array{overview: array<string, mixed>, endpoints: array<string, mixed>, bottlenecks: array<int, array<string, mixed>>, trends: array<string, mixed>}
     */
    public function getDashboard(): array
    {
        return [
            'overview' => $this->getOverviewMetrics(),
            'endpoints' => $this->getEndpointMetrics(),
            'bottlenecks' => $this->identifyBottlenecks(),
            'trends' => $this->getPerformanceTrends(),
        ];
    }

    /**
     * Get overview metrics.
     *
     * @return array{total_requests: int, avg_response_time_ms: float, p95_response_time_ms: float, p99_response_time_ms: float, error_rate: float, slow_request_rate: float, requests_per_minute: float}
     */
    public function getOverviewMetrics(): array
    {
        $metricsKey = self::METRICS_PREFIX.'overview';
        $cached = Cache::get($metricsKey);

        if (is_array($cached)) {
            /** @var array{total_requests: int, avg_response_time_ms: float, p95_response_time_ms: float, p99_response_time_ms: float, error_rate: float, slow_request_rate: float, requests_per_minute: float} $cached */
            return $cached;
        }

        $metrics = [
            'total_requests' => 0,
            'avg_response_time_ms' => 0.0,
            'p95_response_time_ms' => 0.0,
            'p99_response_time_ms' => 0.0,
            'error_rate' => 0.0,
            'slow_request_rate' => 0.0,
            'requests_per_minute' => 0.0,
        ];

        try {
            // Get aggregated metrics from Redis
            $totalsKey = self::METRICS_PREFIX.'totals';
            $totals = Cache::get($totalsKey, [
                'total_requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
                'slow_count' => 0,
                'response_times' => [],
            ]);
            if (! is_array($totals)) {
                $totals = [
                    'total_requests' => 0,
                    'total_duration_ms' => 0,
                    'error_count' => 0,
                    'slow_count' => 0,
                    'response_times' => [],
                ];
            }

            $metrics['total_requests'] = is_int($totals['total_requests']) ? $totals['total_requests'] : 0;

            $totalRequests = is_numeric($totals['total_requests']) ? (int) $totals['total_requests'] : 0;
            $totalDurationMs = is_numeric($totals['total_duration_ms']) ? (float) $totals['total_duration_ms'] : 0.0;
            $errorCount = is_numeric($totals['error_count']) ? (int) $totals['error_count'] : 0;
            $slowCount = is_numeric($totals['slow_count']) ? (int) $totals['slow_count'] : 0;

            if ($totalRequests > 0) {
                $metrics['avg_response_time_ms'] = round(
                    $totalDurationMs / $totalRequests,
                    2
                );
                $metrics['error_rate'] = round(
                    ($errorCount / $totalRequests) * 100,
                    2
                );
                $metrics['slow_request_rate'] = round(
                    ($slowCount / $totalRequests) * 100,
                    2
                );
            }

            // Calculate percentiles from stored response times
            $responseTimes = is_array($totals['response_times']) ? $totals['response_times'] : [];
            if (! empty($responseTimes)) {
                /** @var array<int, float> $responseTimes */
                sort($responseTimes);
                $count = count($responseTimes);
                $p95Index = (int) ($count * 0.95);
                $p99Index = (int) ($count * 0.99);
                $metrics['p95_response_time_ms'] = isset($responseTimes[$p95Index]) && is_numeric($responseTimes[$p95Index])
                    ? (float) $responseTimes[$p95Index]
                    : 0.0;
                $metrics['p99_response_time_ms'] = isset($responseTimes[$p99Index]) && is_numeric($responseTimes[$p99Index])
                    ? (float) $responseTimes[$p99Index]
                    : 0.0;
            }

            // Calculate requests per minute (last hour)
            $requestsLastHour = Cache::get(self::METRICS_PREFIX.'requests_last_hour', 0);
            $requestsLastHourNum = is_numeric($requestsLastHour) ? (float) $requestsLastHour : 0.0;
            $metrics['requests_per_minute'] = round($requestsLastHourNum / 60, 2);

            // Cache for 30 seconds
            Cache::put($metricsKey, $metrics, 30);
        } catch (\Exception $e) {
            Log::warning('[ApiPerformanceMonitoring] Failed to get overview metrics', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metrics;
    }

    /**
     * Get endpoint-specific metrics.
     *
     * @return array<string, array{total_requests: int, avg_response_time_ms: float, max_response_time_ms: float, min_response_time_ms: float, error_rate: float, last_request: string|null}>
     */
    public function getEndpointMetrics(): array
    {
        $metricsKey = self::METRICS_PREFIX.'endpoints';
        $cached = Cache::get($metricsKey, []);

        if (! is_array($cached)) {
            return [];
        }

        /** @var array<string, array{total_requests: int, avg_response_time_ms: float, max_response_time_ms: float, min_response_time_ms: float, error_rate: float, last_request: string|null}> $cached */
        return $cached;
    }

    /**
     * Identify performance bottlenecks.
     *
     * @return array<int, array{type: string, severity: string, endpoint: string, description: string, recommendation: string, metrics: array<string, mixed>}>
     */
    public function identifyBottlenecks(): array
    {
        $bottlenecks = [];
        $endpoints = $this->getEndpointMetrics();

        foreach ($endpoints as $endpoint => $metrics) {
            // Check for slow average response time
            if ($metrics['avg_response_time_ms'] > self::SLOW_REQUEST_THRESHOLD_MS) {
                $bottlenecks[] = [
                    'type' => 'slow_endpoint',
                    'severity' => $metrics['avg_response_time_ms'] > self::VERY_SLOW_REQUEST_THRESHOLD_MS
                        ? 'critical'
                        : 'warning',
                    'endpoint' => $endpoint,
                    'description' => sprintf(
                        'Endpoint has high average response time: %.2fms',
                        $metrics['avg_response_time_ms']
                    ),
                    'recommendation' => 'Consider adding caching, optimizing database queries, or implementing pagination',
                    'metrics' => [
                        'avg_response_time_ms' => $metrics['avg_response_time_ms'],
                        'max_response_time_ms' => $metrics['max_response_time_ms'],
                        'total_requests' => $metrics['total_requests'],
                    ],
                ];
            }

            // Check for high error rate
            if ($metrics['error_rate'] > 5) {
                $bottlenecks[] = [
                    'type' => 'high_error_rate',
                    'severity' => $metrics['error_rate'] > 10 ? 'critical' : 'warning',
                    'endpoint' => $endpoint,
                    'description' => sprintf(
                        'Endpoint has high error rate: %.2f%%',
                        $metrics['error_rate']
                    ),
                    'recommendation' => 'Review error logs and implement proper error handling',
                    'metrics' => [
                        'error_rate' => $metrics['error_rate'],
                        'total_requests' => $metrics['total_requests'],
                    ],
                ];
            }

            // Check for high variance (max >> avg)
            if ($metrics['max_response_time_ms'] > $metrics['avg_response_time_ms'] * 5) {
                $bottlenecks[] = [
                    'type' => 'high_variance',
                    'severity' => 'info',
                    'endpoint' => $endpoint,
                    'description' => sprintf(
                        'Endpoint has high response time variance (avg: %.2fms, max: %.2fms)',
                        $metrics['avg_response_time_ms'],
                        $metrics['max_response_time_ms']
                    ),
                    'recommendation' => 'Investigate intermittent slow queries or external service calls',
                    'metrics' => [
                        'avg_response_time_ms' => $metrics['avg_response_time_ms'],
                        'max_response_time_ms' => $metrics['max_response_time_ms'],
                    ],
                ];
            }
        }

        // Sort by severity
        usort($bottlenecks, function ($a, $b) {
            $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];

            return ($severityOrder[$a['severity']] ?? 3) <=> ($severityOrder[$b['severity']] ?? 3);
        });

        return $bottlenecks;
    }

    /**
     * Get performance trends.
     *
     * @return array{hourly: array<string, array{requests: int, avg_response_time_ms: float, error_rate: float}>, daily: array<string, array{requests: int, avg_response_time_ms: float, error_rate: float}>}
     */
    public function getPerformanceTrends(): array
    {
        return [
            'hourly' => $this->getHourlyTrends(),
            'daily' => $this->getDailyTrends(),
        ];
    }

    /**
     * Get slow requests.
     *
     * @return array<int, array{request_id: string, endpoint: string, duration_ms: float, timestamp: string, metadata: array<string, mixed>}>
     */
    public function getSlowRequests(int $limit = 10): array
    {
        $slowRequestsKey = self::METRICS_PREFIX.'slow_requests';
        $slowRequests = Cache::get($slowRequestsKey, []);

        if (! is_array($slowRequests)) {
            return [];
        }

        /** @var array<int, array{request_id: string, endpoint: string, duration_ms: float, timestamp: string, metadata: array<string, mixed>}> $slowRequests */
        // Sort by duration descending
        usort($slowRequests, fn ($a, $b) => $b['duration_ms'] <=> $a['duration_ms']);

        return array_slice($slowRequests, 0, $limit);
    }

    /**
     * Get request batching recommendations.
     *
     * @return array<int, array{endpoints: array<string>, reason: string, potential_savings_ms: float}>
     */
    public function getBatchingRecommendations(): array
    {
        $recommendations = [];
        $endpoints = $this->getEndpointMetrics();

        // Group endpoints by resource type
        $resourceGroups = [];
        foreach ($endpoints as $endpoint => $metrics) {
            // Extract resource type from endpoint (e.g., "GET:/api/characters" -> "characters")
            if (preg_match('/\/api\/([^\/]+)/', $endpoint, $matches)) {
                $resource = $matches[1];
                $resourceGroups[$resource][] = [
                    'endpoint' => $endpoint,
                    'metrics' => $metrics,
                ];
            }
        }

        // Identify batching opportunities
        foreach ($resourceGroups as $resource => $group) {
            if (count($group) > 1) {
                $totalTime = array_sum(array_column(array_column($group, 'metrics'), 'avg_response_time_ms'));
                $potentialSavings = $totalTime * 0.3; // Estimate 30% savings from batching

                if ($potentialSavings > 100) { // Only recommend if savings > 100ms
                    $recommendations[] = [
                        'endpoints' => array_column($group, 'endpoint'),
                        'reason' => "Multiple {$resource} endpoints could be batched into a single request",
                        'potential_savings_ms' => round($potentialSavings, 2),
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get resource utilization metrics.
     *
     * @return array{memory: array{current_mb: float, peak_mb: float, limit_mb: float|null}, cpu: array{load_average: array<float>|null}, database: array{active_connections: int|null, slow_queries: int}}
     */
    public function getResourceUtilization(): array
    {
        $loadAverage = null;
        if (function_exists('sys_getloadavg')) {
            $loadResult = sys_getloadavg();
            if (is_array($loadResult)) {
                /** @var array<float> $loadAverage */
                $loadAverage = array_map('floatval', $loadResult);
            }
        }

        return [
            'memory' => [
                'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'limit_mb' => $this->getMemoryLimit(),
            ],
            'cpu' => [
                'load_average' => $loadAverage,
            ],
            'database' => [
                'active_connections' => $this->getDatabaseConnectionCount(),
                'slow_queries' => $this->getSlowQueryCount(),
            ],
        ];
    }

    /**
     * Clear all performance metrics.
     */
    public function clearMetrics(): void
    {
        try {
            $patterns = [
                self::METRICS_PREFIX.'*',
                self::REQUEST_PREFIX.'*',
            ];

            if ($this->isRedisAvailable()) {
                foreach ($patterns as $pattern) {
                    $cursor = 0;
                    do {
                        $keys = Redis::scan($cursor, $pattern, 100);
                        if ($keys === false) {
                            break;
                        }
                        /** @var array<int, string> $keys */
                        if (! empty($keys)) {
                            Redis::del(...$keys);
                        }
                    } while ($cursor !== 0);
                }
            }

            Log::info('[ApiPerformanceMonitoring] Metrics cleared');
        } catch (\Exception $e) {
            Log::error('[ApiPerformanceMonitoring] Failed to clear metrics', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store request metrics.
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function storeRequestMetrics(string $endpoint, array $metrics): void
    {
        try {
            // Update totals
            $totalsKey = self::METRICS_PREFIX.'totals';
            $totalsRaw = Cache::get($totalsKey, [
                'total_requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
                'slow_count' => 0,
                'response_times' => [],
            ]);

            /** @var array{total_requests: int, total_duration_ms: float, error_count: int, slow_count: int, response_times: array<int, float>} $totals */
            $totals = is_array($totalsRaw) ? $totalsRaw : [
                'total_requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
                'slow_count' => 0,
                'response_times' => [],
            ];

            $durationMsRaw = $metrics['duration_ms'] ?? 0;
            $statusCodeRaw = $metrics['status_code'] ?? 0;
            $durationMs = is_numeric($durationMsRaw) ? (float) $durationMsRaw : 0.0;
            $statusCode = is_numeric($statusCodeRaw) ? (int) $statusCodeRaw : 0;

            $totals['total_requests'] = (is_int($totals['total_requests']) ? $totals['total_requests'] : 0) + 1;
            $totals['total_duration_ms'] = (is_numeric($totals['total_duration_ms']) ? (float) $totals['total_duration_ms'] : 0.0) + $durationMs;

            if ($statusCode >= 400) {
                $totals['error_count'] = (is_int($totals['error_count']) ? $totals['error_count'] : 0) + 1;
            }

            if ($durationMs >= self::SLOW_REQUEST_THRESHOLD_MS) {
                $totals['slow_count'] = (is_int($totals['slow_count']) ? $totals['slow_count'] : 0) + 1;
            }

            // Keep last 1000 response times for percentile calculation
            $responseTimes = is_array($totals['response_times']) ? $totals['response_times'] : [];
            $responseTimes[] = $durationMs;
            if (count($responseTimes) > 1000) {
                $responseTimes = array_slice($responseTimes, -1000);
            }
            $totals['response_times'] = $responseTimes;

            Cache::put($totalsKey, $totals, 86400);

            // Update hourly counter
            $hourKey = self::METRICS_PREFIX.'requests_last_hour';
            Cache::increment($hourKey);
            Cache::put($hourKey, Cache::get($hourKey, 0), 3600);
        } catch (\Exception $e) {
            // Silently fail metrics storage
        }
    }

    /**
     * Track slow request.
     *
     * @param  array<string, mixed>  $metrics
     * @param  array<string, mixed>  $metadata
     */
    protected function trackSlowRequest(string $requestId, string $endpoint, array $metrics, array $metadata): void
    {
        try {
            $slowRequestsKey = self::METRICS_PREFIX.'slow_requests';
            $slowRequests = Cache::get($slowRequestsKey, []);
            if (! is_array($slowRequests)) {
                $slowRequests = [];
            }

            $durationMsRaw = $metrics['duration_ms'] ?? 0;
            $statusCodeRaw = $metrics['status_code'] ?? 0;
            $memoryBytesRaw = $metrics['memory_bytes'] ?? 0;
            $responseSizeRaw = $metrics['response_size'] ?? 0;

            $slowRequests[] = [
                'request_id' => $requestId,
                'endpoint' => $endpoint,
                'duration_ms' => is_numeric($durationMsRaw) ? (float) $durationMsRaw : 0.0,
                'status_code' => is_numeric($statusCodeRaw) ? (int) $statusCodeRaw : 0,
                'memory_bytes' => is_numeric($memoryBytesRaw) ? (int) $memoryBytesRaw : 0,
                'response_size' => is_numeric($responseSizeRaw) ? (int) $responseSizeRaw : 0,
                'timestamp' => now()->toIso8601String(),
                'metadata' => [
                    'user_id' => (isset($metadata['user_id']) ? $metadata['user_id'] : null),
                    'ip' => (isset($metadata['ip']) ? $metadata['ip'] : null),
                ],
            ];

            // Keep last 100 slow requests
            if (count($slowRequests) > 100) {
                $slowRequests = array_slice($slowRequests, -100);
            }

            Cache::put($slowRequestsKey, $slowRequests, 86400);
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Update endpoint statistics.
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function updateEndpointStats(string $endpoint, array $metrics): void
    {
        try {
            $endpointsKey = self::METRICS_PREFIX.'endpoints';
            $endpointsRaw = Cache::get($endpointsKey, []);

            /** @var array<string, array{total_requests: int, total_duration_ms: float, error_count: int, max_response_time_ms: float, min_response_time_ms: float, last_request: string|null, avg_response_time_ms?: float, error_rate?: float}> $endpoints */
            $endpoints = is_array($endpointsRaw) ? $endpointsRaw : [];

            if (! isset($endpoints[$endpoint])) {
                $endpoints[$endpoint] = [
                    'total_requests' => 0,
                    'total_duration_ms' => 0.0,
                    'error_count' => 0,
                    'max_response_time_ms' => 0.0,
                    'min_response_time_ms' => PHP_FLOAT_MAX,
                    'last_request' => null,
                ];
            }

            $durationMsRaw = $metrics['duration_ms'] ?? 0;
            $statusCodeRaw = $metrics['status_code'] ?? 0;
            $durationMs = is_numeric($durationMsRaw) ? (float) $durationMsRaw : 0.0;
            $statusCode = is_numeric($statusCodeRaw) ? (int) $statusCodeRaw : 0;

            $currentStats = $endpoints[$endpoint];
            $totalRequests = (is_int($currentStats['total_requests']) ? $currentStats['total_requests'] : 0) + 1;
            $totalDurationMs = (is_numeric($currentStats['total_duration_ms']) ? (float) $currentStats['total_duration_ms'] : 0.0) + $durationMs;
            $maxResponseTimeMs = max(
                is_numeric($currentStats['max_response_time_ms']) ? (float) $currentStats['max_response_time_ms'] : 0.0,
                $durationMs
            );
            $minResponseTimeMs = min(
                is_numeric($currentStats['min_response_time_ms']) ? (float) $currentStats['min_response_time_ms'] : PHP_FLOAT_MAX,
                $durationMs
            );
            $errorCount = is_int($currentStats['error_count']) ? $currentStats['error_count'] : 0;

            if ($statusCode >= 400) {
                $errorCount++;
            }

            // Calculate derived metrics
            $avgResponseTimeMs = $totalRequests > 0 ? round($totalDurationMs / $totalRequests, 2) : 0.0;
            $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0.0;

            $endpoints[$endpoint] = [
                'total_requests' => $totalRequests,
                'total_duration_ms' => $totalDurationMs,
                'error_count' => $errorCount,
                'max_response_time_ms' => $maxResponseTimeMs,
                'min_response_time_ms' => $minResponseTimeMs,
                'last_request' => now()->toIso8601String(),
                'avg_response_time_ms' => $avgResponseTimeMs,
                'error_rate' => $errorRate,
            ];

            Cache::put($endpointsKey, $endpoints, 86400);
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Get hourly trends.
     *
     * @return array<string, array{requests: int, avg_response_time_ms: float, error_rate: float}>
     */
    protected function getHourlyTrends(): array
    {
        $trends = [];

        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('Y-m-d H:00');
            $hourKey = self::METRICS_PREFIX.'hourly:'.$hour;
            $hourDataRaw = Cache::get($hourKey, [
                'requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
            ]);

            /** @var array{requests: int, total_duration_ms: float, error_count: int} $hourData */
            $hourData = is_array($hourDataRaw) ? $hourDataRaw : [
                'requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
            ];

            $requests = is_int($hourData['requests']) ? $hourData['requests'] : 0;
            $totalDurationMs = is_numeric($hourData['total_duration_ms']) ? (float) $hourData['total_duration_ms'] : 0.0;
            $errorCount = is_int($hourData['error_count']) ? $hourData['error_count'] : 0;

            $trends[$hour] = [
                'requests' => $requests,
                'avg_response_time_ms' => $requests > 0
                    ? round($totalDurationMs / $requests, 2)
                    : 0.0,
                'error_rate' => $requests > 0
                    ? round(($errorCount / $requests) * 100, 2)
                    : 0.0,
            ];
        }

        return $trends;
    }

    /**
     * Get daily trends.
     *
     * @return array<string, array{requests: int, avg_response_time_ms: float, error_rate: float}>
     */
    protected function getDailyTrends(): array
    {
        $trends = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $dayKey = self::METRICS_PREFIX.'daily:'.$day;
            $dayDataRaw = Cache::get($dayKey, [
                'requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
            ]);

            /** @var array{requests: int, total_duration_ms: float, error_count: int} $dayData */
            $dayData = is_array($dayDataRaw) ? $dayDataRaw : [
                'requests' => 0,
                'total_duration_ms' => 0,
                'error_count' => 0,
            ];

            $requests = is_int($dayData['requests']) ? $dayData['requests'] : 0;
            $totalDurationMs = is_numeric($dayData['total_duration_ms']) ? (float) $dayData['total_duration_ms'] : 0.0;
            $errorCount = is_int($dayData['error_count']) ? $dayData['error_count'] : 0;

            $trends[$day] = [
                'requests' => $requests,
                'avg_response_time_ms' => $requests > 0
                    ? round($totalDurationMs / $requests, 2)
                    : 0.0,
                'error_rate' => $requests > 0
                    ? round(($errorCount / $requests) * 100, 2)
                    : 0.0,
            ];
        }

        return $trends;
    }

    /**
     * Get memory limit in MB.
     */
    protected function getMemoryLimit(): ?float
    {
        $limit = ini_get('memory_limit');

        if ($limit === '-1') {
            return null;
        }

        $value = (is_numeric($limit) ? (int) $limit : 0);
        $unit = strtoupper(substr($limit, -1));

        return match ($unit) {
            'G' => $value * 1024,
            'M' => (float) $value,
            'K' => $value / 1024,
            default => $value / 1024 / 1024,
        };
    }

    /**
     * Get database connection count.
     */
    protected function getDatabaseConnectionCount(): ?int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            return isset($result[0]) ? (isset($result[0]) && is_numeric($result[0]->Value) ? (int) $result[0]->Value : 0) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get slow query count.
     */
    protected function getSlowQueryCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Slow_queries'");

            return isset($result[0]) ? (isset($result[0]) && is_numeric($result[0]->Value) ? (int) $result[0]->Value : 0) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if Redis is available.
     */
    protected function isRedisAvailable(): bool
    {
        return extension_loaded('redis') && config('cache.default', '') === 'redis';
    }
}
