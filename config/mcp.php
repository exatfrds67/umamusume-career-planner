<?php

return [
    'enabled' => env('MCP_ENABLED', true),
    'debug' => env('MCP_DEBUG', false),

    'servers' => [
        'strands-agents' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['strands-agents@latest'],
            'capabilities' => ['agent_creation', 'workflow_management', 'multi_model_support'],
        ],

        'agentcore-mcp-server' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['agentcore-mcp-server@latest'],
            'capabilities' => ['agent_deployment', 'performance_monitoring', 'scaling'],
        ],

        'awspricing' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['awspricing@latest'],
            'capabilities' => ['pricing_lookup', 'cost_calculation'],
        ],

        'awsknowledge' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['awsknowledge@latest'],
            'capabilities' => ['documentation_search', 'best_practices'],
        ],

        'awsapi' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['awsapi@latest'],
            'capabilities' => ['infrastructure_management'],
        ],

        'context7' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['context7@latest'],
            'capabilities' => ['context_management', 'data_processing'],
        ],

        'fetch' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['fetch@latest'],
            'capabilities' => ['http_client', 'external_api_integration'],
        ],

        'memory' => [
            'enabled' => true,
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-memory'],
            'capabilities' => ['knowledge_graph', 'persistent_memory', 'entity_management'],
        ],

        'figma' => [
            'enabled' => false,  // Optional
            'command' => 'uvx',
            'args' => ['figma@latest'],
            'capabilities' => ['design_system', 'asset_management'],
        ],
    ],

    'health_check_interval' => 300,
    'connection_timeout' => 10,
    'max_concurrent_calls' => 5,
];
