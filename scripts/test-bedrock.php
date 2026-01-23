<?php

/**
 * Bedrock Configuration & Connectivity Test
 *
 * Tests if Bedrock is properly configured and working in this application.
 */

require __DIR__.'/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "\n".str_repeat('=', 80)."\n";
echo "BEDROCK CONFIGURATION & CONNECTIVITY TEST\n";
echo str_repeat('=', 80)."\n\n";

// Test 1: Environment variables
echo "1. CHECKING ENVIRONMENT VARIABLES\n";
echo str_repeat('-', 80)."\n";

$envVars = [
    'AWS_ACCESS_KEY_ID' => $_ENV['AWS_ACCESS_KEY_ID'] ?? null,
    'AWS_SECRET_ACCESS_KEY' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null,
    'AWS_DEFAULT_REGION' => $_ENV['AWS_DEFAULT_REGION'] ?? null,
    'AWS_BEDROCK_ENABLED' => $_ENV['AWS_BEDROCK_ENABLED'] ?? null,
    'BEDROCK_MODEL_PREFERENCES' => $_ENV['BEDROCK_MODEL_PREFERENCES'] ?? null,
];

foreach ($envVars as $key => $value) {
    if ($key === 'AWS_SECRET_ACCESS_KEY') {
        $display = $value ? '✓ SET (hidden)' : '✗ NOT SET';
    } elseif ($key === 'AWS_ACCESS_KEY_ID') {
        $display = $value ? '✓ SET ('.substr($value, 0, 4).'...'.substr($value, -4).')' : '✗ NOT SET';
    } else {
        $display = $value ? "✓ {$value}" : '✗ NOT SET';
    }
    printf("%-30s: %s\n", $key, $display);
}

echo "\n";

// Test 2: Laravel Config
echo "2. CHECKING LARAVEL CONFIGURATION\n";
echo str_repeat('-', 80)."\n";

$app = require __DIR__.'/bootstrap/app.php';
$container = $app->make(\Illuminate\Contracts\Foundation\Application::class);

try {
    $config = $container->make('config');

    $bedrockEnabled = $config->get('ai.bedrock.enabled', false);
    $bedrockDefaultModel = $config->get('ai.bedrock.default_model', 'unknown');
    $awsRegion = $config->get('aws.region', 'unknown');

    echo 'Bedrock Enabled: '.($bedrockEnabled ? '✓ YES' : '✗ NO')."\n";
    echo 'Default Model: '.($bedrockDefaultModel ? "✓ {$bedrockDefaultModel}" : '✗ NOT SET')."\n";
    echo 'AWS Region: '.($awsRegion ? "✓ {$awsRegion}" : '✗ NOT SET')."\n";
} catch (\Exception $e) {
    echo '✗ Error loading config: '.$e->getMessage()."\n";
}

echo "\n";

// Test 3: AWS SDK
echo "3. CHECKING AWS SDK\n";
echo str_repeat('-', 80)."\n";

try {
    if (! class_exists(\Aws\BedrockRuntime\BedrockRuntimeClient::class)) {
        echo "✗ AWS SDK is not installed\n";
    } else {
        echo "✓ AWS SDK installed\n";

        // Try to initialize Bedrock client
        try {
            $credentials = [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ];

            if (! $credentials['key'] || ! $credentials['secret']) {
                echo "✗ Credentials not available\n";
            } else {
                $client = new \Aws\BedrockRuntime\BedrockRuntimeClient([
                    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                    'version' => 'latest',
                    'credentials' => $credentials,
                    'http' => [
                        'timeout' => 5,
                        'connect_timeout' => 5,
                    ],
                ]);
                echo "✓ Bedrock client initialized successfully\n";
            }
        } catch (\Exception $e) {
            echo '✗ Failed to initialize Bedrock client: '.$e->getMessage()."\n";
        }
    }
} catch (\Exception $e) {
    echo '✗ AWS SDK error: '.$e->getMessage()."\n";
}

echo "\n";

// Test 4: BedrockService
echo "4. CHECKING BEDROCK SERVICE\n";
echo str_repeat('-', 80)."\n";

try {
    $bedrockService = $container->make(\App\Services\AI\BedrockService::class);
    echo "✓ BedrockService loaded successfully\n";

    // Try a simple test
    echo "Attempting to call Bedrock API...\n";
    try {
        // This will attempt to invoke a model
        $result = $bedrockService->generate("Say 'Bedrock is working'", [], 'claude-3-5-sonnet', 5);
        echo "✓ Bedrock API call successful\n";
        echo '  Response preview: '.substr($result['content'], 0, 100)."...\n";
    } catch (\Exception $e) {
        echo '✗ Bedrock API call failed: '.$e->getMessage()."\n";

        // Additional diagnostics
        if (strpos($e->getMessage(), 'credentials') !== false) {
            echo "\n  💡 Tip: AWS credentials are invalid or expired\n";
        } elseif (strpos($e->getMessage(), 'UnrecognizedClientException') !== false) {
            echo "\n  💡 Tip: AWS credentials are not valid for Bedrock\n";
        } elseif (strpos($e->getMessage(), 'timeout') !== false) {
            echo "\n  💡 Tip: Connection timeout. Check AWS region and network connectivity\n";
        }
    }
} catch (\Exception $e) {
    echo '✗ BedrockService error: '.$e->getMessage()."\n";
}

echo "\n";

// Test 5: BedrockConfigurationService
echo "5. CHECKING BEDROCK CONFIGURATION SERVICE\n";
echo str_repeat('-', 80)."\n";

try {
    $configService = $container->make(\App\Services\AI\BedrockConfigurationService::class);
    echo "✓ BedrockConfigurationService loaded successfully\n";

    $validation = $configService->validateCredentials();
    echo 'Credentials validation: '.($validation['valid'] ? '✓ VALID' : '✗ INVALID')."\n";
    echo '  Message: '.$validation['message']."\n";
    echo '  Region: '.$validation['region']."\n";
} catch (\Exception $e) {
    echo '✗ BedrockConfigurationService error: '.$e->getMessage()."\n";
}

echo "\n";

// Test 6: Summary
echo "6. SUMMARY\n";
echo str_repeat('-', 80)."\n";

$checks = [
    'AWS Credentials configured' => ! empty(env('AWS_ACCESS_KEY_ID')) && ! empty(env('AWS_SECRET_ACCESS_KEY')),
    'Bedrock enabled in config' => (bool) env('AWS_BEDROCK_ENABLED'),
    'AWS region configured' => ! empty(env('AWS_DEFAULT_REGION')),
    'AWS SDK installed' => class_exists(\Aws\BedrockRuntime\BedrockRuntimeClient::class),
    'BedrockService available' => class_exists(\App\Services\AI\BedrockService::class),
];

$passed = 0;
$failed = 0;

foreach ($checks as $check => $result) {
    if ($result) {
        echo '✓ '.$check."\n";
        $passed++;
    } else {
        echo '✗ '.$check."\n";
        $failed++;
    }
}

echo "\n".str_repeat('=', 80)."\n";
echo 'RESULT: '.$passed.' passed, '.$failed." failed\n";
echo str_repeat('=', 80)."\n\n";

if ($failed > 0) {
    exit(1);
}

exit(0);
