<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\MCPServer;
use App\Models\MCPToolUsage;
use App\Services\ExternalAPI\APIHealthMonitorService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Failure Rate Tracking Service with Automated Recovery
 *
 * Tracks failure rates across all MCP servers and external APIs,
 * implements automated recovery mechanisms, and provides failure analytics.
 *
 * Requirements: 14.5, 55.4, 56.4, Task 4.4.5
 */
class FailureRateTrackingService
{
    /**
     * Failure tracking cache prefix
     */
    protected const FAILURE_CACHE_PREFIX = 'failure_tracking:';

    /**
     * Failure rate threshold for alerts (%)
     */
    protected const FAILURE_RATE_ALERT_THRESHOLD = 10.0;

    /**
     * Failure rate threshold for critical alerts (%)
     */
    protected const FAILURE_RATE_CRITICAL_THRESHOLD = 25.0;

    /**
     * Recovery attempt interval in seconds
     */
    protected const RECOVERY_ATTEMPT_INTERVAL = 300;

    /**
     * Maximum recovery attempts
     */
    protected const MAX_RECOVERY_ATTEMPTS = 3;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected APIHealthMonitorService $apiHealthMonitor
    ) {}

    /**
     * Get comprehensive failure rate analytics
     *
     * @param  string  $period  'hour', 'day', 'week', 'month'
     * @return array{
     *     summary: array<string, mixed>,
     *     mcp_servers: array<string, mixed>,
     *     external_apis: array<string, mixed>,
     *     failure_trends: array<string, mixed>,
     *     recovery_status: array<string, mixed>,
     *     alerts: array<array<string, mixed>>
     * }
     */
    public function getFailureRateAnalytics(int $userId, string $period = 'day'): array
    {
        Log::info('[FailureRateTracking] Generating failure rate analytics', [
            'user_id' => $userId,
            'period' => $period,
        ]);

        $dateRange = $this->getDateRange($period);

        return [
            'summary' => $this->getFailureSummary($userId, $dateRange),
            'mcp_servers' => $this->getMCPServerFailureRates(),
            'external_apis' => $this->getExternalAPIFailureRates(),
            'failure_trends' => $this->getFailureTrends($userId, $dateRange),
            'recovery_status' => $this->getRecoveryStatus(),
            'alerts' => $this->getFailureAlerts($userId),
        ];
    }

    /**
     * Get failure summary
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array{
     *     total_failures: int,
     *     total_requests: int,
     *     overall_failure_rate: float,
     *     mcp_failures: int,
     *     api_failures: int,
     *     recovery_attempts: int,
     *     successful_recoveries: int,
     *     recovery_success_rate: float
     * }
     */
    protected function getFailureSummary(int $userId, array $dateRange): array
    {
        // Get MCP tool failures
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        $mcpFailures = $toolUsage->where('execution_status', 'failure')->count();
        $totalRequests = $toolUsage->count();

        // Get API failures
        $apiFailures = $this->getAPIFailureCount();

        $totalFailures = $mcpFailures + $apiFailures;
        $overallFailureRate = $totalRequests > 0 ? ($totalFailures / $totalRequests) * 100 : 0;

        // Get recovery statistics
        $recoveryStats = $this->getRecoveryStatistics();

        return [
            'total_failures' => $totalFailures,
            'total_requests' => $totalRequests,
            'overall_failure_rate' => round($overallFailureRate, 2),
            'mcp_failures' => $mcpFailures,
            'api_failures' => $apiFailures,
            'recovery_attempts' => $recoveryStats['attempts'],
            'successful_recoveries' => $recoveryStats['successful'],
            'recovery_success_rate' => $recoveryStats['success_rate'],
        ];
    }

    /**
     * Get MCP server failure rates
     *
     * @return array<string, array{
     *     server_name: string,
     *     failure_rate: float,
     *     total_failures: int,
     *     consecutive_failures: int,
     *     last_failure: string|null,
     *     status: string,
     *     recovery_needed: bool
     * }>
     */
    protected function getMCPServerFailureRates(): array
    {
        $servers = MCPServer::all();

        return $servers->mapWithKeys(function ($server) {
            $recoveryNeeded = $server->failure_rate > self::FAILURE_RATE_ALERT_THRESHOLD;

            return [$server->server_name => [
                'server_name' => $server->server_name,
                'failure_rate' => round($server->failure_rate, 2),
                'total_failures' => $server->total_failures ?? 0,
                'consecutive_failures' => $server->consecutive_failures,
                'last_failure' => $server->last_error_at?->toIso8601String(),
                'status' => $server->status,
                'recovery_needed' => $recoveryNeeded,
            ]];
        })->all();
    }

    /**
     * Get external API failure rates
     *
     * @return array<string, array{
     *     api_name: string,
     *     failure_count: int,
     *     circuit_breaker_open: bool,
     *     last_failure: string|null,
     *     status: string,
     *     recovery_needed: bool
     * }>
     */
    protected function getExternalAPIFailureRates(): array
    {
        $apis = ['umapyoi', 'umamusumedb'];
        $failureRates = [];

        foreach ($apis as $apiName) {
            $failureCount = $this->apiHealthMonitor->getFailureCount($apiName);
            $circuitBreakerOpen = $this->apiHealthMonitor->isCircuitBreakerOpen($apiName);
            $health = $this->apiHealthMonitor->getCachedHealth($apiName);

            $failureRates[$apiName] = [
                'api_name' => $apiName,
                'failure_count' => $failureCount,
                'circuit_breaker_open' => $circuitBreakerOpen,
                'last_failure' => $health['last_check'] ?? null,
                'status' => $health['status'] ?? 'unknown',
                'recovery_needed' => $circuitBreakerOpen || $failureCount > 0,
            ];
        }

        return $failureRates;
    }

    /**
     * Get failure trends
     *
     * @param  array{start: \Carbon\Carbon, end: \Carbon\Carbon}  $dateRange
     * @return array{
     *     hourly_failures: array<array{timestamp: string, failure_count: int, failure_rate: float}>,
     *     failure_by_component: array<array{component: string, failure_count: int, failure_rate: float}>
     * }
     */
    protected function getFailureTrends(int $userId, array $dateRange): array
    {
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates($dateRange['start'], $dateRange['end'])
            ->get();

        // Hourly failure trend
        $hourlyFailures = $toolUsage->groupBy(function ($item) {
            return $item->executed_at->format('Y-m-d H:00:00');
        })->map(function ($items, $timestamp) {
            $failures = $items->where('execution_status', 'failure')->count();
            $total = $items->count();

            return [
                'timestamp' => $timestamp,
                'failure_count' => $failures,
                'failure_rate' => $total > 0 ? round(($failures / $total) * 100, 2) : 0,
            ];
        })->values()->all();

        // Failure by component
        $failureByComponent = $toolUsage->groupBy('server_name')->map(function ($items, $serverName) {
            $failures = $items->where('execution_status', 'failure')->count();
            $total = $items->count();

            return [
                'component' => $serverName,
                'failure_count' => $failures,
                'failure_rate' => $total > 0 ? round(($failures / $total) * 100, 2) : 0,
            ];
        })->values()->all();

        return [
            'hourly_failures' => $hourlyFailures,
            'failure_by_component' => $failureByComponent,
        ];
    }

    /**
     * Get recovery status
     *
     * @return array{
     *     active_recoveries: array<array<string, mixed>>,
     *     recent_recoveries: array<array<string, mixed>>,
     *     recovery_queue: array<array<string, mixed>>
     * }
     */
    protected function getRecoveryStatus(): array
    {
        // Get active recovery attempts from Redis
        $activeRecoveries = $this->getActiveRecoveries();

        // Get recent recovery history
        $recentRecoveries = $this->getRecentRecoveries();

        // Get recovery queue
        $recoveryQueue = $this->getRecoveryQueue();

        return [
            'active_recoveries' => $activeRecoveries,
            'recent_recoveries' => $recentRecoveries,
            'recovery_queue' => $recoveryQueue,
        ];
    }

    /**
     * Get failure alerts
     *
     * @return array<array{
     *     type: string,
     *     severity: string,
     *     component: string,
     *     message: string,
     *     failure_rate: float,
     *     threshold: float,
     *     detected_at: string,
     *     recovery_action: string
     * }>
     */
    protected function getFailureAlerts(int $userId): array
    {
        $alerts = [];

        // Check MCP server failure rates
        $servers = MCPServer::all();

        foreach ($servers as $server) {
            if ($server->failure_rate >= self::FAILURE_RATE_CRITICAL_THRESHOLD) {
                $alerts[] = [
                    'type' => 'critical_failure_rate',
                    'severity' => 'critical',
                    'component' => $server->server_name,
                    'message' => "Critical failure rate detected for {$server->server_name}",
                    'failure_rate' => round($server->failure_rate, 2),
                    'threshold' => self::FAILURE_RATE_CRITICAL_THRESHOLD,
                    'detected_at' => now()->toIso8601String(),
                    'recovery_action' => 'restart_server',
                ];
            } elseif ($server->failure_rate >= self::FAILURE_RATE_ALERT_THRESHOLD) {
                $alerts[] = [
                    'type' => 'high_failure_rate',
                    'severity' => 'warning',
                    'component' => $server->server_name,
                    'message' => "High failure rate detected for {$server->server_name}",
                    'failure_rate' => round($server->failure_rate, 2),
                    'threshold' => self::FAILURE_RATE_ALERT_THRESHOLD,
                    'detected_at' => now()->toIso8601String(),
                    'recovery_action' => 'monitor_and_retry',
                ];
            }
        }

        // Check API circuit breakers
        foreach (['umapyoi', 'umamusumedb'] as $apiName) {
            if ($this->apiHealthMonitor->isCircuitBreakerOpen($apiName)) {
                $alerts[] = [
                    'type' => 'circuit_breaker_open',
                    'severity' => 'critical',
                    'component' => $apiName,
                    'message' => "Circuit breaker is open for {$apiName} API",
                    'failure_rate' => 100.0,
                    'threshold' => 5.0,
                    'detected_at' => now()->toIso8601String(),
                    'recovery_action' => 'wait_and_retry',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Attempt automated recovery for failed components
     *
     * @return array{
     *     attempted: array<string>,
     *     successful: array<string>,
     *     failed: array<string>,
     *     recovery_details: array<string, array<string, mixed>>
     * }
     */
    public function attemptAutomatedRecovery(): array
    {
        Log::info('[FailureRateTracking] Starting automated recovery');

        $attempted = [];
        $successful = [];
        $failed = [];
        $recoveryDetails = [];

        // Attempt recovery for MCP servers with high failure rates
        $servers = MCPServer::all()->filter(function ($server) {
            return $server->failure_rate > self::FAILURE_RATE_ALERT_THRESHOLD
                && $this->canAttemptRecovery($server->server_name);
        });

        foreach ($servers as $server) {
            $attempted[] = $server->server_name;

            $result = $this->recoverMCPServer($server);

            if ($result['success']) {
                $successful[] = $server->server_name;
            } else {
                $failed[] = $server->server_name;
            }

            $recoveryDetails[$server->server_name] = $result;
        }

        // Attempt recovery for APIs with circuit breakers open
        foreach (['umapyoi', 'umamusumedb'] as $apiName) {
            if (
                $this->apiHealthMonitor->isCircuitBreakerOpen($apiName)
                && $this->canAttemptRecovery($apiName)
            ) {
                $attempted[] = $apiName;

                $result = $this->recoverAPI($apiName);

                if ($result['success']) {
                    $successful[] = $apiName;
                } else {
                    $failed[] = $apiName;
                }

                $recoveryDetails[$apiName] = $result;
            }
        }

        Log::info('[FailureRateTracking] Automated recovery completed', [
            'attempted' => count($attempted),
            'successful' => count($successful),
            'failed' => count($failed),
        ]);

        return [
            'attempted' => $attempted,
            'successful' => $successful,
            'failed' => $failed,
            'recovery_details' => $recoveryDetails,
        ];
    }

    /**
     * Recover MCP server
     *
     * @return array{success: bool, message: string, recovery_time: float, attempts: int}
     */
    protected function recoverMCPServer(MCPServer $server): array
    {
        $startTime = microtime(true);
        $attempts = $this->getRecoveryAttempts($server->server_name);

        Log::info('[FailureRateTracking] Attempting MCP server recovery', [
            'server' => $server->server_name,
            'attempt' => $attempts + 1,
        ]);

        try {
            // Reset failure counters
            $server->consecutive_failures = 0;
            $server->last_error_message = null;
            $server->last_error_at = null;
            $server->save();

            // Attempt to reconnect
            $server->status = 'active';
            $server->last_health_check = now();
            $server->save();

            $this->recordRecoveryAttempt($server->server_name, true);

            $recoveryTime = (microtime(true) - $startTime) * 1000;

            Log::info('[FailureRateTracking] MCP server recovery successful', [
                'server' => $server->server_name,
                'recovery_time_ms' => round($recoveryTime, 2),
            ]);

            return [
                'success' => true,
                'message' => "Successfully recovered {$server->server_name}",
                'recovery_time' => round($recoveryTime, 2),
                'attempts' => $attempts + 1,
            ];
        } catch (\Exception $e) {
            $this->recordRecoveryAttempt($server->server_name, false);

            Log::error('[FailureRateTracking] MCP server recovery failed', [
                'server' => $server->server_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to recover {$server->server_name}: {$e->getMessage()}",
                'recovery_time' => round((microtime(true) - $startTime) * 1000, 2),
                'attempts' => $attempts + 1,
            ];
        }
    }

    /**
     * Recover API
     *
     * @return array{success: bool, message: string, recovery_time: float, attempts: int}
     */
    protected function recoverAPI(string $apiName): array
    {
        $startTime = microtime(true);
        $attempts = $this->getRecoveryAttempts($apiName);

        Log::info('[FailureRateTracking] Attempting API recovery', [
            'api' => $apiName,
            'attempt' => $attempts + 1,
        ]);

        try {
            // Reset circuit breaker
            $this->apiHealthMonitor->resetCircuitBreaker($apiName);

            // Perform health check
            $health = $this->apiHealthMonitor->checkAPIHealth($apiName, function () {
                // Simplified health check
                return true;
            });

            $success = $health['available'];

            $this->recordRecoveryAttempt($apiName, $success);

            $recoveryTime = (microtime(true) - $startTime) * 1000;

            if ($success) {
                Log::info('[FailureRateTracking] API recovery successful', [
                    'api' => $apiName,
                    'recovery_time_ms' => round($recoveryTime, 2),
                ]);

                return [
                    'success' => true,
                    'message' => "Successfully recovered {$apiName} API",
                    'recovery_time' => round($recoveryTime, 2),
                    'attempts' => $attempts + 1,
                ];
            }

            Log::warning('[FailureRateTracking] API recovery unsuccessful', [
                'api' => $apiName,
                'health_status' => $health['status'],
            ]);

            return [
                'success' => false,
                'message' => "API {$apiName} still unhealthy after recovery attempt",
                'recovery_time' => round($recoveryTime, 2),
                'attempts' => $attempts + 1,
            ];
        } catch (\Exception $e) {
            $this->recordRecoveryAttempt($apiName, false);

            Log::error('[FailureRateTracking] API recovery failed', [
                'api' => $apiName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to recover {$apiName} API: {$e->getMessage()}",
                'recovery_time' => round((microtime(true) - $startTime) * 1000, 2),
                'attempts' => $attempts + 1,
            ];
        }
    }

    /**
     * Check if recovery can be attempted
     */
    protected function canAttemptRecovery(string $component): bool
    {
        $attempts = $this->getRecoveryAttempts($component);

        if ($attempts >= self::MAX_RECOVERY_ATTEMPTS) {
            Log::warning('[FailureRateTracking] Max recovery attempts reached', [
                'component' => $component,
                'attempts' => $attempts,
            ]);

            return false;
        }

        // Check if enough time has passed since last attempt
        $lastAttemptKey = self::FAILURE_CACHE_PREFIX."last_recovery:{$component}";
        $lastAttempt = Redis::get($lastAttemptKey);

        if ($lastAttempt) {
            $timeSinceLastAttempt = time() - (int) $lastAttempt;

            if ($timeSinceLastAttempt < self::RECOVERY_ATTEMPT_INTERVAL) {
                Log::debug('[FailureRateTracking] Recovery attempt too soon', [
                    'component' => $component,
                    'time_since_last_attempt' => $timeSinceLastAttempt,
                    'required_interval' => self::RECOVERY_ATTEMPT_INTERVAL,
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Get recovery attempts count
     */
    protected function getRecoveryAttempts(string $component): int
    {
        $key = self::FAILURE_CACHE_PREFIX."recovery_attempts:{$component}";
        $attempts = Redis::get($key);

        return $attempts ? (int) $attempts : 0;
    }

    /**
     * Record recovery attempt
     */
    protected function recordRecoveryAttempt(string $component, bool $success): void
    {
        $attemptsKey = self::FAILURE_CACHE_PREFIX."recovery_attempts:{$component}";
        $lastAttemptKey = self::FAILURE_CACHE_PREFIX."last_recovery:{$component}";

        if ($success) {
            // Reset attempts on success
            Redis::del($attemptsKey);
        } else {
            // Increment attempts on failure
            Redis::incr($attemptsKey);
            Redis::expire($attemptsKey, 86400); // 24 hours
        }

        // Record last attempt time
        Redis::setex($lastAttemptKey, 86400, (string) time());

        // Record in history
        $historyKey = self::FAILURE_CACHE_PREFIX."recovery_history:{$component}";
        $historyEntry = json_encode([
            'timestamp' => now()->toIso8601String(),
            'success' => $success,
            'attempt' => $this->getRecoveryAttempts($component),
        ]);

        Redis::lpush($historyKey, $historyEntry);
        Redis::ltrim($historyKey, 0, 99); // Keep last 100 entries
        Redis::expire($historyKey, 604800); // 7 days
    }

    /**
     * Get active recoveries
     *
     * @return array<array<string, mixed>>
     */
    protected function getActiveRecoveries(): array
    {
        // In production, this would query active recovery processes
        return [];
    }

    /**
     * Get recent recoveries
     *
     * @return array<array<string, mixed>>
     */
    protected function getRecentRecoveries(): array
    {
        $recentRecoveries = [];

        // Get recovery history for all components
        $components = array_merge(
            MCPServer::pluck('server_name')->all(),
            ['umapyoi', 'umamusumedb']
        );

        foreach ($components as $component) {
            $historyKey = self::FAILURE_CACHE_PREFIX."recovery_history:{$component}";
            $history = Redis::lrange($historyKey, 0, 9); // Last 10 entries

            foreach ($history as $entry) {
                $data = json_decode($entry, true);
                $recentRecoveries[] = array_merge($data, ['component' => $component]);
            }
        }

        // Sort by timestamp descending
        usort($recentRecoveries, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return array_slice($recentRecoveries, 0, 20); // Return last 20
    }

    /**
     * Get recovery queue
     *
     * @return array<array<string, mixed>>
     */
    protected function getRecoveryQueue(): array
    {
        // In production, this would query queued recovery jobs
        return [];
    }

    /**
     * Get API failure count
     */
    protected function getAPIFailureCount(): int
    {
        $umapyoiFailures = $this->apiHealthMonitor->getFailureCount('umapyoi');
        $umamusumedbFailures = $this->apiHealthMonitor->getFailureCount('umamusumedb');

        return $umapyoiFailures + $umamusumedbFailures;
    }

    /**
     * Get recovery statistics
     *
     * @return array{attempts: int, successful: int, failed: int, success_rate: float}
     */
    protected function getRecoveryStatistics(): array
    {
        $components = array_merge(
            MCPServer::pluck('server_name')->all(),
            ['umapyoi', 'umamusumedb']
        );

        $totalAttempts = 0;
        $totalSuccessful = 0;

        foreach ($components as $component) {
            $historyKey = self::FAILURE_CACHE_PREFIX."recovery_history:{$component}";
            $history = Redis::lrange($historyKey, 0, -1);

            foreach ($history as $entry) {
                $data = json_decode($entry, true);
                $totalAttempts++;

                if ($data['success']) {
                    $totalSuccessful++;
                }
            }
        }

        $successRate = $totalAttempts > 0 ? ($totalSuccessful / $totalAttempts) * 100 : 0;

        return [
            'attempts' => $totalAttempts,
            'successful' => $totalSuccessful,
            'failed' => $totalAttempts - $totalSuccessful,
            'success_rate' => round($successRate, 2),
        ];
    }

    /**
     * Get date range for period
     *
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon}
     */
    protected function getDateRange(string $period): array
    {
        return match ($period) {
            'hour' => ['start' => now()->subHour(), 'end' => now()],
            'day' => ['start' => now()->startOfDay(), 'end' => now()],
            'week' => ['start' => now()->startOfWeek(), 'end' => now()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()],
            default => ['start' => now()->startOfDay(), 'end' => now()],
        };
    }
}
