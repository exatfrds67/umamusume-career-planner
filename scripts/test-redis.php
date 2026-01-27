<?php

try {
    $redis = new Redis;
    echo "Attempting to connect to 127.0.0.1:6379...\n";
    $connected = $redis->connect('127.0.0.1', 6379, 2.5);

    if ($connected) {
        echo "Connected successfully!\n";
        $pong = $redis->ping();
        echo 'Ping response: '.($pong === true ? 'PONG' : $pong)."\n";

        $redis->set('test_key', 'Hello from Windows with mirrored networking!');
        echo "Set test_key\n";
        $value = $redis->get('test_key');
        echo 'Get test_key: '.$value."\n";
    } else {
        echo "Failed to connect\n";
    }
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
