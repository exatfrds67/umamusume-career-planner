# Redis Setup Checklist

## Pre-Installation Status

- [x] Redis installed in WSL
- [x] Redis running and accessible at 127.0.0.1:6379
- [x] .env file updated with Redis configuration
- [ ] phpredis extension installed for Windows PHP
- [ ] Tests updated to use Redis
- [ ] Full test suite passing

## Installation Steps

### Step 1: Install phpredis Extension (10 minutes)

- [ ] Download phpredis DLL for PHP 8.4.11 NTS x64
  - URL: <https://pecl.php.net/package/redis>
  - Or: <https://windows.php.net/downloads/pecl/releases/redis/>
  - Version: Latest compatible with PHP 8.4.11
  - Architecture: x64
  - Thread Safety: NTS (Non-Thread Safe)
  - Compiler: VS16

- [ ] Copy `php_redis.dll` to `C:\xampp\php\ext\`

- [ ] Edit `C:\xampp\php\php.ini`
  - [ ] Add line: `extension=redis`
  - [ ] Save file

- [ ] Restart Apache

  ```powershell
  C:\xampp\apache\bin\httpd.exe -k stop
  C:\xampp\apache\bin\httpd.exe -k start
  ```text

- [ ] Verify installation

  ```powershell
  php -m | Select-String -Pattern "redis"
  ```

  Expected output: `redis`

### Step 2: Configure Laravel for Redis (5 minutes)

- [ ] Update `.env` file:

  ```env
  CACHE_STORE=redis
  QUEUE_CONNECTION=redis
  SESSION_DRIVER=redis
  ```text

- [ ] Clear configuration cache:

  ```powershell
  php artisan config:clear
  php artisan cache:clear
  ```

- [ ] Test Redis connection:

  ```powershell
  php artisan redis:health
  ```text

  Expected: Connection successful message

- [ ] Test detailed Redis info:

  ```powershell
  php artisan redis:health --detailed
  ```

### Step 3: Update Tests (10 minutes)

- [ ] Update `tests/Feature/FallbackRecoveryTest.php`
  - [ ] Update beforeEach hook (line 21-26)
  - [ ] Remove skip from "queues sync job" test (line 150)
  - [ ] Remove skip from "gets health status via API" test (line 285)
  - [ ] Remove skip from "gets degradation status via API" test (line 299)
  - [ ] Remove skip from "gets system status via API" test (line 315)

- [ ] Update `tests/Feature/CacheManagementTest.php`
  - [ ] Update condition in "record api response time stores metrics" (line 178)
  - [ ] Update condition in "get api response time stats calculates percentiles" (line 197)
  - [ ] Update condition in "clear all removes all application caches" (line 222)

### Step 4: Run Tests (10 minutes)

- [ ] Run Redis-specific tests:

  ```powershell
  php artisan test --filter=Redis --compact
  ```text

- [ ] Run FallbackRecovery tests:

  ```powershell
  php artisan test --filter=FallbackRecovery --compact
  ```

- [ ] Run CacheManagement tests:

  ```powershell
  php artisan test --filter=CacheManagement --compact
  ```text

- [ ] Run full test suite:

  ```powershell
  php artisan test --compact
  ```

### Step 5: Warm Cache (5 minutes)

- [ ] Warm application cache:

  ```powershell
  php artisan cache:warm
  ```text

- [ ] Verify cache is working:

  ```powershell
  wsl bash -c "redis-cli keys 'umamusume-career-planner:*'"
  ```

  Expected: List of cached keys

### Step 6: Monitor Redis (Optional)

- [ ] Check Redis memory usage:

  ```powershell
  wsl bash -c "redis-cli info memory"
  ```text

- [ ] Monitor Redis in real-time:

  ```powershell
  wsl bash -c "redis-cli monitor"
  ```

  (Press Ctrl+C to stop)

- [ ] Check Redis stats:

  ```powershell
  wsl bash -c "redis-cli info stats"
  ```text

## Verification Checklist

### Redis Service

- [ ] Redis is running in WSL

  ```powershell
  wsl bash -c "redis-cli ping"
  ```

  Expected: `PONG`

- [ ] Redis is accessible from Windows

  ```powershell
  wsl bash -c "redis-cli -h 127.0.0.1 -p 6379 ping"
  ```text

  Expected: `PONG`

### PHP Extension

- [ ] phpredis extension is loaded

  ```powershell
  php -m | Select-String -Pattern "redis"
  ```

  Expected: `redis`

- [ ] PHP can connect to Redis

  ```powershell
  php -r "try { $redis = new Redis(); $redis->connect('127.0.0.1', 6379); echo 'Connected'; } catch (Exception $e) { echo 'Failed: ' . $e->getMessage(); }"
  ```text

  Expected: `Connected`

### Laravel Configuration

- [ ] Cache driver is set to Redis

  ```powershell
  php artisan tinker --execute="echo config('cache.default');"
  ```

  Expected: `redis`

- [ ] Queue driver is set to Redis

  ```powershell
  php artisan tinker --execute="echo config('queue.default');"
  ```text

  Expected: `redis`

- [ ] Session driver is set to Redis

  ```powershell
  php artisan tinker --execute="echo config('session.driver');"
  ```

  Expected: `redis`

### Application Health

- [ ] Redis health check passes

  ```powershell
  php artisan redis:health
  ```text

- [ ] Cache operations work

  ```powershell
  php artisan tinker --execute="Cache::put('test', 'value', 60); echo Cache::get('test');"
  ```

  Expected: `value`

- [ ] All tests pass

  ```powershell
  php artisan test --compact
  ```text

## Troubleshooting

### Issue: phpredis extension not loading

**Symptoms:**

- `php -m` doesn't show redis
- "Class 'Redis' not found" errors

**Solutions:**

1. Verify DLL is in correct location: `C:\xampp\php\ext\php_redis.dll`
2. Check php.ini has `extension=redis` (not `extension=php_redis.dll`)
3. Restart Apache completely
4. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
5. Verify DLL matches PHP version (8.4.11 NTS x64)

### Issue: Connection refused

**Symptoms:**

- "Connection refused" errors
- Tests fail with connection errors

**Solutions:**

1. Check Redis is running: `wsl bash -c "redis-cli ping"`
2. Restart Redis: `wsl bash -c "sudo service redis-server restart"`
3. Check Redis is listening on 127.0.0.1: `wsl bash -c "redis-cli -h 127.0.0.1 ping"`
4. Verify .env has correct host: `REDIS_HOST=127.0.0.1`
5. Check firewall settings

### Issue: Tests still skipping

**Symptoms:**

- Tests show "skipped" status
- "Redis extension is not available" messages

**Solutions:**

1. Verify .env has `CACHE_STORE=redis`
2. Clear config cache: `php artisan config:clear`
3. Check test file conditions are updated
4. Verify phpredis is loaded: `php -m | Select-String redis`

### Issue: Performance problems

**Symptoms:**

- Slow cache operations
- High memory usage

**Solutions:**

1. Check Redis memory: `wsl bash -c "redis-cli info memory"`
2. Monitor slow queries: `wsl bash -c "redis-cli slowlog get 10"`
3. Adjust cache TTL values in .env
4. Consider Redis persistence settings
5. Check for memory leaks

## Post-Installation Tasks

- [ ] Document any issues encountered
- [ ] Update team documentation
- [ ] Configure Redis persistence (if needed)
- [ ] Set up Redis monitoring (if needed)
- [ ] Configure Redis backups (if needed)
- [ ] Review and optimize cache TTL values
- [ ] Monitor application performance

## Success Criteria

✅ All items checked above
✅ phpredis extension loaded
✅ Redis connection working
✅ All tests passing
✅ Cache operations working
✅ No errors in logs

## Estimated Total Time

- Installation: 10 minutes
- Configuration: 5 minutes
- Test Updates: 10 minutes
- Testing: 10 minutes
- Cache Warming: 5 minutes
- **Total: ~40 minutes**

## Next Steps After Completion

1. Monitor Redis performance in production
2. Set up Redis persistence if needed
3. Configure Redis backups
4. Optimize cache strategies based on usage patterns
5. Consider Redis Sentinel for high availability (future)

## Support Resources

- Laravel Redis Documentation: <https://laravel.com/docs/12.x/redis>
- phpredis GitHub: <https://github.com/phpredis/phpredis>
- Redis Documentation: <https://redis.io/documentation>
- PECL Redis: <https://pecl.php.net/package/redis>

## Files Created/Modified

- ✅ `.env` - Updated with complete Redis configuration
- ✅ `REDIS_SETUP_INSTRUCTIONS.md` - Detailed setup guide
- ✅ `UPDATE_REDIS_TESTS.md` - Test update instructions
- ✅ `REDIS_SETUP_CHECKLIST.md` - This checklist
- ⏳ `tests/Feature/FallbackRecoveryTest.php` - To be updated
- ⏳ `tests/Feature/CacheManagementTest.php` - To be updated
