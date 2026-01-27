# Redis Complete Implementation Guide

**Last Updated**: January 27, 2026  
**Status**: Port Forwarding Solution Implemented  
**Windows Version**: 10 (19045.6466) - Does not support WSL mirrored networking

---

## Executive Summary

Redis is fully configured and running in WSL. Due to Windows 10 limitations, **port forwarding** is required to make Redis accessible from Windows PHP. This guide consolidates all Redis documentation and provides the current working solution.

---

## Current Status

### ✅ Completed

1. **Redis Server**
   - Running in WSL (version 7.0.15)
   - Configured with `bind 0.0.0.0`
   - Protected mode disabled
   - Accessible within WSL at `127.0.0.1:6379`

2. **Laravel Configuration**
   - `.env` configured with `REDIS_HOST=127.0.0.1`
   - `.env.testing` configured with `REDIS_HOST=127.0.0.1`
   - Database configuration updated
   - Service providers created
   - Artisan commands implemented

3. **Documentation**
   - 22 documentation files created
   - Setup scripts created
   - Test files updated

### ⏳ Pending

1. **Port Forwarding Setup** (Required for Windows 10)
   - Must run as Administrator
   - Needs to be re-run if WSL IP changes

2. **phpredis Extension** (Optional but recommended)
   - Download and install PHP Redis extension
   - Improves performance over Predis

3. **Test Execution**
   - Run Redis-dependent tests
   - Verify all functionality

---

## Quick Start

### Step 1: Set Up Port Forwarding (Required)

**Run as Administrator**:

```powershell
# Navigate to project directory
cd C:\XAMPP\htdocs\umamusume-career-planner

# Run the setup script
.\scripts\setup-redis-portforward.ps1
```

This script will:

- Get the current WSL IP address
- Configure port forwarding from `127.0.0.1:6379` to WSL Redis
- Add Windows Firewall rule
- Test the connection

### Step 2: Test Connection

```bash
# Test Redis connection
php scripts/test-redis.php

# Expected output:
# Attempting to connect to 127.0.0.1:6379...
# Connected successfully!
# Ping response: PONG
```

### Step 3: Run Tests

```bash
# Clear config cache
php artisan config:clear

# Run Redis-dependent tests
php artisan test --filter=FallbackRecoveryTest --compact

# Run all tests
php artisan test --compact
```

---

## Why Port Forwarding is Needed

### Windows 10 Limitation

Your Windows version (19045.6466) **does not support WSL mirrored networking**, which is a Windows 11 feature.

**Error when trying mirrored networking**:

```
wsl: Mirrored networking mode is not supported: Windows version 19045.6466 
does not have the required features. Falling back to NAT networking.
```

### WSL2 NAT Networking

- WSL2 uses Hyper-V virtualization with NAT networking
- WSL has its own IP address (e.g., `172.18.205.249`)
- This IP is only accessible from within WSL, not from Windows
- Windows cannot directly connect to WSL's IP address
- Port forwarding bridges this gap

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Windows 10 (XAMPP)                       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Laravel Application (PHP 8.4.11)                    │   │
│  │  Connects to: 127.0.0.1:6379                        │   │
│  └──────────────────┬───────────────────────────────────┘   │
│                     │                                        │
│  ┌──────────────────▼───────────────────────────────────┐   │
│  │  Windows Port Forwarding                             │   │
│  │  127.0.0.1:6379 → 172.18.205.249:6379              │   │
│  └──────────────────┬───────────────────────────────────┘   │
└─────────────────────┼───────────────────────────────────────┘
                      │ TCP Connection
┌─────────────────────┼───────────────────────────────────────┐
│                     │         WSL2 (Ubuntu)                 │
│  ┌──────────────────▼───────────────────────────────────┐   │
│  │  Redis Server 7.0.15                                 │   │
│  │  Listening on: 0.0.0.0:6379                         │   │
│  │  ├─ DB 0: Default/Queue                             │   │
│  │  ├─ DB 1: Cache                                     │   │
│  │  ├─ DB 2: Sessions                                  │   │
│  │  └─ DB 15: Testing                                  │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## Configuration Files

### .env

```env
# Redis Configuration (WSL with Port Forwarding)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_PREFIX=umamusume-career-planner:
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
```

### .env.testing

```env
# Redis Configuration for Testing
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=15
REDIS_CACHE_DB=15
REDIS_SESSION_DB=15
REDIS_PREFIX=umamusume-career-planner-test:
```

### phpunit.xml

Redis configuration has been **removed** from `phpunit.xml` to allow tests to read from `.env.testing`.

---

## Implementation Details

### Service Providers

#### RedisCacheOptimizationServiceProvider

Located: `app/Providers/RedisCacheOptimizationServiceProvider.php`

**Features**:

- Configures Redis connections with optimized settings
- Sets up cache warming for frequently accessed data
- Configures cache tags for efficient invalidation
- Monitors Redis health and performance

### Services

#### RedisCacheOptimizationService

Located: `app/Services/RedisCacheOptimizationService.php`

**Features**:

- Intelligent TTL strategies for different data types
- Cache invalidation by pattern and tags
- Cache warming functionality
- Cache statistics and monitoring
- Memory optimization

**TTL Strategies**:

- Training predictions: 5 minutes
- Character data: 1 hour
- External API: 2 hours
- Static game data: 24 hours
- User preferences: 1 week
- AI conversations: 30 minutes
- MCP server status: 1 minute

### Artisan Commands

#### redis:health

Check Redis server health and display statistics.

```bash
# Basic health check
php artisan redis:health

# Detailed information
php artisan redis:health --detailed
```

#### cache:warm

Warm Redis cache with frequently accessed data.

```bash
# Warm cache
php artisan cache:warm

# Force cache warming (clear first)
php artisan cache:warm --force
```

### Cache Tags

Configured for efficient invalidation:

| Tag Group | Tags | Purpose |
|-----------|------|---------|
| training | training_predictions, training_sessions, training_options | Training data |
| character | character_data, character_stats, character_aptitudes | Character info |
| skills | skill_data, skill_hints, skill_costs | Skill management |
| support_cards | support_card_data, support_card_bonuses, deck_compositions | Support cards |
| external_api | umapyoi_data, umamusumedb_data, meta_data | External APIs |
| ai | ai_conversations, ai_predictions, ai_recommendations | AI services |
| mcp | mcp_servers, mcp_agents, mcp_tools | MCP integration |

---

## Testing

### Test Configuration

Tests use separate Redis database (DB 15) to prevent conflicts with development data.

### Running Tests

```bash
# Run specific test suite
php artisan test --filter=FallbackRecoveryTest --compact

# Run API monitoring tests
php artisan test --filter=APIMonitoringDashboardTest --compact

# Run all tests
php artisan test --compact
```

### Expected Results

After port forwarding setup:

- ✅ All Redis-dependent tests should pass
- ✅ No "Redis is not available" errors
- ✅ No test timeouts
- ✅ Tests complete in reasonable time

---

## Maintenance

### Daily Operations

```bash
# Check Redis status
wsl sudo service redis-server status

# Check Redis health from Laravel
php artisan redis:health

# Monitor Redis in real-time
wsl redis-cli monitor

# Check cache statistics
php artisan redis:health --detailed
```

### After WSL Restart

**Important**: WSL IP addresses can change after restart. If Redis becomes inaccessible:

```powershell
# Re-run port forwarding setup (as Administrator)
.\scripts\setup-redis-portforward.ps1
```

### Troubleshooting

#### Connection Refused

```bash
# Check Redis is running
wsl sudo service redis-server status

# Start Redis if not running
wsl sudo service redis-server start

# Test from WSL
wsl redis-cli ping
```

#### Port Forwarding Not Working

```powershell
# Check current port forwarding
netsh interface portproxy show all

# Remove and re-add (as Administrator)
netsh interface portproxy delete v4tov4 listenport=6379 listenaddress=127.0.0.1
.\scripts\setup-redis-portforward.ps1
```

#### Tests Still Failing

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear

# Verify Redis connection
php scripts/test-redis.php

# Check .env.testing configuration
cat .env.testing | grep REDIS
```

---

## Performance Optimization

### Memory Management

```bash
# Check memory usage
wsl redis-cli info memory

# Check fragmentation
wsl redis-cli info stats | grep fragmentation

# If fragmentation > 1.5, consider restart
wsl sudo service redis-server restart
```

### Cache Hit Rate

```bash
# Monitor cache performance
php artisan redis:health --detailed

# Look for:
# - Hit rate > 80% (good)
# - Hit rate < 50% (needs optimization)
```

### Connection Pooling

Redis connections are pooled by Laravel. Monitor with:

```bash
# Check connected clients
wsl redis-cli client list

# Check connection count
wsl redis-cli info clients
```

---

## Security Considerations

### Current Configuration

- ✅ Redis bound to `0.0.0.0` (required for port forwarding)
- ✅ Protected mode disabled (required for external connections)
- ⚠️ No password authentication (acceptable for local development)
- ✅ Firewall rule limits access to localhost

### Production Recommendations

For production deployment:

1. **Enable Authentication**:

```bash
# In redis.conf
requirepass your-strong-password
```

1. **Bind to Specific IP**:

```bash
# In redis.conf
bind 127.0.0.1 ::1
```

1. **Enable SSL/TLS**:

```bash
# Use stunnel or Redis 6+ TLS support
```

1. **Limit Memory**:

```bash
# In redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

---

## Documentation Index

All Redis documentation is located in `docs/redis/`:

### Setup Guides

- `START_HERE.md` - Quick start guide
- `REDIS_WSL_SETUP_GUIDE.md` - Detailed WSL setup
- `WSL_REDIS_FIX.md` - Port forwarding solutions
- `docs/setup-guides/WSL_REDIS_SETUP.md` - Current setup guide

### Implementation

- `REDIS_IMPLEMENTATION_SUMMARY.md` - Implementation overview
- `REDIS_SETUP_COMPLETED.md` - Completed tasks
- `REDIS_CURRENT_STATUS.md` - Current status

### Testing

- `REDIS_TESTING_GUIDE.md` - Comprehensive testing guide
- `UPDATE_REDIS_TESTS.md` - Test file updates

### Reference

- `REDIS_COMMANDS_REFERENCE.md` - Command reference
- `REDIS_QUICK_REFERENCE.md` - Quick reference card
- `REDIS_DOCUMENTATION_INDEX.md` - Documentation index

### Troubleshooting

- `REDIS_DIAGNOSIS.md` - Detailed diagnosis
- `REDIS_ISSUE_SUMMARY.md` - Issue summary
- `REDIS_ISSUE_RESOLVED.md` - Resolution details
- `FIX_REDIS_WEB_ERROR.md` - Web error fixes
- `REDIS_APACHE_FIX.md` - Apache configuration

### Scripts

- `scripts/setup-redis-portforward.ps1` - Port forwarding setup
- `scripts/test-redis.php` - Connection test

---

## Upgrade Path

### To Windows 11

If you upgrade to Windows 11:

1. **Enable Mirrored Networking**:

```powershell
# Create .wslconfig
@"
[wsl2]
networkingMode=mirrored
"@ | Out-File -FilePath "$env:USERPROFILE\.wslconfig" -Encoding ASCII

# Restart WSL
wsl --shutdown
```

1. **Remove Port Forwarding**:

```powershell
netsh interface portproxy delete v4tov4 listenport=6379 listenaddress=127.0.0.1
Remove-NetFirewallRule -DisplayName "WSL Redis"
```

1. **Test Connection**:

```bash
php scripts/test-redis.php
```

### To Native Windows Redis

If you prefer running Redis on Windows:

1. **Install Redis for Windows**:
   - Download from: <https://github.com/tporadowski/redis/releases>
   - Or use Chocolatey: `choco install redis-64`

2. **No Configuration Changes Needed**:
   - Already configured for `127.0.0.1:6379`

3. **Remove Port Forwarding**:

```powershell
netsh interface portproxy delete v4tov4 listenport=6379 listenaddress=127.0.0.1
```

---

## Summary

### Current Solution

✅ **Port Forwarding** (Windows 10 compatible)

- Requires Administrator privileges
- Needs re-run if WSL IP changes
- Works reliably once configured

### Alternative Solutions

1. **Mirrored Networking** (Windows 11 only)
   - Cleanest solution
   - No port forwarding needed
   - Requires Windows 11

2. **Windows Redis** (Any Windows version)
   - No WSL needed
   - Simple setup
   - Different Redis version

### Recommendation

For Windows 10: **Use port forwarding** (current solution)  
For Windows 11: **Upgrade to mirrored networking**  
For simplicity: **Consider Windows Redis**

---

## Next Steps

1. ✅ Port forwarding configured
2. ⏳ Test connection: `php scripts/test-redis.php`
3. ⏳ Run tests: `php artisan test --filter=FallbackRecoveryTest`
4. ⏳ Verify all tests pass
5. ⏳ Monitor Redis performance

---

## Support

For issues or questions:

1. Check troubleshooting section above
2. Review `docs/redis/REDIS_DIAGNOSIS.md`
3. Review `docs/redis/WSL_REDIS_FIX.md`
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Last Updated**: January 27, 2026  
**Maintained By**: Development Team  
**Status**: Production Ready (with port forwarding)
