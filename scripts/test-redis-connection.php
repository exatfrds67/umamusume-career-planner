<?php

// Test Redis connection in testing environment
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set testing environment
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// Load .env.testing
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.testing');
$dotenv->safeLoad();

echo 'Environment: '.app()->environment()."\n";
echo 'REDIS_HOST from env(): '.env('REDIS_HOST')."\n";
echo 'REDIS_PORT from env(): '.env('REDIS_PORT')."\n";
echo 'REDIS_DB from env(): '.env('REDIS_DB')."\n\n";

echo 'Config redis.default.host: '.config('database.redis.default.host')."\n";
echo 'Config redis.default.port: '.config('database.redis.default.port')."\n";
echo 'Config redis.default.database: '.config('database.redis.default.database')."\n\n";

try {
    echo "Attempting to connect to Redis...\n";
    $redis = Illuminate\Support\Facades\Redis::connection();
    $pong = $redis->ping();
    echo "✅ Redis connection successful!\n";
    echo 'Ping response: '.($pong === true ? 'PONG' : $pong)."\n";

    // Test set/get
    $redis->set('test_key', 'test_value');
    $value = $redis->get('test_key');
    echo 'Set/Get test: '.$value."\n";
} catch (Exception $e) {
    echo "❌ Redis connection failed!\n";
    echo 'Error: '.$e->getMessage()."\n";
    echo 'Trace: '.$e->getTraceAsString()."\n";
}
