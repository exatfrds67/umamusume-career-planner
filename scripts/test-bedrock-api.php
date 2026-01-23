<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

// Boot the application
$app->boot();

$container = $app;

echo "\n".str_repeat('=', 80)."\n";
echo "BEDROCK API CONNECTIVITY TEST\n";
echo str_repeat('=', 80)."\n\n";

try {
    $service = $container->make(\App\Services\AI\BedrockService::class);
    echo "✓ BedrockService instantiated\n\n";

    echo "Attempting API call with test prompt...\n";
    $response = $service->generate('Say "test"', [], 'claude-3-5-sonnet');

    echo "✓ SUCCESS - Bedrock API responded\n";
    echo "\nResponse Details:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

} catch (\Exception $e) {
    echo '✗ ERROR - '.$e->getMessage()."\n\n";

    if ($e->getPrevious()) {
        echo 'Cause: '.$e->getPrevious()->getMessage()."\n\n";
    }

    // Provide diagnostics
    echo "DIAGNOSTICS:\n";
    echo str_repeat('-', 80)."\n";

    $message = $e->getMessage();

    if (strpos($message, 'UnrecognizedClientException') !== false) {
        echo "❌ The AWS Access Key or Secret Key is invalid.\n";
        echo "   - Verify your AWS_ACCESS_KEY_ID is correct\n";
        echo "   - Verify your AWS_SECRET_ACCESS_KEY is correct\n";
        echo "   - Check if credentials have expired\n";
    } elseif (strpos($message, 'AccessDeniedException') !== false) {
        echo "❌ The AWS credentials don't have permission to use Bedrock.\n";
        echo "   - Verify the IAM user/role has Bedrock permissions\n";
        echo '   - Check the AWS region (currently: '.env('AWS_DEFAULT_REGION').")\n";
        echo "   - Bedrock may not be available in your region\n";
    } elseif (strpos($message, 'ModelNotFound') !== false) {
        echo "❌ The specified Bedrock model is not available.\n";
        echo "   - The model ID might be wrong\n";
        echo "   - The model might not be accessible in your region\n";
    } elseif (strpos($message, 'timeout') !== false || strpos($message, 'timed out') !== false) {
        echo "❌ Connection timeout when calling Bedrock API.\n";
        echo "   - Check your network connectivity\n";
        echo "   - Check if AWS is reachable from your location\n";
        echo "   - Try increasing the timeout value\n";
    } else {
        echo "❌ Unexpected error occurred.\n";
        echo '   Details: '.$message."\n";
    }
}

echo "\n".str_repeat('=', 80)."\n";
