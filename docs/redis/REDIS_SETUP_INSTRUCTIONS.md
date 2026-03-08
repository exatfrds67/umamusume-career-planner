# Redis Setup Instructions for Windows + WSL

## Current Status

✅ Redis is installed and running in WSL
✅ Redis is accessible at 127.0.0.1:6379
✅ .env file has been updated with Redis configuration
⏳ phpredis extension needs to be installed for Windows PHP

## Step 1: Install phpredis Extension for Windows

### Option A: Using PECL (Recommended)

1. Download the appropriate DLL from PECL:
   - Visit: <https://pecl.php.net/package/redis>
   - Or direct download: <https://windows.php.net/downloads/pecl/releases/redis/>
   - Choose the version matching your PHP 8.4.11 NTS x64

2. Extract and copy `php_redis.dll` to:

   ```text
   C:\xampp\php\ext\php_redis.dll
   ```

3. Edit `C:\xampp\php\php.ini` and add:

   ```ini
   extension=redis
   ```text

4. Restart Apache:

   ```powershell
   # Stop Apache
   C:\xampp\apache\bin\httpd.exe -k stop
   
   # Start Apache
   C:\xampp\apache\bin\httpd.exe -k start
   ```

## Option B: Manual Download

1. Download from: <https://github.com/phpredis/phpredis/releases>
2. Look for `php_redis-X.X.X-8.4-nts-vs16-x64.zip`
3. Follow steps 2-4 from Option A

## Step 2: Verify Installation

After installing phpredis, run:

```powershell
php -m | Select-String -Pattern "redis"
```text

You should see `redis` in the output.

## Step 3: Test Redis Connection

Run the health check command:

```powershell
php artisan redis:health
```text

If successful, you should see Redis connection details.

## Step 4: Switch to Redis in .env

Once phpredis is installed and working, update your `.env` file:

```env
# Change these lines:
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```text

## Step 5: Warm the Cache

```powershell
php artisan cache:warm
```

## Step 6: Run Tests

After Redis is fully configured, run the tests:

```powershell
php artisan test --filter=Redis
php artisan test --filter=Cache
php artisan test --filter=FallbackRecovery
```text

## Troubleshooting

### Redis Connection Refused

If you get "Connection refused" errors:

1. Check Redis is running in WSL:

   ```powershell
   wsl bash -c "redis-cli ping"
   ```

   Should return: `PONG`

1. Check Redis is listening on 127.0.0.1:

   ```powershell
   wsl bash -c "redis-cli -h 127.0.0.1 -p 6379 ping"
   ```text

2. Restart Redis in WSL:

   ```powershell
   wsl bash -c "sudo service redis-server restart"
   ```

### phpredis Extension Not Loading

1. Verify the DLL is in the correct location
2. Check php.ini has `extension=redis` (not `extension=php_redis.dll`)
3. Restart Apache completely
4. Check PHP error logs: `C:\xampp\php\logs\php_error_log`

### Version Mismatch

Ensure the phpredis DLL matches:

- PHP Version: 8.4.11
- Thread Safety: NTS (Non-Thread Safe)
- Architecture: x64
- Compiler: VS16 (Visual Studio 2019/2022)

## Next Steps After Installation

1. Update skipped tests (see below)
2. Run full test suite
3. Monitor Redis performance
4. Configure Redis persistence if needed

## Files Modified

- `.env` - Added complete Redis configuration
- Tests will be updated after phpredis installation
