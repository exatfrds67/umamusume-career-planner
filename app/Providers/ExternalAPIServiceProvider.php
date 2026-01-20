<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ExternalAPI\Context7Service;
use App\Services\ExternalAPI\ExternalAPIFacade;
use App\Services\ExternalAPI\UmamusumeDBApiClient;
use App\Services\ExternalAPI\UmapyoiApiClient;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\ServiceProvider;

/**
 * External API Service Provider
 *
 * Registers external API client services with dependency injection.
 *
 * Requirements: Task 4.4.1
 */
class ExternalAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register UmapyoiApiClient
        $this->app->singleton(UmapyoiApiClient::class, function ($app) {
            return new UmapyoiApiClient(
                $app->make(MCPClientService::class),
                $app->make(\App\Services\CacheManagementService::class)
            );
        });

        // Register UmamusumeDBApiClient
        $this->app->singleton(UmamusumeDBApiClient::class, function ($app) {
            return new UmamusumeDBApiClient(
                $app->make(MCPClientService::class)
            );
        });

        // Register Context7Service
        $this->app->singleton(Context7Service::class, function ($app) {
            return new Context7Service(
                $app->make(MCPClientService::class)
            );
        });

        // Register ExternalAPIFacade
        $this->app->singleton(ExternalAPIFacade::class, function ($app) {
            return new ExternalAPIFacade(
                $app->make(UmapyoiApiClient::class),
                $app->make(UmamusumeDBApiClient::class),
                $app->make(Context7Service::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/external-apis.php' => config_path('external-apis.php'),
        ], 'external-apis-config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            UmapyoiApiClient::class,
            UmamusumeDBApiClient::class,
            Context7Service::class,
            ExternalAPIFacade::class,
        ];
    }
}
