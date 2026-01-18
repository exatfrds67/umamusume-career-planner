<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AWS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS services including Bedrock, S3, and other AWS APIs.
    | Credentials should be stored in environment variables for security.
    |
    */

    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID', env('AWS_KEY')),
        'secret' => env('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET')),
    ],

    'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'us-east-1')),

    /*
    |--------------------------------------------------------------------------
    | AWS Bedrock Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to AWS Bedrock service for AI model access.
    |
    */

    'bedrock' => [
        'region' => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        'version' => 'latest',
        'timeout' => env('AWS_BEDROCK_TIMEOUT', 30),
        'connect_timeout' => env('AWS_BEDROCK_CONNECT_TIMEOUT', 10),

        // Supported models with their Bedrock model IDs
        'models' => [
            'claude-3-5-sonnet' => [
                'id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
                'name' => 'Claude 3.5 Sonnet',
                'version' => '20241022-v2',
                'provider' => 'anthropic',
                'input_cost' => 3.00, // per 1M tokens
                'output_cost' => 15.00, // per 1M tokens
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            'claude-3-5-haiku' => [
                'id' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
                'name' => 'Claude 3.5 Haiku',
                'version' => '20241022-v1',
                'provider' => 'anthropic',
                'input_cost' => 1.00, // per 1M tokens
                'output_cost' => 5.00, // per 1M tokens
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            'claude-opus-4-5' => [
                'id' => 'anthropic.claude-opus-4-5-20250514-v1:0',
                'name' => 'Claude Opus 4.5',
                'version' => '20250514-v1',
                'provider' => 'anthropic',
                'input_cost' => 5.00, // per 1M tokens
                'output_cost' => 25.00, // per 1M tokens
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            'nova-2-lite' => [
                'id' => 'amazon.nova-lite-v1:0',
                'name' => 'Amazon Nova 2 Lite',
                'version' => 'v1',
                'provider' => 'amazon',
                'input_cost' => 0.00125, // per 1K tokens
                'output_cost' => 0.00125, // per 1K tokens
                'max_tokens' => 300000,
                'context_window' => 300000,
            ],
            'nova-2-pro' => [
                'id' => 'amazon.nova-pro-v1:0',
                'name' => 'Amazon Nova 2 Pro',
                'version' => 'v1',
                'provider' => 'amazon',
                'input_cost' => 0.008, // per 1K tokens
                'output_cost' => 0.024, // per 1K tokens
                'max_tokens' => 300000,
                'context_window' => 300000,
            ],
            'titan-text-express' => [
                'id' => 'amazon.titan-text-express-v1',
                'name' => 'Amazon Titan Text Express',
                'version' => 'v1',
                'provider' => 'amazon',
                'input_cost' => 0.0008, // per 1K tokens
                'output_cost' => 0.0016, // per 1K tokens
                'max_tokens' => 8000,
                'context_window' => 8000,
            ],
        ],

        // Default model preferences (in order of preference)
        'model_preferences' => array_filter(
            explode(',', env('BEDROCK_MODEL_PREFERENCES', 'claude-3-5-sonnet,claude-3-5-haiku,nova-2-lite')),
            fn ($model) => ! empty(trim($model))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | S3 Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS S3 storage service.
    |
    */

    's3' => [
        'bucket' => env('AWS_BUCKET'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | IAM Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS IAM permissions and roles.
    |
    */

    'iam' => [
        // Required IAM permissions for Bedrock access
        'required_permissions' => [
            'bedrock:InvokeModel',
            'bedrock:InvokeModelWithResponseStream',
            'bedrock:ListFoundationModels',
            'bedrock:GetFoundationModel',
        ],
    ],
];
