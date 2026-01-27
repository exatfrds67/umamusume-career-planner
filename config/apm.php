<?php

/**
 * Application Performance Monitoring (APM) Configuration
 *
 * Configuration settings for comprehensive APM including:
 * - Real-time performance metrics aggregation
 * - Automated alerting and notifications
 * - Performance regression detection
 * - Dashboard configuration
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */

return [

    /*
    |--------------------------------------------------------------------------
    | APM General Settings
    |--------------------------------------------------------------------------
    |
    | General configuration for the APM system.
    |
    */

    'enabled' => env('APM_ENABLED', true),

    // APM data storage prefix
    'prefix' => env('APM_PREFIX', 'apm:'),

    // Data retention period in seconds
    'retention_period' => env('APM_RETENTION_PERIOD', 604800), // 7 days

    // Metrics collection interval in seconds
    'collection_interval' => env('APM_COLLECTION_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | Metrics Aggregation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how metrics are aggregated from various sources.
    |
    */

    'aggregation' => [
        // Enable metrics aggregation
        'enabled' => env('APM_AGGREGATION_ENABLED', true),

        // Aggregation interval in seconds
        'interval' => env('APM_AGGREGATION_INTERVAL', 60),

        // Sources to aggregate from
        'sources' => [
            'database' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'redis' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'api' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'system' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
        ],

        // Metrics to collect
        'metrics' => [
            'response_time' => true,
            'throughput' => true,
            'error_rate' => true,
            'memory_usage' => true,
            'cpu_usage' => true,
            'cache_hit_rate' => true,
            'database_connections' => true,
            'slow_queries' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated performance alerts and notifications.
    |
    */

    'alerting' => [
        // Enable alerting
        'enabled' => env('APM_ALERTING_ENABLED', true),

        // Alert check interval in seconds
        'check_interval' => env('APM_ALERT_CHECK_INTERVAL', 60),

        // Notification channels
        'channels' => [
            'log' => [
                'enabled' => true,
                'level' => 'warning',
            ],
            'database' => [
                'enabled' => true,
                'table' => 'ucp_system_logs',
            ],
            'slack' => [
                'enabled' => env('APM_SLACK_ENABLED', false),
                'webhook_url' => env('APM_SLACK_WEBHOOK'),
            ],
            'mail' => [
                'enabled' => env('APM_MAIL_ENABLED', false),
                'recipients' => explode(',', env('APM_MAIL_RECIPIENTS', 'admin@example.com')),
            ],
        ],

        // Alert thresholds
        'thresholds' => [
            // Response time thresholds (milliseconds)
            'response_time' => [
                'warning' => env('APM_ALERT_RESPONSE_TIME_WARNING', 1000),
                'critical' => env('APM_ALERT_RESPONSE_TIME_CRITICAL', 3000),
            ],

            // Error rate thresholds (percentage)
            'error_rate' => [
                'warning' => env('APM_ALERT_ERROR_RATE_WARNING', 5),
                'critical' => env('APM_ALERT_ERROR_RATE_CRITICAL', 10),
            ],

            // Memory usage thresholds (percentage)
            'memory_usage' => [
                'warning' => env('APM_ALERT_MEMORY_WARNING', 70),
                'critical' => env('APM_ALERT_MEMORY_CRITICAL', 90),
            ],

            // Cache hit rate thresholds (percentage, alert when below)
            'cache_hit_rate' => [
                'warning' => env('APM_ALERT_CACHE_HIT_WARNING', 70),
                'critical' => env('APM_ALERT_CACHE_HIT_CRITICAL', 50),
            ],

            // Slow query count thresholds (per minute)
            'slow_queries' => [
                'warning' => env('APM_ALERT_SLOW_QUERIES_WARNING', 10),
                'critical' => env('APM_ALERT_SLOW_QUERIES_CRITICAL', 25),
            ],

            // Database connection thresholds (percentage of max)
            'database_connections' => [
                'warning' => env('APM_ALERT_DB_CONN_WARNING', 70),
                'critical' => env('APM_ALERT_DB_CONN_CRITICAL', 90),
            ],

            // Throughput thresholds (requests per minute, alert when below)
            'throughput' => [
                'warning' => env('APM_ALERT_THROUGHPUT_WARNING', 10),
                'critical' => env('APM_ALERT_THROUGHPUT_CRITICAL', 1),
            ],
        ],

        // Alert cooldown period in seconds (prevent alert spam)
        'cooldown' => env('APM_ALERT_COOLDOWN', 300),

        // Maximum alerts per hour
        'max_alerts_per_hour' => env('APM_MAX_ALERTS_PER_HOUR', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Regression Detection Configuration
    |--------------------------------------------------------------------------
    |
    | Configure performance regression detection and reporting.
    |
    */

    'regression' => [
        // Enable regression detection
        'enabled' => env('APM_REGRESSION_ENABLED', true),

        // Baseline calculation period in hours
        'baseline_period_hours' => env('APM_BASELINE_PERIOD', 24),

        // Minimum data points required for baseline
        'min_data_points' => env('APM_MIN_DATA_POINTS', 100),

        // Regression detection thresholds (percentage deviation from baseline)
        'thresholds' => [
            // Response time regression threshold
            'response_time' => env('APM_REGRESSION_RESPONSE_TIME', 25),

            // Error rate regression threshold
            'error_rate' => env('APM_REGRESSION_ERROR_RATE', 50),

            // Throughput regression threshold (decrease)
            'throughput' => env('APM_REGRESSION_THROUGHPUT', 30),

            // Memory usage regression threshold
            'memory_usage' => env('APM_REGRESSION_MEMORY', 20),
        ],

        // Statistical significance level (0.05 = 95% confidence)
        'significance_level' => env('APM_SIGNIFICANCE_LEVEL', 0.05),

        // Regression check interval in minutes
        'check_interval' => env('APM_REGRESSION_CHECK_INTERVAL', 15),

        // Auto-report regressions
        'auto_report' => env('APM_REGRESSION_AUTO_REPORT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the real-time performance metrics dashboard.
    |
    */

    'dashboard' => [
        // Enable dashboard
        'enabled' => env('APM_DASHBOARD_ENABLED', true),

        // Dashboard refresh interval in seconds
        'refresh_interval' => env('APM_DASHBOARD_REFRESH', 30),

        // Time ranges available for selection
        'time_ranges' => [
            '1h' => 3600,
            '6h' => 21600,
            '24h' => 86400,
            '7d' => 604800,
            '30d' => 2592000,
        ],

        // Default time range
        'default_time_range' => '24h',

        // Widgets to display
        'widgets' => [
            'overview' => [
                'enabled' => true,
                'position' => 1,
            ],
            'response_time' => [
                'enabled' => true,
                'position' => 2,
            ],
            'throughput' => [
                'enabled' => true,
                'position' => 3,
            ],
            'error_rate' => [
                'enabled' => true,
                'position' => 4,
            ],
            'database' => [
                'enabled' => true,
                'position' => 5,
            ],
            'cache' => [
                'enabled' => true,
                'position' => 6,
            ],
            'alerts' => [
                'enabled' => true,
                'position' => 7,
            ],
            'regressions' => [
                'enabled' => true,
                'position' => 8,
            ],
        ],

        // Chart configuration
        'charts' => [
            'max_data_points' => 100,
            'smoothing' => true,
            'show_annotations' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Score Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the overall application health score calculation.
    |
    */

    'health_score' => [
        // Enable health score calculation
        'enabled' => true,

        // Component weights for health score (must sum to 1.0)
        'weights' => [
            'response_time' => 0.25,
            'error_rate' => 0.25,
            'cache_hit_rate' => 0.15,
            'database_health' => 0.15,
            'memory_usage' => 0.10,
            'throughput' => 0.10,
        ],

        // Health score thresholds
        'thresholds' => [
            'excellent' => 90,
            'good' => 75,
            'fair' => 60,
            'poor' => 40,
            // Below 40 is critical
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated performance reports.
    |
    */

    'reporting' => [
        // Enable automated reports
        'enabled' => env('APM_REPORTING_ENABLED', true),

        // Report generation schedule (cron expression)
        'schedule' => env('APM_REPORT_SCHEDULE', '0 8 * * *'), // Daily at 8 AM

        // Report types
        'types' => [
            'daily_summary' => true,
            'weekly_summary' => true,
            'regression_report' => true,
            'alert_summary' => true,
        ],

        // Report storage path
        'storage_path' => storage_path('app/apm-reports'),

        // Report retention in days
        'retention_days' => env('APM_REPORT_RETENTION', 30),
    ],

];
