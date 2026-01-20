<?php

/**
 * API Performance Configuration
 *
 * Configuration settings for API performance optimization including:
 * - Response compression settings
 * - Rate limiting tiers for different user types
 * - API response caching configuration
 * - Performance monitoring thresholds
 *
 * @see Requirements: 52.3, 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Response Compression Settings
    |--------------------------------------------------------------------------
    |
    | Configure response compression for API endpoints.
    |
    */

    'compression' => [
        // Enable response compression
        'enabled' => env('API_COMPRESSION_ENABLED', true),

        // Compression level (1-9, higher = better compression, slower)
        'level' => env('API_COMPRESSION_LEVEL', 6),

        // Minimum response size for compression (bytes)
        'min_size' => env('API_COMPRESSION_MIN_SIZE', 1024),

        // Compressible content types
        'content_types' => [
            'application/json',
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/xml',
            'text/xml',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting tiers for different user types.
    | Rates are specified as requests per time window.
    |
    */

    'rate_limiting' => [
        // Enable rate limiting
        'enabled' => env('API_RATE_LIMITING_ENABLED', true),

        // Rate limit tiers
        'tiers' => [
            // Public/unauthenticated users
            'public' => [
                'requests_per_minute' => env('RATE_LIMIT_PUBLIC_PER_MINUTE', 60),
                'requests_per_hour' => env('RATE_LIMIT_PUBLIC_PER_HOUR', 500),
                'requests_per_day' => env('RATE_LIMIT_PUBLIC_PER_DAY', 5000),
                'burst_limit' => env('RATE_LIMIT_PUBLIC_BURST', 10),
            ],

            // Authenticated standard users
            'authenticated' => [
                'requests_per_minute' => env('RATE_LIMIT_AUTH_PER_MINUTE', 120),
                'requests_per_hour' => env('RATE_LIMIT_AUTH_PER_HOUR', 2000),
                'requests_per_day' => env('RATE_LIMIT_AUTH_PER_DAY', 20000),
                'burst_limit' => env('RATE_LIMIT_AUTH_BURST', 20),
            ],

            // Premium users (if applicable)
            'premium' => [
                'requests_per_minute' => env('RATE_LIMIT_PREMIUM_PER_MINUTE', 300),
                'requests_per_hour' => env('RATE_LIMIT_PREMIUM_PER_HOUR', 10000),
                'requests_per_day' => env('RATE_LIMIT_PREMIUM_PER_DAY', 100000),
                'burst_limit' => env('RATE_LIMIT_PREMIUM_BURST', 50),
            ],

            // Admin users
            'admin' => [
                'requests_per_minute' => env('RATE_LIMIT_ADMIN_PER_MINUTE', 600),
                'requests_per_hour' => env('RATE_LIMIT_ADMIN_PER_HOUR', 30000),
                'requests_per_day' => env('RATE_LIMIT_ADMIN_PER_DAY', 300000),
                'burst_limit' => env('RATE_LIMIT_ADMIN_BURST', 100),
            ],
        ],

        // Endpoint-specific rate limits (overrides tier limits)
        'endpoint_limits' => [
            // AI endpoints have stricter limits due to resource intensity
            'api/ai/*' => [
                'requests_per_minute' => 30,
                'requests_per_hour' => 300,
            ],

            // OCR endpoints have stricter limits
            'api/ocr/*' => [
                'requests_per_minute' => 10,
                'requests_per_hour' => 100,
            ],

            // Export endpoints
            'api/export/*' => [
                'requests_per_minute' => 5,
                'requests_per_hour' => 50,
            ],

            // Backup endpoints
            'api/backup/*' => [
                'requests_per_minute' => 5,
                'requests_per_hour' => 30,
            ],
        ],

        // Rate limit response headers
        'headers' => [
            'enabled' => true,
            'limit_header' => 'X-RateLimit-Limit',
            'remaining_header' => 'X-RateLimit-Remaining',
            'reset_header' => 'X-RateLimit-Reset',
        ],

        // Retry-After header value in seconds when rate limited
        'retry_after' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure intelligent caching for API responses.
    |
    */

    'cache' => [
        // Enable API response caching
        'enabled' => env('API_CACHE_ENABLED', true),

        // Default TTL in seconds
        'default_ttl' => env('API_CACHE_DEFAULT_TTL', 300),

        // TTL by endpoint pattern (in seconds)
        'ttl_by_endpoint' => [
            // Static game data - long TTL
            'api/skills*' => 86400, // 24 hours
            'api/support-cards*' => 86400, // 24 hours

            // Semi-static data
            'api/meta-rankings*' => 43200, // 12 hours

            // Dynamic calculations
            'api/training-predictions*' => 3600, // 1 hour
            'api/characters/*/recommendations*' => 1800, // 30 minutes

            // User-specific data
            'api/characters*' => 1800, // 30 minutes
            'api/careers*' => 1800, // 30 minutes

            // Real-time data - short TTL
            'api/performance*' => 60, // 1 minute
            'api/health*' => 30, // 30 seconds
        ],

        // Endpoints to exclude from caching
        'exclude_endpoints' => [
            'api/login',
            'api/logout',
            'api/register',
            'api/password/*',
            'api/ai/chat/*',
            'api/ocr/*',
            'api/export/*',
            'api/import/*',
            'api/backup/*',
        ],

        // Cache invalidation cascade rules
        'cascade_rules' => [
            // When characters change, invalidate related caches
            'characters' => ['training', 'careers', 'recommendations'],

            // When skills change, invalidate training calculations
            'skills' => ['training', 'recommendations'],

            // When support cards change, invalidate deck and training
            'support_cards' => ['deck', 'training', 'recommendations'],
        ],

        // Data type to tag mapping
        'data_type_tags' => [
            'characters' => ['api_response', 'characters'],
            'skills' => ['api_response', 'skills'],
            'training' => ['api_response', 'training'],
            'support_cards' => ['api_response', 'support_cards'],
            'careers' => ['api_response', 'careers'],
            'recommendations' => ['api_response', 'recommendations'],
            'deck' => ['api_response', 'deck'],
        ],

        // Cache warming configuration
        'warming' => [
            'enabled' => env('API_CACHE_WARMING_ENABLED', true),
            'schedule' => '0 */4 * * *', // Every 4 hours
            'endpoints' => [
                ['method' => 'GET', 'path' => '/api/skills'],
                ['method' => 'GET', 'path' => '/api/support-cards'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API performance monitoring and alerting.
    |
    */

    'monitoring' => [
        // Enable performance monitoring
        'enabled' => env('API_MONITORING_ENABLED', true),

        // Slow request threshold in milliseconds
        'slow_request_threshold_ms' => env('API_SLOW_REQUEST_THRESHOLD', 1000),

        // Very slow request threshold in milliseconds
        'very_slow_request_threshold_ms' => env('API_VERY_SLOW_REQUEST_THRESHOLD', 3000),

        // Maximum requests to track for metrics
        'max_tracked_requests' => env('API_MAX_TRACKED_REQUESTS', 1000),

        // Metrics retention period in seconds
        'metrics_retention' => env('API_METRICS_RETENTION', 86400), // 24 hours

        // Sample rate for detailed metrics (1.0 = 100%)
        'sample_rate' => env('API_METRICS_SAMPLE_RATE', 1.0),

        // Performance thresholds for alerts
        'thresholds' => [
            // Alert when average response time exceeds this (ms)
            'avg_response_time_ms' => 500,

            // Alert when error rate exceeds this (percentage)
            'error_rate_percent' => 5,

            // Alert when slow request rate exceeds this (percentage)
            'slow_request_rate_percent' => 10,

            // Alert when p95 response time exceeds this (ms)
            'p95_response_time_ms' => 2000,
        ],

        // Alerting configuration
        'alerts' => [
            'enabled' => env('API_ALERTS_ENABLED', true),
            'channels' => ['log'], // 'log', 'slack', 'mail'
            'cooldown_seconds' => 300, // Minimum time between alerts
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Batching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure request batching for improved efficiency.
    |
    */

    'batching' => [
        // Enable request batching
        'enabled' => env('API_BATCHING_ENABLED', true),

        // Maximum requests per batch
        'max_batch_size' => env('API_MAX_BATCH_SIZE', 10),

        // Batch timeout in milliseconds
        'timeout_ms' => env('API_BATCH_TIMEOUT', 5000),

        // Endpoints that support batching
        'supported_endpoints' => [
            'api/characters',
            'api/skills',
            'api/support-cards',
            'api/training-predictions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pooling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure connection pooling for external API integrations.
    |
    */

    'connection_pooling' => [
        // Enable connection pooling
        'enabled' => env('API_CONNECTION_POOLING_ENABLED', true),

        // Maximum connections per host
        'max_connections' => env('API_MAX_CONNECTIONS', 10),

        // Connection timeout in seconds
        'connect_timeout' => env('API_CONNECT_TIMEOUT', 5),

        // Request timeout in seconds
        'request_timeout' => env('API_REQUEST_TIMEOUT', 30),

        // Keep-alive timeout in seconds
        'keep_alive_timeout' => env('API_KEEP_ALIVE_TIMEOUT', 60),
    ],

];
