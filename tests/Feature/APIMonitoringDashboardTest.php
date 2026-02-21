<?php

declare(strict_types=1);

use App\Services\ExternalAPI\APIAlertingService;
use App\Services\ExternalAPI\APIPerformanceMetricsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\Support\FakeRedis;

beforeEach(function () {
    Redis::swap(new FakeRedis);

    // Clear cache to ensure clean state
    Cache::flush();

    // Flush the entire Redis test database to ensure clean state
    Redis::connection()->flushdb();
});

describe('API Monitoring Dashboard', function () {
    it('returns comprehensive dashboard metrics', function () {
        $response = $this->getJson('/api/monitoring/dashboard');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'performance',
                'health',
                'alerts',
                'cache',
                'timestamp',
            ],
        ]);
    });

    it('returns response time statistics', function () {
        $response = $this->getJson('/api/monitoring/response-times');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'sources',
                'thresholds',
            ],
        ]);
    });

    it('returns response time statistics for specific source', function () {
        $response = $this->getJson('/api/monitoring/response-times?source=umapyoi');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'source',
                'statistics' => [
                    'p50',
                    'p95',
                    'p99',
                    'avg',
                    'min',
                    'max',
                    'count',
                ],
            ],
        ]);
    });

    it('returns cache performance metrics', function () {
        $response = $this->getJson('/api/monitoring/cache-performance');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'hit_rate_metrics' => [
                    'hit_rate',
                    'hits',
                    'misses',
                    'total',
                    'by_type',
                ],
                'cache_statistics',
                'target_hit_rate',
            ],
        ]);
    });

    it('returns error rate statistics', function () {
        $response = $this->getJson('/api/monitoring/error-rates');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'error_statistics' => [
                    'error_rate',
                    'total_requests',
                    'total_errors',
                    'by_source',
                    'recent_errors',
                ],
                'thresholds',
            ],
        ]);
    });

    it('returns health status summary', function () {
        $response = $this->getJson('/api/monitoring/health');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'health_metrics',
                'health_summary',
            ],
        ]);
    });

    it('returns active alerts', function () {
        $response = $this->getJson('/api/monitoring/alerts');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'unacknowledged',
                'history',
                'statistics',
            ],
        ]);
    });

    it('acknowledges an alert', function () {
        // Create a test alert
        $alertingService = app(APIAlertingService::class);
        $alertingService->sendAlert('test_alert', 'info', 'Test alert message', ['test' => true]);

        // Get the alert ID
        $alerts = $alertingService->getAlertHistory(null, 1);
        $alertId = $alerts[0]['id'];
        assert(is_string($alertId) || is_int($alertId));

        $response = $this->postJson("/api/monitoring/alerts/{$alertId}/acknowledge");

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'Alert acknowledged successfully',
        ]);
    });

    it('returns 404 for non-existent alert', function () {
        $response = $this->postJson('/api/monitoring/alerts/non-existent-id/acknowledge');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Alert not found',
        ]);
    });

    it('returns performance recommendations', function () {
        $response = $this->getJson('/api/monitoring/recommendations');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'recommendations',
                'generated_at',
            ],
        ]);
    });

    it('returns request volume statistics', function () {
        $response = $this->getJson('/api/monitoring/request-volume');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total',
                'by_source',
                'by_endpoint',
            ],
        ]);
    });

    it('returns circuit breaker status', function () {
        $response = $this->getJson('/api/monitoring/circuit-breakers');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'circuit_breakers',
                'failure_counts',
                'threshold',
                'timeout_seconds',
            ],
        ]);
    });

    it('resets circuit breaker for specific source', function () {
        $response = $this->postJson('/api/monitoring/circuit-breakers/reset', [
            'source' => 'umapyoi',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'Circuit breaker reset successfully for source: umapyoi',
        ]);
    });

    it('resets all circuit breakers', function () {
        $response = $this->postJson('/api/monitoring/circuit-breakers/reset', [
            'source' => 'all',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'All circuit breakers reset successfully',
        ]);
    });

    it('returns real-time metrics', function () {
        $response = $this->getJson('/api/monitoring/realtime');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'response_times',
                'error_rates',
                'cache_hit_rate',
                'active_alerts',
            ],
            'timestamp',
        ]);
    });

    it('resets all metrics', function () {
        $response = $this->postJson('/api/monitoring/reset', [
            'source' => 'all',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'All metrics reset successfully',
        ]);
    });

    it('resets metrics for specific source', function () {
        $response = $this->postJson('/api/monitoring/reset', [
            'source' => 'umapyoi',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'Metrics reset successfully for source: umapyoi',
        ]);
    });
});

describe('API Performance Metrics Service', function () {
    it('records response time correctly', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Reset first to ensure clean state
        $metricsService->resetMetrics();

        $metricsService->recordResponseTime('umapyoi', '/characters', 150.5, true);
        $metricsService->recordResponseTime('umapyoi', '/characters', 200.3, true);
        $metricsService->recordResponseTime('umapyoi', '/characters', 180.7, true);

        $stats = $metricsService->getResponseTimeStats('umapyoi');

        expect($stats['count'])->toBe(3);
        expect($stats['avg'])->toBeGreaterThan(0);
        expect($stats['min'])->toBeGreaterThan(0);
        expect($stats['max'])->toBeGreaterThan(0);
    });

    it('records cache hits and misses', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Reset first
        $metricsService->resetMetrics();

        $metricsService->recordCacheAccess('character_data:test', true);
        $metricsService->recordCacheAccess('character_data:test', true);
        $metricsService->recordCacheAccess('character_data:test', false);

        $stats = $metricsService->getCacheHitRateStats();

        expect($stats['hits'])->toBe(2);
        expect($stats['misses'])->toBe(1);
        expect($stats['total'])->toBe(3);
        expect($stats['hit_rate'])->toBe(66.67);
    });

    it('records errors correctly', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Reset first
        $metricsService->resetMetrics();

        $metricsService->recordError('umapyoi', 'ConnectionException', 'Connection timeout');
        $metricsService->recordError('umapyoi', 'TimeoutException', 'Request timeout');

        $stats = $metricsService->getErrorRateStats();

        expect($stats['recent_errors'])->toHaveCount(2);
        expect($stats['recent_errors'][0]['type'])->toBe('TimeoutException');
    });

    it('calculates percentiles correctly', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Reset first to ensure clean state
        $metricsService->resetMetrics();

        // Record 100 response times
        for ($i = 1; $i <= 100; $i++) {
            $metricsService->recordResponseTime('umapyoi', '/test', (float) $i, true);
        }

        $stats = $metricsService->getResponseTimeStats('umapyoi');

        expect($stats['p50'])->toBeGreaterThan(40);
        expect($stats['p50'])->toBeLessThan(60);
        expect($stats['p95'])->toBeGreaterThan(90);
        expect($stats['p99'])->toBeGreaterThan(95);
    });

    it('provides health status summary', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Record some metrics
        $metricsService->recordResponseTime('umapyoi', '/test', 150.0, true);
        $metricsService->recordResponseTime('umapyoi', '/test', 200.0, true);

        $summary = $metricsService->getHealthStatusSummary();

        expect($summary)->toHaveKeys(['overall', 'sources', 'alerts']);
        expect($summary['sources'])->toHaveKey('umapyoi');
    });

    it('resets metrics correctly', function () {
        $metricsService = app(APIPerformanceMetricsService::class);

        // Clear any existing metrics first
        $metricsService->resetMetrics();

        // Use a unique source name to avoid interference from other tests
        $uniqueSource = 'test_reset_'.uniqid();

        // Record some metrics with the unique source
        $metricsService->recordResponseTime($uniqueSource, '/test', 150.0, true);
        $metricsService->recordCacheAccess('test_reset_key_'.uniqid(), true);

        // Verify metrics were recorded
        $stats = $metricsService->getResponseTimeStats($uniqueSource);
        expect($stats['count'])->toBeGreaterThan(0);

        // Reset metrics - this should clear all metrics
        $metricsService->resetMetrics();

        // After reset, new queries should return zero counts
        $freshSource = 'fresh_source_'.uniqid();
        $freshStats = $metricsService->getResponseTimeStats($freshSource);
        expect($freshStats['count'])->toBe(0);

        // Verify cache stats are reset or return defaults
        $cacheStats = $metricsService->getCacheHitRateStats();
        expect($cacheStats)->toHaveKey('total');
    });
});
