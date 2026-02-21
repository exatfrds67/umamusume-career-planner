<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\APIAlertingService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\APIPerformanceMetricsService;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Monitoring Dashboard Controller
 *
 * Provides comprehensive monitoring endpoints for external API integration including:
 * - API response time tracking (p50, p95, p99)
 * - Cache hit rate monitoring
 * - Error rate tracking
 * - Real-time alerts and health status
 * - Performance metrics and recommendations
 *
 * Endpoints:
 * - GET /api/monitoring/dashboard - Comprehensive dashboard metrics
 * - GET /api/monitoring/response-times - Response time statistics
 * - GET /api/monitoring/cache-performance - Cache hit rate metrics
 * - GET /api/monitoring/error-rates - Error rate statistics
 * - GET /api/monitoring/health - Health status summary
 * - GET /api/monitoring/alerts - Active alerts
 * - POST /api/monitoring/alerts/{id}/acknowledge - Acknowledge alert
 * - GET /api/monitoring/recommendations - Performance recommendations
 * - POST /api/monitoring/reset - Reset metrics
 *
 * Requirements: 14.5 (Performance Optimization and Monitoring)
 * Task: 5.1.1
 */
class APIMonitoringController extends Controller
{
    public function __construct(
        protected APIPerformanceMetricsService $metricsService,
        protected APIHealthMonitorService $healthMonitor,
        protected APIAlertingService $alertingService,
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Get comprehensive dashboard metrics
     *
     * Returns all monitoring data in a single response for dashboard display.
     */
    public function dashboard(): JsonResponse
    {
        $metrics = $this->metricsService->getDashboardMetrics();
        $healthMetrics = $this->healthMonitor->getHealthMetrics();
        $alertStats = $this->alertingService->getAlertStatistics();
        $cacheStats = $this->cacheManager->getStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'performance' => $metrics,
                'health' => $healthMetrics,
                'alerts' => $alertStats,
                'cache' => $cacheStats,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get response time statistics
     *
     * Returns detailed response time metrics including percentiles (p50, p95, p99),
     * average, min, max for each API source.
     */
    public function responseTimes(Request $request): JsonResponse
    {
        $source = $request->query('source');

        if ($source) {
            $stats = $this->metricsService->getResponseTimeStats($source);

            return response()->json([
                'success' => true,
                'data' => [
                    'source' => $source,
                    'statistics' => $stats,
                ],
            ]);
        }

        // Get stats for all sources
        $sources = ['umapyoi', 'umamusumedb'];
        $allStats = [];

        foreach ($sources as $src) {
            $allStats[$src] = $this->metricsService->getResponseTimeStats($src);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sources' => $allStats,
                'thresholds' => [
                    'healthy' => 2000,
                    'degraded' => 5000,
                ],
            ],
        ]);
    }

    /**
     * Get cache performance metrics
     *
     * Returns cache hit rate statistics overall and by data type.
     */
    public function cachePerformance(): JsonResponse
    {
        $cacheStats = $this->metricsService->getCacheHitRateStats();
        $cacheInfo = $this->cacheManager->getStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'hit_rate_metrics' => $cacheStats,
                'cache_statistics' => $cacheInfo,
                'target_hit_rate' => 95.0,
            ],
        ]);
    }

    /**
     * Get error rate statistics
     *
     * Returns error rates by source and recent error details.
     */
    public function errorRates(): JsonResponse
    {
        $errorStats = $this->metricsService->getErrorRateStats();

        return response()->json([
            'success' => true,
            'data' => [
                'error_statistics' => $errorStats,
                'thresholds' => [
                    'acceptable' => 5.0,
                    'warning' => 10.0,
                ],
            ],
        ]);
    }

    /**
     * Get health status summary
     *
     * Returns current health status for all API sources and overall system health.
     */
    public function health(): JsonResponse
    {
        $healthMetrics = $this->healthMonitor->getHealthMetrics();
        $healthSummary = $this->metricsService->getHealthStatusSummary();

        return response()->json([
            'success' => true,
            'data' => [
                'health_metrics' => $healthMetrics,
                'health_summary' => $healthSummary,
            ],
        ]);
    }

    /**
     * Get active alerts
     *
     * Returns unacknowledged alerts and recent alert history.
     */
    public function alerts(Request $request): JsonResponse
    {
        $limitInput = $request->query('limit', '50');
        $limit = is_numeric($limitInput) ? (int) $limitInput : 50;
        $typeInput = $request->query('type');
        $type = is_string($typeInput) ? $typeInput : null;

        $unacknowledged = $this->alertingService->getUnacknowledgedAlerts();
        $history = $this->alertingService->getAlertHistory($type, $limit);
        $statistics = $this->alertingService->getAlertStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'unacknowledged' => $unacknowledged,
                'history' => $history,
                'statistics' => $statistics,
            ],
        ]);
    }

    /**
     * Acknowledge an alert
     *
     * Marks an alert as acknowledged to remove it from active alerts.
     */
    public function acknowledgeAlert(string $alertId): JsonResponse
    {
        $success = $this->alertingService->acknowledgeAlert($alertId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Alert acknowledged successfully',
                'data' => [
                    'alert_id' => $alertId,
                    'acknowledged_at' => now()->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Alert not found',
            'data' => [
                'alert_id' => $alertId,
            ],
        ], 404);
    }

    /**
     * Get performance recommendations
     *
     * Returns actionable recommendations based on current metrics.
     */
    public function recommendations(): JsonResponse
    {
        $healthMetrics = $this->healthMonitor->getHealthMetrics();
        $recommendations = $healthMetrics['recommendations'];

        // Add cache-specific recommendations
        $cacheStats = $this->metricsService->getCacheHitRateStats();
        if ($cacheStats['hit_rate'] < 95.0) {
            $recommendations[] = sprintf(
                'Cache hit rate is %.2f%% (target: 95%%). Consider increasing cache TTL or warming more data.',
                $cacheStats['hit_rate']
            );
        }

        // Add error rate recommendations
        $errorStats = $this->metricsService->getErrorRateStats();
        if ($errorStats['error_rate'] > 5.0) {
            $recommendations[] = sprintf(
                'Error rate is %.2f%% (target: <5%%). Review error logs and consider enabling graceful degradation.',
                $errorStats['error_rate']
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get request volume statistics
     *
     * Returns request volume metrics by source and endpoint.
     */
    public function requestVolume(): JsonResponse
    {
        $volumeStats = $this->metricsService->getRequestVolumeStats();

        return response()->json([
            'success' => true,
            'data' => $volumeStats,
        ]);
    }

    /**
     * Reset metrics
     *
     * Resets all performance metrics. Optionally reset for specific source.
     */
    public function resetMetrics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'sometimes|string|in:umapyoi,umamusumedb,all',
        ]);

        $source = $validated['source'] ?? 'all';

        if ($source === 'all') {
            $this->metricsService->resetMetrics();
            $this->cacheManager->resetStatistics();
            $message = 'All metrics reset successfully';
        } else {
            $this->metricsService->resetSourceMetrics($source);
            $message = "Metrics reset successfully for source: {$source}";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'source' => $source,
                'reset_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get circuit breaker status
     *
     * Returns circuit breaker status for all API sources.
     */
    public function circuitBreakers(): JsonResponse
    {
        $healthMetrics = $this->healthMonitor->getHealthMetrics();
        $circuitBreakers = $healthMetrics['circuit_breakers'];

        return response()->json([
            'success' => true,
            'data' => [
                'circuit_breakers' => $circuitBreakers,
                'failure_counts' => $healthMetrics['failure_counts'],
                'threshold' => 5,
                'timeout_seconds' => APIHealthMonitorService::CIRCUIT_BREAKER_TIMEOUT,
            ],
        ]);
    }

    /**
     * Reset circuit breaker
     *
     * Manually resets circuit breaker for a specific source or all sources.
     */
    public function resetCircuitBreaker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'sometimes|string|in:umapyoi,umamusumedb,all',
        ]);

        $source = $validated['source'] ?? 'all';

        if ($source === 'all') {
            $this->healthMonitor->resetAllCircuitBreakers();
            $message = 'All circuit breakers reset successfully';
        } else {
            $this->healthMonitor->resetCircuitBreaker($source);
            $message = "Circuit breaker reset successfully for source: {$source}";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'source' => $source,
                'reset_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get real-time metrics stream
     *
     * Returns current metrics for real-time dashboard updates.
     */
    public function realtime(): JsonResponse
    {
        $metrics = [
            'response_times' => [],
            'error_rates' => [],
            'cache_hit_rate' => 0,
            'active_alerts' => 0,
        ];

        $sources = ['umapyoi', 'umamusumedb'];

        foreach ($sources as $source) {
            $stats = $this->metricsService->getResponseTimeStats($source);
            $metrics['response_times'][$source] = [
                'p95' => $stats['p95'],
                'avg' => $stats['avg'],
            ];

            $errorRate = $this->getSourceErrorRate($source);
            $metrics['error_rates'][$source] = $errorRate;
        }

        $cacheStats = $this->metricsService->getCacheHitRateStats();
        $metrics['cache_hit_rate'] = $cacheStats['hit_rate'];

        $unacknowledged = $this->alertingService->getUnacknowledgedAlerts();
        $metrics['active_alerts'] = count($unacknowledged);

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get historical metrics
     *
     * Returns historical metrics for trend analysis.
     */
    public function historical(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|string|in:1h,6h,24h,7d',
            'metric' => 'sometimes|string|in:response_time,error_rate,cache_hit_rate',
        ]);

        $period = $validated['period'] ?? '1h';
        $metric = $validated['metric'] ?? 'response_time';

        // For now, return current metrics
        // In production, this would query time-series data
        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'metric' => $metric,
                'message' => 'Historical metrics feature coming soon',
            ],
        ]);
    }

    /**
     * Get source error rate
     */
    protected function getSourceErrorRate(string $source): float
    {
        $errorStats = $this->metricsService->getErrorRateStats();
        $bySource = $errorStats['by_source'];

        return $bySource[$source]['error_rate'] ?? 0.0;
    }
}
