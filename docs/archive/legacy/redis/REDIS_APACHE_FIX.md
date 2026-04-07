# Redis Apache Configuration Fix

**Date:** January 20, 2026
**Issue:** `Class "Redis" not found` when accessing application via web browser
**Status:** Fixing

---

## Problem

The application works fine in CLI (tests pass), but throws an error when accessed via web browser:

```text
Class "Redis" not found
```

**Root Cause:**

- CLI PHP has redis extension enabled ✅
- Apache/XAMPP PHP doesn't have redis extension loaded ❌
- Apache needs to be restarted after php.ini changes

---

## Solution

### Step 1: Verify php_redis.dll exists

```powershell
Test-Path "C:\xampp\php\ext\php_redis.dll"
```text

**Status:** ✅ File exists

### Step 2: Verify php.ini has extension enabled

```powershell
Select-String -Path "C:\xampp\php\php.ini" -Pattern "extension=redis"
```

**Status:** ✅ Extension enabled in php.ini

### Step 3: Restart Apache

#### Option A: Using XAMPP Control Panel

1. Open XAMPP Control Panel
2. Click "Stop" next to Apache
3. Wait for it to stop
4. Click "Start" next to Apache

#### Option B: Using Command Line

```powershell
# Stop Apache
net stop Apache2.4

# Start Apache
net start Apache2.4
```text

#### Option C: Using Services

1. Press Win + R
2. Type: `services.msc`
3. Find "Apache2.4" service
4. Right-click → Restart

---

## Verification

After restarting Apache, verify Redis is loaded:

### Create a test file: `public/phpinfo-test.php`

```php
<?php
phpinfo();
```

### Access in browser

```text
http://127.0.0.1:8000/phpinfo-test.php
```

### Search for "redis"

- Should see "redis" section with version info
- If not visible, redis extension is not loaded

### Delete test file after verification

```powershell
Remove-Item public/phpinfo-test.php
```text

---

## Alternative: Use PHP Built-in Server

If Apache continues to have issues, use PHP's built-in server instead:

```powershell
# Stop Apache first
net stop Apache2.4

# Start PHP built-in server
php artisan serve
```

**Access application at:**

```text
http://127.0.0.1:8000
```

**Benefits:**

- Uses same PHP as CLI (redis already working)
- No Apache configuration needed
- Simpler setup

---

## Troubleshooting

### Issue 1: Apache won't restart

**Check if Apache is running:**

```powershell
Get-Process httpd -ErrorAction SilentlyContinue
```text

**Force stop Apache:**

```powershell
Stop-Process -Name httpd -Force
```

**Start Apache:**

```powershell
net start Apache2.4
```text

### Issue 2: Redis still not found after restart

**Check Apache's PHP version:**
Create `public/test.php`:

```php
<?php
echo PHP_VERSION;
echo "\n";
echo "Redis: " . (extension_loaded('redis') ? 'Loaded' : 'Not Loaded');
```

Access: `http://127.0.0.1:8000/test.php`

**If Redis not loaded:**

1. Check php.ini location: `<?php phpinfo(); ?>`
2. Verify correct php.ini is being used
3. Check for multiple PHP installations

### Issue 3: Wrong php.ini being used

**Find which php.ini Apache uses:**

```php
<?php
echo php_ini_loaded_file();
```text

**If different from C:\xampp\php\php.ini:**

- Edit the correct php.ini file
- Add `extension=redis`
- Restart Apache

---

## Quick Fix Commands

```powershell
# 1. Stop Apache
net stop Apache2.4

# 2. Verify redis extension in php.ini
Select-String -Path "C:\xampp\php\php.ini" -Pattern "extension=redis"

# 3. Start Apache
net start Apache2.4

# 4. Test application
Start-Process "http://127.0.0.1:8000"
```

---

## Recommended Solution

**Use PHP built-in server instead of Apache:**

```powershell
# Stop Apache
net stop Apache2.4

# Start PHP server
php artisan serve
```text

**Why?**

- ✅ Uses same PHP as CLI (redis already working)
- ✅ No configuration needed
- ✅ Simpler to manage
- ✅ Perfect for development

---

## Status

- [x] Identified issue: Apache PHP doesn't have redis loaded
- [x] Verified php_redis.dll exists
- [x] Verified php.ini has extension enabled
- [ ] Restart Apache
- [ ] Verify application works

---

## Next Steps

1. Restart Apache using one of the methods above
2. Access application: <http://127.0.0.1:8000>
3. Verify no "Class Redis not found" error
4. If still issues, switch to PHP built-in server
