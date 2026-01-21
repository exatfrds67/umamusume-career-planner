# Redis WSL Setup Guide for UmamusumeCareerPlanner

## Overview

This guide provides comprehensive instructions for setting up Redis on Windows Subsystem for Linux (WSL) for the UmamusumeCareerPlanner application. Redis is used for caching, session management, and queue processing.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Windows (XAMPP)                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Laravel Application (PHP 8.4)                       │   │
│  │  - Web Server (Apache)                               │   │
│  │  - Application Logic                                 │   │
│  │  - Redis Client (phpredis)                           │   │
│  └──────────────────┬───────────────────────────────────┘   │
│                     │ TCP Connection (127.0.0.1:6379)       │
└─────────────────────┼───────────────────────────────────────┘
                      │
┌─────────────────────┼───────────────────────────────────────┐
│                     │         WSL (Ubuntu)                  │
│  ┌──────────────────▼───────────────────────────────────┐   │
│  │  Redis Server 7.0+                                   │   │
│  │  - Database 0: Default/Queue                         │   │
│  │  - Database 1: Cache                                 │   │
│  │  - Database 2: Sessions                              │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Prerequisites

1. **Windows 10/11** with WSL 2 enabled
2. **Ubuntu** (or preferred Linux distribution) installed on WSL
3. **XAMPP** installed with PHP 8.4+
4. **phpredis** extension installed in XAMPP PHP

## Step 1: Install Redis on WSL

### 1.1 Update WSL Package Manager

```bash
sudo apt update
sudo apt upgrade -y
```

### 1.2 Install Redis Server

```bash
sudo apt install redis-server -y
```

### 1.3 Verify Redis Installation

```bash
redis-server --version
```

Expected output: `Redis server v=7.0.x` or higher

## Step 2: Configure Redis for Windows Access

### 2.1 Edit Redis Configuration

```bash
sudo nano /etc/redis/redis.conf
```

### 2.2 Update Configuration Settings

Find and modify the following lines:

```conf
# Bind to all interfaces (allows Windows to connect)
bind 0.0.0.0

# Disable protected mode for local development
protected-mode no

# Set maximum memory (adjust based on your system)
maxmemory 256mb
maxmemory-policy allkeys-lru

# Enable persistence
save 900 1
save 300 10
save 60 10000

# Set log level
loglevel notice
logfile /var/log/redis/redis-server.log

# Database count (we use 0, 1, 2)
databases 16
```

### 2.3 Save and Exit

Press `Ctrl+X`, then `Y`, then `Enter`

## Step 3: Start Redis Service

### 3.1 Start Redis

```bash
sudo service redis-server start
```

### 3.2 Verify Redis is Running

```bash
sudo service redis-server status
```

Expected output: `redis-server is running`

### 3.3 Test Redis Connection

```bash
redis-cli ping
```

Expected output: `PONG`

## Step 4: Configure Redis to Start Automatically

### 4.1 Create Startup Script

Create a file in your Windows startup folder or use Task Scheduler:

```bash
# In WSL, create a startup script
sudo nano /usr/local/bin/start-redis.sh
```

Add the following content:

```bash
#!/bin/bash
sudo service redis-server start
```

Make it executable:

```bash
sudo chmod +x /usr/local/bin/start-redis.sh
```

### 4.2 Windows Task Scheduler (Optional)

1. Open Task Scheduler
2. Create Basic Task
3. Name: "Start Redis WSL"
4. Trigger: At log on
5. Action: Start a program
6. Program: `wsl`
7. Arguments: `-u root service redis-server start`

## Step 5: Install phpredis Extension in XAMPP

### 5.1 Download phpredis DLL

1. Visit: <https://pecl.php.net/package/redis>
2. Download the appropriate DLL for PHP 8.4 (Thread Safe x64)
3. Extract `php_redis.dll` to `C:\xampp\php\ext\`

### 5.2 Enable Extension in php.ini

Edit `C:\xampp\php\php.ini`:

```ini
extension=redis
```

### 5.3 Restart Apache

Restart Apache from XAMPP Control Panel

### 5.4 Verify phpredis Installation

Create a test file `C:\xampp\htdocs\redis-test.php`:

```php
<?php
phpinfo();
```

Search for "redis" in the output. You should see the Redis extension loaded.

## Step 6: Configure Laravel Application

### 6.1 Update .env File

Copy the Redis configuration from `.env.example`:

```env
# Redis Configuration (WSL)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_PREFIX=umamusume-career-planner:
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=umamusume-career-planner-cache-

# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default

# Session Configuration
SESSION_DRIVER=redis
SESSION_CONNECTION=session
```

### 6.2 Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Step 7: Test Redis Integration

### 7.1 Test Cache

```bash
php artisan tinker
```

```php
Cache::put('test', 'Hello Redis!', 60);
Cache::get('test'); // Should return "Hello Redis!"
```

### 7.2 Test Queue

```bash
# Start queue worker
php artisan queue:work redis --queue=default
```

In another terminal:

```bash
php artisan tinker
```

```php
dispatch(function () {
    logger('Test job executed!');
});
```

### 7.3 Test Session

Create a test route in `routes/web.php`:

```php
Route::get('/test-session', function () {
    session(['test' => 'Redis session working!']);
    return session('test');
});
```

Visit: `http://localhost/test-session`

## Step 8: Monitor Redis

### 8.1 Redis CLI Monitor

```bash
redis-cli monitor
```

This shows all commands being executed in real-time.

### 8.2 Check Redis Info

```bash
redis-cli info
```

### 8.3 Check Database Keys

```bash
# Check cache database (DB 1)
redis-cli -n 1 keys "*"

# Check session database (DB 2)
redis-cli -n 2 keys "*"

# Check queue database (DB 0)
redis-cli -n 0 keys "*"
```

## Database Allocation

| Database | Purpose | Prefix |
|----------|---------|--------|
| DB 0 | Default/Queue | `umamusume-career-planner:` |
| DB 1 | Cache | `umamusume-career-planner-cache-` |
| DB 2 | Sessions | `umamusume-career-planner:session:` |

## Performance Optimization

### Memory Management

```bash
# Check memory usage
redis-cli info memory

# Set max memory (in redis.conf)
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### Persistence Configuration

```bash
# RDB snapshots (in redis.conf)
save 900 1      # Save after 900 seconds if at least 1 key changed
save 300 10     # Save after 300 seconds if at least 10 keys changed
save 60 10000   # Save after 60 seconds if at least 10000 keys changed
```

## Troubleshooting

### Redis Not Starting

```bash
# Check Redis logs
sudo tail -f /var/log/redis/redis-server.log

# Check if port is in use
sudo netstat -tulpn | grep 6379

# Restart Redis
sudo service redis-server restart
```

### Connection Refused from Windows

```bash
# Verify Redis is listening on all interfaces
sudo netstat -tulpn | grep 6379

# Should show: 0.0.0.0:6379

# Check firewall (if enabled)
sudo ufw status
sudo ufw allow 6379/tcp
```

### phpredis Not Working

```bash
# Verify extension is loaded
php -m | grep redis

# Check php.ini location
php --ini

# Verify extension path
php -i | grep extension_dir
```

### Performance Issues

```bash
# Check slow log
redis-cli slowlog get 10

# Monitor commands
redis-cli monitor

# Check memory fragmentation
redis-cli info memory | grep fragmentation
```

## Security Considerations

### Production Recommendations

1. **Enable Authentication**:

   ```conf
   requirepass your_strong_password_here
   ```

2. **Bind to Specific IP**:

   ```conf
   bind 127.0.0.1
   ```

3. **Enable Protected Mode**:

   ```conf
   protected-mode yes
   ```

4. **Disable Dangerous Commands**:

   ```conf
   rename-command FLUSHDB ""
   rename-command FLUSHALL ""
   rename-command CONFIG ""
   ```

5. **Use TLS** (for production):

   ```conf
   tls-port 6380
   tls-cert-file /path/to/redis.crt
   tls-key-file /path/to/redis.key
   ```

## Maintenance

### Daily Tasks

```bash
# Check Redis status
sudo service redis-server status

# Monitor memory usage
redis-cli info memory | grep used_memory_human
```

### Weekly Tasks

```bash
# Backup Redis data
sudo cp /var/lib/redis/dump.rdb /backup/redis-backup-$(date +%Y%m%d).rdb

# Check slow queries
redis-cli slowlog get 100
```

### Monthly Tasks

```bash
# Analyze key distribution
redis-cli --bigkeys

# Check fragmentation
redis-cli info memory | grep fragmentation_ratio
```

## Laravel Horizon Integration

For queue monitoring with Laravel Horizon (requires WSL):

```bash
# In WSL, install Horizon
composer require laravel/horizon

# Publish Horizon assets
php artisan horizon:install

# Start Horizon
php artisan horizon
```

Access Horizon dashboard at: `http://localhost/horizon`

## Useful Commands

### Redis CLI Commands

```bash
# Connect to Redis
redis-cli

# Select database
SELECT 1

# List all keys
KEYS *

# Get key value
GET key_name

# Delete key
DEL key_name

# Flush database
FLUSHDB

# Flush all databases
FLUSHALL

# Get key TTL
TTL key_name

# Set key with expiration
SETEX key_name 3600 "value"
```

### Laravel Artisan Commands

```bash
# Clear cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Queue worker
php artisan queue:work redis

# Queue listener
php artisan queue:listen redis

# Failed jobs
php artisan queue:failed
php artisan queue:retry all
```

## References

- [Redis Documentation](https://redis.io/documentation)
- [Laravel Redis Documentation](https://laravel.com/docs/12.x/redis)
- [phpredis GitHub](https://github.com/phpredis/phpredis)
- [Laravel Horizon Documentation](https://laravel.com/docs/12.x/horizon)

## Support

For issues specific to this project, refer to:

- Project documentation: `docs/`
- Spec document: `.kiro/specs/umamusume-career-planner-main/`
- Task list: `.kiro/specs/umamusume-career-planner-main/tasks.md`
