# Manual phpredis Installation Required

## ✅ What I've Completed

1. **Updated .env file**
   - Changed `CACHE_STORE=redis`
   - Changed `QUEUE_CONNECTION=redis`
   - Changed `SESSION_DRIVER=redis`

2. **Updated test files**
   - `tests/Feature/FallbackRecoveryTest.php` (5 changes)
   - `tests/Feature/CacheManagementTest.php` (3 changes)

## ⚠️ Manual Step Required: Install phpredis Extension

I cannot automatically install the phpredis extension because it requires:

- Downloading files from external websites
- Accessing system directories outside the workspace (C:\xampp\)
- Editing system PHP configuration
- Restarting Apache service

### You Need to Do This Manually

#### Step 1: Download phpredis DLL

##### Option A: PECL (Recommended)

1. Visit: <https://pecl.php.net/package/redis>
2. Click on "DLL" link for Windows
3. Find version for:
   - PHP 8.4
   - NTS (Non-Thread Safe)
   - x64
   - VS16 (Visual Studio 2019/2022)

##### Option B: Windows PHP Downloads

1. Visit: <https://windows.php.net/downloads/pecl/releases/redis/>
2. Download latest version matching:
   - `php_redis-X.X.X-8.4-nts-vs16-x64.zip`

#### Step 2: Install the Extension

1. **Extract the ZIP file**
   - Find `php_redis.dll` in the extracted files

2. **Copy to PHP extensions directory**

   ```
   Copy: php_redis.dll
   To: C:\xampp\php\ext\php_redis.dll
   ```

3. **Edit php.ini**
   - Open: `C:\xampp\php\php.ini`
   - Find the extensions section (search for "extension=")
   - Add this line:

     ```ini
     extension=redis
     ```

   - Save the file

4. **Restart Apache**
   - Open XAMPP Control Panel
   - Stop Apache
   - Start Apache

   Or via command line:

   ```powershell
   C:\xampp\apache\bin\httpd.exe -k stop
   C:\xampp\apache\bin\httpd.exe -k start
   ```

#### Step 3: Verify Installation

Run this command:

```powershell
php -m | Select-String -Pattern "redis"
```

**Expected output:** `redis`

If you see "redis" in the output, the extension is installed correctly!

#### Step 4: Test Redis Connection

```powershell
php artisan redis:health --detailed
```

**Expected:** Connection successful message with Redis details

## After phpredis is Installed

Once you've completed the manual installation above, run these commands:

### Clear Configuration Cache

```powershell
php artisan config:clear
```

### Test Redis Connection

```powershell
php artisan redis:health --detailed
```

### Run Tests

```powershell
# Run Redis-specific tests
php artisan test --filter=FallbackRecovery --compact
php artisan test --filter=CacheManagement --compact

# Run full test suite
php artisan test --compact
```

### Warm Cache

```powershell
php artisan cache:warm
```

## Troubleshooting

### Extension Not Loading

**Symptom:** `php -m` doesn't show redis

**Solutions:**

1. Verify DLL is in correct location: `C:\xampp\php\ext\php_redis.dll`
2. Check php.ini has `extension=redis` (NOT `extension=php_redis.dll`)
3. Make sure you edited the correct php.ini:

   ```powershell
   php --ini
   ```

   This shows which php.ini file is being used
4. Restart Apache completely (stop, wait, start)
5. Check PHP error log: `C:\xampp\php\logs\php_error_log`

### Wrong DLL Version

**Symptom:** PHP crashes or shows DLL load errors

**Solution:** Ensure the DLL matches:

- PHP Version: 8.4.11 (check with `php -v`)
- Thread Safety: NTS (Non-Thread Safe)
- Architecture: x64
- Compiler: VS16

### Connection Refused

**Symptom:** "Connection refused" errors

**Solutions:**

1. Check Redis is running:

   ```powershell
   wsl bash -c "redis-cli ping"
   ```

   Should return: `PONG`

2. Restart Redis if needed:

   ```powershell
   wsl bash -c "sudo service redis-server restart"
   ```

3. Verify .env has correct host:

   ```env
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

## Quick Verification Checklist

After installation, verify:

- [ ] `php -m | Select-String redis` shows "redis"
- [ ] `php artisan redis:health` succeeds
- [ ] `php artisan config:clear` runs without errors
- [ ] `php artisan test --filter=Redis --compact` passes
- [ ] No errors in `storage/logs/laravel.log`

## Need More Help?

See these files for detailed information:

- `REDIS_SETUP_INSTRUCTIONS.md` - Complete setup guide
- `REDIS_COMMANDS_REFERENCE.md` - Redis commands
- `REDIS_SETUP_CHECKLIST.md` - Full checklist

## Summary

**What's Done:**

- ✅ .env updated to use Redis
- ✅ Test files updated
- ✅ Redis running in WSL

**What You Need to Do:**

1. Download phpredis DLL
2. Copy to C:\xampp\php\ext\
3. Add `extension=redis` to php.ini
4. Restart Apache
5. Verify with `php -m`
6. Run tests

**Estimated Time:** 10 minutes

Once phpredis is installed, everything else is ready to go! 🚀
