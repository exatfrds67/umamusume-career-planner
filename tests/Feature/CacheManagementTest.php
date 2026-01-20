<?php

declare(strict_types=1);

use App\Services\CacheManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Cache Management Service Tests
 *
 * Tests for MCP-enhanced caching with intelligent cache warming,
 * invalidation, and performance monitoring.
 *
 * Requirements: 14.5, 55.3, 56.4, Task 4.4.2
 *
 * @property CacheManagementService $cacheManager
 * @property MCPClientService&\Mockery\MockInterface $mcpClient
 */
beforeEach(function (): void {
    // Use array cache driver for testing (no Redis required)
    config(['cache.default' => 'array']);

    // Clear all caches before each test
    Cache::flush();

    // Mock MCP client
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);
    $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);

    // @phpstan-ignore-next-line - Pest dynamic property binding
    $this->cacheManager = new CacheManagementService($mcpClient);
    // @phpstan-ignore-next-line - Pest dynamic property binding
    $this->mcpClient = $mcpClient;
});

afterEach(fn () => Mockery::close());

test('remember method caches callback result', function () {
    $key = 'test_key';
    $value = ['data' => 'test_value'];
    $callCount = 0;

    $callback = function () use ($value, &$callCount) {
        $callCount++;

        return $value;
    };

    // First call should execute callback
    $result1 = $this->cacheManager->remember($key, $callback, 60);
    expect($result1)->toBe($value);
    expect($callCount)->toBe(1);

    // Second call should use cache
    $result2 = $this->cacheManager->remember($key, $callback, 60);
    expect($result2)->toBe($value);
    expect($callCount)->toBe(1); // Callback not called again
});

test('cache warming warms multiple keys successfully', function () {
    $keys = ['key1', 'key2', 'key3'];
    $callbacks = [
        'key1' => fn () => ['data' => 'value1'],
        'key2' => fn () => ['data' => 'value2'],
        'key3' => fn () => ['data' => 'value3'],
    ];

    $result = $this->cacheManager->warmCache($keys, $callbacks);

    expect($result['warmed'])->toBe(3);
    expect($result['failed'])->toBe(0);
    expect($result['duration_ms'])->toBeGreaterThan(0);

    // Verify all keys are cached
    foreach ($keys as $key) {
        expect(Cache::has("umamusume-career-planner:{$key}"))->toBeTrue();
    }
});

test('cache warming handles missing callbacks', function () {
    $keys = ['key1', 'key2'];
    $callbacks = [
        'key1' => fn () => ['data' => 'value1'],
        // key2 callback missing
    ];

    $result = $this->cacheManager->warmCache($keys, $callbacks);

    expect($result['warmed'])->toBe(1);
    expect($result['failed'])->toBe(1);
});

test('cache warming skips already cached keys', function () {
    $key = 'key1';
    $callbacks = [
        $key => fn () => ['data' => 'value1'],
    ];

    // Pre-cache the key
    Cache::put("umamusume-career-planner:{$key}", ['data' => 'cached'], 60);

    $result = $this->cacheManager->warmCache([$key], $callbacks);

    // Should skip already cached key
    expect($result['warmed'])->toBe(0);
    expect($result['failed'])->toBe(0);
});

test('invalidate removes single key from cache', function () {
    $key = 'test_key';
    Cache::put("umamusume-career-planner:{$key}", ['data' => 'value'], 60);

    expect(Cache::has("umamusume-career-planner:{$key}"))->toBeTrue();

    $this->cacheManager->invalidate($key, 'test_reason');

    expect(Cache::has("umamusume-career-planner:{$key}"))->toBeFalse();
});

test('invalidate removes multiple keys from cache', function () {
    $keys = ['key1', 'key2', 'key3'];

    foreach ($keys as $key) {
        Cache::put("umamusume-career-planner:{$key}", ['data' => 'value'], 60);
    }

    $this->cacheManager->invalidate($keys, 'bulk_invalidation');

    foreach ($keys as $key) {
        expect(Cache::has("umamusume-career-planner:{$key}"))->toBeFalse();
    }
});

test('get hit rate statistics returns correct metrics', function () {
    // Simulate some cache hits and misses
    $this->cacheManager->remember('key1', fn () => ['data' => 'value1'], 60);
    $this->cacheManager->remember('key1', fn () => ['data' => 'value1'], 60); // Hit
    $this->cacheManager->remember('key2', fn () => ['data' => 'value2'], 60);

    $stats = $this->cacheManager->getHitRateStatistics();

    expect($stats)->toHaveKeys(['hit_rate', 'total_hits', 'total_misses', 'total_requests', 'avg_response_time_ms']);
    expect($stats['total_requests'])->toBe(3);
    expect($stats['total_hits'])->toBe(1);
    expect($stats['total_misses'])->toBe(2);
    expect($stats['hit_rate'])->toBeGreaterThan(0);
});

test('get detailed metrics returns per-key statistics', function () {
    $this->cacheManager->remember('key1', fn () => ['data' => 'value1'], 60);
    $this->cacheManager->remember('key1', fn () => ['data' => 'value1'], 60); // Hit
    $this->cacheManager->remember('key2', fn () => ['data' => 'value2'], 60);

    $detailed = $this->cacheManager->getDetailedMetrics();

    expect($detailed)->toHaveKey('key1');
    expect($detailed)->toHaveKey('key2');
    expect($detailed['key1'])->toHaveKeys(['hits', 'misses', 'hit_rate', 'avg_time_ms']);
    expect($detailed['key1']['hits'])->toBe(1);
    expect($detailed['key1']['misses'])->toBe(1);
});

test('get cost optimized strategy returns recommendations', function () {
    $strategy = $this->cacheManager->getCostOptimizedStrategy('characters', 1024 * 1024); // 1MB

    expect($strategy)->toHaveKeys(['recommended_ttl', 'estimated_cost', 'strategy']);
    expect($strategy['recommended_ttl'])->toBeGreaterThan(0);
    expect($strategy['estimated_cost'])->toBeGreaterThan(0);
    expect($strategy['strategy'])->toBeString();
});

test('record api response time stores metrics', function () {
    // Skip this test in non-Redis environments
    if (config('cache.default') !== 'redis') {
        $this->markTestSkipped('Redis required for API response time tracking');
    }

    $apiName = 'test_api';
    $responseTime = 123.45;

    $this->cacheManager->recordApiResponseTime($apiName, $responseTime);

    // Verify data is stored in Redis
    $key = "api_response_time:{$apiName}";
    $times = Redis::zrange($key, 0, -1);

    expect($times)->toHaveCount(1);
    expect((float) $times[0])->toBe($responseTime);
});

test('get api response time stats calculates percentiles', function () {
    // Skip this test in non-Redis environments
    if (config('cache.default') !== 'redis') {
        $this->markTestSkipped('Redis required for API response time tracking');
    }

    $apiName = 'test_api';
    $times = [100, 150, 200, 250, 300, 350, 400, 450, 500, 550];

    foreach ($times as $time) {
        $this->cacheManager->recordApiResponseTime($apiName, $time);
    }

    $stats = $this->cacheManager->getApiResponseTimeStats($apiName);

    expect($stats)->toHaveKeys(['avg', 'min', 'max', 'p50', 'p95', 'p99', 'count']);
    expect($stats['count'])->toBe(10);
    expect($stats['min'])->toBe(100.0);
    expect($stats['max'])->toBe(550.0);
    expect($stats['avg'])->toBeGreaterThan(0);
    expect($stats['p50'])->toBeGreaterThan(0);
    expect($stats['p95'])->toBeGreaterThan(0);
    expect($stats['p99'])->toBeGreaterThan(0);
});

test('clear all removes all application caches', function () {
    // Skip this test in non-Redis environments
    if (config('cache.default') !== 'redis') {
        $this->markTestSkipped('Redis required for pattern-based cache clearing');
    }

    // Create multiple cache entries
    Cache::put('umamusume-career-planner:key1', 'value1', 60);
    Cache::put('umamusume-career-planner:key2', 'value2', 60);
    Cache::put('umamusume-career-planner:key3', 'value3', 60);

    $this->cacheManager->clearAll();

    // Verify all are cleared
    expect(Cache::has('umamusume-career-planner:key1'))->toBeFalse();
    expect(Cache::has('umamusume-career-planner:key2'))->toBeFalse();
    expect(Cache::has('umamusume-career-planner:key3'))->toBeFalse();
});

test('cache manager integrates with mcp health monitoring', function () {
    // Create a fresh mock for this test
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerHealthy')
        ->with('awspricing')
        ->once()
        ->andReturn(true);

    $cacheManager = new CacheManagementService($mcpClient);

    $result = $cacheManager->remember(
        'test_key',
        fn () => ['data' => 'value'],
        60
    );

    expect($result)->toBe(['data' => 'value']);
});

test('cache warming processes large batches efficiently', function () {
    $keys = [];
    $callbacks = [];

    // Create 100 keys
    for ($i = 0; $i < 100; $i++) {
        $key = "key_{$i}";
        $keys[] = $key;
        $callbacks[$key] = fn () => ['data' => "value_{$i}"];
    }

    $result = $this->cacheManager->warmCache($keys, $callbacks);

    expect($result['warmed'])->toBe(100);
    expect($result['failed'])->toBe(0);
    expect($result['duration_ms'])->toBeGreaterThan(0);
});
