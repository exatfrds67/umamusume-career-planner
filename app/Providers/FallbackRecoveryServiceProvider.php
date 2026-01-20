<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ExternalAPI\APIAlertingService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\BackgroundSyncService;
use App\Services\ExternalAPI\GracefulDegradationService;
use Illuminate\Support\ServiceProvider;

/**
 * Fallback and Recovery Service Provider
 *
 * Registers MCP-powered intelligent fallback and recovery services.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class FallbackRecoveryServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register API Health Monitor Service
        $this->app->singleton(APIHealthMonitorService::class, function ($app) {
            return new APIHealthMonitorService(
                $app->make(\App\Services\MCP\MCPClientService::class),
                $app->make(\App\Services\ExternalAPI\UmapyoiApiClient::class),
                $app->make(\App\Services\ExternalAPI\UmamusumeDBApiClient::class)
            );
        });

        // Register Graceful Degradation Service
        $this->app->singleton(GracefulDegradationService::class, function ($app) {
            return new GracefulDegradationService(
                $app->make(\App\Services\MCP\MCPClientService::class),
                $app->make(\App\Services\CacheManagementService::class),
                $app->make(APIHealthMonitorService::class)
            );
        });

        // Register Background Sync Service
        $this->app->singleton(BackgroundSyncService::class, function ($app) {
            return new BackgroundSyncService(
                $app->make(\App\Services\MCP\MCPClientService::class),
                $app->make(\App\Services\CacheManagementService::class),
                $app->make(\App\Services\ExternalAPI\ExternalAPIFacade::class),
                $app->make(GracefulDegradationService::class)
            );
        });

        // Register API Alerting Service
        $this->app->singleton(APIAlertingService::class, function ($app) {
            return new APIAlertingService(
                $app->make(\App\Services\MCP\MCPClientService::class),
                $app->make(APIHealthMonitorService::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
