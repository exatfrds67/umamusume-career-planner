<?php

/**
 * Cache Management Configuration
 *
 * Configuration settings for Redis caching optimization, cache warming,
 * memory management, and intelligent invalidation strategies.
 *
 * @see Requirements: 17.4, 59.2
 * @see Task: 6.1.2 Redis caching optimization and strategy
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Redis Connection Settings
    |--------------------------------------------------------------------------
    |
    | Configure Redis connection parameters for optimal performance.
    |
    */

    'redis' => [
        // Primary Redis connection name
        'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),

        // Connection timeout in seconds
        'timeout' => env('REDIS_TIMEOUT', 5),

        // Read timeout in seconds
        'read_timeout' => env('REDIS_READ_TIMEOUT', 5),

        // Retry interval in milliseconds
        'retry_interval' => env('REDIS_RETRY_INTERVAL', 100),

        // Maximum retries for failed operations
        'max_retries' => env('REDIS_MAX_RETRIES', 3),

        // Enable persistent connections
        'persistent' => env('REDIS_PERSISTENT', true),

        // Connection pool size (for phpredis)
        'pool_size' => env('REDIS_POOL_SIZE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache warming schedules and strategies for frequently
    | accessed data to improve application performance.
    |
    */

    'warming' => [
        // Enable automatic cache warming
        'enabled' => env('CACHE_WARMING_ENABLED', true),

        // Batch size for warming operations
        'batch_size' => env('CACHE_WARMING_BATCH_SIZE', 50),

        // Concurrent warming workers
        'concurrency' => env('CACHE_WARMING_CONCURRENCY', 3),

        // Warming schedule (cron expression)
        'schedule' => env('CACHE_WARMING_SCHEDULE', '0 */6 * * *'), // Every 6 hours

        // Priority data types for warming (ordered by priority)
        'priority_types' => [
            'skills' => [
                'enabled' => true,
                'ttl' => 86400, // 24 hours
                'priority' => 1,
            ],
            'support_cards' => [
                'enabled' => true,
                'ttl' => 86400, // 24 hours
                'priority' => 2,
            ],
            'meta_rankings' => [
                'enabled' => true,
                'ttl' => 43200, // 12 hours
                'priority' => 3,
            ],
            'characters' => [
                'enabled' => true,
                'ttl' => 86400, // 24 hours
                'priority' => 4,
            ],
            'training_calculations' => [
                'enabled' => true,
                'ttl' => 3600, // 1 hour
                'priority' => 5,
            ],
        ],

        // Maximum warming duration in seconds
        'max_duration' => env('CACHE_WARMING_MAX_DURATION', 300),

        // Warming failure threshold before alerting
        'failure_threshold' => env('CACHE_WARMING_FAILURE_THRESHOLD', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Management Settings
    |--------------------------------------------------------------------------
    |
    | Configure memory thresholds and optimization strategies for Redis.
    |
    */

    'memory' => [
        // Maximum memory usage threshold (percentage)
        'max_usage_percent' => env('REDIS_MAX_MEMORY_PERCENT', 80),

        // Warning threshold (percentage)
        'warning_threshold_percent' => env('REDIS_WARNING_MEMORY_PERCENT', 70),

        // Critical threshold (percentage)
        'critical_threshold_percent' => env('REDIS_CRITICAL_MEMORY_PERCENT', 90),

        // Enable automatic eviction when threshold exceeded
        'auto_eviction' => env('REDIS_AUTO_EVICTION', true),

        // Eviction policy (volatile-lru, allkeys-lru, volatile-ttl, etc.)
        'eviction_policy' => env('REDIS_EVICTION_POLICY', 'volatile-lru'),

        // Memory check interval in seconds
        'check_interval' => env('REDIS_MEMORY_CHECK_INTERVAL', 60),

        // Maximum memory limit in bytes (0 = no limit)
        'max_memory_bytes' => env('REDIS_MAX_MEMORY', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | TTL Configuration by Data Type
    |--------------------------------------------------------------------------
    |
    | Configure Time-To-Live settings for different data types.
    |
    */

    'ttl' => [
        // Default TTL in seconds
        'default' => env('CACHE_DEFAULT_TTL', 3600),

        // TTL by data type
        'by_type' => [
            // Static game data (rarely changes)
            'skills' => env('CACHE_TTL_SKILLS', 86400), // 24 hours
            'support_cards' => env('CACHE_TTL_SUPPORT_CARDS', 86400), // 24 hours
            'aptitudes' => env('CACHE_TTL_APTITUDES', 86400), // 24 hours
            'races' => env('CACHE_TTL_RACES', 86400), // 24 hours

            // Semi-static data (changes occasionally)
            'meta_rankings' => env('CACHE_TTL_META_RANKINGS', 43200), // 12 hours
            'external_api' => env('CACHE_TTL_EXTERNAL_API', 21600), // 6 hours

            // Dynamic data (changes frequently)
            'training_calculations' => env('CACHE_TTL_TRAINING', 3600), // 1 hour
            'race_strategies' => env('CACHE_TTL_RACE_STRATEGIES', 7200), // 2 hours
            'character_stats' => env('CACHE_TTL_CHARACTER_STATS', 1800), // 30 minutes

            // Session-related data
            'user_preferences' => env('CACHE_TTL_USER_PREFS', 3600), // 1 hour
            'ai_conversations' => env('CACHE_TTL_AI_CONVERSATIONS', 1800), // 30 minutes

            // Short-lived data
            'news' => env('CACHE_TTL_NEWS', 3600), // 1 hour
            'health_checks' => env('CACHE_TTL_HEALTH', 60), // 1 minute
        ],

        // Minimum TTL allowed
        'min' => env('CACHE_MIN_TTL', 60),

        // Maximum TTL allowed
        'max' => env('CACHE_MAX_TTL', 604800), // 7 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Invalidation Strategies
    |--------------------------------------------------------------------------
    |
    | Configure cache invalidation strategies and tag management.
    |
    */

    'invalidation' => [
        // Enable tag-based invalidation
        'tags_enabled' => env('CACHE_TAGS_ENABLED', true),

        // Default tags for data types
        'default_tags' => [
            'skills' => ['game_data', 'skills', 'static'],
            'support_cards' => ['game_data', 'support_cards', 'static'],
            'characters' => ['user_data', 'characters'],
            'training_calculations' => ['calculations', 'training'],
            'race_strategies' => ['calculations', 'races'],
            'meta_rankings' => ['game_data', 'meta', 'rankings'],
            'external_api' => ['external', 'api'],
            'user_preferences' => ['user_data', 'preferences'],
        ],

        // Cascade invalidation rules
        'cascade_rules' => [
            // When skills change, invalidate training calculations
            'skills' => ['training_calculations', 'race_strategies'],
            // When support cards change, invalidate training calculations
            'support_cards' => ['training_calculations'],
            // When meta rankings change, invalidate recommendations
            'meta_rankings' => ['recommendations'],
        ],

        // Invalidation batch size
        'batch_size' => env('CACHE_INVALIDATION_BATCH_SIZE', 100),

        // Delay between batch invalidations (milliseconds)
        'batch_delay_ms' => env('CACHE_INVALIDATION_BATCH_DELAY', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hit Rate Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure cache hit rate tracking and optimization recommendations.
    |
    */

    'monitoring' => [
        // Enable hit rate monitoring
        'enabled' => env('CACHE_MONITORING_ENABLED', true),

        // Metrics retention period in seconds
        'retention_period' => env('CACHE_METRICS_RETENTION', 86400), // 24 hours

        // Minimum hit rate threshold for alerts (percentage)
        'min_hit_rate' => env('CACHE_MIN_HIT_RATE', 70),

        // Sample rate for metrics collection (1 = 100%, 0.1 = 10%)
        'sample_rate' => env('CACHE_METRICS_SAMPLE_RATE', 1.0),

        // Metrics aggregation interval in seconds
        'aggregation_interval' => env('CACHE_METRICS_AGGREGATION', 60),

        // Enable detailed per-key metrics
        'detailed_metrics' => env('CACHE_DETAILED_METRICS', true),

        // Maximum keys to track for detailed metrics
        'max_tracked_keys' => env('CACHE_MAX_TRACKED_KEYS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup for expired and stale cache data.
    |
    */

    'cleanup' => [
        // Enable automatic cleanup
        'enabled' => env('CACHE_CLEANUP_ENABLED', true),

        // Cleanup schedule (cron expression)
        'schedule' => env('CACHE_CLEANUP_SCHEDULE', '0 3 * * *'), // Daily at 3 AM

        // Maximum keys to scan per cleanup run
        'max_keys_per_run' => env('CACHE_CLEANUP_MAX_KEYS', 10000),

        // Stale data threshold in seconds (data not accessed)
        'stale_threshold' => env('CACHE_STALE_THRESHOLD', 604800), // 7 days

        // Enable orphan key detection
        'detect_orphans' => env('CACHE_DETECT_ORPHANS', true),

        // Cleanup batch size
        'batch_size' => env('CACHE_CLEANUP_BATCH_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Configure performance optimization settings.
    |
    */

    'optimization' => [
        // Enable compression for large values
        'compression_enabled' => env('CACHE_COMPRESSION_ENABLED', true),

        // Minimum size for compression (bytes)
        'compression_threshold' => env('CACHE_COMPRESSION_THRESHOLD', 1024),

        // Compression level (1-9, higher = better compression, slower)
        'compression_level' => env('CACHE_COMPRESSION_LEVEL', 6),

        // Enable pipelining for batch operations
        'pipelining_enabled' => env('CACHE_PIPELINING_ENABLED', true),

        // Maximum pipeline size
        'max_pipeline_size' => env('CACHE_MAX_PIPELINE_SIZE', 100),

        // Enable Lua scripting for atomic operations
        'lua_scripting' => env('CACHE_LUA_SCRIPTING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure alerting for cache-related issues.
    |
    */

    'alerts' => [
        // Enable alerting
        'enabled' => env('CACHE_ALERTS_ENABLED', true),

        // Alert channels
        'channels' => [
            'log',
            // 'slack',
            // 'mail',
        ],

        // Alert thresholds
        'thresholds' => [
            // Alert when hit rate falls below this percentage
            'low_hit_rate' => env('ALERT_LOW_HIT_RATE', 60),

            // Alert when memory usage exceeds this percentage
            'high_memory' => env('ALERT_HIGH_MEMORY', 85),

            // Alert when connection errors exceed this count per minute
            'connection_errors' => env('ALERT_CONNECTION_ERRORS', 5),

            // Alert when cache warming fails this many times
            'warming_failures' => env('ALERT_WARMING_FAILURES', 3),
        ],

        // Cooldown period between alerts (seconds)
        'cooldown' => env('CACHE_ALERT_COOLDOWN', 300),
    ],

];
