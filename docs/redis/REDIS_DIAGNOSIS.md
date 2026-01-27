# WSL Redis Connectivity and Test Execution Diagnosis

## Executive Summary

**Root Cause Identified**: The `phpunit.xml` file hardcodes `REDIS_HOST=127.0.0.1` in the `<php>` section, which overrides the `.env` file configuration. Tests are attempting to connect to `127.0.0.1:6379` instead of the WSL IP `172.18.205.249:6379`.

**Status**: ✅ Redis is running correctly in WSL and accessible from Windows  
**Issue**: ❌ Test configuration prevents tests from using the correct Redis host

---

## Detailed Analysis

### 1. Redis Server Status ✅

**Verification**:

```bash
wsl bash -c "redis-cli -h 172.18.205.249 ping"
# Output: PONG
```

**Configuration**:

- Redis is running on WSL at `172.18.205.249:6379`
- Configured with `bind 0.0.0.0` (accepts connections from any IP)
- Configured with `protected-mode no` (allows external connections)
- Direct PHP connection works: `php test-redis.php` successfully connects

**Conclusion**: Redis server is properly configured and accessible.

---

### 2. Application Configuration ✅

**`.env` file** (correct):

```env
REDIS_CLIENT=phpredis
REDIS_HOST=172.18.205.249
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
```

**Laravel config** (`config/database.php`):

```php
'default' => [
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'port' => env('REDIS_PORT', '6379'),
    'database' => env('REDIS_DB', '0'),
    // ...
]
```

**Verification**:

```php
// php artisan tinker
config('database.redis.default.host')
// Output: "172.18.205.249"
```

**Conclusion**: Application configuration is correct and reads from `.env` properly.

---

### 3. Test Configuration ❌ **ROOT CAUSE**

**`phpunit.xml` file** (lines 60-63):

```xml
<env name="REDIS_CLIENT" value="phpredis"/>
<env name="REDIS_HOST" value="127.0.0.1"/>  <!-- ❌ HARDCODED -->
<env name="REDIS_PORT" value="6379"/>
<env name="REDIS_DB" value="15"/>
```

**Problem**: PHPUnit's `<env>` directives override environment variables, including those from `.env` files. When tests run:

1. PHPUnit loads `phpunit.xml`
2. Sets `REDIS_HOST=127.0.0.1` (hardcoded value)
3. Laravel's `env('REDIS_HOST')` returns `127.0.0.1`
4. Tests attempt to connect to `127.0.0.1:6379` (localhost)
5. Connection fails because Redis is on `172.18.205.249:6379` (WSL)

**Test Behavior**:

```php
// tests/Feature/FallbackRecoveryTest.php (lines 18-28)
beforeEach(function () {
    try {
        $pong = Redis::connection()->ping();
        if ($pong !== true && $pong !== 'PONG') {
            $this->markTestSkipped('Redis is not responding correctly');
        }
        Redis::flushdb();
    } catch (\Exception $e) {
        $this->markTestSkipped('Redis is not available: '.$e->getMessage());
    }
    Cache::flush();
});
```

The test correctly tries to connect but fails because it's using `127.0.0.1` instead of the WSL IP.

---

### 4. WSL Networking Behavior

**WSL2 Port Forwarding**:

- WSL2 version 2.6.1.0 uses a virtualized network adapter
- Automatic port forwarding from `127.0.0.1` to WSL **does not work** for Redis
- This is a known WSL2 limitation with certain services
- Manual connection to WSL IP (`172.18.205.249`) works correctly

**Why `127.0.0.1` doesn't work**:

- WSL2 creates a separate network namespace
- Redis binds to `0.0.0.0` inside WSL, but Windows sees it as a different network
- Port forwarding requires explicit configuration or using the WSL IP directly

---

## Solution

### Option 1: Update phpunit.xml (Recommended)

**Change** `phpunit.xml` line 61:

```xml
<!-- Before -->
<env name="REDIS_HOST" value="127.0.0.1"/>

<!-- After -->
<env name="REDIS_HOST" value="172.18.205.249"/>
```

**Pros**:

- Simple one-line fix
- Tests will connect to WSL Redis immediately
- No code changes required

**Cons**:

- Hardcodes WSL IP (may change on WSL restart)
- Other developers may have different WSL IPs

---

### Option 2: Remove REDIS_HOST from phpunit.xml (Better)

**Remove** line 61 from `phpunit.xml`:

```xml
<!-- Remove this line -->
<env name="REDIS_HOST" value="127.0.0.1"/>
```

**Pros**:

- Tests will read from `.env` file
- Flexible for different environments
- Developers can configure their own Redis host

**Cons**:

- Requires developers to have `.env` configured correctly
- May need documentation update

---

### Option 3: Use Environment Variable Override (Most Flexible)

**Modify** `phpunit.xml` to use environment variable:

```xml
<!-- Before -->
<env name="REDIS_HOST" value="127.0.0.1"/>

<!-- After -->
<env name="REDIS_HOST" value="${REDIS_HOST}"/>
```

**Note**: PHPUnit doesn't support variable substitution in `<env>` tags, so this won't work. Use Option 2 instead.

---

### Option 4: Create .env.testing (Laravel Standard)

**Create** `.env.testing` file:

```env
REDIS_HOST=172.18.205.249
REDIS_PORT=6379
REDIS_DB=15
REDIS_CACHE_DB=15
REDIS_SESSION_DB=15
```

**Remove** Redis configuration from `phpunit.xml`:

```xml
<!-- Remove these lines -->
<env name="REDIS_HOST" value="127.0.0.1"/>
<env name="REDIS_PORT" value="6379"/>
<env name="REDIS_DB" value="15"/>
<env name="REDIS_CACHE_DB" value="15"/>
<env name="REDIS_SESSION_DB" value="15"/>
```

**Pros**:

- Laravel standard practice
- Separate test configuration
- Easy to maintain
- Doesn't affect development environment

**Cons**:

- Requires creating new file
- Developers need to configure `.env.testing`

---

## Recommended Solution

**Use Option 4: Create `.env.testing`** (Laravel best practice)

### Step-by-Step Implementation

1. **Create `.env.testing`**:

```bash
cp .env .env.testing
```

1. **Edit `.env.testing`** to set Redis configuration:

```env
# Redis Configuration (WSL)
REDIS_CLIENT=phpredis
REDIS_HOST=172.18.205.249
REDIS_PORT=6379
REDIS_DB=15
REDIS_CACHE_DB=15
REDIS_SESSION_DB=15
```

1. **Update `phpunit.xml`** - Remove Redis env overrides:

```xml
<!-- Remove these lines from <php> section -->
<env name="REDIS_CLIENT" value="phpredis"/>
<env name="REDIS_HOST" value="127.0.0.1"/>
<env name="REDIS_PORT" value="6379"/>
<env name="REDIS_DB" value="15"/>
<env name="REDIS_CACHE_DB" value="15"/>
<env name="REDIS_SESSION_DB" value="15"/>
```

1. **Clear configuration cache**:

```bash
php artisan config:clear
```

1. **Run tests**:

```bash
php artisan test --filter=FallbackRecoveryTest
```

---

## Alternative Quick Fix (For Immediate Testing)

If you just want to run tests immediately without creating `.env.testing`:

**Edit `phpunit.xml` line 61**:

```xml
<env name="REDIS_HOST" value="172.18.205.249"/>
```

**Run tests**:

```bash
php artisan config:clear
php artisan test --filter=FallbackRecoveryTest
```

---

## Why Tests Timeout

**Current Behavior**:

1. Test tries to connect to `127.0.0.1:6379`
2. Connection attempt times out (no Redis on localhost)
3. Exception is caught in `beforeEach()`
4. Test is marked as skipped with message: "Redis is not available: No connection could be made"

**Expected Behavior After Fix**:

1. Test connects to `172.18.205.249:6379`
2. Connection succeeds
3. Redis is flushed
4. Tests run normally

---

## WSL IP Address Stability

**Important Note**: WSL IP addresses can change when:

- WSL is restarted
- Windows is restarted
- Network configuration changes

**To find current WSL IP**:

```bash
wsl hostname -I
```

**To make WSL IP more stable**, consider:

1. Using WSL2 with fixed IP (requires advanced configuration)
2. Using `localhost` with proper port forwarding (requires WSL configuration)
3. Documenting the IP check process for developers

---

## Testing Verification

After implementing the fix, verify with:

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear

# Run specific test
php artisan test --filter=FallbackRecoveryTest --compact

# Run all tests
php artisan test --compact
```

**Expected Output**:

```
PASS  Tests\Feature\FallbackRecoveryTest
✓ checks health status of all APIs
✓ tracks failure count for APIs
✓ opens circuit breaker after threshold failures
...
```

---

## Documentation Updates Needed

After implementing the fix, update:

1. **README.md** - Add Redis configuration section
2. **INSTALL.md** - Document WSL Redis setup
3. **.env.example** - Add Redis configuration with WSL IP example
4. **CONTRIBUTING.md** - Add testing setup instructions

---

## Summary

| Component | Status | Issue | Fix |
|-----------|--------|-------|-----|
| Redis Server | ✅ Working | None | None |
| WSL Networking | ✅ Working | Port forwarding limitation | Use WSL IP directly |
| .env Configuration | ✅ Working | None | None |
| phpunit.xml | ❌ Broken | Hardcoded 127.0.0.1 | Update to WSL IP or remove |
| Tests | ❌ Skipped | Can't connect to Redis | Fix phpunit.xml |

**Action Required**: Update `phpunit.xml` to use WSL IP (`172.18.205.249`) or create `.env.testing` with correct Redis configuration.

---

## Additional Notes

### Redis Client Configuration

The project uses `phpredis` extension (correct):

```env
REDIS_CLIENT=phpredis
```

This is the recommended Redis client for Laravel and provides better performance than `predis`.

### Test Database Configuration

Tests correctly use separate Redis database (DB 15):

```xml
<env name="REDIS_DB" value="15"/>
<env name="REDIS_CACHE_DB" value="15"/>
<env name="REDIS_SESSION_DB" value="15"/>
```

This prevents test data from interfering with development data.

### Cache Configuration

Tests use array cache driver (correct):

```xml
<env name="CACHE_STORE" value="array"/>
```

This is appropriate for testing and doesn't require Redis for cache operations.

---

## Conclusion

The issue is **not** with Redis, WSL, or networking. It's a **configuration issue** in `phpunit.xml` that hardcodes the wrong Redis host. The fix is simple: either update the hardcoded IP or remove it to use `.env` configuration.

**Recommended Action**: Create `.env.testing` with WSL Redis configuration and remove Redis env overrides from `phpunit.xml`.
