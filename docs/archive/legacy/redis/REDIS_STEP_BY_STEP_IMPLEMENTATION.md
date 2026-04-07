# Redis Step-by-Step Implementation Guide

## Overview

This guide provides a complete step-by-step process to implement Redis in your
UmamusumeCareerPlanner application. Follow each step in order for a successful implementation.

**Estimated Time**: 30-45 minutes

---

## Phase 1: Install Redis on WSL (10 minutes)

### Step 1.1: Open WSL Terminal

1. Press `Win + R`
2. Type `wsl` and press Enter
3. Wait for Ubuntu terminal to open

### Step 1.2: Update Package Manager

```bash
sudo apt update
sudo apt upgrade -y
```text

**Expected Output**: Package lists updated successfully

### Step 1.3: Install Redis Server

```bash
sudo apt install redis-server -y
```

**Expected Output**: `redis-server is already the newest version` or installation completes

### Step 1.4: Verify Installation

```bash
redis-server --version
```text

**Expected Output**: `Redis server v=7.0.x` or higher

### Step 1.5: Configure Redis

```bash
sudo nano /etc/redis/redis.conf
```

Find and modify these lines:

```conf
# Change this line:
bind 127.0.0.1 ::1
# To:
bind 0.0.0.0

# Change this line:
protected-mode yes
# To:
protected-mode no

# Add these lines if not present:
maxmemory 256mb
maxmemory-policy allkeys-lru
```text

Save and exit: `Ctrl+X`, then `Y`, then `Enter`

### Step 1.6: Start Redis

```bash
sudo service redis-server start
```

**Expected Output**: `Starting redis-server: redis-server.`

### Step 1.7: Test Redis

```bash
redis-cli ping
```text

**Expected Output**: `PONG`

✅ **Checkpoint**: Redis is now installed and running on WSL

---

## Phase 2: Install phpredis Extension (10 minutes)

### Step 2.1: Download phpredis

1. Visit: <https://pecl.php.net/package/redis>
2. Click on "DLL" link for Windows
3. Download the PHP 8.4 Thread Safe (TS) x64 version
4. Extract the ZIP file

### Step 2.2: Install Extension

1. Copy `php_redis.dll` from the extracted folder
2. Paste it into `C:\xampp\php\ext\`

### Step 2.3: Enable Extension

1. Open `C:\xampp\php\php.ini` in a text editor
2. Find the section with other extensions (search for `extension=`)
3. Add this line:

```ini
extension=redis
```

1. Save the file

### Step 2.4: Restart Apache

1. Open XAMPP Control Panel
2. Click "Stop" for Apache
3. Wait 2 seconds
4. Click "Start" for Apache

### Step 2.5: Verify Installation

1. Create a file: `C:\xampp\htdocs\phpinfo.php`
2. Add this content:

```php
<?php
phpinfo();
```text

1. Visit: `http://localhost/phpinfo.php`
2. Search for "redis" on the page (Ctrl+F)

**Expected Result**: You should see a "redis" section with version information

✅ **Checkpoint**: phpredis extension is now installed and active

---

## Phase 3: Configure Laravel Application (5 minutes)

### Step 3.1: Update .env File

1. Open your project's `.env` file
2. Find or add these lines:

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=umamusume-career-planner-cache-

# Queue Configuration
QUEUE_CONNECTION=redis

# Session Configuration
SESSION_DRIVER=redis
SESSION_CONNECTION=session

# Redis Configuration (WSL)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_PREFIX=umamusume-career-planner:
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_CACHE_CONNECTION=cache
```

1. Save the file

### Step 3.2: Clear Configuration Cache

Open Command Prompt in your project directory:

```bash
cd C:\xampp\htdocs\umamusume-career-planner
php artisan config:clear
php artisan cache:clear
```text

**Expected Output**: `Configuration cache cleared!` and `Application cache cleared!`

✅ **Checkpoint**: Laravel is now configured to use Redis

---

## Phase 4: Test Redis Integration (10 minutes)

### Step 4.1: Test Redis Health

```bash
php artisan redis:health
```

**Expected Output**:

```text
✅ Redis connection successful!

📊 Cache Statistics:
+---------------------+----------+
| Metric              | Value    |
+---------------------+----------+
| Used Memory         | 1.23M    |
| ...                 | ...      |
+---------------------+----------+
```

If you see an error, go to [Troubleshooting](#troubleshooting) section.

### Step 4.2: Test Cache Operations

```bash
php artisan tinker
```text

In Tinker, run these commands:

```php
// Test basic cache
Cache::put('test', 'Hello Redis!', 60);
Cache::get('test');
// Expected: "Hello Redis!"

// Test tagged cache
Cache::tags(['test'])->put('tagged', 'Tagged data', 60);
Cache::tags(['test'])->get('tagged');
// Expected: "Tagged data"

// Exit Tinker
exit
```

### Step 4.3: Warm Cache

```bash
php artisan cache:warm
```text

**Expected Output**: `Cache warming completed successfully!`

### Step 4.4: Test Session Storage

1. Create a test route in `routes/web.php`:

```php
Route::get('/test-redis', function () {
    session(['redis_test' => 'Session working!']);
    return session('redis_test');
});
```

1. Visit: `http://localhost/test-redis`

**Expected Output**: `Session working!`

1. Check Redis for session data:

```bash
# In WSL
redis-cli -n 2 keys "*"
```text

**Expected Output**: You should see session keys

✅ **Checkpoint**: Redis integration is working correctly

---

## Phase 5: Verify Everything Works (5 minutes)

### Step 5.1: Run Health Check with Details

```bash
php artisan redis:health --detailed
```

**Expected Output**: Detailed information about all Redis connections

### Step 5.2: Check Redis Monitoring

In WSL, run:

```bash
redis-cli monitor
```text

Then in another terminal, run:

```bash
php artisan cache:warm
```

You should see Redis commands being executed in the monitor window.

Press `Ctrl+C` to stop monitoring.

### Step 5.3: Verify Cache Statistics

```bash
php artisan tinker
```text

```php
$service = app('redis.cache.optimizer');
$stats = $service->getStatistics();
print_r($stats);
exit
```

**Expected Output**: Array with cache statistics

✅ **Checkpoint**: All Redis features are working correctly

---

## Phase 6: Set Up Auto-Start (Optional, 5 minutes)

### Option A: Manual Start (Recommended for Development)

Every time you restart your computer, run:

```bash
wsl sudo service redis-server start
```text

### Option B: Windows Task Scheduler (Automatic)

1. Open Task Scheduler (search in Start menu)
2. Click "Create Basic Task"
3. Name: "Start Redis WSL"
4. Trigger: "When I log on"
5. Action: "Start a program"
6. Program: `wsl`
7. Arguments: `-u root service redis-server start`
8. Click "Finish"

✅ **Checkpoint**: Redis will start automatically

---

## Troubleshooting

### Problem: "Connection refused [tcp://127.0.0.1:6379]"

**Solution**:

```bash
# In WSL
sudo service redis-server status
# If not running:
sudo service redis-server start
```

### Problem: "Class 'Redis' not found"

**Solution**:

1. Verify phpredis is installed:

   ```bash
   php -m | grep redis
   ```

2. If not listed, check `php.ini`:

   ```bash
   php --ini
   ```

3. Ensure `extension=redis` is present and uncommented

4. Restart Apache

### Problem: "Redis health check failed"

**Solution**:

1. Check Redis is running:

   ```bash
   # In WSL
   sudo service redis-server status
   ```

2. Check Redis is listening:

   ```bash
   # In WSL
   sudo netstat -tulpn | grep 6379
   ```

3. Verify `.env` configuration:

   ```env
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

### Problem: High memory usage

**Solution**:

```bash
# In WSL
redis-cli info memory | grep used_memory_human

# If too high, clear cache:
redis-cli -n 1 FLUSHDB
```text

---

## Success Checklist

Use this checklist to verify your implementation:

- [ ] Redis is installed on WSL
- [ ] Redis starts successfully with `sudo service redis-server start`
- [ ] `redis-cli ping` returns `PONG`
- [ ] phpredis extension shows in `phpinfo()`
- [ ] `.env` file has Redis configuration
- [ ] `php artisan redis:health` shows success
- [ ] `php artisan cache:warm` completes without errors
- [ ] Cache operations work in Tinker
- [ ] Session test route works
- [ ] Redis monitor shows activity
- [ ] Cache statistics are available

---

## What's Next?

Now that Redis is implemented, you can:

1. **Use Cache in Your Code**:

   ```php
   $data = Cache::remember('key', 3600, function () {
       return expensive_operation();
   });
   ```

2. **Use Tagged Cache**:

   ```php
   Cache::tags(['training'])->put('key', 'value', 300);
   Cache::tags(['training'])->flush(); // Clear all training cache
   ```

3. **Monitor Performance**:

   ```bash
   php artisan redis:health --detailed
   ```

4. **Optimize Cache**:

   ```bash
   php artisan cache:warm
   ```

---

## Additional Resources

- [Redis WSL Setup Guide](./REDIS_WSL_SETUP_GUIDE.md) - Comprehensive setup documentation
- [Redis Testing Guide](./REDIS_TESTING_GUIDE.md) - 14 comprehensive tests
- [Redis Quick Reference](./REDIS_QUICK_REFERENCE.md) - Command reference card
- [Implementation Summary](./REDIS_IMPLEMENTATION_SUMMARY.md) - What was implemented

---

## Support

If you encounter issues not covered in this guide:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review the [Redis WSL Setup Guide](./REDIS_WSL_SETUP_GUIDE.md)
3. Check Redis logs:

   ```bash
   # In WSL
   sudo tail -f /var/log/redis/redis-server.log
   ```

---

## Congratulations

You have successfully implemented Redis in your UmamusumeCareerPlanner application! 🎉
