<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CacheManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\ServiceProvider;

/**
 * Cache Service Provider
 *
 * Registers cache management services with MCP integration.
 *
 * Requirements: 14.5, 55.3, 56.4, Task 4.4.2
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CacheManagementService::class, function ($app) {
            return new CacheManagementService(
                $app->make(MCPClientService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
