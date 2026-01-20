<?php

/**
 * Query Optimization Configuration
 *
 * Configuration settings for database query optimization, slow query monitoring,
 * and automated optimization alerts.
 *
 * @see Requirements: 17.3, 50.2, 50.3
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold Settings
    |--------------------------------------------------------------------------
    |
    | Configure thresholds for identifying slow queries. Queries exceeding
    | these thresholds will be logged and flagged for optimization.
    |
    */

    'slow_query' => [
        // Threshold in milliseconds for warning-level slow queries
        'warning_threshold_ms' => env('SLOW_QUERY_WARNING_MS', 100),

        // Threshold in milliseconds for critical-level slow queries
        'critical_threshold_ms' => env('SLOW_QUERY_CRITICAL_MS', 500),

        // Maximum number of slow queries to store in memory
        'max_stored_queries' => env('SLOW_QUERY_MAX_STORED', 100),

        // Enable/disable slow query logging
        'logging_enabled' => env('SLOW_QUERY_LOGGING', true),

        // Log channel for slow queries
        'log_channel' => env('SLOW_QUERY_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Result Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for database query results.
    |
    */

    'cache' => [
        // Default TTL for cached query results (in seconds)
        'default_ttl' => env('QUERY_CACHE_TTL', 300),

        // TTL for frequently accessed data (in seconds)
        'frequent_access_ttl' => env('QUERY_CACHE_FREQUENT_TTL', 600),

        // TTL for rarely changing data (in seconds)
        'static_data_ttl' => env('QUERY_CACHE_STATIC_TTL', 3600),

        // Cache prefix for query results
        'prefix' => env('QUERY_CACHE_PREFIX', 'query_cache:'),

        // Enable/disable query result caching
        'enabled' => env('QUERY_CACHE_ENABLED', true),

        // Cache driver to use
        'driver' => env('QUERY_CACHE_DRIVER', 'redis'),

        // Tables that should always be cached
        'always_cache_tables' => [
            'skills',
            'support_cards',
            'external_data',
        ],

        // Tables that should never be cached
        'never_cache_tables' => [
            'sessions',
            'cache',
            'jobs',
            'failed_jobs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Index Recommendation Settings
    |--------------------------------------------------------------------------
    |
    | Configure the index recommendation engine behavior.
    |
    */

    'indexing' => [
        // Minimum query count before recommending an index
        'min_query_count' => env('INDEX_MIN_QUERY_COUNT', 10),

        // Minimum average execution time (ms) before recommending an index
        'min_avg_execution_ms' => env('INDEX_MIN_AVG_EXECUTION_MS', 50),

        // Enable automatic index recommendations
        'auto_recommend' => env('INDEX_AUTO_RECOMMEND', true),

        // Tables to exclude from index recommendations
        'excluded_tables' => [
            'migrations',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ],

        // Maximum number of index recommendations to store
        'max_recommendations' => env('INDEX_MAX_RECOMMENDATIONS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pooling Settings
    |--------------------------------------------------------------------------
    |
    | Configure database connection pooling behavior.
    |
    */

    'connection_pooling' => [
        // Enable connection pooling
        'enabled' => env('DB_POOL_ENABLED', true),

        // Maximum number of connections in the pool
        'max_connections' => env('DB_POOL_MAX_CONNECTIONS', 10),

        // Minimum number of connections to maintain
        'min_connections' => env('DB_POOL_MIN_CONNECTIONS', 2),

        // Connection timeout in seconds
        'connection_timeout' => env('DB_POOL_TIMEOUT', 30),

        // Idle connection timeout in seconds
        'idle_timeout' => env('DB_POOL_IDLE_TIMEOUT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting Settings
    |--------------------------------------------------------------------------
    |
    | Configure automated monitoring and alerting for query performance.
    |
    */

    'monitoring' => [
        // Enable performance monitoring
        'enabled' => env('QUERY_MONITORING_ENABLED', true),

        // Interval for collecting metrics (in seconds)
        'collection_interval' => env('QUERY_MONITORING_INTERVAL', 60),

        // Enable automated alerts
        'alerts_enabled' => env('QUERY_ALERTS_ENABLED', true),

        // Alert thresholds
        'alert_thresholds' => [
            // Alert when average query time exceeds this (ms)
            'avg_query_time_ms' => env('ALERT_AVG_QUERY_TIME_MS', 200),

            // Alert when slow query count exceeds this per minute
            'slow_queries_per_minute' => env('ALERT_SLOW_QUERIES_PER_MIN', 10),

            // Alert when cache hit rate falls below this percentage
            'min_cache_hit_rate' => env('ALERT_MIN_CACHE_HIT_RATE', 70),

            // Alert when connection pool utilization exceeds this percentage
            'max_pool_utilization' => env('ALERT_MAX_POOL_UTILIZATION', 80),
        ],

        // Notification channels for alerts
        'notification_channels' => [
            'log',
            // 'slack',
            // 'mail',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Analysis Settings
    |--------------------------------------------------------------------------
    |
    | Configure query analysis and profiling behavior.
    |
    */

    'analysis' => [
        // Enable query analysis
        'enabled' => env('QUERY_ANALYSIS_ENABLED', true),

        // Store query execution plans
        'store_execution_plans' => env('QUERY_STORE_PLANS', true),

        // Maximum number of execution plans to store
        'max_stored_plans' => env('QUERY_MAX_PLANS', 100),

        // Analyze queries with these patterns
        'analyze_patterns' => [
            'SELECT',
            'UPDATE',
            'DELETE',
            'INSERT',
        ],

        // Exclude queries matching these patterns
        'exclude_patterns' => [
            '/^SHOW/',
            '/^DESCRIBE/',
            '/^EXPLAIN/',
            '/information_schema/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Metrics Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long performance metrics are retained.
    |
    */

    'retention' => [
        // Days to retain detailed query logs
        'detailed_logs_days' => env('QUERY_RETENTION_DETAILED', 7),

        // Days to retain aggregated metrics
        'aggregated_metrics_days' => env('QUERY_RETENTION_AGGREGATED', 30),

        // Days to retain slow query logs
        'slow_query_logs_days' => env('QUERY_RETENTION_SLOW', 14),

        // Enable automatic cleanup
        'auto_cleanup' => env('QUERY_AUTO_CLEANUP', true),
    ],

];
