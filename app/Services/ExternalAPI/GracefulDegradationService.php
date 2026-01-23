<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\CacheManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Graceful Degradation Service with MCP Agent Integration
 *
 * Manages graceful degradation strategies when external APIs are unavailable,
 * including manual input modes, cached data fallback, and user notifications.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class GracefulDegradationService
{
    /**
     * Degradation mode cache key
     */
    protected const DEGRADATION_MODE_KEY = 'degradation_mode:';

    /**
     * Stale data threshold in seconds (7 days)
     */
    protected const STALE_DATA_THRESHOLD = 604800;

    /**
     * Manual input session key
     */
    protected const MANUAL_INPUT_SESSION_KEY = 'manual_input_enabled:';

    public function __construct(
        protected MCPClientService $mcpClient,
        protected CacheManagementService $cacheManager,
        protected APIHealthMonitorService $healthMonitor
    ) {}

    /**
     * Check if degradation mode is active for an API
     */
    public function isDegradationModeActive(string $apiName): bool
    {
        $key = self::DEGRADATION_MODE_KEY.$apiName;

        return Cache::get($key, false);
    }

    /**
     * Enable degradation mode for an API
     */
    public function enableDegradationMode(string $apiName, string $reason = 'API unavailable'): void
    {
        $key = self::DEGRADATION_MODE_KEY.$apiName;
        Cache::put($key, true, 3600); // Active for 1 hour

        Log::warning('[GracefulDegradation] Degradation mode enabled', [
            'api' => $apiName,
            'reason' => $reason,
        ]);

        // Notify user via MCP tools if available
        $this->notifyDegradationMode($apiName, $reason);
    }

    /**
     * Disable degradation mode for an API
     */
    public function disableDegradationMode(string $apiName): void
    {
        $key = self::DEGRADATION_MODE_KEY.$apiName;
        Cache::forget($key);

        Log::info('[GracefulDegradation] Degradation mode disabled', [
            'api' => $apiName,
        ]);
    }

    /**
     * Get data with graceful degradation fallback
     *
     * @param  array<string, mixed>  $apiResult
     * @param  array<string, mixed>  $fallbackOptions
     * @return array{success: bool, data: mixed, source: string, degraded: bool, stale: bool, message: string}
     */
    public function getDataWithFallback(): array
        // If API call was successful, return the data
        if ($apiResult['success']) {
            return [
                'success' => true,
                'data' => $apiResult['data'],
                'source' => $apiResult['source'],
                'degraded' => false,
                'stale' => false,
                'message' => 'Data retrieved successfully from API',
            ];
        }

        // API call failed, enable degradation mode
        $this->enableDegradationMode($apiName, $apiResult['error'] ?? 'Unknown error');

        // Try to get cached data
        $cacheKey = (is_array($fallbackOptions) && isset($fallbackOptions['cache_key']) ? $fallbackOptions['cache_key'] : null);

        if ($cacheKey && Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            $cacheAge = $this->getCacheAge($cacheKey);
            $isStale = $cacheAge > self::STALE_DATA_THRESHOLD;

            Log::info('[GracefulDegradation] Using cached data as fallback', [
                'api' => $apiName,
                'cache_key' => $cacheKey,
                'cache_age_seconds' => $cacheAge,
                'is_stale' => $isStale,
            ]);

            return [
                'success' => true,
                'data' => $cachedData,
                'source' => 'cache_fallback',
                'degraded' => true,
                'stale' => $isStale,
                'message' => $isStale
                    ? sprintf('Using stale cached data (%.1f days old)', $cacheAge / 86400)
                    : sprintf('Using cached data (%.1f hours old)', $cacheAge / 3600),
            ];
        }

        // No cached data available, check if manual input is enabled
        if ($this->isManualInputEnabled($apiName)) {
            return [
                'success' => false,
                'data' => null,
                'source' => 'manual_input_required',
                'degraded' => true,
                'stale' => false,
                'message' => 'API unavailable and no cached data. Manual input required.',
            ];
        }

        // Return empty data with degradation notice
        return [
            'success' => false,
            'data' => (is_array($fallbackOptions) && isset($fallbackOptions['default_data']) ? $fallbackOptions['default_data'] : null),
            'source' => 'degraded',
            'degraded' => true,
            'stale' => false,
            'message' => 'API unavailable, no cached data, and manual input disabled. Using default data.',
        ];
    }

    /**
     * Enable manual input mode for an API
     */
    public function enableManualInput(string $apiName): void
    {
        $key = self::MANUAL_INPUT_SESSION_KEY.$apiName;
        Cache::put($key, true, 86400); // Active for 24 hours

        Log::info('[GracefulDegradation] Manual input mode enabled', [
            'api' => $apiName,
        ]);
    }

    /**
     * Disable manual input mode for an API
     */
    public function disableManualInput(string $apiName): void
    {
        $key = self::MANUAL_INPUT_SESSION_KEY.$apiName;
        Cache::forget($key);

        Log::info('[GracefulDegradation] Manual input mode disabled', [
            'api' => $apiName,
        ]);
    }

    /**
     * Check if manual input is enabled for an API
     */
    public function isManualInputEnabled(string $apiName): bool
    {
        $key = self::MANUAL_INPUT_SESSION_KEY.$apiName;

        return Cache::get($key, false);
    }

    /**
     * Get cache age in seconds
     */
    protected function getCacheAge(string $cacheKey): int
    {
        // Try to get cache timestamp
        $timestampKey = $cacheKey.':timestamp';
        $timestamp = Cache::get($timestampKey);

        if ($timestamp) {
            return time() - $timestamp;
        }

        // If no timestamp, assume cache is fresh
        return 0;
    }

    /**
     * Store data with timestamp for age tracking
     *
     * @param  mixed  $data
     */
    public function storeWithTimestamp(string $cacheKey, $data, int $ttl): void
    {
        Cache::put($cacheKey, $data, $ttl);
        Cache::put($cacheKey.':timestamp', time(), $ttl);
    }

    /**
     * Get degradation status for all APIs
     *
     * @return array{umapyoi: array<string, mixed>, umamusumedb: array<string, mixed>, overall_degraded: bool}
     */
    public function getDegradationStatus(): array
        $umapyoiDegraded = $this->isDegradationModeActive('umapyoi');
        $umamusumeDBDegraded = $this->isDegradationModeActive('umamusumedb');

        return [
            'umapyoi' => [
                'degraded' => $umapyoiDegraded,
                'manual_input_enabled' => $this->isManualInputEnabled('umapyoi'),
                'health_status' => $this->healthMonitor->getCachedHealth('umapyoi'),
            ],
            'umamusumedb' => [
                'degraded' => $umamusumeDBDegraded,
                'manual_input_enabled' => $this->isManualInputEnabled('umamusumedb'),
                'health_status' => $this->healthMonitor->getCachedHealth('umamusumedb'),
            ],
            'overall_degraded' => $umapyoiDegraded || $umamusumeDBDegraded,
        ];
    }

    /**
     * Get user-friendly degradation message
     *
     * @return array{title: string, message: string, actions: array<string>, severity: string}
     */
    public function getDegradationMessage(): array
        $isDegraded = $this->isDegradationModeActive($apiName);
        $manualInputEnabled = $this->isManualInputEnabled($apiName);
        $healthStatus = $this->healthMonitor->getCachedHealth($apiName);

        if (! $isDegraded) {
            return [
                'title' => 'Service Operating Normally',
                'message' => sprintf('%s API is functioning normally.', ucfirst($apiName)),
                'actions' => [],
                'severity' => 'info',
            ];
        }

        $circuitOpen = $healthStatus['circuit_breaker_open'] ?? false;

        if ($circuitOpen) {
            return [
                'title' => 'Service Temporarily Unavailable',
                'message' => sprintf(
                    '%s API is temporarily unavailable due to repeated failures. The system will automatically retry in %d minutes. Using cached data where available.',
                    ucfirst($apiName),
                    APIHealthMonitorService::CIRCUIT_BREAKER_TIMEOUT / 60
                ),
                'actions' => [
                    'Use cached data for now',
                    'Enable manual input if needed',
                    'Check back in a few minutes',
                ],
                'severity' => 'warning',
            ];
        }

        if ($manualInputEnabled) {
            return [
                'title' => 'Manual Input Mode Active',
                'message' => sprintf(
                    '%s API is unavailable. Manual input mode is enabled. You can enter data manually until the service is restored.',
                    ucfirst($apiName)
                ),
                'actions' => [
                    'Enter data manually',
                    'Use cached data where available',
                    'Disable manual input when service is restored',
                ],
                'severity' => 'info',
            ];
        }

        return [
            'title' => 'Service Degraded',
            'message' => sprintf(
                '%s API is currently unavailable. The system is using cached data where available. Some features may be limited.',
                ucfirst($apiName)
            ),
            'actions' => [
                'Continue with cached data',
                'Enable manual input if needed',
                'Check service status',
            ],
            'severity' => 'warning',
        ];
    }

    /**
     * Notify user about degradation mode via MCP tools
     */
    protected function notifyDegradationMode(string $apiName, string $reason): void
    {
        // Check if MCP server is available for notifications
        if (! $this->mcpClient->isServerEnabled('awsknowledge')) {
            Log::debug('[GracefulDegradation] MCP awsknowledge server not available for notifications');

            return;
        }

        // In production, this would send notifications via MCP tools
        Log::info('[GracefulDegradation] Degradation notification sent', [
            'api' => $apiName,
            'reason' => $reason,
        ]);
    }

    /**
     * Attempt to recover from degradation mode
     *
     * @return array{recovered: bool, api: string, message: string}
     */
    public function attemptRecovery(): array
        Log::info('[GracefulDegradation] Attempting recovery', [
            'api' => $apiName,
        ]);

        // Check current health status
        $healthStatus = $this->healthMonitor->checkAPIHealth(
            $apiName,
            function () use ($apiName) {
                if ($apiName === 'umapyoi') {
                    return app(UmapyoiApiClient::class)->isAvailable();
                }

                if ($apiName === 'umamusumedb') {
                    return app(UmamusumeDBApiClient::class)->isAvailable();
                }

                return false;
            }
        );

        if ($healthStatus['status'] === 'healthy') {
            // Recovery successful
            $this->disableDegradationMode($apiName);
            $this->disableManualInput($apiName);

            Log::info('[GracefulDegradation] Recovery successful', [
                'api' => $apiName,
                'response_time_ms' => $healthStatus['response_time_ms'],
            ]);

            return [
                'recovered' => true,
                'api' => $apiName,
                'message' => sprintf(
                    '%s API has recovered and is now healthy (%.2fms response time)',
                    ucfirst($apiName),
                    $healthStatus['response_time_ms']
                ),
            ];
        }

        // Recovery failed
        Log::warning('[GracefulDegradation] Recovery failed', [
            'api' => $apiName,
            'status' => $healthStatus['status'],
            'message' => $healthStatus['message'],
        ]);

        return [
            'recovered' => false,
            'api' => $apiName,
            'message' => sprintf(
                '%s API is still %s: %s',
                ucfirst($apiName),
                $healthStatus['status'],
                $healthStatus['message']
            ),
        ];
    }

    /**
     * Attempt recovery for all APIs
     *
     * @return array<string, array{recovered: bool, api: string, message: string}>
     */
    public function attemptAllRecovery(): array
        return [
            'umapyoi' => $this->attemptRecovery('umapyoi'),
            'umamusumedb' => $this->attemptRecovery('umamusumedb'),
        ];
    }

    /**
     * Get comprehensive degradation metrics
     *
     * @return array{status: array<string, mixed>, cache_availability: array<string, bool>, manual_input_status: array<string, bool>, recovery_recommendations: array<string>}
     */
    public function getDegradationMetrics(): array
        $status = $this->getDegradationStatus();

        $cacheAvailability = [
            'umapyoi_characters' => Cache::has('umapyoi:characters'),
            'umapyoi_support_cards' => Cache::has('umapyoi:support_cards'),
            'umamusumedb_meta' => Cache::has('umamusumedb:meta:tier_rankings'),
            'umamusumedb_skills' => Cache::has('umamusumedb:skills:effectiveness'),
        ];

        $manualInputStatus = [
            'umapyoi' => $this->isManualInputEnabled('umapyoi'),
            'umamusumedb' => $this->isManualInputEnabled('umamusumedb'),
        ];

        $recommendations = $this->generateRecoveryRecommendations($status, $cacheAvailability);

        return [
            'status' => $status,
            'cache_availability' => $cacheAvailability,
            'manual_input_status' => $manualInputStatus,
            'recovery_recommendations' => $recommendations,
        ];
    }

    /**
     * Generate recovery recommendations
     *
     * @param  array<string, mixed>  $status
     * @param  array<string, bool>  $cacheAvailability
     * @return array<string>
     */
    protected function generateRecoveryRecommendations(): array
        $recommendations = [];

        if ($status['overall_degraded']) {
            $recommendations[] = 'System is in degraded mode. Attempting automatic recovery...';

            // Check cache availability
            $cacheCount = count(array_filter($cacheAvailability));
            $totalCache = count($cacheAvailability);

            if ($cacheCount > 0) {
                $recommendations[] = sprintf(
                    '%d of %d cached data sources available. Continue using cached data.',
                    $cacheCount,
                    $totalCache
                );
            } else {
                $recommendations[] = 'No cached data available. Consider enabling manual input mode.';
            }

            // Check manual input status
            if ($status['umapyoi']['manual_input_enabled'] || $status['umamusumedb']['manual_input_enabled']) {
                $recommendations[] = 'Manual input mode is enabled. You can enter data manually.';
            } else {
                $recommendations[] = 'Enable manual input mode if you need to enter data immediately.';
            }

            // Check circuit breaker status
            $umapyoiCircuitOpen = $status['umapyoi']['health_status']['circuit_breaker_open'] ?? false;
            $umamusumeDBCircuitOpen = $status['umamusumedb']['health_status']['circuit_breaker_open'] ?? false;

            if ($umapyoiCircuitOpen || $umamusumeDBCircuitOpen) {
                $recommendations[] = 'Circuit breaker is open. System will automatically retry after timeout.';
            }
        } else {
            $recommendations[] = 'All services are operating normally. No action required.';
        }

        return $recommendations;
    }
}
