# Redis Quick Reference Card

## Essential Commands

### Laravel Artisan Commands

```bash
# Health Check
php artisan redis:health              # Basic health check
php artisan redis:health --detailed   # Detailed information

# Cache Management
php artisan cache:warm                # Warm cache
php artisan cache:warm --force        # Force cache warming
php artisan cache:clear               # Clear all cache
php artisan config:clear              # Clear config cache

# Queue Management
php artisan queue:work redis          # Start queue worker
php artisan queue:listen redis        # Start queue listener
php artisan queue:failed              # List failed jobs
php artisan queue:retry all           # Retry all failed jobs
```text

## Redis CLI Commands

```bash
# Connection
redis-cli                             # Connect to Redis
redis-cli -n 1                        # Connect to database 1
redis-cli ping                        # Test connection

# Key Operations
KEYS *                                # List all keys (use with caution)
KEYS pattern*                         # List keys matching pattern
GET key                               # Get key value
SET key value                         # Set key value
DEL key                               # Delete key
EXISTS key                            # Check if key exists
TTL key                               # Get key TTL
EXPIRE key seconds                    # Set key expiration

# Database Operations
SELECT 0                              # Switch to database 0
DBSIZE                                # Count keys in current database
FLUSHDB                               # Clear current database
FLUSHALL                              # Clear all databases (dangerous!)

# Monitoring
MONITOR                               # Monitor all commands
INFO                                  # Get server information
INFO memory                           # Get memory information
INFO stats                            # Get statistics
SLOWLOG GET 10                        # Get slow queries
```text

## PHP/Laravel Cache Operations

```php
// Basic Operations
Cache::put('key', 'value', 60);      // Store for 60 seconds
Cache::get('key');                    // Retrieve value
Cache::get('key', 'default');         // With default value
Cache::has('key');                    // Check existence
Cache::forget('key');                 // Delete key
Cache::flush();                       // Clear all cache

// Remember Pattern
Cache::remember('key', 60, function () {
    return expensive_operation();
});

// Tagged Cache
Cache::tags(['tag1', 'tag2'])->put('key', 'value', 60);
Cache::tags(['tag1'])->get('key');
Cache::tags(['tag1'])->flush();      // Clear all keys with tag1

// Increment/Decrement
Cache::increment('counter');
Cache::decrement('counter');
Cache::increment('counter', 5);      // Increment by 5
```text

## Database Allocation

| Database | Purpose       | Prefix                              | Connection |
| -------- | ------------- | ----------------------------------- | ---------- |
| DB 0     | Default/Queue | `umamusume-career-planner:`         | `default`  |
| DB 1     | Cache         | `umamusume-career-planner-cache-`   | `cache`    |
| DB 2     | Sessions      | `umamusume-career-planner:session:` | `session`  |

## Cache Tag Groups

```php
// Training Data
Cache::tags(['training'])->put('key', 'value', 300);

// Character Data
Cache::tags(['character'])->put('key', 'value', 3600);

// Skills Data
Cache::tags(['skills'])->put('key', 'value', 3600);

// Support Cards
Cache::tags(['support_cards'])->put('key', 'value', 3600);

// External API
Cache::tags(['external_api'])->put('key', 'value', 7200);

// AI Data
Cache::tags(['ai'])->put('key', 'value', 1800);

// MCP Data
Cache::tags(['mcp'])->put('key', 'value', 60);
```

## TTL Strategies

| Strategy               | TTL      | Use Case               |
| ---------------------- | -------- | ---------------------- |
| `training_predictions` | 5 min    | Training predictions   |
| `character_data`       | 1 hour   | Character information  |
| `external_api`         | 2 hours  | External API responses |
| `static_game_data`     | 24 hours | Game reference data    |
| `user_preferences`     | 1 week   | User settings          |
| `ai_conversations`     | 30 min   | AI chat history        |
| `mcp_server_status`    | 1 min    | MCP server health      |

## Common Patterns

### Cache with Service

```php
use App\Services\RedisCacheOptimizationService;

$service = app('redis.cache.optimizer');

// Remember with strategy
$data = $service->remember('key', 'training_predictions', function () {
    return expensive_calculation();
});

// Remember with tags
$data = $service->rememberWithTags(
    ['training', 'character'],
    'key',
    'training_predictions',
    function () {
        return expensive_calculation();
    }
);
```text

### Invalidate Cache

```php
// By pattern
$service->invalidatePattern('training:*');

// By tags
$service->invalidateTags(['training', 'character']);

// Direct cache flush
Cache::tags(['training'])->flush();
```text

### Monitor Performance

```php
// Get statistics
$stats = $service->getStatistics();

// Test connection
$isConnected = $service->testConnection();

// Optimize memory
$results = $service->optimizeMemory();
```text

## Troubleshooting Quick Fixes

### Connection Refused

```bash
# Check Redis status
sudo service redis-server status

# Start Redis
sudo service redis-server start

# Verify listening
sudo netstat -tulpn | grep 6379
```

## High Memory Usage

```bash
# Check memory
redis-cli info memory | grep used_memory_human

# Clear specific database
redis-cli -n 1 FLUSHDB

# Restart Redis
sudo service redis-server restart
```text

## Slow Performance

```bash
# Check slow log
redis-cli slowlog get 10

# Monitor commands
redis-cli monitor

# Check fragmentation
redis-cli info memory | grep fragmentation
```text

## phpredis Not Working

```bash
# Check extension
php -m | grep redis

# Check php.ini
php --ini

# Restart Apache
# (via XAMPP Control Panel)
```text

## Monitoring Commands

```bash
# Real-time monitoring
redis-cli monitor

# Memory usage
redis-cli info memory

# Statistics
redis-cli info stats

# Client connections
redis-cli info clients

# Key distribution
redis-cli --bigkeys

# Scan keys
redis-cli --scan --pattern "*training*"

# Count keys by pattern
redis-cli -n 1 keys "umamusume-career-planner-cache-*" | wc -l
```

## Performance Benchmarks

| Operation      | Expected Time |
| -------------- | ------------- |
| Cache Read     | < 1ms         |
| Cache Write    | < 2ms         |
| Session Read   | < 0.5ms       |
| Queue Job      | < 10ms        |
| Cache Hit Rate | > 80%         |

## Environment Variables

```env
# Required
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis Connection
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Database Allocation
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Prefixes
REDIS_PREFIX=umamusume-career-planner:
CACHE_PREFIX=umamusume-career-planner-cache-

# Queue
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
```text

## Testing Checklist

- [ ] `php artisan redis:health` shows success
- [ ] `php artisan cache:warm` completes
- [ ] `Cache::put()` and `Cache::get()` work
- [ ] Tagged cache operations work
- [ ] Queue jobs process correctly
- [ ] Sessions store in Redis
- [ ] All tests pass

## Quick Links

- [Setup Guide](./REDIS_WSL_SETUP_GUIDE.md)
- [Testing Guide](./REDIS_TESTING_GUIDE.md)
- [Implementation Summary](./REDIS_IMPLEMENTATION_SUMMARY.md)
- [Laravel Redis Docs](https://laravel.com/docs/12.x/redis)

---

**Print this page for quick reference during development!**
