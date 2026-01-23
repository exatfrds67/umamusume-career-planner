<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Performance Alerting Service
 *
 * Provides automated performance alerting capabilities including:
 * - Configurable threshold-based alerts
 * - Multiple notification channels (log, database, etc.)
 * - Alert cooldown to prevent spam
 * - Alert acknowledgment and management
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
class PerformanceAlertingService
{
    /**
     * Alert storage prefix
     */
    protected const ALERT_PREFIX = 'apm:alerts:';

    /**
     * Cooldown tracking prefix
     */
    protected const COOLDOWN_PREFIX = 'apm:cooldown:';

    /**
     * Alert severity levels
     */
    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Create a new PerformanceAlertingService instance.
     */
    public function __construct(
        protected readonly ApmService $apmService
    ) {}

    /**
     * Check all alert conditions and trigger alerts as needed.
     *
     * @return array{checked: int, triggered: int, alerts: array<int, array<string, mixed>>}
     */
    public function checkAlerts(): array
        if (! config('apm.alerting.enabled', true)) {
            return ['checked' => 0, 'triggered' => 0, 'alerts' => []];
        }

        $checked = 0;
        $triggered = 0;
        $alerts = [];

        // Check response time
        $checked = ($checked ?? 0) + 1;
        $responseTimeAlert = $this->checkResponseTimeAlert();
        if ($responseTimeAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $responseTimeAlert;
        }

        // Check error rate
        $checked = ($checked ?? 0) + 1;
        $errorRateAlert = $this->checkErrorRateAlert();
        if ($errorRateAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $errorRateAlert;
        }

        // Check memory usage
        $checked = ($checked ?? 0) + 1;
        $memoryAlert = $this->checkMemoryAlert();
        if ($memoryAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $memoryAlert;
        }

        // Check cache hit rate
        $checked = ($checked ?? 0) + 1;
        $cacheAlert = $this->checkCacheHitRateAlert();
        if ($cacheAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $cacheAlert;
        }

        // Check slow queries
        $checked = ($checked ?? 0) + 1;
        $slowQueryAlert = $this->checkSlowQueryAlert();
        if ($slowQueryAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $slowQueryAlert;
        }

        // Check database connections
        $checked = ($checked ?? 0) + 1;
        $dbConnAlert = $this->checkDatabaseConnectionAlert();
        if ($dbConnAlert !== null) {
            $triggered = ($triggered ?? 0) + 1;
            $alerts[] = $dbConnAlert;
        }

        return [
            'checked' => $checked,
            'triggered' => $triggered,
            'alerts' => $alerts,
        ];
    }

    /**
     * Create and dispatch an alert.
     *
     * @param  array<string, mixed>  $context
     * @return array{id: string, type: string, severity: string, message: string, context: array<string, mixed>, timestamp: string, acknowledged: bool}|null
     */
    public function createAlert(
        string $type,
        string $severity,
        string $message,
        array $context = []
    ): ?array {
        // Check cooldown
        if ($this->isInCooldown($type)) {
            return null;
        }

        // Check max alerts per hour
        if ($this->hasExceededMaxAlerts()) {
            Log::warning('[PerformanceAlerting] Max alerts per hour exceeded, skipping alert', [
                'type' => $type,
                'message' => $message,
            ]);

            return null;
        }

        $alert = [
            'id' => uniqid('alert_', true),
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
            'acknowledged' => false,
        ];

        // Store alert
        $this->storeAlert($alert);

        // Set cooldown
        $this->setCooldown($type);

        // Dispatch to notification channels
        $this->dispatchAlert($alert);

        return $alert;
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledgeAlert(string $alertId): bool
    {
        $alerts = $this->getAlerts();

        foreach ($alerts as &$alert) {
            if ($alert['id'] === $alertId) {
                $alert['acknowledged'] = true;
                $alert['acknowledged_at'] = now()->toIso8601String();
                $this->saveAlerts($alerts);

                return true;
            }
        }

        return false;
    }

    /**
     * Get all alerts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAlerts(): array
        $alerts = Cache::get(self::ALERT_PREFIX.'list', []);

        // Sort by timestamp descending
        usort($alerts, fn ($a, $b) => strtotime($b['timestamp']) <=> strtotime($a['timestamp']));

        return \array_slice($alerts, 0, $limit);
    }

    /**
     * Get unacknowledged alerts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnacknowledgedAlerts(): array
        $alerts = $this->getAlerts();

        return array_values(array_filter($alerts, fn ($alert) => ! $alert['acknowledged']));
    }

    /**
     * Get alerts by severity.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAlertsBySeverity(): array
        $alerts = $this->getAlerts();

        return array_values(array_filter($alerts, fn ($alert) => $alert['severity'] === $severity));
    }

    /**
     * Get alert statistics.
     *
     * @return array{total: int, unacknowledged: int, by_severity: array<string, int>, by_type: array<string, int>}
     */
    public function getAlertStatistics(): array
        $alerts = $this->getAlerts();

        $bySeverity = [
            self::SEVERITY_INFO => 0,
            self::SEVERITY_WARNING => 0,
            self::SEVERITY_CRITICAL => 0,
        ];

        $byType = [];
        $unacknowledged = 0;

        foreach ($alerts as $alert) {
            $bySeverity[$alert['severity']] = ($bySeverity[$alert['severity']] ?? 0) + 1;
            $byType[$alert['type']] = ($byType[$alert['type']] ?? 0) + 1;

            if (! $alert['acknowledged']) {
                $unacknowledged = ($unacknowledged ?? 0) + 1;
            }
        }

        return [
            'total' => \count($alerts),
            'unacknowledged' => $unacknowledged,
            'by_severity' => $bySeverity,
            'by_type' => $byType,
        ];
    }

    /**
     * Clear all alerts.
     */
    public function clearAlerts(): void
    {
        Cache::forget(self::ALERT_PREFIX.'list');
        Cache::forget(self::ALERT_PREFIX.'count_hour');

        Log::info('[PerformanceAlerting] All alerts cleared');
    }

    /**
     * Check response time alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkResponseTimeAlert(): ?array
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $responseTime = $healthScore['components']['response_time'] ?? null;

        if ($responseTime === null) {
            return null;
        }

        $value = $responseTime['value'];
        $warningThreshold = (float) config('apm.alerting.thresholds.response_time.warning', 1000);
        $criticalThreshold = (float) config('apm.alerting.thresholds.response_time.critical', 3000);

        if ($value >= $criticalThreshold) {
            return $this->createAlert(
                'response_time',
                self::SEVERITY_CRITICAL,
                \sprintf('Critical: Average response time (%.2fms) exceeds critical threshold (%.2fms)', $value, $criticalThreshold),
                ['value' => $value, 'threshold' => $criticalThreshold]
            );
        }

        if ($value >= $warningThreshold) {
            return $this->createAlert(
                'response_time',
                self::SEVERITY_WARNING,
                \sprintf('Warning: Average response time (%.2fms) exceeds warning threshold (%.2fms)', $value, $warningThreshold),
                ['value' => $value, 'threshold' => $warningThreshold]
            );
        }

        return null;
    }

    /**
     * Check error rate alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkErrorRateAlert(): ?array
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $errorRate = $healthScore['components']['error_rate'] ?? null;

        if ($errorRate === null) {
            return null;
        }

        $value = $errorRate['value'];
        $warningThreshold = (float) config('apm.alerting.thresholds.error_rate.warning', 5);
        $criticalThreshold = (float) config('apm.alerting.thresholds.error_rate.critical', 10);

        if ($value >= $criticalThreshold) {
            return $this->createAlert(
                'error_rate',
                self::SEVERITY_CRITICAL,
                \sprintf('Critical: Error rate (%.2f%%) exceeds critical threshold (%.2f%%)', $value, $criticalThreshold),
                ['value' => $value, 'threshold' => $criticalThreshold]
            );
        }

        if ($value >= $warningThreshold) {
            return $this->createAlert(
                'error_rate',
                self::SEVERITY_WARNING,
                \sprintf('Warning: Error rate (%.2f%%) exceeds warning threshold (%.2f%%)', $value, $warningThreshold),
                ['value' => $value, 'threshold' => $warningThreshold]
            );
        }

        return null;
    }

    /**
     * Check memory usage alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkMemoryAlert(): ?array
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $memory = $healthScore['components']['memory_usage'] ?? null;

        if ($memory === null) {
            return null;
        }

        $value = $memory['value'];
        $warningThreshold = (float) config('apm.alerting.thresholds.memory_usage.warning', 70);
        $criticalThreshold = (float) config('apm.alerting.thresholds.memory_usage.critical', 90);

        if ($value >= $criticalThreshold) {
            return $this->createAlert(
                'memory_usage',
                self::SEVERITY_CRITICAL,
                \sprintf('Critical: Memory usage (%.2f%%) exceeds critical threshold (%.2f%%)', $value, $criticalThreshold),
                ['value' => $value, 'threshold' => $criticalThreshold]
            );
        }

        if ($value >= $warningThreshold) {
            return $this->createAlert(
                'memory_usage',
                self::SEVERITY_WARNING,
                \sprintf('Warning: Memory usage (%.2f%%) exceeds warning threshold (%.2f%%)', $value, $warningThreshold),
                ['value' => $value, 'threshold' => $warningThreshold]
            );
        }

        return null;
    }

    /**
     * Check cache hit rate alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkCacheHitRateAlert(): ?array
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $cache = $healthScore['components']['cache_hit_rate'] ?? null;

        if ($cache === null) {
            return null;
        }

        $value = $cache['value'];
        $warningThreshold = (float) config('apm.alerting.thresholds.cache_hit_rate.warning', 70);
        $criticalThreshold = (float) config('apm.alerting.thresholds.cache_hit_rate.critical', 50);

        // For cache hit rate, we alert when BELOW threshold
        if ($value <= $criticalThreshold) {
            return $this->createAlert(
                'cache_hit_rate',
                self::SEVERITY_CRITICAL,
                \sprintf('Critical: Cache hit rate (%.2f%%) below critical threshold (%.2f%%)', $value, $criticalThreshold),
                ['value' => $value, 'threshold' => $criticalThreshold]
            );
        }

        if ($value <= $warningThreshold) {
            return $this->createAlert(
                'cache_hit_rate',
                self::SEVERITY_WARNING,
                \sprintf('Warning: Cache hit rate (%.2f%%) below warning threshold (%.2f%%)', $value, $warningThreshold),
                ['value' => $value, 'threshold' => $warningThreshold]
            );
        }

        return null;
    }

    /**
     * Check slow query alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkSlowQueryAlert(): ?array
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $database = $healthScore['components']['database_health'] ?? null;

        if ($database === null) {
            return null;
        }

        $value = $database['value'];
        $warningThreshold = (int) config('apm.alerting.thresholds.slow_queries.warning', 10);
        $criticalThreshold = (int) config('apm.alerting.thresholds.slow_queries.critical', 25);

        if ($value >= $criticalThreshold) {
            return $this->createAlert(
                'slow_queries',
                self::SEVERITY_CRITICAL,
                \sprintf('Critical: Slow query count (%d) exceeds critical threshold (%d)', $value, $criticalThreshold),
                ['value' => $value, 'threshold' => $criticalThreshold]
            );
        }

        if ($value >= $warningThreshold) {
            return $this->createAlert(
                'slow_queries',
                self::SEVERITY_WARNING,
                \sprintf('Warning: Slow query count (%d) exceeds warning threshold (%d)', $value, $warningThreshold),
                ['value' => $value, 'threshold' => $warningThreshold]
            );
        }

        return null;
    }

    /**
     * Check database connection alert condition.
     *
     * @return array<string, mixed>|null
     */
    protected function checkDatabaseConnectionAlert(): ?array
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            $current = isset($result[0]) ? (isset($result[0]) && is_numeric($result[0]->Value) ? (int) $result[0]->Value : 0) : 0;

            $maxResult = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $max = isset($maxResult[0]) ? (int) $maxResult[0]->Value : 100;

            $usagePercent = ($current / $max) * 100;

            $warningThreshold = (float) config('apm.alerting.thresholds.database_connections.warning', 70);
            $criticalThreshold = (float) config('apm.alerting.thresholds.database_connections.critical', 90);

            if ($usagePercent >= $criticalThreshold) {
                return $this->createAlert(
                    'database_connections',
                    self::SEVERITY_CRITICAL,
                    \sprintf('Critical: Database connection usage (%.2f%%) exceeds critical threshold (%.2f%%)', $usagePercent, $criticalThreshold),
                    ['current' => $current, 'max' => $max, 'usage_percent' => $usagePercent]
                );
            }

            if ($usagePercent >= $warningThreshold) {
                return $this->createAlert(
                    'database_connections',
                    self::SEVERITY_WARNING,
                    \sprintf('Warning: Database connection usage (%.2f%%) exceeds warning threshold (%.2f%%)', $usagePercent, $warningThreshold),
                    ['current' => $current, 'max' => $max, 'usage_percent' => $usagePercent]
                );
            }
        } catch (\Exception $e) {
            Log::warning('[PerformanceAlerting] Failed to check database connections', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Store an alert.
     *
     * @param  array<string, mixed>  $alert
     */
    protected function storeAlert(array $alert): void
    {
        $alerts = Cache::get(self::ALERT_PREFIX.'list', []);
        $alerts[] = $alert;

        // Keep last 1000 alerts
        if (\count($alerts) > 1000) {
            $alerts = \array_slice($alerts, -1000);
        }

        Cache::put(self::ALERT_PREFIX.'list', $alerts, 604800); // 7 days

        // Increment hourly counter
        $hourKey = self::ALERT_PREFIX.'count_hour';
        Cache::increment($hourKey);
        Cache::put($hourKey, Cache::get($hourKey, 0), 3600);

        // Also store in APM alerts cache for dashboard
        Cache::put('apm:alerts', $alerts, 604800);
    }

    /**
     * Save alerts to cache.
     *
     * @param  array<int, array<string, mixed>>  $alerts
     */
    protected function saveAlerts(array $alerts): void
    {
        Cache::put(self::ALERT_PREFIX.'list', $alerts, 604800);
        Cache::put('apm:alerts', $alerts, 604800);
    }

    /**
     * Dispatch alert to notification channels.
     *
     * @param  array<string, mixed>  $alert
     */
    protected function dispatchAlert(array $alert): void
    {
        $channels = config('apm.alerting.channels', []);

        // Log channel
        if ($channels['log']['enabled'] ?? true) {
            $level = $channels['log']['level'] ?? 'warning';
            Log::$level('[PerformanceAlert] '.$alert['message'], [
                'alert_id' => $alert['id'],
                'type' => $alert['type'],
                'severity' => $alert['severity'],
                'context' => $alert['context'],
            ]);
        }

        // Database channel
        if ($channels['database']['enabled'] ?? true) {
            try {
                DB::table($channels['database']['table'] ?? 'ucp_system_logs')->insert([
                    'log_type' => 'performance_alert',
                    'log_level' => $alert['severity'],
                    'message' => $alert['message'],
                    'context' => json_encode($alert['context']),
                    'source' => 'PerformanceAlertingService',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('[PerformanceAlerting] Failed to store alert in database', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if alert type is in cooldown.
     */
    protected function isInCooldown(string $type): bool
    {
        return Cache::has(self::COOLDOWN_PREFIX.$type);
    }

    /**
     * Set cooldown for alert type.
     */
    protected function setCooldown(string $type): void
    {
        $cooldown = (int) config('apm.alerting.cooldown', 300);
        Cache::put(self::COOLDOWN_PREFIX.$type, true, $cooldown);
    }

    /**
     * Check if max alerts per hour has been exceeded.
     */
    protected function hasExceededMaxAlerts(): bool
    {
        $maxAlerts = (int) config('apm.alerting.max_alerts_per_hour', 20);
        $currentCount = (int) Cache::get(self::ALERT_PREFIX.'count_hour', 0);

        return $currentCount >= $maxAlerts;
    }
}
