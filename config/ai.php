<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hybrid AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for hybrid AI processing with local Ollama and cloud Bedrock.
    | Implements intelligent routing between local and cloud AI providers.
    |
    */

    'hybrid' => [
        'enabled' => env('AI_HYBRID_ENABLED', true),
        'cost_threshold' => env('AI_COST_THRESHOLD', 0.01), // Maximum cost per request
        'prefer_local' => env('AI_PREFER_LOCAL', true), // Prefer local Ollama when available
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for local Ollama AI models.
    | Supports Llama 3.3, Mistral, and Qwen models.
    |
    */

    'ollama' => [
        'enabled' => env('OLLAMA_ENABLED', true),
        'host' => env('OLLAMA_HOST', 'http://localhost:11434'),
        'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3.3'), // Synced with frontend default
        'timeout' => env('OLLAMA_TIMEOUT', 120), // seconds
        'temperature' => env('OLLAMA_TEMPERATURE', 0.3),
        'max_tokens' => env('OLLAMA_MAX_TOKENS', 2048),

        'available_models' => [
            'llama3.3' => [
                'name' => 'Llama 3.3',
                'version' => 'latest',
                'context_window' => 131072,
                'description' => 'Meta\'s Llama 3.3 model - installed locally (default)',
            ],
            'llama3' => [
                'name' => 'Llama 3',
                'version' => 'latest',
                'context_window' => 8192,
                'description' => 'Meta\'s Llama 3 model - installed locally',
            ],
            'mistral' => [
                'name' => 'Mistral',
                'version' => 'latest',
                'context_window' => 8192,
                'description' => 'Mistral - fast and efficient, installed locally',
            ],
            'deepseek-r1' => [
                'name' => 'DeepSeek R1',
                'version' => '8b',
                'context_window' => 8192,
                'description' => 'DeepSeek R1 reasoning model - installed locally',
            ],
            'gemma3' => [
                'name' => 'Gemma 3',
                'version' => '4b',
                'context_window' => 8192,
                'description' => 'Google\'s Gemma 3 model - installed locally',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS Bedrock Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS Bedrock cloud AI models.
    | Supports Claude 4.5 series, Nova 2, and other Bedrock models.
    |
    */

    'bedrock' => [
        'enabled' => env('BEDROCK_ENABLED', true),
        'default_model' => env('BEDROCK_DEFAULT_MODEL', 'claude-3-5-sonnet'),
        'timeout' => env('BEDROCK_TIMEOUT', 30), // seconds
        'temperature' => env('BEDROCK_TEMPERATURE', 0.3),
        'max_tokens' => env('BEDROCK_MAX_TOKENS', 4096),

        'pricing' => [
            // Claude 3.5 Sonnet - Recommended for balanced intelligence and cost
            'claude-3-5-sonnet' => [
                'input' => 3.00, // per 1M tokens
                'output' => 15.00, // per 1M tokens
                'description' => 'Balanced intelligence and cost',
            ],

            // Claude 3.5 Haiku - Fast and affordable
            'claude-3-5-haiku' => [
                'input' => 1.00, // per 1M tokens
                'output' => 5.00, // per 1M tokens
                'description' => 'Fast and affordable',
            ],

            // Claude Opus 4.5 - Maximum intelligence
            'claude-opus-4-5' => [
                'input' => 5.00, // per 1M tokens
                'output' => 25.00, // per 1M tokens
                'description' => 'Maximum intelligence for complex tasks',
            ],

            // Amazon Nova 2 Lite - Ultra-budget
            'nova-2-lite' => [
                'input' => 0.00125, // per 1K tokens
                'output' => 0.00125, // per 1K tokens
                'description' => 'Ultra-budget option',
            ],

            // Amazon Nova 2 Pro - Multimodal capabilities
            'nova-2-pro' => [
                'input' => 0.008, // per 1K tokens
                'output' => 0.024, // per 1K tokens
                'description' => 'Multimodal capabilities',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MCP server integration with AI services.
    | Enables advanced agent capabilities via strands-agents and agentcore.
    |
    */

    'mcp' => [
        'enabled' => env('MCP_ENABLED', true),

        'strands_agents' => [
            'enabled' => env('MCP_STRANDS_ENABLED', true),
            'timeout' => env('MCP_STRANDS_TIMEOUT', 60), // seconds
        ],

        'agentcore' => [
            'enabled' => env('MCP_AGENTCORE_ENABLED', true),
            'timeout' => env('MCP_AGENTCORE_TIMEOUT', 60), // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for individual AI agents.
    |
    */

    'agents' => [
        'training_optimization' => [
            'enabled' => env('AI_AGENT_TRAINING_OPTIMIZATION_ENABLED', true),
            'timeout' => env('AI_AGENT_TRAINING_OPTIMIZATION_TIMEOUT', 30),
        ],

        'skill_management' => [
            'enabled' => env('AI_AGENT_SKILL_MANAGEMENT_ENABLED', true),
            'timeout' => env('AI_AGENT_SKILL_MANAGEMENT_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI performance monitoring and metrics tracking.
    |
    */

    'monitoring' => [
        'enabled' => env('AI_MONITORING_ENABLED', true),
        'metrics_ttl' => env('AI_METRICS_TTL', 3600), // seconds
        'track_costs' => env('AI_TRACK_COSTS', true),
        'track_performance' => env('AI_TRACK_PERFORMANCE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI conversation context tracking and session management.
    |
    */

    'conversation' => [
        'enabled' => env('AI_CONVERSATION_ENABLED', true),
        'max_history' => env('AI_MAX_HISTORY', 50), // messages
        'context_window' => env('AI_CONTEXT_WINDOW', 10), // messages
        'session_ttl' => env('AI_SESSION_TTL', 3600), // seconds
    ],
];
