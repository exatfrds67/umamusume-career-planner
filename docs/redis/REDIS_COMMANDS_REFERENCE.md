# Redis Commands Quick Reference

## WSL Redis Management

### Service Control

```powershell
# Start Redis
wsl bash -c "sudo service redis-server start"

# Stop Redis
wsl bash -c "sudo service redis-server stop"

# Restart Redis
wsl bash -c "sudo service redis-server restart"

# Check Status
wsl bash -c "sudo service redis-server status"
```

### Basic Connection Tests

```powershell
# Ping Redis
wsl bash -c "redis-cli ping"
# Expected: PONG

# Ping with host/port
wsl bash -c "redis-cli -h 127.0.0.1 -p 6379 ping"

# Connect to Redis CLI
wsl bash -c "redis-cli"
```

## Laravel Artisan Commands

### Redis Health

```powershell
# Basic health check
php artisan redis:health

# Detailed health information
php artisan redis:health --detailed
```

### Cache Management

```powershell
# Warm cache
php artisan cache:warm

# Warm specific cache type
php artisan cache:warm --type=characters
php artisan cache:warm --type=support-cards
php artisan cache:warm --type=meta

# Force cache refresh
php artisan cache:warm --force

# Clear cache
php artisan cache:clear

# Clear config cache
php artisan config:clear
```

### Queue Management (Redis-based)

```powershell
# Start queue worker
php artisan queue:work

# Start Horizon (if using)
php artisan horizon

# Check queue status
php artisan queue:monitor
```

## Redis CLI Commands (from WSL)

### Key Operations

```bash
# List all keys
redis-cli keys '*'

# List application keys
redis-cli keys 'umamusume-career-planner:*'

# Get key value
redis-cli get 'umamusume-career-planner:key_name'

# Delete key
redis-cli del 'umamusume-career-planner:key_name'

# Check if key exists
redis-cli exists 'umamusume-career-planner:key_name'

# Get key TTL (time to live)
redis-cli ttl 'umamusume-career-planner:key_name'

# Set key with expiration
redis-cli setex 'test_key' 60 'test_value'
```

### Database Operations

```bash
# Select database
redis-cli select 0  # Default database
redis-cli select 1  # Cache database
redis-cli select 2  # Session database

# Flush current database
redis-cli flushdb

# Flush all databases (DANGEROUS!)
redis-cli flushall

# Get database size
redis-cli dbsize
```

### Monitoring & Stats

```bash
# Monitor all commands in real-time
redis-cli monitor
# Press Ctrl+C to stop

# Get server info
redis-cli info

# Get memory info
redis-cli info memory

# Get stats
redis-cli info stats

# Get replication info
redis-cli info replication

# Get slow log
redis-cli slowlog get 10

# Get client list
redis-cli client list
```

### Performance Analysis

```bash
# Check memory usage by key pattern
redis-cli --bigkeys

# Scan keys with pattern
redis-cli --scan --pattern 'umamusume-career-planner:*'

# Get memory usage of specific key
redis-cli memory usage 'umamusume-career-planner:key_name'

# Latency monitoring
redis-cli --latency

# Latency history
redis-cli --latency-history
```

## PHP Redis Commands (via Tinker)

### Basic Operations

```powershell
# Connect and test
php artisan tinker --execute="$redis = Redis::connection(); echo $redis->ping();"

# Set and get value
php artisan tinker --execute="Cache::put('test', 'value', 60); echo Cache::get('test');"

# Check if key exists
php artisan tinker --execute="echo Cache::has('test') ? 'exists' : 'not found';"

# Delete key
php artisan tinker --execute="Cache::forget('test'); echo 'deleted';"

# Get all keys (pattern)
php artisan tinker --execute="print_r(Redis::keys('umamusume-career-planner:*'));"
```

### Advanced Operations

```powershell
# Get Redis info
php artisan tinker --execute="print_r(Redis::info());"

# Flush database
php artisan tinker --execute="Redis::flushdb(); echo 'flushed';"

# Get database size
php artisan tinker --execute="echo Redis::dbsize();"

# Set with expiration
php artisan tinker --execute="Redis::setex('test', 60, 'value'); echo 'set';"

# Increment counter
php artisan tinker --execute="echo Redis::incr('counter');"

# Get TTL
php artisan tinker --execute="echo Redis::ttl('test');"
```

## Common Workflows

### Check Redis Health

```powershell
# 1. Check service
wsl bash -c "sudo service redis-server status"

# 2. Test connection
wsl bash -c "redis-cli ping"

# 3. Check Laravel connection
php artisan redis:health --detailed

# 4. Test cache operations
php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
```

### Debug Cache Issues

```powershell
# 1. Check what's cached
wsl bash -c "redis-cli keys 'umamusume-career-planner:*'"

# 2. Check specific key
wsl bash -c "redis-cli get 'umamusume-career-planner:key_name'"

# 3. Check TTL
wsl bash -c "redis-cli ttl 'umamusume-career-planner:key_name'"

# 4. Clear and rebuild
php artisan cache:clear
php artisan cache:warm
```

### Monitor Performance

```powershell
# 1. Check memory usage
wsl bash -c "redis-cli info memory"

# 2. Monitor commands
wsl bash -c "redis-cli monitor"

# 3. Check slow queries
wsl bash -c "redis-cli slowlog get 10"

# 4. Check database size
wsl bash -c "redis-cli dbsize"
```

### Clear Everything (Nuclear Option)

```powershell
# WARNING: This deletes ALL cached data!

# 1. Clear Laravel cache
php artisan cache:clear

# 2. Clear config cache
php artisan config:clear

# 3. Flush Redis database
wsl bash -c "redis-cli flushdb"

# 4. Rebuild cache
php artisan cache:warm
```

## Testing Commands

### Run Redis Tests

```powershell
# All Redis tests
php artisan test --filter=Redis --compact

# Fallback recovery tests
php artisan test --filter=FallbackRecovery --compact

# Cache management tests
php artisan test --filter=CacheManagement --compact

# Specific test
php artisan test --filter="test name" --compact
```

### Debug Test Failures

```powershell
# 1. Check Redis is running
wsl bash -c "redis-cli ping"

# 2. Check Laravel config
php artisan config:clear
php artisan tinker --execute="echo config('cache.default');"

# 3. Check extension loaded
php -m | Select-String -Pattern "redis"

# 4. Run test with verbose output
php artisan test --filter=TestName
```

## Environment-Specific Commands

### Development

```powershell
# Use database cache (no Redis needed)
# In .env:
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### Production (with Redis)

```powershell
# Use Redis for everything
# In .env:
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:warm
```

## Troubleshooting Commands

### Connection Issues

```powershell
# Test WSL Redis
wsl bash -c "redis-cli ping"

# Test from Windows
wsl bash -c "redis-cli -h 127.0.0.1 -p 6379 ping"

# Check Redis config
wsl bash -c "cat /etc/redis/redis.conf | grep bind"

# Check if Redis is listening
wsl bash -c "netstat -tlnp | grep 6379"
```

### Extension Issues

```powershell
# Check if loaded
php -m | Select-String -Pattern "redis"

# Check PHP info
php -i | Select-String -Pattern "redis"

# Test connection
php -r "try { $r = new Redis(); $r->connect('127.0.0.1', 6379); echo 'OK'; } catch (Exception $e) { echo $e->getMessage(); }"
```

### Performance Issues

```powershell
# Check memory
wsl bash -c "redis-cli info memory | grep used_memory_human"

# Check slow queries
wsl bash -c "redis-cli slowlog get 10"

# Check connected clients
wsl bash -c "redis-cli client list"

# Check hit rate
wsl bash -c "redis-cli info stats | grep keyspace"
```

## Safety Notes

⚠️ **DANGEROUS COMMANDS** (use with caution):

- `flushdb` - Deletes all keys in current database
- `flushall` - Deletes all keys in ALL databases
- `config set` - Changes Redis configuration

✅ **SAFE COMMANDS** (read-only):

- `ping` - Test connection
- `info` - Get server information
- `keys` - List keys (use with pattern)
- `get` - Get key value
- `ttl` - Get time to live
- `dbsize` - Get database size

## Quick Reference Card

| Task | Command |
|------|---------|
| Start Redis | `wsl bash -c "sudo service redis-server start"` |
| Test Connection | `wsl bash -c "redis-cli ping"` |
| Laravel Health | `php artisan redis:health` |
| Warm Cache | `php artisan cache:warm` |
| Clear Cache | `php artisan cache:clear` |
| List Keys | `wsl bash -c "redis-cli keys 'umamusume-career-planner:*'"` |
| Monitor | `wsl bash -c "redis-cli monitor"` |
| Memory Info | `wsl bash -c "redis-cli info memory"` |
| Run Tests | `php artisan test --filter=Redis --compact` |
