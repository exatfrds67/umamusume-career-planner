<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Ollama Configuration...\n";
$configModel = config('ai.ollama.model');
$defaultModel = config('ai.ollama.default_model');
$host = config('ai.ollama.host');

echo "Config 'ai.ollama.model': ".($configModel ?? 'NULL')."\n";
echo "Config 'ai.ollama.default_model': ".($defaultModel ?? 'NULL')."\n";
echo "Host: $host\n";

// Test Connectivity
echo "\nTesting Connectivity to $host...\n";
$ch = curl_init("$host/api/tags");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "Connection Successful!\n";
    $data = json_decode($response, true);
    echo "Available Models:\n";
    foreach ($data['models'] as $model) {
        echo '- '.$model['name']."\n";
    }
} else {
    echo "Connection Failed! HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

// Test Facade
echo "\nTesting Ollama Facade...\n";
try {
    $modelToUse = $configModel ?? $defaultModel ?? 'llama3.3';
    echo "Attempting to use model: $modelToUse\n";

    $response = \Cloudstudio\Ollama\Facades\Ollama::agent('Debug Agent')
        ->model($modelToUse)
        ->prompt('Hello, are you working?')
        ->ask();

    echo 'Facade Response: '.$response."\n";
} catch (\Exception $e) {
    echo 'Facade Error: '.$e->getMessage()."\n";
}
