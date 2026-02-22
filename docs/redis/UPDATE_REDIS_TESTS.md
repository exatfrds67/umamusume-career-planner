# Redis Tests Update Guide

## Overview

After installing phpredis extension, these tests need to be updated to work with Redis.

## Tests to Update

### 1. FallbackRecoveryTest.php

**Current Status:** Tests are skipped when Redis is not available

**Changes Needed:**

1. Update the `beforeEach` hook to check for Redis connection instead of just extension:

```php
beforeEach(function () {
    // Skip if Redis is not available or not configured
    if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
        $this->markTestSkipped('Redis is not available or not configured');
    }
    // Clear cache before each test
    Cache::flush();
});
```text

1. Remove the `->skip()` calls from these tests:
   - "queues sync job"
   - "gets health status via API"
   - "gets degradation status via API"
   - "gets system status via API"

### 2. CacheManagementTest.php

**Current Status:** Some tests check for Redis and skip if not available

**Changes Needed:**

1. Update the conditional checks to be more robust:

```php
// Replace this pattern:
if (config('cache.default') !== 'redis') {
    $this->markTestSkipped('Redis required for API response time tracking');
}

// With this:
if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
    $this->markTestSkipped('Redis required for this test');
}
```

1. Tests that need this update:
   - "record api response time stores metrics"
   - "get api response time stats calculates percentiles"
   - "clear all removes all application caches"

## Automated Update Script

Run this after phpredis is installed and .env is configured:

```powershell
# Step 1: Update .env to use Redis
# Manually change these lines in .env:
# CACHE_STORE=redis
# QUEUE_CONNECTION=redis
# SESSION_DRIVER=redis

# Step 2: Clear config cache
php artisan config:clear

# Step 3: Test Redis connection
php artisan redis:health

# Step 4: Run Redis-specific tests
php artisan test --filter=FallbackRecovery
php artisan test --filter=CacheManagement

# Step 5: Run full test suite
php artisan test
```text

## Manual Test File Updates

### FallbackRecoveryTest.php Updates

**Line 21-26:** Update beforeEach hook

```php
beforeEach(function () {
    // Skip if Redis is not available or not configured
    if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
        $this->markTestSkipped('Redis is not available or not configured');
    }
    // Clear cache before each test
    Cache::flush();
});
```

**Line 150:** Remove skip

```php
// Remove: })->skip('Requires Redis which is not available in test environment');
// Replace with: });
```text

**Line 285:** Remove skip

```php
// Remove: })->skip('Requires Redis which is not available in test environment');
// Replace with: });
```

**Line 299:** Remove skip

```php
// Remove: })->skip('Requires Redis which is not available in test environment');
// Replace with: });
```text

**Line 315:** Remove skip

```php
// Remove: })->skip('Requires Redis which is not available in test environment');
// Replace with: });
```

### CacheManagementTest.php Updates

**Line 178-180:** Update condition

```php
if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
    $this->markTestSkipped('Redis required for API response time tracking');
}
```text

**Line 197-199:** Update condition

```php
if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
    $this->markTestSkipped('Redis required for API response time tracking');
}
```

**Line 222-224:** Update condition

```php
if (! extension_loaded('redis') || config('cache.default') !== 'redis') {
    $this->markTestSkipped('Redis required for pattern-based cache clearing');
}
```text

## Verification Steps

After making changes:

1. **Verify Redis is running:**

   ```powershell
   wsl bash -c "redis-cli ping"
   # Should return: PONG
   ```

1. **Verify phpredis is loaded:**

   ```powershell
   php -m | Select-String -Pattern "redis"
   # Should show: redis
   ```text

2. **Test Redis connection from Laravel:**

   ```powershell
   php artisan redis:health --detailed
   ```

3. **Run updated tests:**

   ```powershell
   php artisan test --filter=FallbackRecovery --compact
   php artisan test --filter=CacheManagement --compact
   ```

4. **Check for any failures:**
   - If tests fail, check Redis connection
   - Verify .env has correct Redis configuration
   - Check Laravel logs for errors

## Expected Test Results

After updates, all tests should pass:

- ✅ FallbackRecoveryTest: All tests should run (not skipped)
- ✅ CacheManagementTest: Redis-specific tests should run
- ✅ No "Redis extension is not available" messages
- ✅ No "Requires Redis which is not available" skips

## Troubleshooting

### Tests Still Skipping

- Check .env has `CACHE_STORE=redis`
- Run `php artisan config:clear`
- Verify Redis is running in WSL

### Connection Errors

- Check Redis is accessible: `wsl bash -c "redis-cli -h 127.0.0.1 -p 6379 ping"`
- Verify firewall allows connection
- Check Redis configuration in config/database.php

### Performance Issues

- Monitor Redis memory usage
- Check for slow queries
- Consider adjusting cache TTL values
