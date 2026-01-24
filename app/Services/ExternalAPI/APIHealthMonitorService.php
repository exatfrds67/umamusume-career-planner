<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * API Health Monitor Service with MCP Agent Integration
 *
 * Monitors external API health status with automatic failover coordination,
 * circuit breaker patterns, and comprehensive health metrics tracking.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class APIHealthMonitorService
{
    /**
     * Health check interval in seconds
     */
    protected const HEALTH_CHECK_INTERVAL = 60;

    /**
     * Circuit breaker failure threshold
     */
    protected const FAILURE_THRESHOLD = 5;

    /**
     * Circuit breaker timeout in seconds
     */
    public const CIRCUIT_BREAKER_TIMEOUT = 300;

    /**
     * Response time threshold for degraded status (ms)
     */
    protected const DEGRADED_THRESHOLD_MS = 2000;

    /**
     * Response time threshold for unhealthy status (ms)
     */
    protected const UNHEALTHY_THRESHOLD_MS = 5000;

    /**
     * Cache prefix for health status
     */
    protected const CACHE_PREFIX = 'api_health:';

    /**
     * Redis key for failure counts
     */
    protected const FAILURE_COUNT_KEY = 'api_failures:';

    /**
     * Redis key for circuit breaker state
     */
    protected const CIRCUIT_BREAKER_KEY = 'circuit_breaker:';

    public function __construct(
        protected MCPClientService $mcpClient,
        protected UmapyoiApiClient $umapyoiClient,
        protected UmamusumeDBApiClient $umamusumeDBClient
    ) {}

    /**
     * Check health status of all external APIs
     *
     * @return array{umapyoi: array<string, mixed>, umamusumedb: array<string, mixed>, overall_status: string, timestamp: string}
     */
    public function checkAllAPIs(): array
    {
        Log::info('[APIHealthMonitor] Starting comprehensive health check');

        $umapyoiHealth = $this->checkAPIHealth('umapyoi', function () {
            return $this->umapyoiClient->isAvailable();
        });

        $umamusumeDBHealth = $this->checkAPIHealth('umamusumedb', function () {
            return $this->umamusumeDBClient->isAvailable();
        });

        // Determine overall status
        $overallStatus = $this->determineOverallStatus([
            $umapyoiHealth['status'],
            $umamusumeDBHealth['status'],
        ]);

        $result = [
            'umapyoi' => $umapyoiHealth,
            'umamusumedb' => $umamusumeDBHealth,
            'overall_status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
        ];

        // Cache the health status
        Cache::put(
            self::CACHE_PREFIX.'all',
            $result,
            self::HEALTH_CHECK_INTERVAL
        );

        // Trigger alerts if needed
        if ($overallStatus !== 'healthy') {
            $this->triggerHealthAlert($result);
        }

        Log::info('[APIHealthMonitor] Health check completed', [
            'overall_status' => $overallStatus,
            'umapyoi_status' => $umapyoiHealth['status'],
            'umamusumedb_status' => $umamusumeDBHealth['status'],
        ]);

        return $result;
    }

    /**
     * Check health of a specific API
     *
     * @return array{status: string, available: bool, response_time_ms: float|null, failure_count: int, circuit_breaker_open: bool, last_check: string, message: string}
     */
    public function checkAPIHealth(string $apiName, callable $healthCheck): array
    {
        $startTime = microtime(true);

        // Check if circuit breaker is open
        if ($this->isCircuitBreakerOpen($apiName)) {
            return [
                'status' => 'circuit_open',
                'available' => false,
                'response_time_ms' => null,
                'failure_count' => $this->getFailureCount($apiName),
                'circuit_breaker_open' => true,
                'last_check' => now()->toIso8601String(),
                'message' => 'Circuit breaker is open due to repeated failures',
            ];
        }

        try {
            // Perform health check
            $available = $healthCheck();
            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($available) {
                // Reset failure count on success
                $this->resetFailureCount($apiName);

                // Determine status based on response time
                $status = $this->determineStatusFromResponseTime($responseTime);

                return [
                    'status' => $status,
                    'available' => true,
                    'response_time_ms' => round($responseTime, 2),
                    'failure_count' => 0,
                    'circuit_breaker_open' => false,
                    'last_check' => now()->toIso8601String(),
                    'message' => $this->getStatusMessage($status, $responseTime),
                ];
            }

            // API returned false
            $this->incrementFailureCount($apiName);

            return [
                'status' => 'unhealthy',
                'available' => false,
                'response_time_ms' => round($responseTime, 2),
                'failure_count' => $this->getFailureCount($apiName),
                'circuit_breaker_open' => false,
                'last_check' => now()->toIso8601String(),
                'message' => 'API health check returned false',
            ];
        } catch (\Exception $e) {
            $this->incrementFailureCount($apiName);
            $responseTime = (microtime(true) - $startTime) * 1000;

            Log::error('[APIHealthMonitor] Health check failed', [
                'api' => $apiName,
                'error' => $e->getMessage(),
                'failure_count' => $this->getFailureCount($apiName),
            ]);

            return [
                'status' => 'error',
                'available' => false,
                'response_time_ms' => round($responseTime, 2),
                'failure_count' => $this->getFailureCount($apiName),
                'circuit_breaker_open' => $this->isCircuitBreakerOpen($apiName),
                'last_check' => now()->toIso8601String(),
                'message' => 'Health check failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get cached health status for an API
     *
     * @return array<string, mixed>|null
     */
    public function getCachedHealth(string $apiName): ?array
    {
        $cacheKey = self::CACHE_PREFIX.$apiName;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        return null;
    }

    /**
     * Get cached health status for all APIs
     *
     * @return array<string, mixed>|null
     */
    public function getCachedAllHealth(): ?array
    {
        $cached = Cache::get(self::CACHE_PREFIX.'all');

        if (is_array($cached)) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        return null;
    }

    /**
     * Check if circuit breaker is open for an API
     */
    public function isCircuitBreakerOpen(string $apiName): bool
    {
        $key = self::CIRCUIT_BREAKER_KEY.$apiName;
        $failureCount = $this->getFailureCount($apiName);

        // Open circuit breaker if failure threshold exceeded
        if ($failureCount >= self::FAILURE_THRESHOLD) {
            // Check if circuit breaker timeout has expired
            $circuitOpenTime = Redis::get($key);

            if ($circuitOpenTime !== null && $circuitOpenTime !== false) {
                $elapsedTime = time() - (is_numeric($circuitOpenTime) ? (int) $circuitOpenTime : 0);

                if ($elapsedTime < self::CIRCUIT_BREAKER_TIMEOUT) {
                    return true;
                }

                // Timeout expired, allow retry
                Redis::del($key);
                $this->resetFailureCount($apiName);

                return false;
            }

            // Set circuit breaker open time
            Redis::setex($key, self::CIRCUIT_BREAKER_TIMEOUT, (string) time());

            Log::warning('[APIHealthMonitor] Circuit breaker opened', [
                'api' => $apiName,
                'failure_count' => $failureCount,
                'timeout_seconds' => self::CIRCUIT_BREAKER_TIMEOUT,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get failure count for an API
     */
    public function getFailureCount(string $apiName): int
    {
        $key = self::FAILURE_COUNT_KEY.$apiName;
        $count = Redis::get($key);

        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Increment failure count for an API
     */
    protected function incrementFailureCount(string $apiName): void
    {
        $key = self::FAILURE_COUNT_KEY.$apiName;
        Redis::incr($key);
        Redis::expire($key, 3600); // Expire after 1 hour
    }

    /**
     * Reset failure count for an API
     */
    protected function resetFailureCount(string $apiName): void
    {
        $key = self::FAILURE_COUNT_KEY.$apiName;
        Redis::del($key);
    }

    /**
     * Determine status from response time
     */
    protected function determineStatusFromResponseTime(float $responseTimeMs): string
    {
        if ($responseTimeMs >= self::UNHEALTHY_THRESHOLD_MS) {
            return 'unhealthy';
        }

        if ($responseTimeMs >= self::DEGRADED_THRESHOLD_MS) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * Determine overall status from individual API statuses
     *
     * @param  array<string>  $statuses
     */
    protected function determineOverallStatus(array $statuses): string
    {
        // If any API is in error or circuit_open state, overall is unhealthy
        if (in_array('error', $statuses) || in_array('circuit_open', $statuses)) {
            return 'unhealthy';
        }

        // If any API is unhealthy, overall is unhealthy
        if (in_array('unhealthy', $statuses)) {
            return 'unhealthy';
        }

        // If any API is degraded, overall is degraded
        if (in_array('degraded', $statuses)) {
            return 'degraded';
        }

        // All APIs are healthy
        return 'healthy';
    }

    /**
     * Get status message based on status and response time
     */
    protected function getStatusMessage(string $status, float $responseTimeMs): string
    {
        return match ($status) {
            'healthy' => sprintf('API is healthy (%.2fms)', $responseTimeMs),
            'degraded' => sprintf('API is degraded - slow response time (%.2fms)', $responseTimeMs),
            'unhealthy' => sprintf('API is unhealthy - very slow response time (%.2fms)', $responseTimeMs),
            'circuit_open' => 'Circuit breaker is open due to repeated failures',
            'error' => 'API health check failed',
            default => 'Unknown status',
        };
    }

    /**
     * Trigger health alert via MCP tools
     *
     * @param  array<string, mixed>  $healthStatus
     */
    protected function triggerHealthAlert(array $healthStatus): void
    {
        // Check if MCP server is available for alerting
        if (! $this->mcpClient->isServerEnabled('awsknowledge')) {
            Log::debug('[APIHealthMonitor] MCP awsknowledge server not available for alerting');

            return;
        }

        Log::warning('[APIHealthMonitor] Health alert triggered', [
            'overall_status' => $healthStatus['overall_status'],
            'details' => $healthStatus,
        ]);

        // In production, this would send alerts via MCP tools
        // For now, we log the alert
    }

    /**
     * Get comprehensive health metrics
     *
     * @return array{current_status: array<string, mixed>, failure_counts: array<string, int>, circuit_breakers: array<string, bool>, response_times: array<string, array<string, float>>, recommendations: array<string>}
     */
    public function getHealthMetrics(): array
    {
        $currentStatus = $this->getCachedAllHealth() ?? $this->checkAllAPIs();

        $failureCounts = [
            'umapyoi' => $this->getFailureCount('umapyoi'),
            'umamusumedb' => $this->getFailureCount('umamusumedb'),
        ];

        $circuitBreakers = [
            'umapyoi' => $this->isCircuitBreakerOpen('umapyoi'),
            'umamusumedb' => $this->isCircuitBreakerOpen('umamusumedb'),
        ];

        $recommendations = $this->generateRecommendations($currentStatus, $failureCounts, $circuitBreakers);

        // Extract response times with proper type validation
        $umapyoiStatus = is_array($currentStatus['umapyoi'] ?? null) ? $currentStatus['umapyoi'] : [];
        $umamusumedbStatus = is_array($currentStatus['umamusumedb'] ?? null) ? $currentStatus['umamusumedb'] : [];

        $umapyoiResponseTime = isset($umapyoiStatus['response_time_ms']) && is_numeric($umapyoiStatus['response_time_ms'])
            ? (float) $umapyoiStatus['response_time_ms']
            : 0.0;
        $umamusumedbResponseTime = isset($umamusumedbStatus['response_time_ms']) && is_numeric($umamusumedbStatus['response_time_ms'])
            ? (float) $umamusumedbStatus['response_time_ms']
            : 0.0;

        return [
            'current_status' => $currentStatus,
            'failure_counts' => $failureCounts,
            'circuit_breakers' => $circuitBreakers,
            'response_times' => [
                'umapyoi' => [
                    'current' => $umapyoiResponseTime,
                    'threshold_degraded' => (float) self::DEGRADED_THRESHOLD_MS,
                    'threshold_unhealthy' => (float) self::UNHEALTHY_THRESHOLD_MS,
                ],
                'umamusumedb' => [
                    'current' => $umamusumedbResponseTime,
                    'threshold_degraded' => (float) self::DEGRADED_THRESHOLD_MS,
                    'threshold_unhealthy' => (float) self::UNHEALTHY_THRESHOLD_MS,
                ],
            ],
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Generate recommendations based on health metrics
     *
     * @param  array<string, mixed>  $currentStatus
     * @param  array<string, int>  $failureCounts
     * @param  array<string, bool>  $circuitBreakers
     * @return array<string>
     */
    protected function generateRecommendations(
        array $currentStatus,
        array $failureCounts,
        array $circuitBreakers
    ): array {
        $recommendations = [];

        foreach (['umapyoi', 'umamusumedb'] as $apiName) {
            $apiStatus = is_array($currentStatus[$apiName] ?? null) ? $currentStatus[$apiName] : [];
            $status = isset($apiStatus['status']) && is_string($apiStatus['status']) ? $apiStatus['status'] : 'unknown';
            $failureCount = $failureCounts[$apiName] ?? 0;
            $circuitOpen = $circuitBreakers[$apiName] ?? false;

            if ($circuitOpen) {
                $recommendations[] = sprintf(
                    '%s: Circuit breaker is open. Wait %d seconds before retry.',
                    $apiName,
                    self::CIRCUIT_BREAKER_TIMEOUT
                );
            } elseif ($status === 'unhealthy') {
                $recommendations[] = sprintf(
                    '%s: API is unhealthy. Use cached data and enable graceful degradation.',
                    $apiName
                );
            } elseif ($status === 'degraded') {
                $recommendations[] = sprintf(
                    '%s: API is degraded. Consider increasing cache TTL and reducing request frequency.',
                    $apiName
                );
            } elseif ($failureCount > 0) {
                $recommendations[] = sprintf(
                    '%s: %d recent failures detected. Monitor closely.',
                    $apiName,
                    $failureCount
                );
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'All APIs are healthy. No action required.';
        }

        return $recommendations;
    }

    /**
     * Force reset circuit breaker for an API
     */
    public function resetCircuitBreaker(string $apiName): void
    {
        $circuitBreakerKey = self::CIRCUIT_BREAKER_KEY.$apiName;
        Redis::del($circuitBreakerKey);
        $this->resetFailureCount($apiName);

        Log::info('[APIHealthMonitor] Circuit breaker manually reset', [
            'api' => $apiName,
        ]);
    }

    /**
     * Force reset all circuit breakers
     */
    public function resetAllCircuitBreakers(): void
    {
        $this->resetCircuitBreaker('umapyoi');
        $this->resetCircuitBreaker('umamusumedb');

        Log::info('[APIHealthMonitor] All circuit breakers manually reset');
    }
}
