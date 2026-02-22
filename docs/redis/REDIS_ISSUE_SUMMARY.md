# Redis Connectivity Issue - Executive Summary

## Problem Statement

Tests fail to connect to Redis running in WSL, resulting in skipped tests and timeouts.

## Root Causes Identified

### 1. Test Configuration Issue (FIXED) ✅

**Problem**: `phpunit.xml` hardcoded `REDIS_HOST=127.0.0.1`, overriding `.env` configuration.

**Solution**:

- Created `.env.testing` with proper Redis configuration
- Removed hardcoded Redis settings from `phpunit.xml`
- Tests now read Redis configuration from `.env.testing`

### 2. WSL2 Networking Issue (PRIMARY ISSUE) ❌

**Problem**: Windows cannot directly connect to WSL's IP address (`172.18.205.249:6379`) due to WSL2's virtualized
networking.

**Evidence**:

```text
✅ Redis running in WSL: wsl redis-cli ping → PONG
❌ Windows to WSL: Test-NetConnection 172.18.205.249:6379 → Timeout
❌ PHP to WSL: new Redis()->connect('172.18.205.249', 6379) → Timeout
```

**Root Cause**: WSL2 uses Hyper-V virtualization with a separate network namespace. The WSL IP is only accessible from
within WSL, not from Windows.

## Solutions

### Recommended: WSL Mirrored Networking (WSL 2.0+)

**Why**: Cleanest solution, works for all WSL services, no manual port forwarding.

**Implementation**:

```powershell
# Run the setup script
.\setup-wsl-redis.ps1
```text

**Manual steps**:

1. Create `C:\Users\<YourUsername>\.wslconfig`:

   ```ini
   [wsl2]
   networkingMode=mirrored
   ```

1. Restart WSL:

   ```powershell
   wsl --shutdown
   # Wait 8 seconds
   wsl
   ```text

2. Update `.env` and `.env.testing`:

   ```env
   REDIS_HOST=127.0.0.1
   ```

3. Test:

   ```bash
   php test-redis.php
   php artisan test --filter=FallbackRecoveryTest
   ```text

## Alternative: Windows Port Forwarding

If mirrored networking doesn't work:

```powershell
# Run as Administrator
$wslIP = (wsl hostname -I).Trim()
netsh interface portproxy add v4tov4 listenport=6379 listenaddress=127.0.0.1 connectport=6379 connectaddress=$wslIP
```

## Files Created

1. **REDIS_DIAGNOSIS.md** - Comprehensive technical analysis
2. **WSL_REDIS_FIX.md** - Detailed solution guide with 4 options
3. **setup-wsl-redis.ps1** - Automated setup script
4. **.env.testing** - Test environment configuration
5. **phpunit.xml** - Updated (removed hardcoded Redis config)

## Quick Start

```powershell
# Option 1: Automated setup (recommended)
.\setup-wsl-redis.ps1

# Option 2: Manual setup
# 1. Create .wslconfig with mirrored networking
# 2. Restart WSL: wsl --shutdown
# 3. Update .env files to use 127.0.0.1
# 4. Test: php test-redis.php
```text

## Testing Verification

After fix:

```bash
# Clear caches
php artisan config:clear

# Test Redis connection
php test-redis.php

# Run specific test
php artisan test --filter="API Health Monitoring" --compact

# Run all fallback tests
php artisan test --filter=FallbackRecoveryTest --compact
```

## Why This Happened

1. **WSL2 Architecture**: Uses virtualized networking, unlike WSL1's shared network stack
2. **IP Address Isolation**: WSL IP (`172.18.205.249`) is only accessible within WSL
3. **Port Forwarding Limitation**: Automatic port forwarding doesn't work for all services
4. **Configuration Override**: PHPUnit's `<env>` tags override environment variables

## Expected Outcome

After implementing the fix:

- ✅ Tests connect to Redis via `127.0.0.1:6379`
- ✅ No more "Redis is not available" errors
- ✅ No more test timeouts
- ✅ All FallbackRecoveryTest tests run successfully

## Additional Notes

### WSL IP Stability

WSL IP addresses change on restart. Using `127.0.0.1` with mirrored networking or port forwarding solves this.

### Test Database Isolation

Tests use Redis database 15 (separate from development databases 0, 1, 2) to prevent data conflicts.

### Laravel Best Practices

- `.env.testing` is the standard Laravel approach for test configuration
- Removing hardcoded values from `phpunit.xml` makes configuration more flexible
- Using `APP_ENV=testing` automatically loads `.env.testing`

## Troubleshooting

If tests still fail after setup:

1. **Verify WSL version**: `wsl --version` (need 2.0.0+)
2. **Check Redis status**: `wsl sudo service redis-server status`
3. **Test connection**: `php test-redis.php`
4. **Check firewall**: Windows Firewall may block connections
5. **Restart computer**: `.wslconfig` changes may require full restart

## Documentation Updates Needed

- [ ] Update README.md with Redis setup instructions
- [ ] Update INSTALL.md with WSL configuration
- [ ] Update .env.example with Redis configuration
- [ ] Add troubleshooting section for WSL networking

## References

- [WSL Networking Documentation](https://learn.microsoft.com/en-us/windows/wsl/networking)
- [WSL Mirrored Networking](https://learn.microsoft.com/en-us/windows/wsl/wsl-config#mirrored-mode-networking)
- [Laravel Testing Environment](https://laravel.com/docs/12.x/testing#environment)
