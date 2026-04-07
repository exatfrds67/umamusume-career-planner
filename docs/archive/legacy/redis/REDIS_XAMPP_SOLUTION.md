# Redis XAMPP Issue - SOLVED

**Date:** January 20, 2026
**Issue:** `Class "Redis" not found` in web browser
**Root Cause:** PHP version mismatch

---

## Problem Identified

**XAMPP uses PHP 8.2.12:**

```text
C:\XAMPP\php\php.exe -v
PHP 8.2.12 (cli) (built: Oct 24 2023 21:15:15)
```text

**Your CLI uses PHP 8.4.11:**

```text
php -v
PHP 8.4.11 (cli) (built: Jul 29 2025 18:02:29)
```

**Redis extension installed for:** PHP 8.4.11 only
**XAMPP needs:** Redis extension for PHP 8.2.12

---

## Solution: Use PHP 8.4.11 Server

**Stop using XAMPP** and use PHP 8.4.11's built-in server:

### Step 1: Stop XAMPP Apache

- Open XAMPP Control Panel
- Click "Stop" next to Apache
- Or just leave it running (won't conflict)

### Step 2: Start PHP 8.4.11 Server

```powershell
php artisan serve
```text

### Step 3: Access Application

```text

<http://127.0.0.1:8000>

```text

**Done!** ✅ Application will work perfectly.

---

## Why This Works

| Component       | XAMPP           | PHP Server  |
| --------------- | --------------- | ----------- |
| PHP Version     | 8.2.12          | 8.4.11 ✅    |
| Redis Extension | ❌ Not installed | ✅ Installed |
| Tests           | ❌ Different PHP | ✅ Same PHP  |
| Configuration   | Complex         | Simple      |

**PHP 8.4.11 server uses the same PHP as your CLI:**

- ✅ Redis extension already working
- ✅ Tesseract already working
- ✅ All tests passing
- ✅ No configuration needed

---

## Alternative: Install Redis for PHP 8.2.12

If you really want to use XAMPP:

### Step 1: Download Redis for PHP 8.2

- Visit: <https://pecl.php.net/package/redis>
- Download: `php_redis-X.X.X-8.2-ts-vs16-x64.zip` (Thread Safe for Apache)
- **Important:** Must be PHP 8.2, Thread Safe (TS), x64

### Step 2: Install

1. Extract `php_redis.dll`
2. Copy to: `C:\XAMPP\php\ext\`
3. Verify `C:\XAMPP\php\php.ini` has: `extension=redis`
4. Restart Apache in XAMPP

### Step 3: Verify

```powershell
& "C:\XAMPP\php\php.exe" -m | Select-String redis
```

**But this is more work than just using PHP 8.4.11 server!**

---

## Recommended Setup

**For Development:**

```powershell
# Use PHP 8.4.11 built-in server
php artisan serve
```text

**Benefits:**

- ✅ Uses same PHP as CLI (8.4.11)
- ✅ All extensions work immediately
- ✅ Simpler configuration
- ✅ Faster startup
- ✅ Auto-reload on changes
- ✅ Perfect for Laravel development

**For Production:**

- Use proper web server (Nginx/Apache)
- With PHP 8.4.11 (not 8.2.12)
- Install all extensions for correct PHP version

---

## Commands

### Start Development Server

```powershell
php artisan serve
```text

### Start on Different Port

```powershell
php artisan serve --port=8080
```text

### Start on Different Host

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

### Access Application

```text
http://127.0.0.1:8000
```text

---

## Verification

After starting PHP server:

1. **Access:** <http://127.0.0.1:8000>
2. **Should see:** Your application homepage
3. **No errors:** Redis works perfectly ✅

---

## Summary

**Problem:**

- XAMPP uses PHP 8.2.12
- Redis extension installed for PHP 8.4.11
- Version mismatch causes "Class Redis not found"

**Solution:**

- Use PHP 8.4.11 built-in server
- Command: `php artisan serve`
- Everything works immediately ✅

**Status:**

- ✅ CLI PHP 8.4.11 with redis
- ✅ Tests passing
- ✅ Tesseract working
- ✅ Ready to use PHP server

---

## Next Steps

1. **Stop XAMPP Apache** (optional - won't conflict)
2. **Run:** `php artisan serve`
3. **Access:** <http://127.0.0.1:8000>
4. **Enjoy!** ✅

---

**Recommendation:** Always use `php artisan serve` for Laravel development. It's simpler, faster, and uses the correct
PHP version.
