<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\ApmService;
use App\Services\PerformanceAlertingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * APM Dashboard Livewire Component
 *
 * Displays real-time performance metrics including request throughput,
 * error rates, response times, cache hit rates, and active alerts.
 *
 * @see \Requirements: NFR-O-01, NFR-O-04
 */
class ApmDashboard extends Component
{
    public int $refreshInterval = 30;

    public string $timeRange = '24h';

    /** @var array<string, mixed> */
    public array $healthScore = [];

    /** @var array<string, mixed> */
    public array $overviewMetrics = [];

    /** @var array<string, mixed> */
    public array $databaseMetrics = [];

    /** @var array<string, mixed> */
    public array $cacheMetrics = [];

    /** @var array<string, mixed> */
    public array $systemMetrics = [];

    /** @var array<string, mixed> */
    public array $alertStatistics = [];

    /** @var array<int, array<string, mixed>> */
    public array $recentAlerts = [];

    /** @var array<string, mixed> */
    public array $thresholds = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_admin === true, 403, 'Admin access required.');

        $refreshConfig = config('apm.dashboard.refresh_interval', 30);
        $this->refreshInterval = is_numeric($refreshConfig) ? (int) $refreshConfig : 30;
        $this->loadThresholds();
        $this->refreshMetrics();
    }

    public function refreshMetrics(): void
    {
        /** @var ApmService $apmService */
        $apmService = app(ApmService::class);

        /** @var PerformanceAlertingService $alertingService */
        $alertingService = app(PerformanceAlertingService::class);

        $this->healthScore = $apmService->calculateHealthScore();
        $this->overviewMetrics = $apmService->getOverviewMetrics();
        $this->cacheMetrics = $apmService->getCacheMetrics();

        // Transform getDatabaseMetrics() to match view's expected keys.
        $rawDatabaseMetrics = $apmService->getDatabaseMetrics();
        $this->databaseMetrics = array_merge($rawDatabaseMetrics, [
            'active_connections' => $rawDatabaseMetrics['connection_count'] ?? 0,
            'avg_query_time' => $rawDatabaseMetrics['avg_query_time_ms'] ?? 0.0,
        ]);

        // Transform getSystemMetrics() nested structure to flat keys expected by the view.
        $rawSystemMetrics = $apmService->getSystemMetrics();
        $this->systemMetrics = [
            'memory_usage_percent' => $rawSystemMetrics['memory']['usage_percent'] ?? 0.0,
            'memory_used' => number_format((float) ($rawSystemMetrics['memory']['current_mb'] ?? 0), 1).'MB',
            'php_version' => $rawSystemMetrics['php']['version'] ?? 'Unknown',
            'uptime_hours' => $this->overviewMetrics['uptime_hours'] ?? 0.0,
        ];

        $this->alertStatistics = $alertingService->getAlertStatistics();
        $this->recentAlerts = $alertingService->getAlerts(20);
    }

    public function acknowledgeAlert(string $alertId): void
    {
        abort_unless(auth()->user()?->is_admin === true, 403);

        /** @var PerformanceAlertingService $alertingService */
        $alertingService = app(PerformanceAlertingService::class);
        $alertingService->acknowledgeAlert($alertId);
        $this->refreshMetrics();
    }

    public function clearAlerts(): void
    {
        abort_unless(auth()->user()?->is_admin === true, 403);

        /** @var PerformanceAlertingService $alertingService */
        $alertingService = app(PerformanceAlertingService::class);
        $alertingService->clearAlerts();
        $this->refreshMetrics();
    }

    public function runAlertCheck(): void
    {
        abort_unless(auth()->user()?->is_admin === true, 403);

        /** @var PerformanceAlertingService $alertingService */
        $alertingService = app(PerformanceAlertingService::class);
        $alertingService->checkAlerts();
        $this->refreshMetrics();
    }

    public function setTimeRange(string $range): void
    {
        $validRanges = ['1h', '6h', '24h', '7d', '30d'];
        if (in_array($range, $validRanges, true)) {
            $this->timeRange = $range;
            $this->refreshMetrics();
        }
    }

    protected function loadThresholds(): void
    {
        /** @var array<string, array<string, mixed>> $configThresholds */
        $configThresholds = config('apm.alerting.thresholds', []);

        $this->thresholds = [
            'response_time_warning' => $this->getNumericConfig($configThresholds, 'response_time', 'warning', 1000),
            'response_time_critical' => $this->getNumericConfig($configThresholds, 'response_time', 'critical', 3000),
            'error_rate_warning' => $this->getNumericConfig($configThresholds, 'error_rate', 'warning', 5),
            'error_rate_critical' => $this->getNumericConfig($configThresholds, 'error_rate', 'critical', 10),
            'cache_hit_warning' => $this->getNumericConfig($configThresholds, 'cache_hit_rate', 'warning', 70),
            'cache_hit_critical' => $this->getNumericConfig($configThresholds, 'cache_hit_rate', 'critical', 50),
            'memory_warning' => $this->getNumericConfig($configThresholds, 'memory_usage', 'warning', 70),
            'memory_critical' => $this->getNumericConfig($configThresholds, 'memory_usage', 'critical', 90),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $thresholds
     */
    protected function getNumericConfig(array $thresholds, string $key, string $level, float $default): float
    {
        $group = is_array($thresholds[$key] ?? null) ? $thresholds[$key] : [];
        $value = $group[$level] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
    }

    #[Layout('components.admin-layout', ['title' => 'APM Dashboard'])]
    public function render(): View
    {
        return view('livewire.admin.apm-dashboard');
    }
}
