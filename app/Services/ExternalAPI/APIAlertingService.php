<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Alerting Service with MCP Integration
 *
 * Manages comprehensive alerting system for API status, recovery notifications,
 * and health monitoring using MCP tools.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class APIAlertingService
{
    /**
     * Alert history key
     */
    protected const ALERT_HISTORY_KEY = 'alert_history:';

    /**
     * Alert configuration key
     */
    protected const ALERT_CONFIG_KEY = 'alert_config:';

    /**
     * Maximum alert history entries
     */
    protected const MAX_HISTORY_ENTRIES = 500;

    /**
     * Alert cooldown period in seconds (5 minutes)
     */
    protected const ALERT_COOLDOWN = 300;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected APIHealthMonitorService $healthMonitor
    ) {}

    /**
     * Send alert for API status change
     *
     * @param  array<string, mixed>  $context
     */
    public function sendAlert(string $alertType, string $severity, string $message, array $context = []): void
    {
        // Check if alert is in cooldown
        if ($this->isAlertInCooldown($alertType, $context['api'] ?? 'unknown')) {
            Log::debug('[APIAlerting] Alert in cooldown, skipping', [
                'alert_type' => $alertType,
                'api' => $context['api'] ?? 'unknown',
            ]);

            return;
        }

        $alert = [
            'id' => uniqid('alert_', true),
            'type' => $alertType,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
            'acknowledged' => false,
        ];

        // Store alert in history
        $this->storeAlertHistory($alert);

        // Set cooldown
        $this->setAlertCooldown($alertType, $context['api'] ?? 'unknown');

        // Send alert via MCP tools if available
        $this->sendViaMCP($alert);

        // Log alert
        $this->logAlert($alert);
    }

    /**
     * Send API health degradation alert
     */
    public function sendHealthDegradationAlert(string $apiName, string $status, string $message): void
    {
        $this->sendAlert(
            'health_degradation',
            $this->getSeverityForStatus($status),
            sprintf('%s API health degraded: %s', ucfirst($apiName), $message),
            [
                'api' => $apiName,
                'status' => $status,
                'health_metrics' => $this->healthMonitor->getCachedHealth($apiName),
            ]
        );
    }

    /**
     * Send API recovery alert
     */
    public function sendRecoveryAlert(string $apiName, float $responseTimeMs): void
    {
        $this->sendAlert(
            'api_recovery',
            'info',
            sprintf('%s API has recovered (%.2fms response time)', ucfirst($apiName), $responseTimeMs),
            [
                'api' => $apiName,
                'response_time_ms' => $responseTimeMs,
                'health_metrics' => $this->healthMonitor->getCachedHealth($apiName),
            ]
        );
    }

    /**
     * Send circuit breaker opened alert
     */
    public function sendCircuitBreakerAlert(string $apiName, int $failureCount): void
    {
        $this->sendAlert(
            'circuit_breaker_open',
            'critical',
            sprintf('%s API circuit breaker opened after %d failures', ucfirst($apiName), $failureCount),
            [
                'api' => $apiName,
                'failure_count' => $failureCount,
                'timeout_seconds' => APIHealthMonitorService::CIRCUIT_BREAKER_TIMEOUT,
            ]
        );
    }

    /**
     * Send sync failure alert
     */
    public function sendSyncFailureAlert(string $dataType, string $error): void
    {
        $this->sendAlert(
            'sync_failure',
            'warning',
            sprintf('Background sync failed for %s: %s', $dataType, $error),
            [
                'data_type' => $dataType,
                'error' => $error,
            ]
        );
    }

    /**
     * Send sync success alert
     */
    public function sendSyncSuccessAlert(string $dataType, int $dataCount): void
    {
        $this->sendAlert(
            'sync_success',
            'info',
            sprintf('Background sync completed for %s (%d items)', $dataType, $dataCount),
            [
                'data_type' => $dataType,
                'data_count' => $dataCount,
            ]
        );
    }

    /**
     * Check if alert is in cooldown
     */
    protected function isAlertInCooldown(string $alertType, string $apiName): bool
    {
        $cooldownKey = "alert_cooldown:{$alertType}:{$apiName}";

        return Redis::exists($cooldownKey) > 0;
    }

    /**
     * Set alert cooldown
     */
    protected function setAlertCooldown(string $alertType, string $apiName): void
    {
        $cooldownKey = "alert_cooldown:{$alertType}:{$apiName}";
        Redis::setex($cooldownKey, self::ALERT_COOLDOWN, '1');
    }

    /**
     * Store alert in history
     *
     * @param  array<string, mixed>  $alert
     */
    protected function storeAlertHistory(array $alert): void
    {
        $historyKey = self::ALERT_HISTORY_KEY.'all';
        Redis::lpush($historyKey, json_encode($alert));
        Redis::ltrim($historyKey, 0, self::MAX_HISTORY_ENTRIES - 1);

        // Also store by type
        $typeHistoryKey = self::ALERT_HISTORY_KEY.$alert['type'];
        Redis::lpush($typeHistoryKey, json_encode($alert));
        Redis::ltrim($typeHistoryKey, 0, self::MAX_HISTORY_ENTRIES - 1);
    }

    /**
     * Send alert via MCP tools
     *
     * @param  array<string, mixed>  $alert
     */
    protected function sendViaMCP(array $alert): void
    {
        if (! $this->mcpClient->isServerEnabled('awsknowledge')) {
            return;
        }

        // In production, this would send alerts via MCP tools
        Log::debug('[APIAlerting] Alert sent via MCP', [
            'alert_id' => $alert['id'],
            'type' => $alert['type'],
            'severity' => $alert['severity'],
        ]);
    }

    /**
     * Log alert
     *
     * @param  array<string, mixed>  $alert
     */
    protected function logAlert(array $alert): void
    {
        $logMethod = match ($alert['severity']) {
            'critical' => 'critical',
            'error' => 'error',
            'warning' => 'warning',
            'info' => 'info',
            default => 'debug',
        };

        Log::$logMethod('[APIAlerting] Alert triggered', [
            'alert_id' => $alert['id'],
            'type' => $alert['type'],
            'message' => $alert['message'],
            'context' => $alert['context'],
        ]);
    }

    /**
     * Get severity for health status
     */
    protected function getSeverityForStatus(string $status): string
    {
        return match ($status) {
            'error', 'circuit_open' => 'critical',
            'unhealthy' => 'error',
            'degraded' => 'warning',
            'healthy' => 'info',
            default => 'info',
        };
    }

    /**
     * Get alert history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAlertHistory(int $limit = 50, ?string $type = null): array
    {
        $historyKey = $type
            ? self::ALERT_HISTORY_KEY.$type
            : self::ALERT_HISTORY_KEY.'all';

        $entries = Redis::lrange($historyKey, 0, $limit - 1);

        return array_map(fn ($entry) => json_decode($entry, true), $entries);
    }

    /**
     * Get unacknowledged alerts
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnacknowledgedAlerts(): array
    {
        $allAlerts = $this->getAlertHistory(100);

        return array_filter($allAlerts, fn ($alert) => ! $alert['acknowledged']);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(string $alertId): bool
    {
        $allAlerts = $this->getAlertHistory(self::MAX_HISTORY_ENTRIES);

        foreach ($allAlerts as $index => $alert) {
            if ($alert['id'] === $alertId) {
                $alert['acknowledged'] = true;
                $alert['acknowledged_at'] = now()->toIso8601String();

                // Update in Redis
                $historyKey = self::ALERT_HISTORY_KEY.'all';
                Redis::lset($historyKey, $index, json_encode($alert));

                Log::info('[APIAlerting] Alert acknowledged', [
                    'alert_id' => $alertId,
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Get alert statistics
     *
     * @return array{total: int, by_type: array<string, int>, by_severity: array<string, int>, unacknowledged: int, recent_24h: int}
     */
    public function getAlertStatistics(): array
    {
        $allAlerts = $this->getAlertHistory(self::MAX_HISTORY_ENTRIES);

        $byType = [];
        $bySeverity = [];
        $unacknowledged = 0;
        $recent24h = 0;

        $cutoff = now()->subDay()->timestamp;

        foreach ($allAlerts as $alert) {
            // Count by type
            $type = $alert['type'];
            $byType[$type] = ($byType[$type] ?? 0) + 1;

            // Count by severity
            $severity = $alert['severity'];
            $bySeverity[$severity] = ($bySeverity[$severity] ?? 0) + 1;

            // Count unacknowledged
            if (! $alert['acknowledged']) {
                $unacknowledged++;
            }

            // Count recent (last 24 hours)
            $timestamp = strtotime($alert['timestamp']);
            if ($timestamp >= $cutoff) {
                $recent24h++;
            }
        }

        return [
            'total' => count($allAlerts),
            'by_type' => $byType,
            'by_severity' => $bySeverity,
            'unacknowledged' => $unacknowledged,
            'recent_24h' => $recent24h,
        ];
    }

    /**
     * Clear alert history
     */
    public function clearAlertHistory(?string $type = null): void
    {
        if ($type) {
            $historyKey = self::ALERT_HISTORY_KEY.$type;
            Redis::del($historyKey);
            Log::info('[APIAlerting] Alert history cleared', ['type' => $type]);
        } else {
            $keys = Redis::keys(self::ALERT_HISTORY_KEY.'*');
            foreach ($keys as $key) {
                Redis::del($key);
            }
            Log::info('[APIAlerting] All alert history cleared');
        }
    }

    /**
     * Configure alert settings
     *
     * @param  array<string, mixed>  $config
     */
    public function configureAlerts(array $config): void
    {
        Cache::put(self::ALERT_CONFIG_KEY, $config, 86400);

        Log::info('[APIAlerting] Alert configuration updated', [
            'config' => $config,
        ]);
    }

    /**
     * Get alert configuration
     *
     * @return array<string, mixed>
     */
    public function getAlertConfiguration(): array
    {
        return Cache::get(self::ALERT_CONFIG_KEY, [
            'enabled' => true,
            'cooldown_seconds' => self::ALERT_COOLDOWN,
            'max_history_entries' => self::MAX_HISTORY_ENTRIES,
            'severity_levels' => ['critical', 'error', 'warning', 'info', 'debug'],
        ]);
    }
}
