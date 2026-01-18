<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Tools Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MCP tool integrations including AWS services,
    | context management, and external API integration.
    |
    */

    'aws_pricing' => [
        'enabled' => env('MCP_AWS_PRICING_ENABLED', true),
        'cache_ttl' => env('MCP_AWS_PRICING_CACHE_TTL', 3600),
    ],

    'aws_knowledge' => [
        'enabled' => env('MCP_AWS_KNOWLEDGE_ENABLED', true),
        'cache_ttl' => env('MCP_AWS_KNOWLEDGE_CACHE_TTL', 7200),
    ],

    'aws_api' => [
        'enabled' => env('MCP_AWS_API_ENABLED', true),
        'cache_ttl' => env('MCP_AWS_API_CACHE_TTL', 300),
    ],

    'context7' => [
        'enabled' => env('MCP_CONTEXT7_ENABLED', true),
        'cache_ttl' => env('MCP_CONTEXT7_CACHE_TTL', 600),
        'max_context_size' => env('MCP_CONTEXT7_MAX_SIZE', 10000),
        'retention_days' => env('MCP_CONTEXT7_RETENTION_DAYS', 30),
    ],

    'fetch' => [
        'enabled' => env('MCP_FETCH_ENABLED', true),
        'cache_ttl' => env('MCP_FETCH_CACHE_TTL', 3600),
        'timeout' => env('MCP_FETCH_TIMEOUT', 30),
        'max_retries' => env('MCP_FETCH_MAX_RETRIES', 3),
        'retry_delay' => env('MCP_FETCH_RETRY_DELAY', 1000),
    ],

    'chaining' => [
        'enabled' => env('MCP_CHAINING_ENABLED', true),
        'templates' => [
            'cost_optimization' => [
                'description' => 'Analyze and optimize AI costs',
                'parameters' => [
                    'usage_patterns' => 'Array of usage data',
                ],
                'steps' => [
                    [
                        'tool' => 'aws_pricing',
                        'method' => 'getCostOptimizationRecommendations',
                        'params' => ['usage_patterns' => '{usage_patterns}'],
                    ],
                    [
                        'tool' => 'aws_knowledge',
                        'method' => 'getBedrockOptimizations',
                        'params' => [],
                    ],
                ],
            ],
            'data_sync' => [
                'description' => 'Sync data from external APIs',
                'parameters' => [
                    'endpoints' => 'Array of API endpoints',
                ],
                'steps' => [
                    [
                        'tool' => 'fetch',
                        'method' => 'batchFetch',
                        'params' => ['requests' => '{endpoints}'],
                    ],
                ],
            ],
        ],
    ],
];
