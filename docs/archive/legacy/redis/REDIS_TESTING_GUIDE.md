# Redis Testing Guide for UmamusumeCareerPlanner

## Overview

This guide provides comprehensive testing procedures for Redis integration in the UmamusumeCareerPlanner application.

## Prerequisites

Before testing, ensure:

1. ✅ Redis is installed and running on WSL
2. ✅ phpredis extension is installed in XAMPP
3. ✅ `.env` file is configured with Redis settings
4. ✅ Laravel application is running

## Quick Test Commands

### 1. Test Redis Connection

```bash
php artisan redis:health
```text

Expected output:

```text

✅ Redis connection successful!

📊 Cache Statistics:
+---------------------+----------+
| Metric              | Value    |
+---------------------+----------+
| Used Memory         | 1.23M    |
| Peak Memory         | 2.45M    |
| Total Keys          | 42       |
| Hit Rate            | 85.5%    |
| Fragmentation Ratio | 1.12     |
| Connected Clients   | 3        |
| Uptime (days)       | 5.23     |
+---------------------+----------+

```text

### 2. Warm Cache

```bash
php artisan cache:warm
```

Expected output:

```text
Starting cache warming process...
Redis connection successful.
Cache warming completed successfully!
```text

### 3. Test Cache Operations

```bash
php artisan tinker
```text

```php
// Test basic cache operations
Cache::put('test_key', 'Hello Redis!', 60);
Cache::get('test_key'); // Should return "Hello Redis!"

// Test cache with tags
Cache::tags(['training', 'character'])->put('test_tagged', 'Tagged data', 60);
Cache::tags(['training', 'character'])->get('test_tagged'); // Should return "Tagged data"

// Test cache invalidation
Cache::tags(['training'])->flush();
Cache::tags(['training', 'character'])->get('test_tagged'); // Should return null
```

## Detailed Testing Procedures

### Test 1: Cache Driver Configuration

**Purpose**: Verify Redis is configured as the cache driver

**Steps**:

1. Check `.env` file:

   ```env
   CACHE_STORE=redis
   ```text

2. Verify configuration:

   ```bash
   php artisan config:show cache.default
   ```

3. Expected output: `redis`

**Pass Criteria**: ✅ Cache driver is set to Redis

---

### Test 2: Queue Driver Configuration

**Purpose**: Verify Redis is configured as the queue driver

**Steps**:

1. Check `.env` file:

   ```env
   QUEUE_CONNECTION=redis
   ```text

2. Verify configuration:

   ```bash
   php artisan config:show queue.default
   ```

3. Expected output: `redis`

**Pass Criteria**: ✅ Queue driver is set to Redis

---

### Test 3: Session Driver Configuration

**Purpose**: Verify Redis is configured as the session driver

**Steps**:

1. Check `.env` file:

   ```env
   SESSION_DRIVER=redis
   ```text

2. Verify configuration:

   ```bash
   php artisan config:show session.driver
   ```

3. Expected output: `redis`

**Pass Criteria**: ✅ Session driver is set to Redis

---

### Test 4: Redis Connection Test

**Purpose**: Verify all Redis connections are working

**Steps**:

1. Test default connection:

   ```bash
   php artisan tinker
   ```text

   ```php
   Redis::connection('default')->ping(); // Should return "PONG" or true
   ```

2. Test cache connection:

   ```php
   Redis::connection('cache')->ping(); // Should return "PONG" or true
   ```text

3. Test session connection:

   ```php
   Redis::connection('session')->ping(); // Should return "PONG" or true
   ```

**Pass Criteria**: ✅ All connections return "PONG" or true

---

### Test 5: Cache Operations

**Purpose**: Verify cache read/write operations

**Steps**:

1. Write to cache:

   ```php
   Cache::put('test_write', 'Test data', 60);
   ```text

2. Read from cache:

   ```php
   $value = Cache::get('test_write');
   echo $value; // Should output "Test data"
   ```

3. Check cache existence:

   ```php
   Cache::has('test_write'); // Should return true
   ```text

4. Delete from cache:

   ```php
   Cache::forget('test_write');
   Cache::has('test_write'); // Should return false
   ```

**Pass Criteria**: ✅ All cache operations work correctly

---

### Test 6: Cache with TTL

**Purpose**: Verify cache expiration works correctly

**Steps**:

1. Set cache with 5-second TTL:

   ```php
   Cache::put('test_ttl', 'Expires soon', 5);
   ```text

2. Immediately check:

   ```php
   Cache::get('test_ttl'); // Should return "Expires soon"
   ```

3. Wait 6 seconds and check again:

   ```php
   sleep(6);
   Cache::get('test_ttl'); // Should return null
   ```text

**Pass Criteria**: ✅ Cache expires after TTL

---

### Test 7: Cache Tags

**Purpose**: Verify cache tagging and invalidation

**Steps**:

1. Create tagged cache entries:

   ```php
   Cache::tags(['training'])->put('training_1', 'Data 1', 60);
   Cache::tags(['training'])->put('training_2', 'Data 2', 60);
   Cache::tags(['character'])->put('character_1', 'Data 3', 60);
   ```

2. Verify tagged cache:

   ```php
   Cache::tags(['training'])->get('training_1'); // Should return "Data 1"
   Cache::tags(['character'])->get('character_1'); // Should return "Data 3"
   ```text

3. Flush specific tag:

   ```php
   Cache::tags(['training'])->flush();
   ```

4. Verify invalidation:

   ```php
   Cache::tags(['training'])->get('training_1'); // Should return null
   Cache::tags(['character'])->get('character_1'); // Should still return "Data 3"
   ```text

**Pass Criteria**: ✅ Tagged cache invalidation works correctly

---

### Test 8: Queue Operations

**Purpose**: Verify Redis queue processing

**Steps**:

1. Create a test job:

   ```bash
   php artisan make:job TestRedisJob
   ```

2. Dispatch the job:

   ```php
   dispatch(new \App\Jobs\TestRedisJob());
   ```text

3. Start queue worker:

   ```bash
   php artisan queue:work redis --once
   ```

4. Check job was processed:

   ```bash
   php artisan queue:failed
   ```text

**Pass Criteria**: ✅ Job is processed successfully without errors

---

### Test 9: Session Storage

**Purpose**: Verify Redis session storage

**Steps**:

1. Create a test route in `routes/web.php`:

   ```php
   Route::get('/test-session', function () {
       session(['test_key' => 'Redis session data']);
       return session('test_key');
   });
   ```

2. Visit the route:

   ```text
   http://localhost/test-session
   ```

3. Check Redis for session data:

   ```bash
   redis-cli -n 2 keys "*"
   ```text

**Pass Criteria**: ✅ Session data is stored in Redis DB 2

---

### Test 10: Cache Optimization Service

**Purpose**: Verify cache optimization service works

**Steps**:

1. Test cache service:

   ```php
   $service = app('redis.cache.optimizer');
   $service->testConnection(); // Should return true
   ```

2. Get cache statistics:

   ```php
   $stats = $service->getStatistics();
   print_r($stats);
   ```text

3. Test cache warming:

   ```php
   $service->warmCache();
   ```

**Pass Criteria**: ✅ All service methods work without errors

---

### Test 11: Performance Test

**Purpose**: Verify Redis improves performance

**Steps**:

1. Test without cache:

   ```php
   $start = microtime(true);
   $data = range(1, 10000);
   $end = microtime(true);
   echo "Without cache: " . ($end - $start) . " seconds\n";
   ```text

2. Test with cache:

   ```php
   $start = microtime(true);
   $data = Cache::remember('test_performance', 60, function () {
       return range(1, 10000);
   });
   $end = microtime(true);
   echo "First cache: " . ($end - $start) . " seconds\n";

   $start = microtime(true);
   $data = Cache::get('test_performance');
   $end = microtime(true);
   echo "From cache: " . ($end - $start) . " seconds\n";
   ```

**Pass Criteria**: ✅ Cache retrieval is significantly faster

---

### Test 12: Memory Usage

**Purpose**: Monitor Redis memory usage

**Steps**:

1. Check current memory:

   ```bash
   redis-cli info memory | grep used_memory_human
   ```text

2. Add large dataset:

   ```php
   for ($i = 0; $i < 1000; $i++) {
       Cache::put("test_memory_{$i}", str_repeat('x', 1000), 60);
   }
   ```

3. Check memory again:

   ```bash
   redis-cli info memory | grep used_memory_human
   ```text

4. Clean up:

   ```php
   for ($i = 0; $i < 1000; $i++) {
       Cache::forget("test_memory_{$i}");
   }
   ```

**Pass Criteria**: ✅ Memory usage increases and decreases appropriately

---

### Test 13: Failover Test

**Purpose**: Verify application handles Redis failure gracefully

**Steps**:

1. Stop Redis:

   ```bash
   sudo service redis-server stop
   ```text

2. Try cache operation:

   ```php
   try {
       Cache::get('test_key');
   } catch (\Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ```

3. Restart Redis:

   ```bash
   sudo service redis-server start
   ```text

4. Verify recovery:

   ```php
   Cache::put('test_recovery', 'Back online', 60);
   Cache::get('test_recovery'); // Should work
   ```

**Pass Criteria**: ✅ Application handles Redis failure and recovers

---

### Test 14: Concurrent Access

**Purpose**: Verify Redis handles concurrent requests

**Steps**:

1. Create test script `test-concurrent.php`:

   ```php
   <?php
   require __DIR__.'/vendor/autoload.php';
   $app = require_once __DIR__.'/bootstrap/app.php';
   $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

   $key = 'concurrent_test';
   $value = Cache::get($key, 0);
   Cache::put($key, $value + 1, 60);
   echo "Count: " . Cache::get($key) . "\n";
   ```text

2. Run multiple instances:

   ```bash
   php test-concurrent.php & php test-concurrent.php & php test-concurrent.php
   ```

3. Check final count:

   ```bash
   php artisan tinker
   ```text

   ```php
   Cache::get('concurrent_test'); // Should be 3
   ```

**Pass Criteria**: ✅ All concurrent operations complete successfully

---

## Automated Test Suite

Create a comprehensive test file `tests/Feature/RedisIntegrationTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedisIntegrationTest extends TestCase
{
    public function test_redis_connection(): void
    {
        $response = Redis::connection('default')->ping();
        $this->assertTrue($response === 'PONG' || $response === true);
    }

    public function test_cache_operations(): void
    {
        Cache::put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_key'));

        Cache::forget('test_key');
        $this->assertNull(Cache::get('test_key'));
    }

    public function test_cache_with_tags(): void
    {
        Cache::tags(['test'])->put('tagged_key', 'tagged_value', 60);
        $this->assertEquals('tagged_value', Cache::tags(['test'])->get('tagged_key'));

        Cache::tags(['test'])->flush();
        $this->assertNull(Cache::tags(['test'])->get('tagged_key'));
    }

    public function test_cache_ttl(): void
    {
        Cache::put('ttl_test', 'value', 1);
        $this->assertEquals('value', Cache::get('ttl_test'));

        sleep(2);
        $this->assertNull(Cache::get('ttl_test'));
    }

    public function test_cache_optimization_service(): void
    {
        $service = app('redis.cache.optimizer');
        $this->assertTrue($service->testConnection());

        $stats = $service->getStatistics();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('used_memory', $stats);
    }
}
```text

Run the test suite:

```bash
php artisan test --filter=RedisIntegrationTest
```text

## Troubleshooting Common Issues

### Issue 1: Connection Refused

**Symptoms**: `Connection refused [tcp://127.0.0.1:6379]`

**Solutions**:

1. Check Redis is running:

   ```bash
   sudo service redis-server status
   ```text

2. Start Redis if stopped:

   ```bash
   sudo service redis-server start
   ```

3. Verify Redis is listening:

   ```bash
   sudo netstat -tulpn | grep 6379
   ```text

---

### Issue 2: phpredis Extension Not Found

**Symptoms**: `Class 'Redis' not found`

**Solutions**:

1. Verify extension is installed:

   ```bash
   php -m | grep redis
   ```

2. Check php.ini:

   ```bash
   php --ini
   ```text

3. Add extension if missing:

   ```ini
   extension=redis
   ```

4. Restart Apache

---

### Issue 3: Permission Denied

**Symptoms**: `Permission denied` errors

**Solutions**:

1. Check Redis socket permissions:

   ```bash
   ls -la /var/run/redis/
   ```text

2. Fix permissions:

   ```bash
   sudo chmod 777 /var/run/redis/redis-server.sock
   ```

---

### Issue 4: High Memory Usage

**Symptoms**: Redis using too much memory

**Solutions**:

1. Check memory usage:

   ```bash
   redis-cli info memory
   ```text

2. Set max memory in redis.conf:

   ```conf
   maxmemory 256mb
   maxmemory-policy allkeys-lru
   ```

3. Restart Redis:

   ```bash
   sudo service redis-server restart
   ```text

---

## Performance Benchmarks

Expected performance metrics:

| Operation    | Without Redis | With Redis | Improvement |
| ------------ | ------------- | ---------- | ----------- |
| Cache Read   | 50ms          | 1ms        | 50x faster  |
| Cache Write  | 30ms          | 2ms        | 15x faster  |
| Session Read | 20ms          | 0.5ms      | 40x faster  |
| Queue Job    | 100ms         | 10ms       | 10x faster  |

## Monitoring Commands

### Real-time Monitoring

```bash
# Monitor all Redis commands
redis-cli monitor

# Monitor specific database
redis-cli -n 1 monitor

# Monitor with filtering
redis-cli monitor | grep "GET"
```

## Statistics

```bash
# Get all info
redis-cli info

# Get specific section
redis-cli info memory
redis-cli info stats
redis-cli info clients

# Get slow log
redis-cli slowlog get 10
```text

## Key Analysis

```bash
# Count keys by pattern
redis-cli -n 1 keys "umamusume-career-planner-cache-*" | wc -l

# Find big keys
redis-cli --bigkeys

# Sample keys
redis-cli --scan --pattern "*training*"
```text

## Conclusion

This testing guide ensures comprehensive validation of Redis integration. All tests should pass before deploying to
production.

For additional support, refer to:

- [Redis WSL Setup Guide](./REDIS_WSL_SETUP_GUIDE.md)
- [Laravel Redis Documentation](https://laravel.com/docs/12.x/redis)
- Project spec: `.kiro/specs/umamusume-career-planner-main/`
