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
        // Only proceed if Redis is configured and available
        if (! $this->isRedisConfigured()) {
            Log::debug('Redis cache optimization provider skipped - Redis not configured or available');

            return;
        }

        try {
            // Configure Redis connection settings
            $this->configureRedisConnections();

            // Set up cache warming for frequently accessed data
            $this->setupCacheWarming();

            // Configure cache tags for efficient invalidation
            $this->configureCacheTags();

            // Monitor Redis health
            $this->monitorRedisHealth();
        } catch (\Exception $e) {
            Log::error('Redis cache optimization provider boot failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't let Redis issues break the application boot
            // The application should continue to work without Redis optimization
        }
    }

    /**
     * Check if Redis is configured and available
     */
    private function isRedisConfigured(): bool
    {
        // Check if Redis is configured in the database config
        $redisConfig = config('database.redis.default');
        if (! is_array($redisConfig) || empty($redisConfig) || empty($redisConfig['host'])) {
            return false;
        }

        // Check if cache or session is using Redis
        $cacheStore = config('cache.default');
        $sessionDriver = config('session.driver');
        $queueConnection = config('queue.default');

        $isRedisUsed = in_array($cacheStore, ['redis']) ||
            in_array($sessionDriver, ['redis']) ||
            in_array($queueConnection, ['redis']);

        if (! $isRedisUsed) {
            return false;
        }

        // Try a quick connection test with a short timeout
        try {
            $redis = Redis::connection('default');
            $redis->ping();

            return true;
        } catch (\Exception $e) {
            Log::debug('Redis not available for optimization', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure Redis connections with optimized settings
     */
    private function configureRedisConnections(): void
    {
        try {
            // Test Redis connection with timeout
            $redis = Redis::connection('default');
            $redis->ping();

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

            // Don't let Redis connection failure break the application boot
            // The application should continue to work without Redis
        }
    }

    /**
     * Setup cache warming for frequently accessed data
     */
    private function setupCacheWarming(): void
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
    private function configureCacheTags(): void
    {
        // Define cache tag groups for efficient invalidation
        $this->app->instance('cache.tags', [
            'training' => ['training_predictions', 'training_sessions', 'training_options'],
            'character' => ['character_data', 'character_stats', 'character_aptitudes'],
            'skills' => ['skill_data', 'skill_hints', 'skill_costs'],
            'support_cards' => ['support_card_data', 'support_card_bonuses', 'deck_compositions'],
            'external_api' => ['umapyoi_data', 'umamusumedb_data', 'meta_data'],
            'ai' => ['ai_conversations', 'ai_predictions', 'ai_recommendations'],
            'mcp' => ['mcp_servers', 'mcp_agents', 'mcp_tools'],
        ]);
    }

    /**
     * Monitor Redis health and performance
     */
    private function monitorRedisHealth(): void
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
