<?php

declare(strict_types=1);

use App\Services\PerformanceRegressionService;
use Illuminate\Support\Facades\Cache;

/**
 * Performance Regression Service Tests
 *
 * Tests for the performance regression detection service including:
 * - Baseline calculation
 * - Regression detection
 * - Regression management
 * - Report generation
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
describe('Performance Regression Service', function () {
    beforeEach(function () {
        // Clear any cached regression data
        Cache::flush();
    });

    describe('Baseline Calculation', function () {
        it('returns null when insufficient data points', function () {
            $regressionService = app(PerformanceRegressionService::class);

            $baseline = $regressionService->calculateBaseline('response_time');

            // With no historical data, baseline should be null
            expect($baseline)->toBeNull();
        });

        it('retrieves baseline from cache if available', function () {
            $regressionService = app(PerformanceRegressionService::class);

            // Manually set a baseline in cache
            $mockBaseline = [
                'metric' => 'test_metric',
                'baseline' => 100.0,
                'std_dev' => 10.0,
                'data_points' => 150,
                'calculated_at' => now()->toIso8601String(),
            ];

            Cache::put('apm:baseline:test_metric', $mockBaseline, 86400);

            $baseline = $regressionService->getBaseline('test_metric');

            expect($baseline)->toBeArray()
                ->toHaveKeys(['metric', 'baseline', 'std_dev', 'data_points', 'calculated_at']);

            expect($baseline['metric'])->toBe('test_metric');
            expect($baseline['baseline'])->toBe(100.0);
        });
    });

    describe('Regression Detection', function () {
        it('checks for regressions and returns result structure', function () {
            $regressionService = app(PerformanceRegressionService::class);

            $result = $regressionService->checkRegressions();

            expect($result)->toBeArray()
                ->toHaveKeys(['checked', 'detected', 'regressions']);

            expect($result['checked'])->toBeInt()->toBeGreaterThan(0);
            expect($result['detected'])->toBeInt()->toBeGreaterThanOrEqual(0);
            expect($result['regressions'])->toBeArray();
        });
    });

    describe('Regression Management', function () {
        it('retrieves all regressions', function () {
            $regressionService = app(PerformanceRegressionService::class);

            $regressions = $regressionService->getRegressions();

            expect($regressions)->toBeArray();
        });

        it('retrieves active regressions only', function () {
            $regressionService = app(PerformanceRegressionService::class);

            // Manually add some regressions
            $mockRegressions = [
                [
                    'id' => 'reg_1',
                    'metric' => 'response_time',
                    'baseline' => 100.0,
                    'current' => 150.0,
                    'deviation_percent' => 50.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_DETECTED,
                ],
                [
                    'id' => 'reg_2',
                    'metric' => 'error_rate',
                    'baseline' => 1.0,
                    'current' => 2.0,
                    'deviation_percent' => 100.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_RESOLVED,
                ],
            ];

            Cache::put('apm:regression:list', $mockRegressions, 86400);
            Cache::put('apm:regressions', $mockRegressions, 86400);

            $activeRegressions = $regressionService->getActiveRegressions();

            expect($activeRegressions)->toBeArray()->toHaveCount(1);
            expect($activeRegressions[0]['status'])->toBe('detected');
        });

        it('updates regression status', function () {
            $regressionService = app(PerformanceRegressionService::class);

            // Add a regression
            $mockRegressions = [
                [
                    'id' => 'reg_update_test',
                    'metric' => 'response_time',
                    'baseline' => 100.0,
                    'current' => 150.0,
                    'deviation_percent' => 50.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_DETECTED,
                ],
            ];

            Cache::put('apm:regression:list', $mockRegressions, 86400);

            $result = $regressionService->updateRegressionStatus(
                'reg_update_test',
                PerformanceRegressionService::STATUS_RESOLVED,
                'Fixed by optimizing database queries'
            );

            expect($result)->toBeTrue();

            $regressions = $regressionService->getRegressions();
            $updated = collect($regressions)->firstWhere('id', 'reg_update_test');

            expect($updated['status'])->toBe('resolved');
            expect($updated['notes'])->toBe('Fixed by optimizing database queries');
        });

        it('returns false when updating non-existent regression', function () {
            $regressionService = app(PerformanceRegressionService::class);

            $result = $regressionService->updateRegressionStatus(
                'non_existent_id',
                PerformanceRegressionService::STATUS_RESOLVED
            );

            expect($result)->toBeFalse();
        });
    });

    describe('Regression Statistics', function () {
        it('returns correct statistics', function () {
            $regressionService = app(PerformanceRegressionService::class);

            // Add some regressions
            $mockRegressions = [
                [
                    'id' => 'reg_1',
                    'metric' => 'response_time',
                    'baseline' => 100.0,
                    'current' => 150.0,
                    'deviation_percent' => 50.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_DETECTED,
                ],
                [
                    'id' => 'reg_2',
                    'metric' => 'error_rate',
                    'baseline' => 1.0,
                    'current' => 2.0,
                    'deviation_percent' => 100.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_RESOLVED,
                ],
                [
                    'id' => 'reg_3',
                    'metric' => 'response_time',
                    'baseline' => 100.0,
                    'current' => 130.0,
                    'deviation_percent' => 30.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_INVESTIGATING,
                ],
            ];

            Cache::put('apm:regression:list', $mockRegressions, 86400);

            $stats = $regressionService->getRegressionStatistics();

            expect($stats)->toBeArray()
                ->toHaveKeys(['total', 'active', 'resolved', 'by_metric', 'avg_deviation']);

            expect($stats['total'])->toBe(3);
            expect($stats['active'])->toBe(2); // detected + investigating
            expect($stats['resolved'])->toBe(1);
            expect($stats['by_metric']['response_time'])->toBe(2);
            expect($stats['by_metric']['error_rate'])->toBe(1);
        });
    });

    describe('Report Generation', function () {
        it('generates comprehensive report', function () {
            $regressionService = app(PerformanceRegressionService::class);

            $report = $regressionService->generateReport();

            expect($report)->toBeArray()
                ->toHaveKeys([
                    'generated_at',
                    'period',
                    'summary',
                    'active_regressions',
                    'baselines',
                    'recommendations',
                ]);

            expect($report['recommendations'])->toBeArray()->not->toBeEmpty();
        });
    });

    describe('Data Clearing', function () {
        it('clears all regression data', function () {
            $regressionService = app(PerformanceRegressionService::class);

            // Add some data
            $mockRegressions = [
                [
                    'id' => 'reg_1',
                    'metric' => 'response_time',
                    'baseline' => 100.0,
                    'current' => 150.0,
                    'deviation_percent' => 50.0,
                    'detected_at' => now()->toIso8601String(),
                    'status' => PerformanceRegressionService::STATUS_DETECTED,
                ],
            ];

            Cache::put('apm:regression:list', $mockRegressions, 86400);
            Cache::put('apm:baseline:response_time', ['baseline' => 100], 86400);

            $regressionService->clearData();

            $regressions = $regressionService->getRegressions();

            expect($regressions)->toBeArray()->toBeEmpty();
        });
    });
});
