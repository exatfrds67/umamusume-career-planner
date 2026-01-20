<?php

declare(strict_types=1);

use App\Services\ApmService;
use Illuminate\Support\Facades\Cache;

/**
 * APM Service Tests
 *
 * Tests for the Application Performance Monitoring service including:
 * - Dashboard data aggregation
 * - Health score calculation
 * - Metrics collection and aggregation
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
describe('APM Service', function () {
    beforeEach(function () {
        // Clear any cached APM data
        Cache::flush();
    });

    describe('Dashboard Data', function () {
        it('returns comprehensive dashboard data structure', function () {
            $apmService = app(ApmService::class);

            $dashboard = $apmService->getDashboardData();

            expect($dashboard)->toBeArray()
                ->toHaveKeys([
                    'health_score',
                    'overview',
                    'database',
                    'cache',
                    'api',
                    'system',
                    'trends',
                    'alerts',
                    'regressions',
                ]);
        });

        it('caches dashboard data for configured interval', function () {
            $apmService = app(ApmService::class);

            // First call should populate cache
            $dashboard1 = $apmService->getDashboardData();

            // Second call should return cached data
            $dashboard2 = $apmService->getDashboardData();

            expect($dashboard1)->toEqual($dashboard2);
        });
    });

    describe('Health Score Calculation', function () {
        it('calculates health score with all components', function () {
            $apmService = app(ApmService::class);

            $healthScore = $apmService->calculateHealthScore();

            expect($healthScore)->toBeArray()
                ->toHaveKeys(['score', 'status', 'components']);

            expect($healthScore['score'])->toBeFloat()
                ->toBeGreaterThanOrEqual(0)
                ->toBeLessThanOrEqual(100);

            expect($healthScore['status'])->toBeString()
                ->toBeIn(['excellent', 'good', 'fair', 'poor', 'critical']);

            expect($healthScore['components'])->toBeArray()
                ->toHaveKeys([
                    'response_time',
                    'error_rate',
                    'cache_hit_rate',
                    'database_health',
                    'memory_usage',
                    'throughput',
                ]);
        });

        it('returns excellent status for high scores', function () {
            $apmService = app(ApmService::class);

            $healthScore = $apmService->calculateHealthScore();

            // The status should match the score thresholds
            if ($healthScore['score'] >= 90) {
                expect($healthScore['status'])->toBe('excellent');
            } elseif ($healthScore['score'] >= 75) {
                expect($healthScore['status'])->toBe('good');
            } elseif ($healthScore['score'] >= 60) {
                expect($healthScore['status'])->toBe('fair');
            } elseif ($healthScore['score'] >= 40) {
                expect($healthScore['status'])->toBe('poor');
            } else {
                expect($healthScore['status'])->toBe('critical');
            }
        });

        it('includes component scores with status and values', function () {
            $apmService = app(ApmService::class);

            $healthScore = $apmService->calculateHealthScore();

            foreach ($healthScore['components'] as $name => $component) {
                expect($component)->toBeArray()
                    ->toHaveKeys(['score', 'status', 'value', 'threshold']);

                expect($component['score'])->toBeFloat()
                    ->toBeGreaterThanOrEqual(0)
                    ->toBeLessThanOrEqual(100);

                expect($component['status'])->toBeString()
                    ->toBeIn(['excellent', 'good', 'warning', 'critical']);
            }
        });
    });

    describe('Overview Metrics', function () {
        it('returns overview metrics with required fields', function () {
            $apmService = app(ApmService::class);

            $overview = $apmService->getOverviewMetrics();

            expect($overview)->toBeArray()
                ->toHaveKeys([
                    'total_requests',
                    'avg_response_time_ms',
                    'error_rate',
                    'cache_hit_rate',
                    'active_connections',
                    'memory_usage_percent',
                    'uptime_hours',
                ]);
        });

        it('returns numeric values for all metrics', function () {
            $apmService = app(ApmService::class);

            $overview = $apmService->getOverviewMetrics();

            expect($overview['total_requests'])->toBeInt();
            expect($overview['avg_response_time_ms'])->toBeFloat();
            expect($overview['error_rate'])->toBeFloat();
            expect($overview['cache_hit_rate'])->toBeFloat();
            expect($overview['active_connections'])->toBeInt();
            expect($overview['memory_usage_percent'])->toBeFloat();
            expect($overview['uptime_hours'])->toBeFloat();
        });
    });

    describe('Database Metrics', function () {
        it('returns database metrics with required fields', function () {
            $apmService = app(ApmService::class);

            $dbMetrics = $apmService->getDatabaseMetrics();

            expect($dbMetrics)->toBeArray()
                ->toHaveKeys([
                    'total_queries',
                    'slow_queries',
                    'avg_query_time_ms',
                    'cache_hit_rate',
                    'recommendations_count',
                    'recent_slow_queries',
                    'connection_count',
                    'thresholds',
                ]);
        });
    });

    describe('Cache Metrics', function () {
        it('returns cache metrics with required fields', function () {
            $apmService = app(ApmService::class);

            $cacheMetrics = $apmService->getCacheMetrics();

            expect($cacheMetrics)->toBeArray()
                ->toHaveKeys([
                    'healthy',
                    'latency_ms',
                    'memory',
                    'hit_rate',
                    'recommendations',
                ]);
        });
    });

    describe('System Metrics', function () {
        it('returns system metrics with required fields', function () {
            $apmService = app(ApmService::class);

            $systemMetrics = $apmService->getSystemMetrics();

            expect($systemMetrics)->toBeArray()
                ->toHaveKeys(['memory', 'cpu', 'disk', 'php']);

            expect($systemMetrics['memory'])->toBeArray()
                ->toHaveKeys(['current_mb', 'peak_mb', 'limit_mb', 'usage_percent']);

            expect($systemMetrics['php'])->toBeArray()
                ->toHaveKeys(['version', 'memory_limit', 'max_execution_time']);
        });
    });

    describe('Metric Recording', function () {
        it('records metric data points', function () {
            $apmService = app(ApmService::class);

            $apmService->recordMetric('test_metric', ['value' => 100]);
            $apmService->recordMetric('test_metric', ['value' => 200]);
            $apmService->recordMetric('test_metric', ['value' => 300]);

            $aggregated = $apmService->getAggregatedMetrics('test_metric', 1);

            expect($aggregated['count'])->toBe(3);
            expect($aggregated['avg'])->toBe(200.0);
            expect($aggregated['min'])->toBe(100.0);
            expect($aggregated['max'])->toBe(300.0);
        });

        it('returns empty aggregation for non-existent metrics', function () {
            $apmService = app(ApmService::class);

            $aggregated = $apmService->getAggregatedMetrics('non_existent_metric', 1);

            expect($aggregated['count'])->toBe(0);
            expect($aggregated['avg'])->toBe(0);
        });
    });

    describe('Data Clearing', function () {
        it('clears all APM data', function () {
            $apmService = app(ApmService::class);

            // Record some data
            $apmService->recordMetric('test_metric', ['value' => 100]);

            // Clear data
            $apmService->clearData();

            // Verify alerts and regressions are cleared
            $alerts = $apmService->getRecentAlerts();
            $regressions = $apmService->getRecentRegressions();

            expect($alerts)->toBeArray()->toBeEmpty();
            expect($regressions)->toBeArray()->toBeEmpty();
        });
    });
});
