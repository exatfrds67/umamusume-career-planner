# Install phpredis Extension - Action Required

## Current Status

✅ **Verified:** phpredis extension is NOT installed

- PHP Location: `C:\Users\exatf\tools\php-8.4.11\`
- php.ini: `C:\Users\exatf\tools\php-8.4.11\php.ini`
- Extensions folder: `C:\Users\exatf\tools\php-8.4.11\ext\`
- Redis DLL: **NOT FOUND**

## Installation Steps

### Step 1: Download phpredis 6.1.0

**Direct Download Link:**
<https://pecl.php.net/package/redis/6.1.0/windows>

**What you need:**

- PHP 8.4
- NTS (Non-Thread Safe)
- x64
- VS16

**File to download:** `php_redis-6.1.0-8.4-nts-vs16-x64.zip`

### Step 2: Extract and Copy

1. **Extract the ZIP file**
   - You'll find `php_redis.dll` inside

2. **Copy the DLL**

   ```text
   From: [extracted folder]\php_redis.dll
   To: C:\Users\exatf\tools\php-8.4.11\ext\php_redis.dll
   ```

### Step 3: Edit php.ini

1. **Open php.ini**

   ```text
   C:\Users\exatf\tools\php-8.4.11\php.ini
   ```

2. **Find the extensions section**
   - Look for lines starting with `extension=`

3. **Add this line:**

   ```ini
   extension=redis
   ```text

   Note: Use `extension=redis` NOT `extension=php_redis.dll`

4. **Save the file**

### Step 4: Verify Installation

Run this command:

```powershell
php -m | Select-String -Pattern "redis"
```text

**Expected output:** `redis`

If you see "redis", the extension is installed correctly!

### Step 5: Test Redis Connection

```powershell
php artisan redis:health --detailed
```text

**Expected:** Connection successful with Redis details

## Quick Verification Commands

```powershell
# Check if DLL exists
Test-Path "C:\Users\exatf\tools\php-8.4.11\ext\php_redis.dll"

# Check if extension is loaded
php -m | Select-String redis

# Check php.ini has the extension
Select-String -Path "C:\Users\exatf\tools\php-8.4.11\php.ini" -Pattern "extension=redis"

# Test Redis connection
php artisan redis:health
```

## After Installation

Once phpredis is installed, run:

```powershell
# Clear config cache
php artisan config:clear

# Test connection
php artisan redis:health --detailed

# Run tests
php artisan test --filter=FallbackRecovery --compact
php artisan test --filter=CacheManagement --compact

# Run all tests
php artisan test --compact

# Warm cache
php artisan cache:warm
```text

## Troubleshooting

### Extension Not Loading

**Check:**

1. DLL is in correct location: `C:\Users\exatf\tools\php-8.4.11\ext\php_redis.dll`
2. php.ini has `extension=redis` (not `extension=php_redis.dll`)
3. You're editing the correct php.ini (check with `php --ini`)
4. No typos in php.ini

**Fix:**

- Restart your terminal/command prompt
- Check PHP error log for DLL load errors

### Wrong DLL Version

**Symptoms:**

- PHP crashes
- "Unable to load dynamic library" error

**Solution:**

- Ensure DLL matches:
  - PHP 8.4.11
  - NTS (Non-Thread Safe)
  - x64
  - VS16

### Connection Refused

**Check Redis is running:**

```powershell
wsl bash -c "redis-cli ping"
```text

Should return: `PONG`

**Restart Redis if needed:**

```powershell
wsl bash -c "sudo service redis-server restart"
```text

## Download Links

**Primary:**

- <https://pecl.php.net/package/redis/6.1.0/windows>

**Alternative:**

- <https://windows.php.net/downloads/pecl/releases/redis/6.1.0/>

**GitHub:**

- <https://github.com/phpredis/phpredis/releases>

## What's Already Done

✅ Redis running in WSL (v7.0.15)
✅ .env configured for Redis
✅ Test files updated
✅ All code changes complete

## What You Need to Do

⏳ Download phpredis DLL
⏳ Copy to ext folder
⏳ Add to php.ini
⏳ Verify installation
⏳ Run tests

**Estimated Time:** 5-10 minutes

## Success Criteria

- [ ] `Test-Path "C:\Users\exatf\tools\php-8.4.11\ext\php_redis.dll"` returns `True`
- [ ] `php -m | Select-String redis` shows `redis`
- [ ] `php artisan redis:health` succeeds
- [ ] All tests pass

## Ready to Install

Start with downloading from: <https://pecl.php.net/package/redis/6.1.0/windows>

Look for the PHP 8.4 NTS x64 VS16 version! 🚀

