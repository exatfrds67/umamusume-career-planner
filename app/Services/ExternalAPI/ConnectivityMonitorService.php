<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Connectivity Monitor Service
 *
 * Monitors network connectivity and external API availability
 * to enable offline mode detection and graceful degradation.
 *
 * Features:
 * - Real-time connectivity status tracking
 * - Offline mode detection
 * - API availability monitoring
 * - Connectivity state caching
 * - Event broadcasting for UI updates
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.1
 */
class ConnectivityMonitorService
{
    /**
     * Cache key for connectivity status
     */
    private const CONNECTIVITY_STATUS_KEY = 'connectivity:status';

    /**
     * Cache key for last successful API call
     */
    private const LAST_SUCCESS_KEY = 'connectivity:last_success';

    /**
     * Cache key for offline mode state
     */
    private const OFFLINE_MODE_KEY = 'connectivity:offline_mode';

    /**
     * Connectivity check interval in seconds
     */
    private const CHECK_INTERVAL = 30;

    /**
     * Number of consecutive failures before marking as offline
     */
    private const FAILURE_THRESHOLD = 3;

    public function __construct(
        protected APIHealthMonitorService $healthMonitor,
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Check current connectivity status
     *
     * @return array{is_online: bool, api_status: array<string, mixed>, last_check: string, offline_since: string|null, consecutive_failures: int}
     */
    public function checkConnectivity(): array
    {
        Log::debug('[ConnectivityMonitor] Checking connectivity status');

        $startTime = microtime(true);

        // Check API health
        $apiHealth = $this->healthMonitor->checkAllAPIs();

        $duration = (microtime(true) - $startTime) * 1000;

        // Determine if we're online based on API availability
        $isOnline = $this->determineOnlineStatus($apiHealth);

        // Get current failure count
        $consecutiveFailures = $this->getConsecutiveFailures();

        if ($isOnline) {
            // Reset failure count on success
            $this->resetFailureCount();
            $this->recordSuccessfulConnection();

            $status = [
                'is_online' => true,
                'api_status' => $apiHealth,
                'last_check' => now()->toIso8601String(),
                'offline_since' => null,
                'consecutive_failures' => 0,
                'check_duration_ms' => round($duration, 2),
            ];
        } else {
            // Increment failure count
            $consecutiveFailures = $this->incrementFailureCount();

            // Check if we should enter offline mode
            $offlineSince = $this->getOfflineSince();

            if ($consecutiveFailures >= self::FAILURE_THRESHOLD && ! $offlineSince) {
                $offlineSince = now()->toIso8601String();
                $this->setOfflineMode(true, $offlineSince);

                Log::warning('[ConnectivityMonitor] Entering offline mode', [
                    'consecutive_failures' => $consecutiveFailures,
                    'offline_since' => $offlineSince,
                ]);
            }

            $status = [
                'is_online' => false,
                'api_status' => $apiHealth,
                'last_check' => now()->toIso8601String(),
                'offline_since' => $offlineSince,
                'consecutive_failures' => $consecutiveFailures,
                'check_duration_ms' => round($duration, 2),
            ];
        }

        // Cache the status
        Cache::put(self::CONNECTIVITY_STATUS_KEY, $status, self::CHECK_INTERVAL);

        return $status;
    }

    /**
     * Get cached connectivity status
     *
     * @return array{is_online: bool, api_status: array<string, mixed>, last_check: string, offline_since: string|null, consecutive_failures: int}|null
     */
    public function getCachedStatus(): ?array
    {
        $cached = Cache::get(self::CONNECTIVITY_STATUS_KEY);

        if (is_array($cached)) {
            /** @var array{is_online: bool, api_status: array<string, mixed>, last_check: string, offline_since: string|null, consecutive_failures: int} $cached */
            return $cached;
        }

        return null;
    }

    /**
     * Get connectivity status (cached or fresh)
     *
     * @return array{is_online: bool, api_status: array<string, mixed>, last_check: string, offline_since: string|null, consecutive_failures: int, cached: bool}
     */
    public function getStatus(): array
    {
        $cached = $this->getCachedStatus();

        if ($cached) {
            $cached['cached'] = true;

            return $cached;
        }

        $status = $this->checkConnectivity();
        $status['cached'] = false;

        return $status;
    }

    /**
     * Check if currently in offline mode
     */
    public function isOffline(): bool
    {
        $status = $this->getStatus();

        return ! $status['is_online'];
    }

    /**
     * Check if currently online
     */
    public function isOnline(): bool
    {
        return ! $this->isOffline();
    }

    /**
     * Get offline mode information
     *
     * @return array{is_offline: bool, offline_since: string|null, duration_seconds: int|null, cached_data_available: bool, cache_statistics: array<string, mixed>}
     */
    public function getOfflineModeInfo(): array
    {
        $status = $this->getStatus();
        $offlineSince = $status['offline_since'];

        $durationSeconds = null;
        if ($offlineSince) {
            $durationSeconds = (int) now()->diffInSeconds($offlineSince);
        }

        // Get cache statistics
        $cacheStats = $this->cacheManager->getStatistics();

        return [
            'is_offline' => ! $status['is_online'],
            'offline_since' => $offlineSince,
            'duration_seconds' => $durationSeconds,
            'cached_data_available' => $cacheStats['total_requests'] > 0,
            'cache_statistics' => $cacheStats,
            'last_successful_connection' => $this->getLastSuccessfulConnection(),
        ];
    }

    /**
     * Force connectivity check (bypass cache)
     *
     * @return array{is_online: bool, api_status: array<string, mixed>, last_check: string, offline_since: string|null, consecutive_failures: int}
     */
    public function forceCheck(): array
    {
        // Clear cached status
        Cache::forget(self::CONNECTIVITY_STATUS_KEY);

        return $this->checkConnectivity();
    }

    /**
     * Manually set offline mode
     */
    public function setOfflineMode(bool $offline, ?string $since = null): void
    {
        if ($offline) {
            $offlineSince = $since ?? now()->toIso8601String();
            Cache::put(self::OFFLINE_MODE_KEY, $offlineSince, 86400); // 24 hours

            Log::info('[ConnectivityMonitor] Offline mode enabled', [
                'offline_since' => $offlineSince,
            ]);
        } else {
            Cache::forget(self::OFFLINE_MODE_KEY);
            $this->resetFailureCount();

            Log::info('[ConnectivityMonitor] Offline mode disabled');
        }
    }

    /**
     * Get time when offline mode started
     */
    protected function getOfflineSince(): ?string
    {
        $value = Cache::get(self::OFFLINE_MODE_KEY);

        return is_string($value) ? $value : null;
    }

    /**
     * Determine if system is online based on API health
     *
     * @param  array<string, mixed>  $apiHealth
     */
    protected function determineOnlineStatus(array $apiHealth): bool
    {
        $overallStatus = isset($apiHealth['overall_status']) && is_string($apiHealth['overall_status'])
            ? $apiHealth['overall_status']
            : 'unhealthy';

        // Consider online if at least one API is healthy or degraded
        if (in_array($overallStatus, ['healthy', 'degraded'])) {
            return true;
        }

        // Check individual API statuses
        $umapyoiData = is_array($apiHealth['umapyoi'] ?? null) ? $apiHealth['umapyoi'] : [];
        $umamusumedbData = is_array($apiHealth['umamusumedb'] ?? null) ? $apiHealth['umamusumedb'] : [];

        $umapyoiStatus = isset($umapyoiData['status']) && is_string($umapyoiData['status'])
            ? $umapyoiData['status']
            : 'error';
        $umamusumeDBStatus = isset($umamusumedbData['status']) && is_string($umamusumedbData['status'])
            ? $umamusumedbData['status']
            : 'error';

        // Online if at least one API is available
        if (in_array($umapyoiStatus, ['healthy', 'degraded']) ||
            in_array($umamusumeDBStatus, ['healthy', 'degraded'])) {
            return true;
        }

        // In local environment, we don't want to show offline banner just because external APIs are unreachable
        if (app()->environment('local')) {
            Log::info('[ConnectivityMonitor] Local environment detected, forcing online status despite API failures');

            return true;
        }

        return false;
    }

    /**
     * Get consecutive failure count
     */
    protected function getConsecutiveFailures(): int
    {
        $value = Cache::get('connectivity:failures', 0);

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Increment consecutive failure count
     */
    protected function incrementFailureCount(): int
    {
        $count = $this->getConsecutiveFailures() + 1;
        Cache::put('connectivity:failures', $count, 3600); // 1 hour

        return $count;
    }

    /**
     * Reset consecutive failure count
     */
    protected function resetFailureCount(): void
    {
        Cache::forget('connectivity:failures');
    }

    /**
     * Record successful connection timestamp
     */
    protected function recordSuccessfulConnection(): void
    {
        Cache::put(self::LAST_SUCCESS_KEY, now()->toIso8601String(), 86400); // 24 hours
    }

    /**
     * Get last successful connection timestamp
     */
    public function getLastSuccessfulConnection(): ?string
    {
        $value = Cache::get(self::LAST_SUCCESS_KEY);

        return is_string($value) ? $value : null;
    }

    /**
     * Get connectivity recommendations
     *
     * @return array<string>
     */
    public function getRecommendations(): array
    {
        $status = $this->getStatus();
        $recommendations = [];

        if (! $status['is_online']) {
            $recommendations[] = 'You are currently offline. The application will use cached data.';

            $offlineSince = $status['offline_since'];
            if ($offlineSince) {
                $duration = now()->diffInMinutes($offlineSince);
                $recommendations[] = sprintf('Offline for %d minutes. Some data may be stale.', $duration);
            }

            $cacheStats = $this->cacheManager->getStatistics();
            if ($cacheStats['hit_rate'] < 50) {
                $recommendations[] = 'Limited cached data available. Some features may not work properly.';
            }

            $recommendations[] = 'Check your internet connection and try refreshing the page.';
        } else {
            $apiStatus = $status['api_status'];

            if ($apiStatus['overall_status'] === 'degraded') {
                $recommendations[] = 'API performance is degraded. Responses may be slower than usual.';
                $recommendations[] = 'Consider using cached data to improve performance.';
            }

            if ($status['consecutive_failures'] > 0) {
                $recommendations[] = sprintf(
                    'Recent connectivity issues detected (%d failures). Monitoring closely.',
                    $status['consecutive_failures']
                );
            }
        }

        return $recommendations;
    }

    /**
     * Get detailed connectivity report
     *
     * @return array{status: array<string, mixed>, offline_info: array<string, mixed>, recommendations: array<string>, cache_info: array<string, mixed>}
     */
    public function getConnectivityReport(): array
    {
        return [
            'status' => $this->getStatus(),
            'offline_info' => $this->getOfflineModeInfo(),
            'recommendations' => $this->getRecommendations(),
            'cache_info' => $this->cacheManager->getCacheInfo(),
        ];
    }
}
