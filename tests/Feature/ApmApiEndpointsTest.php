<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * APM API Endpoints Tests
 *
 * Tests for the APM API endpoints including:
 * - Dashboard endpoints
 * - Alert endpoints
 * - Regression endpoints
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
describe('APM API Endpoints', function () {
    beforeEach(function () {
        Cache::flush();
        $this->user = User::factory()->create();
    });

    describe('APM Dashboard Endpoints', function () {
        it('returns dashboard data for authenticated user', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/apm/dashboard');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'health_score',
                        'overview',
                        'database',
                        'cache',
                        'api',
                        'system',
                        'trends',
                        'alerts',
                        'regressions',
                    ],
                    'timestamp',
                ]);
        });

        it('returns health score', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/apm/health-score');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'score',
                        'status',
                        'components',
                    ],
                    'timestamp',
                ]);
        });

        it('returns overview metrics', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/apm/overview');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_requests',
                        'avg_response_time_ms',
                        'error_rate',
                        'cache_hit_rate',
                        'active_connections',
                        'memory_usage_percent',
                        'uptime_hours',
                    ],
                    'timestamp',
                ]);
        });

        it('returns performance trends', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/apm/trends');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'hourly',
                        'daily',
                    ],
                    'timestamp',
                ]);
        });

        it('returns aggregated metrics for specific metric type', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/apm/metrics/response_time?hours=24');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'metric',
                        'period_hours',
                        'statistics',
                    ],
                    'timestamp',
                ]);
        });

        it('clears APM data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/apm/clear');

            $response->assertSuccessful()
                ->assertJson([
                    'success' => true,
                    'message' => 'APM data cleared successfully',
                ]);
        });

        it('requires authentication for dashboard', function () {
            $response = $this->getJson('/api/performance/apm/dashboard');

            $response->assertUnauthorized();
        });
    });

    describe('Alert Endpoints', function () {
        it('checks alert conditions', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/alerts/check');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'checked',
                        'triggered',
                        'alerts',
                    ],
                    'timestamp',
                ]);
        });

        it('lists all alerts', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/alerts');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'alerts',
                        'count',
                    ],
                    'timestamp',
                ]);
        });

        it('lists unacknowledged alerts', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/alerts/unacknowledged');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'alerts',
                        'count',
                    ],
                    'timestamp',
                ]);
        });

        it('returns alert statistics', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/alerts/statistics');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'unacknowledged',
                        'by_severity',
                        'by_type',
                    ],
                    'timestamp',
                ]);
        });

        it('acknowledges an alert', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/alerts/test_alert_id/acknowledge');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'message',
                    'timestamp',
                ]);
        });

        it('clears all alerts', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/alerts/clear');

            $response->assertSuccessful()
                ->assertJson([
                    'success' => true,
                    'message' => 'All alerts cleared successfully',
                ]);
        });
    });

    describe('Regression Endpoints', function () {
        it('checks for regressions', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/regressions/check');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'checked',
                        'detected',
                        'regressions',
                    ],
                    'timestamp',
                ]);
        });

        it('lists all regressions', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/regressions');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'regressions',
                        'count',
                    ],
                    'timestamp',
                ]);
        });

        it('lists active regressions', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/regressions/active');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'regressions',
                        'count',
                    ],
                    'timestamp',
                ]);
        });

        it('returns regression statistics', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/regressions/statistics');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'active',
                        'resolved',
                        'by_metric',
                        'avg_deviation',
                    ],
                    'timestamp',
                ]);
        });

        it('gets baseline for a metric', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/regressions/baseline/response_time');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data',
                    'timestamp',
                ]);
        });

        it('calculates baseline for a metric', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/regressions/baseline/response_time');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message',
                    'timestamp',
                ]);
        });

        it('updates regression status', function () {
            $response = $this->actingAs($this->user)
                ->patchJson('/api/performance/regressions/test_regression_id', [
                    'status' => 'resolved',
                    'notes' => 'Fixed the issue',
                ]);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'message',
                    'timestamp',
                ]);
        });

        it('validates regression status update', function () {
            $response = $this->actingAs($this->user)
                ->patchJson('/api/performance/regressions/test_regression_id', [
                    'status' => 'invalid_status',
                ]);

            $response->assertStatus(422);
        });

        it('generates regression report', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/regressions/report');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'generated_at',
                        'period',
                        'summary',
                        'active_regressions',
                        'baselines',
                        'recommendations',
                    ],
                    'timestamp',
                ]);
        });

        it('clears all regression data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/regressions/clear');

            $response->assertSuccessful()
                ->assertJson([
                    'success' => true,
                    'message' => 'All regression data cleared successfully',
                ]);
        });
    });
});
