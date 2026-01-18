<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Agents Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MCP-powered AI agents including Training Optimization,
    | Career Strategy, Race Analysis, and Skill Management agents.
    |
    */

    'training_optimization' => [
        'enabled' => env('AI_AGENT_TRAINING_ENABLED', true),
        'cache_ttl' => env('AI_AGENT_TRAINING_CACHE_TTL', 300), // 5 minutes
        'timeout' => env('AI_AGENT_TRAINING_TIMEOUT', 30),
        'max_retries' => env('AI_AGENT_TRAINING_MAX_RETRIES', 2),
    ],

    'career_strategy' => [
        'enabled' => env('AI_AGENT_CAREER_ENABLED', true),
        'cache_ttl' => env('AI_AGENT_CAREER_CACHE_TTL', 600), // 10 minutes
        'timeout' => env('AI_AGENT_CAREER_TIMEOUT', 30),
        'max_retries' => env('AI_AGENT_CAREER_MAX_RETRIES', 2),
    ],

    'race_analysis' => [
        'enabled' => env('AI_AGENT_RACE_ENABLED', true),
        'cache_ttl' => env('AI_AGENT_RACE_CACHE_TTL', 300), // 5 minutes
        'timeout' => env('AI_AGENT_RACE_TIMEOUT', 30),
        'max_retries' => env('AI_AGENT_RACE_MAX_RETRIES', 2),
    ],

    'skill_management' => [
        'enabled' => env('AI_AGENT_SKILL_ENABLED', true),
        'cache_ttl' => env('AI_AGENT_SKILL_CACHE_TTL', 300), // 5 minutes
        'timeout' => env('AI_AGENT_SKILL_TIMEOUT', 30),
        'max_retries' => env('AI_AGENT_SKILL_MAX_RETRIES', 2),
    ],

    'orchestration' => [
        'enabled' => env('AI_AGENT_ORCHESTRATION_ENABLED', true),
        'max_parallel_agents' => env('AI_AGENT_ORCHESTRATION_MAX_PARALLEL', 4),
        'workflow_timeout' => env('AI_AGENT_ORCHESTRATION_WORKFLOW_TIMEOUT', 120),
        'context_sharing_enabled' => env('AI_AGENT_ORCHESTRATION_CONTEXT_SHARING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for tracking agent performance metrics and costs.
    |
    */

    'monitoring' => [
        'enabled' => env('AI_AGENT_MONITORING_ENABLED', true),
        'track_performance' => env('AI_AGENT_MONITORING_TRACK_PERFORMANCE', true),
        'track_costs' => env('AI_AGENT_MONITORING_TRACK_COSTS', true),
        'log_workflows' => env('AI_AGENT_MONITORING_LOG_WORKFLOWS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for agent fallback behavior when MCP servers are unavailable.
    |
    */

    'fallback' => [
        'enabled' => env('AI_AGENT_FALLBACK_ENABLED', true),
        'use_default_recommendations' => env('AI_AGENT_FALLBACK_USE_DEFAULTS', true),
        'log_fallbacks' => env('AI_AGENT_FALLBACK_LOG', true),
    ],
];
