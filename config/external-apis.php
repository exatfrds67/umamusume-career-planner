<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external API integrations including umapyoi.net and
    | UmamusumeDB.com with MCP-enhanced capabilities.
    |
    */

    'umapyoi' => [
        'url' => env('UMAPYOI_API_URL', 'https://api.umapyoi.net'),
        'timeout' => env('UMAPYOI_API_TIMEOUT', 30),
        'enabled' => env('UMAPYOI_API_ENABLED', true),
        'cache_ttl' => env('UMAPYOI_CACHE_TTL', 86400), // 24 hours
        'retry' => [
            'max_attempts' => env('UMAPYOI_MAX_RETRIES', 3),
            'delay_ms' => env('UMAPYOI_RETRY_DELAY', 1000),
        ],
    ],

    'umamusumedb' => [
        'url' => env('UMAMUSUMEDB_API_URL', 'https://api.umamusumedb.com'),
        'timeout' => env('UMAMUSUMEDB_API_TIMEOUT', 30),
        'enabled' => env('UMAMUSUMEDB_API_ENABLED', true),
        'cache_ttl' => env('UMAMUSUMEDB_CACHE_TTL', 43200), // 12 hours
        'retry' => [
            'max_attempts' => env('UMAMUSUMEDB_MAX_RETRIES', 5),
            'initial_delay_ms' => env('UMAMUSUMEDB_INITIAL_RETRY_DELAY', 500),
            'max_delay_ms' => env('UMAMUSUMEDB_MAX_RETRY_DELAY', 10000),
        ],
    ],

    'context7' => [
        'enabled' => env('CONTEXT7_ENABLED', true),
        'cache_ttl' => env('CONTEXT7_CACHE_TTL', 1800), // 30 minutes
        'max_history' => env('CONTEXT7_MAX_HISTORY', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for intelligent fallback behavior when primary APIs fail.
    |
    */

    'fallback' => [
        'enabled' => env('API_FALLBACK_ENABLED', true),
        'manual_input_mode' => env('API_FALLBACK_MANUAL_INPUT', true),
        'cache_stale_threshold' => env('API_FALLBACK_STALE_THRESHOLD', 604800), // 7 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for API health monitoring and alerting.
    |
    */

    'health' => [
        'check_interval' => env('API_HEALTH_CHECK_INTERVAL', 300), // 5 minutes
        'failure_threshold' => env('API_HEALTH_FAILURE_THRESHOLD', 3),
        'alert_enabled' => env('API_HEALTH_ALERT_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration for API rate limiting to prevent abuse.
    |
    */

    'rate_limit' => [
        'enabled' => env('API_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'max_requests_per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
    ],

];
