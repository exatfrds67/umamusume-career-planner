<?php

namespace App\Providers;

use App\Services\MCP\MCPClientService;
use App\Services\MCP\Tools\AWSAPIService;
use App\Services\MCP\Tools\AWSKnowledgeService;
use App\Services\MCP\Tools\AWSPricingService;
use App\Services\MCP\Tools\Context7Service;
use App\Services\MCP\Tools\FetchService;
use App\Services\MCP\Tools\ToolChainingService;
use Illuminate\Support\ServiceProvider;

/**
 * MCP Tools Service Provider
 *
 * Registers MCP tool services for dependency injection.
 *
 * Requirements: 13.4, 56.2
 */
class MCPToolsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register AWS Pricing Service
        $this->app->singleton(AWSPricingService::class, function ($app) {
            return new AWSPricingService(
                $app->make(MCPClientService::class)
            );
        });

        // Register AWS Knowledge Service
        $this->app->singleton(AWSKnowledgeService::class, function ($app) {
            return new AWSKnowledgeService(
                $app->make(MCPClientService::class)
            );
        });

        // Register AWS API Service
        $this->app->singleton(AWSAPIService::class, function ($app) {
            return new AWSAPIService(
                $app->make(MCPClientService::class)
            );
        });

        // Register Context7 Service
        $this->app->singleton(Context7Service::class, function ($app) {
            return new Context7Service(
                $app->make(MCPClientService::class)
            );
        });

        // Register Fetch Service
        $this->app->singleton(FetchService::class, function ($app) {
            return new FetchService(
                $app->make(MCPClientService::class)
            );
        });

        // Register Tool Chaining Service
        $this->app->singleton(ToolChainingService::class, function ($app) {
            return new ToolChainingService(
                $app->make(MCPClientService::class),
                $app->make(AWSPricingService::class),
                $app->make(AWSKnowledgeService::class),
                $app->make(AWSAPIService::class),
                $app->make(Context7Service::class),
                $app->make(FetchService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mcp_tools.php',
            'mcp.tools'
        );

        // Publish configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/mcp_tools.php' => config_path('mcp_tools.php'),
            ], 'mcp-tools-config');
        }
    }
}
