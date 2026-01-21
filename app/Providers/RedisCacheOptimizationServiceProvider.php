<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class RedisCacheOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Redis cache optimization service
        $this->app->singleton('redis.cache.optimizer', function ($app) {
            return new \App\Services\RedisCacheOptimizationService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure Redis connection settings
        $this->configureRedisConnections();

        // Set up cache warming for frequently accessed data
        $this->setupCacheWarming();

        // Configure cache tags for efficient invalidation
        $this->configureCacheTags();

        // Monitor Redis health
        $this->monitorRedisHealth();
    }

    /**
     * Configure Redis connections with optimized settings
     */
    protected function configureRedisConnections(): void
    {
        try {
            // Test Redis connection
            Redis::connection('default')->ping();

            Log::info('Redis connection established successfully', [
                'host' => config('database.redis.default.host'),
                'port' => config('database.redis.default.port'),
                'database' => config('database.redis.default.database'),
            ]);
        } catch (\Exception $e) {
            Log::error('Redis connection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Setup cache warming for frequently accessed data
     */
    protected function setupCacheWarming(): void
    {
        // Cache warming will be handled by scheduled commands
        // This method sets up the foundation for cache warming strategies

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\WarmCache::class,
            ]);
        }
    }

    /**
     * Configure cache tags for efficient invalidation
     */
    protected function configureCacheTags(): void
    {
        // Define cache tag groups for efficient invalidation
        $this->app['cache.tags'] = [
            'training' => ['training_predictions', 'training_sessions', 'training_options'],
            'character' => ['character_data', 'character_stats', 'character_aptitudes'],
            'skills' => ['skill_data', 'skill_hints', 'skill_costs'],
            'support_cards' => ['support_card_data', 'support_card_bonuses', 'deck_compositions'],
            'external_api' => ['umapyoi_data', 'umamusumedb_data', 'meta_data'],
            'ai' => ['ai_conversations', 'ai_predictions', 'ai_recommendations'],
            'mcp' => ['mcp_servers', 'mcp_agents', 'mcp_tools'],
        ];
    }

    /**
     * Monitor Redis health and performance
     */
    protected function monitorRedisHealth(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        try {
            $info = Redis::connection('default')->info();

            // Log Redis memory usage
            if (isset($info['used_memory_human'])) {
                Log::debug('Redis memory usage', [
                    'used_memory' => $info['used_memory_human'],
                    'used_memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                    'mem_fragmentation_ratio' => $info['mem_fragmentation_ratio'] ?? 'N/A',
                ]);
            }

            // Check for high memory usage
            if (isset($info['used_memory']) && isset($info['maxmemory']) && $info['maxmemory'] > 0) {
                $usagePercent = ($info['used_memory'] / $info['maxmemory']) * 100;

                if ($usagePercent > 80) {
                    Log::warning('Redis memory usage high', [
                        'usage_percent' => round($usagePercent, 2),
                        'used_memory' => $info['used_memory_human'],
                        'max_memory' => $info['maxmemory_human'] ?? 'N/A',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Redis health check failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
