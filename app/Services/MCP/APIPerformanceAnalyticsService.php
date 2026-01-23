<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\MCPToolUsage;
use App\Services\CacheManagementService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Performance Analytics Service
 *
 * Provides comprehensive performance analytics for external APIs and MCP tools
 * with trend analysis, anomaly detection, and optimization recommendations.
 *
 * Requirements: 14.5, 55.4, 56.4, Task 4.4.5
 */
class APIPerformanceAnalyticsService
{
    /**
     * Analytics cache prefix
     */
    protected const ANALYTICS_CACHE_PREFIX = 'api_analytics:';

    /**
     * Analytics cache TTL in seconds (15 minutes)
     */
    protected const ANALYTICS_CACHE_TTL = 900;

    /**
     * Performance threshold for slow API calls (ms)
     */
    protected const SLOW_API_THRESHOLD_MS = 2000;

    /**
     * Performance threshold for very slow API calls (ms)
     */
    protected const VERY_SLOW_API_THRESHOLD_MS = 5000;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected APIHealthMonitorService $apiHealthMonitor,
        protected CacheManagementService $cacheManagement
    ) {}

    /**
     * Get comprehensive API performance analytics
     *
     * @param  string  $period  'hour', 'day', 'week', 'month'
     * @return array{
     *     summary: array<string, mixed>,
     *     api_performance: array<string, mixed>,
     *     mcp_tool_performance: array<string, mixed>,
     *     trends: array<string, mixed>,
     *     anomalies: array<string, mixed>,
     *     recommendations: array<string>
     * }
     */
    public function getPerformanceAnalytics(): array
        $cacheKey = self::ANALYTICS_CACHE_PREFIX."{$userId}:{$period}";

        if (Cache::has($cacheKey)) {
            Log::debug('[APIPerformanceAnalytics] Returning cached analytics', [
                'user_id' => $userId,
                'period' => $period,
            ]);

            return Cache::get($cacheKey);
        }

        Log::info('[APIPerformanceAnalytics] Generating performance analytics', [
            'user_id' => $userId,
            'period' => $period,
        ]);

        $startTime = microtime(true);
        $dateRange = $this->getDateRange($period);

        $analytics = [
            'summary' => $this->getPerformanceSummary($userId, $dateRange),
            'api_performance' => $this->getAPIPerformanceMetrics($dateRange),
            'mcp_tool_performance' => $this->getMCPToolPerformanceMetrics($userId, $dateRange),
            'trends' => $this->getPerformanceTrends($userId, $dateRange),
            'anomalies' => $this->detectPerformanceAnomalies($userId, $dateRange),
            'recommendations' => $this->generatePerformanceRecommendations($userId, $dateRange),
        ];

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[APIPerformanceAnalytics] Analytics generated', [
            'user_id' => $userId,
            'period' => $period,
            'duration_ms' => round($duration, 2),
        ]);

        // Cache the analytics
        Cache::put($cacheKey, $analytics, self::ANALYTICS_CACHE_TTL);

        return $analytics;
    }

    /**
     * Get performance summary
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array{
     *     total_requests: int,
     *     successful_requests: int,
     *     failed_requests: int,
     *     success_rate: float,
     *     average_response_time: float,
     *     p50_response_time: float,
     *     p95_response_time: float,
     *     p99_response_time: float,
     *     slow_requests: int,
     *     very_slow_requests: int
     * }
     */
    protected function getPerformanceSummary(): array
        // Get API response times
        $apiStats = [
            'umapyoi' => $this->cacheManagement->getApiResponseTimeStats('umapyoi'),
            'umamusumedb' => $this->cacheManagement->getApiResponseTimeStats('umamusumedb'),
        ];

        // Get MCP tool usage
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        $totalRequests = $apiStats['umapyoi']['count'] + $apiStats['umamusumedb']['count'] + $toolUsage->count();
        $successfulRequests = $toolUsage->where('execution_status', '=', 'success')->count();
        $failedRequests = $toolUsage->where('execution_status', '=', 'failure')->count();

        // Calculate combined metrics
        $allResponseTimes = array_merge(
            $this->getApiResponseTimes('umapyoi'),
            $this->getApiResponseTimes('umamusumedb'),
            $toolUsage->pluck('execution_time')->map(fn ($t) => $t * 1000)->all()
        );

        sort($allResponseTimes);

        $avgResponseTime = ! empty($allResponseTimes) ? array_sum($allResponseTimes) / count($allResponseTimes) : 0;
        $p50 = $this->calculatePercentile($allResponseTimes, 50);
        $p95 = $this->calculatePercentile($allResponseTimes, 95);
        $p99 = $this->calculatePercentile($allResponseTimes, 99);

        $slowRequests = count(array_filter($allResponseTimes, fn ($t) => $t >= self::SLOW_API_THRESHOLD_MS));
        $verySlowRequests = count(array_filter($allResponseTimes, fn ($t) => $t >= self::VERY_SLOW_API_THRESHOLD_MS));

        $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => round($successRate, 2),
            'average_response_time' => round($avgResponseTime, 2),
            'p50_response_time' => round($p50, 2),
            'p95_response_time' => round($p95, 2),
            'p99_response_time' => round($p99, 2),
            'slow_requests' => $slowRequests,
            'very_slow_requests' => $verySlowRequests,
        ];
    }

    /**
     * Get API performance metrics
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array<string, array{
     *     api_name: string,
     *     total_requests: int,
     *     average_response_time: float,
     *     p50: float,
     *     p95: float,
     *     p99: float,
     *     min: float,
     *     max: float,
     *     health_status: string,
     *     availability: float
     * }>
     */
    protected function getAPIPerformanceMetrics(): array
        $apis = ['umapyoi', 'umamusumedb'];
        $metrics = [];

        foreach ($apis as $apiName) {
            $stats = $this->cacheManagement->getApiResponseTimeStats($apiName);
            $health = $this->apiHealthMonitor->getCachedHealth($apiName);

            $metrics[$apiName] = [
                'api_name' => $apiName,
                'total_requests' => $stats['count'],
                'average_response_time' => $stats['avg'],
                'p50' => $stats['p50'],
                'p95' => $stats['p95'],
                'p99' => $stats['p99'],
                'min' => $stats['min'],
                'max' => $stats['max'],
                'health_status' => $health['status'] ?? 'unknown',
                'availability' => $this->calculateAPIAvailability($apiName, $dateRange),
            ];
        }

        return $metrics;
    }

    /**
     * Get MCP tool performance metrics
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array{
     *     by_server: array<string, array<string, mixed>>,
     *     by_tool: array<string, array<string, mixed>>,
     *     slowest_tools: array<array<string, mixed>>
     * }
     */
    protected function getMCPToolPerformanceMetrics(): array
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        // Performance by server
        $byServer = $toolUsage->groupBy('server_name')->map(function ($items, $serverName) {
            $successful = $items->where('execution_status', '=', 'success')->count();
            $total = $items->count();

            return [
                'server_name' => $serverName,
                'total_requests' => $total,
                'successful_requests' => $successful,
                'failed_requests' => $total - $successful,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'average_execution_time' => round($items->avg('execution_time'), 3),
                'total_cost' => round($items->sum('cost_estimate'), 6),
            ];
        })->values()->all();

        // Performance by tool
        $byTool = $toolUsage->groupBy('tool_name')->map(function ($items, $toolName) {
            $successful = $items->where('execution_status', '=', 'success')->count();
            $total = $items->count();

            return [
                'tool_name' => $toolName,
                'server_name' => $items->first()->server_name ?? 'unknown',
                'total_requests' => $total,
                'successful_requests' => $successful,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'average_execution_time' => round($items->avg('execution_time'), 3),
                'total_cost' => round($items->sum('cost_estimate'), 6),
            ];
        })->values()->all();

        // Slowest tools
        $slowestTools = $toolUsage->sortByDesc('execution_time')->take(10)->map(function ($item) {
            return [
                'tool_name' => $item->tool_name,
                'server_name' => $item->server_name,
                'execution_time' => $item->execution_time,
                'executed_at' => $item->executed_at->toIso8601String(),
                'status' => $item->execution_status,
            ];
        })->values()->all();

        return [
            'by_server' => $byServer,
            'by_tool' => $byTool,
            'slowest_tools' => $slowestTools,
        ];
    }

    /**
     * Get performance trends
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array{
     *     response_time_trend: array<array{timestamp: string, average_response_time: float}>,
     *     success_rate_trend: array<array{timestamp: string, success_rate: float}>,
     *     request_volume_trend: array<array{timestamp: string, request_count: int}>
     * }
     */
    protected function getPerformanceTrends(): array
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        // Group by hour for trends
        $responseTimeTrend = $toolUsage->groupBy(function ($item) {
            return $item->executed_at->format('Y-m-d H:00:00');
        })->map(function ($items, $timestamp) {
            return [
                'timestamp' => $timestamp,
                'average_response_time' => round($items->avg('execution_time') * 1000, 2),
            ];
        })->values()->all();

        $successRateTrend = $toolUsage->groupBy(function ($item) {
            return $item->executed_at->format('Y-m-d H:00:00');
        })->map(function ($items, $timestamp) {
            $successful = $items->where('execution_status', '=', 'success')->count();
            $total = $items->count();

            return [
                'timestamp' => $timestamp,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            ];
        })->values()->all();

        $requestVolumeTrend = $toolUsage->groupBy(function ($item) {
            return $item->executed_at->format('Y-m-d H:00:00');
        })->map(function ($items, $timestamp) {
            return [
                'timestamp' => $timestamp,
                'request_count' => $items->count(),
            ];
        })->values()->all();

        return [
            'response_time_trend' => $responseTimeTrend,
            'success_rate_trend' => $successRateTrend,
            'request_volume_trend' => $requestVolumeTrend,
        ];
    }

    /**
     * Detect performance anomalies
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array<array{
     *     type: string,
     *     severity: string,
     *     description: string,
     *     detected_at: string,
     *     affected_component: string,
     *     metric_value: float,
     *     threshold: float
     * }>
     */
    protected function detectPerformanceAnomalies(): array
        $anomalies = [];

        // Check for response time anomalies
        $apiStats = [
            'umapyoi' => $this->cacheManagement->getApiResponseTimeStats('umapyoi'),
            'umamusumedb' => $this->cacheManagement->getApiResponseTimeStats('umamusumedb'),
        ];

        foreach ($apiStats as $apiName => $stats) {
            if ($stats['p95'] > self::VERY_SLOW_API_THRESHOLD_MS) {
                $anomalies[] = [
                    'type' => 'slow_response_time',
                    'severity' => 'high',
                    'description' => "95th percentile response time exceeds threshold for {$apiName}",
                    'detected_at' => now()->toIso8601String(),
                    'affected_component' => $apiName,
                    'metric_value' => $stats['p95'],
                    'threshold' => self::VERY_SLOW_API_THRESHOLD_MS,
                ];
            }
        }

        // Check for success rate anomalies
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        $byServer = $toolUsage->groupBy('server_name');

        foreach ($byServer as $serverName => $items) {
            $successful = $items->where('execution_status', '=', 'success')->count();
            $total = $items->count();
            $successRate = $total > 0 ? ($successful / $total) * 100 : 0;

            if ($successRate < 90 && $total > 10) {
                $anomalies[] = [
                    'type' => 'low_success_rate',
                    'severity' => 'medium',
                    'description' => "Success rate below 90% for {$serverName}",
                    'detected_at' => now()->toIso8601String(),
                    'affected_component' => $serverName,
                    'metric_value' => round($successRate, 2),
                    'threshold' => 90.0,
                ];
            }
        }

        // Check for circuit breaker activations
        if ($this->apiHealthMonitor->isCircuitBreakerOpen('umapyoi')) {
            $anomalies[] = [
                'type' => 'circuit_breaker_open',
                'severity' => 'critical',
                'description' => 'Circuit breaker is open for umapyoi API',
                'detected_at' => now()->toIso8601String(),
                'affected_component' => 'umapyoi',
                'metric_value' => $this->apiHealthMonitor->getFailureCount('umapyoi'),
                'threshold' => 5.0,
            ];
        }

        if ($this->apiHealthMonitor->isCircuitBreakerOpen('umamusumedb')) {
            $anomalies[] = [
                'type' => 'circuit_breaker_open',
                'severity' => 'critical',
                'description' => 'Circuit breaker is open for umamusumedb API',
                'detected_at' => now()->toIso8601String(),
                'affected_component' => 'umamusumedb',
                'metric_value' => $this->apiHealthMonitor->getFailureCount('umamusumedb'),
                'threshold' => 5.0,
            ];
        }

        return $anomalies;
    }

    /**
     * Generate performance recommendations
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array<string>
     */
    protected function generatePerformanceRecommendations(): array
        $recommendations = [];

        // Check API response times
        $apiStats = [
            'umapyoi' => $this->cacheManagement->getApiResponseTimeStats('umapyoi'),
            'umamusumedb' => $this->cacheManagement->getApiResponseTimeStats('umamusumedb'),
        ];

        foreach ($apiStats as $apiName => $stats) {
            if ($stats['avg'] > self::SLOW_API_THRESHOLD_MS) {
                $recommendations[] = "Consider implementing aggressive caching for {$apiName} API to reduce average response time from {$stats['avg']}ms";
            }

            if ($stats['p99'] > self::VERY_SLOW_API_THRESHOLD_MS) {
                $recommendations[] = "Investigate tail latency issues for {$apiName} API (P99: {$stats['p99']}ms)";
            }
        }

        // Check cache hit rate
        $cacheStats = $this->cacheManagement->getHitRateStatistics();
        if ($cacheStats['hit_rate'] < 80) {
            $recommendations[] = "Improve cache hit rate (currently {$cacheStats['hit_rate']}%) by implementing cache warming strategies";
        }

        // Check MCP tool usage
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        $slowTools = $toolUsage->where('execution_time', '>', 5.0);
        if ($slowTools->count() > 0) {
            $recommendations[] = "Optimize slow MCP tool executions ({$slowTools->count()} tools taking >5s)";
        }

        // Check for high-cost operations
        $highCostTools = $toolUsage->where('cost_estimate', '>', 0.01);
        if ($highCostTools->count() > 10) {
            $recommendations[] = "Review high-cost MCP tool usage ({$highCostTools->count()} operations >$0.01 each)";
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Performance is optimal. No recommendations at this time.';
        }

        return $recommendations;
    }

    /**
     * Get date range for period
     *
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon}
     */
    protected function getDateRange(): array
        return match ($period) {
            'hour' => ['start' => now()->subHour(), 'end' => now()],
            'day' => ['start' => now()->startOfDay(), 'end' => now()],
            'week' => ['start' => now()->startOfWeek(), 'end' => now()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()],
            default => ['start' => now()->startOfDay(), 'end' => now()],
        };
    }

    /**
     * Get API response times from Redis
     *
     * @return array<float>
     */
    protected function getApiResponseTimes(): array
        $key = "api_response_time:{$apiName}";

        try {
            $times = Redis::zrange($key, 0, -1);

            return array_map('floatval', $times);
        } catch (\Exception $e) {
            Log::error('[APIPerformanceAnalytics] Failed to get API response times', [
                'api' => $apiName,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Calculate percentile from sorted array
     *
     * @param  array<float>  $values
     */
    protected function calculatePercentile(array $values, int $percentile): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $count = count($values);
        $index = ($percentile / 100) * ($count - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower === $upper) {
            return $values[(int) $index];
        }

        $fraction = $index - $lower;

        return $values[(int) $lower] * (1 - $fraction) + $values[(int) $upper] * $fraction;
    }

    /**
     * Calculate API availability percentage
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     */
    protected function calculateAPIAvailability(string $apiName, array $dateRange): float
    {
        // In production, this would query historical health check data
        // For now, we'll use current health status as a proxy

        $health = $this->apiHealthMonitor->getCachedHealth($apiName);

        if (! $health) {
            return 0.0;
        }

        return match ($health['status']) {
            'healthy' => 100.0,
            'degraded' => 95.0,
            'unhealthy' => 50.0,
            'circuit_open', 'error' => 0.0,
            default => 0.0,
        };
    }

    /**
     * Export performance analytics to CSV
     *
     * @param  string  $period  'hour', 'day', 'week', 'month'
     */
    public function exportAnalytics(int $userId, string $period = 'day'): string
    {
        $analytics = $this->getPerformanceAnalytics($userId, $period);

        $csv = "Timestamp,Metric,Value\n";

        // Export summary metrics
        foreach ($analytics['summary'] as $metric => $value) {
            $csv .= sprintf("%s,%s,%s\n", now()->toIso8601String(), $metric, $value);
        }

        // Export API performance
        foreach ($analytics['api_performance'] as $apiName => $metrics) {
            foreach ($metrics as $metric => $value) {
                $csv .= sprintf("%s,%s_%s,%s\n", now()->toIso8601String(), $apiName, $metric, $value);
            }
        }

        return $csv;
    }
}
