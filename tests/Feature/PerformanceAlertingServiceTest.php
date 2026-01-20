<?php

declare(strict_types=1);

use App\Services\PerformanceAlertingService;
use Illuminate\Support\Facades\Cache;

/**
 * Performance Alerting Service Tests
 *
 * Tests for the performance alerting service including:
 * - Alert creation and management
 * - Alert acknowledgment
 * - Alert statistics
 * - Cooldown functionality
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
describe('Performance Alerting Service', function () {
    beforeEach(function () {
        // Clear any cached alert data
        Cache::flush();
    });

    describe('Alert Creation', function () {
        it('creates alerts with required fields', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alert = $alertingService->createAlert(
                'test_type',
                PerformanceAlertingService::SEVERITY_WARNING,
                'Test alert message',
                ['key' => 'value']
            );

            expect($alert)->toBeArray()
                ->toHaveKeys([
                    'id',
                    'type',
                    'severity',
                    'message',
                    'context',
                    'timestamp',
                    'acknowledged',
                ]);

            expect($alert['type'])->toBe('test_type');
            expect($alert['severity'])->toBe('warning');
            expect($alert['message'])->toBe('Test alert message');
            expect($alert['acknowledged'])->toBeFalse();
        });

        it('respects cooldown period for same alert type', function () {
            $alertingService = app(PerformanceAlertingService::class);

            // First alert should be created
            $alert1 = $alertingService->createAlert(
                'cooldown_test',
                PerformanceAlertingService::SEVERITY_WARNING,
                'First alert'
            );

            // Second alert of same type should be blocked by cooldown
            $alert2 = $alertingService->createAlert(
                'cooldown_test',
                PerformanceAlertingService::SEVERITY_WARNING,
                'Second alert'
            );

            expect($alert1)->not->toBeNull();
            expect($alert2)->toBeNull();
        });

        it('allows different alert types during cooldown', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alert1 = $alertingService->createAlert(
                'type_a',
                PerformanceAlertingService::SEVERITY_WARNING,
                'Alert A'
            );

            $alert2 = $alertingService->createAlert(
                'type_b',
                PerformanceAlertingService::SEVERITY_WARNING,
                'Alert B'
            );

            expect($alert1)->not->toBeNull();
            expect($alert2)->not->toBeNull();
        });
    });

    describe('Alert Retrieval', function () {
        it('retrieves all alerts', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alertingService->createAlert('type_1', PerformanceAlertingService::SEVERITY_INFO, 'Alert 1');
            $alertingService->createAlert('type_2', PerformanceAlertingService::SEVERITY_WARNING, 'Alert 2');
            $alertingService->createAlert('type_3', PerformanceAlertingService::SEVERITY_CRITICAL, 'Alert 3');

            $alerts = $alertingService->getAlerts();

            expect($alerts)->toBeArray()->toHaveCount(3);
        });

        it('retrieves unacknowledged alerts only', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alert1 = $alertingService->createAlert('type_1', PerformanceAlertingService::SEVERITY_INFO, 'Alert 1');
            $alertingService->createAlert('type_2', PerformanceAlertingService::SEVERITY_WARNING, 'Alert 2');

            // Acknowledge first alert
            $alertingService->acknowledgeAlert($alert1['id']);

            $unacknowledged = $alertingService->getUnacknowledgedAlerts();

            expect($unacknowledged)->toBeArray()->toHaveCount(1);
            expect($unacknowledged[0]['type'])->toBe('type_2');
        });

        it('retrieves alerts by severity', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alertingService->createAlert('type_1', PerformanceAlertingService::SEVERITY_INFO, 'Info alert');
            $alertingService->createAlert('type_2', PerformanceAlertingService::SEVERITY_WARNING, 'Warning alert');
            $alertingService->createAlert('type_3', PerformanceAlertingService::SEVERITY_CRITICAL, 'Critical alert');

            $criticalAlerts = $alertingService->getAlertsBySeverity(PerformanceAlertingService::SEVERITY_CRITICAL);

            expect($criticalAlerts)->toBeArray()->toHaveCount(1);
            expect($criticalAlerts[0]['severity'])->toBe('critical');
        });
    });

    describe('Alert Acknowledgment', function () {
        it('acknowledges an alert successfully', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alert = $alertingService->createAlert(
                'ack_test',
                PerformanceAlertingService::SEVERITY_WARNING,
                'Test alert'
            );

            $result = $alertingService->acknowledgeAlert($alert['id']);

            expect($result)->toBeTrue();

            $alerts = $alertingService->getAlerts();
            $acknowledgedAlert = collect($alerts)->firstWhere('id', $alert['id']);

            expect($acknowledgedAlert['acknowledged'])->toBeTrue();
        });

        it('returns false for non-existent alert', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $result = $alertingService->acknowledgeAlert('non_existent_id');

            expect($result)->toBeFalse();
        });
    });

    describe('Alert Statistics', function () {
        it('returns correct statistics', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alertingService->createAlert('type_1', PerformanceAlertingService::SEVERITY_INFO, 'Info');
            $alertingService->createAlert('type_2', PerformanceAlertingService::SEVERITY_WARNING, 'Warning');
            $alert3 = $alertingService->createAlert('type_3', PerformanceAlertingService::SEVERITY_CRITICAL, 'Critical');

            // Acknowledge one alert
            $alertingService->acknowledgeAlert($alert3['id']);

            $stats = $alertingService->getAlertStatistics();

            expect($stats)->toBeArray()
                ->toHaveKeys(['total', 'unacknowledged', 'by_severity', 'by_type']);

            expect($stats['total'])->toBe(3);
            expect($stats['unacknowledged'])->toBe(2);
            expect($stats['by_severity']['info'])->toBe(1);
            expect($stats['by_severity']['warning'])->toBe(1);
            expect($stats['by_severity']['critical'])->toBe(1);
        });
    });

    describe('Alert Checking', function () {
        it('checks all alert conditions', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $result = $alertingService->checkAlerts();

            expect($result)->toBeArray()
                ->toHaveKeys(['checked', 'triggered', 'alerts']);

            expect($result['checked'])->toBeInt()->toBeGreaterThan(0);
            expect($result['triggered'])->toBeInt()->toBeGreaterThanOrEqual(0);
            expect($result['alerts'])->toBeArray();
        });
    });

    describe('Alert Clearing', function () {
        it('clears all alerts', function () {
            $alertingService = app(PerformanceAlertingService::class);

            $alertingService->createAlert('type_1', PerformanceAlertingService::SEVERITY_INFO, 'Alert 1');
            $alertingService->createAlert('type_2', PerformanceAlertingService::SEVERITY_WARNING, 'Alert 2');

            $alertingService->clearAlerts();

            $alerts = $alertingService->getAlerts();

            expect($alerts)->toBeArray()->toBeEmpty();
        });
    });
});
