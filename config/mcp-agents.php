<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Agents Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for MCP (Model Context Protocol) agents
    | used throughout the application for AI-powered features.
    |
    */

    'agents' => [
        /*
        |--------------------------------------------------------------------------
        | Skill Analysis Agent
        |--------------------------------------------------------------------------
        |
        | Analyzes skill synergies, optimal acquisition strategies, and provides
        | recommendations for skill builds based on character type and goals.
        |
        */
        'skill_analysis' => [
            'enabled' => env('MCP_SKILL_ANALYSIS_ENABLED', true),
            'server' => 'strands-agents',
            'model' => env('MCP_SKILL_ANALYSIS_MODEL', 'claude-3-5-sonnet-20241022'),
            'temperature' => 0.3,
            'max_tokens' => 2048,
            'system_prompt' => 'You are an expert Umamusume skill analyst. Analyze skill combinations, synergies, and provide strategic recommendations for optimal skill builds based on character stats, running style, and race goals.',
            'capabilities' => [
                'skill_synergy_analysis',
                'sp_optimization',
                'hint_collection_strategy',
                'evolution_planning',
                'meta_tier_recommendations',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Skill Evolution Agent
        |--------------------------------------------------------------------------
        |
        | Plans long-term skill development roadmaps and optimal evolution timing.
        |
        */
        'skill_evolution' => [
            'enabled' => env('MCP_SKILL_EVOLUTION_ENABLED', true),
            'server' => 'strands-agents',
            'model' => env('MCP_SKILL_EVOLUTION_MODEL', 'claude-3-5-sonnet-20241022'),
            'temperature' => 0.2,
            'max_tokens' => 1536,
            'system_prompt' => 'You are a skill evolution specialist for Umamusume. Plan optimal skill evolution paths, timing, and prerequisite acquisition strategies to maximize SP efficiency.',
            'capabilities' => [
                'evolution_path_planning',
                'prerequisite_tracking',
                'sp_efficiency_calculation',
                'timing_optimization',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | SP Budget Management Agent
        |--------------------------------------------------------------------------
        |
        | Manages SP budget allocation and hint collection optimization.
        |
        */
        'sp_budget' => [
            'enabled' => env('MCP_SP_BUDGET_ENABLED', true),
            'server' => 'strands-agents',
            'model' => env('MCP_SP_BUDGET_MODEL', 'claude-3-5-haiku-20241022'),
            'temperature' => 0.1,
            'max_tokens' => 1024,
            'system_prompt' => 'You are an SP budget optimizer for Umamusume. Track SP spending, optimize hint collection, and ensure efficient resource allocation for skill acquisition.',
            'capabilities' => [
                'sp_tracking',
                'hint_optimization',
                'cost_reduction_planning',
                'budget_forecasting',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Hint Farming Strategy Agent
        |--------------------------------------------------------------------------
        |
        | Develops strategies for maximum hint collection and cost reduction.
        |
        */
        'hint_farming' => [
            'enabled' => env('MCP_HINT_FARMING_ENABLED', true),
            'server' => 'strands-agents',
            'model' => env('MCP_HINT_FARMING_MODEL', 'claude-3-5-haiku-20241022'),
            'temperature' => 0.2,
            'max_tokens' => 1024,
            'system_prompt' => 'You are a hint farming strategist for Umamusume. Identify optimal support card combinations, training patterns, and event choices to maximize skill hint acquisition.',
            'capabilities' => [
                'hint_source_identification',
                'support_card_optimization',
                'training_pattern_analysis',
                'event_choice_recommendations',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Skill Build Planning Agent
        |--------------------------------------------------------------------------
        |
        | Creates comprehensive skill builds with character synergy analysis.
        |
        */
        'skill_build' => [
            'enabled' => env('MCP_SKILL_BUILD_ENABLED', true),
            'server' => 'strands-agents',
            'model' => env('MCP_SKILL_BUILD_MODEL', 'claude-3-5-sonnet-20241022'),
            'temperature' => 0.3,
            'max_tokens' => 2048,
            'system_prompt' => 'You are a skill build architect for Umamusume. Design comprehensive skill builds that synergize with character stats, aptitudes, and racing goals while considering meta tier rankings.',
            'capabilities' => [
                'build_creation',
                'synergy_analysis',
                'meta_optimization',
                'character_specialization',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Collaboration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for multi-agent collaboration workflows.
    |
    */
    'collaboration' => [
        'enabled' => env('MCP_COLLABORATION_ENABLED', true),
        'max_agents' => 5,
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Track agent performance and effectiveness.
    |
    */
    'monitoring' => [
        'enabled' => env('MCP_MONITORING_ENABLED', true),
        'log_requests' => true,
        'track_performance' => true,
        'store_conversations' => true,
    ],
];
