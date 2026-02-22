# Fix: Class "Redis" not found (Web Server)

**Issue:** Application works in CLI but fails in web browser with `Class "Redis" not found`

---

## Quick Fix (Recommended)

### Option 1: Use PHP Built-in Server (Easiest)

Stop using Apache and use PHP's built-in server instead:

```powershell
# Stop Apache (run as Administrator)
net stop Apache2.4

# Start PHP server (no admin needed)
php artisan serve
```text

**Access application at:** <http://127.0.0.1:8000>

**Why this works:**

- Uses same PHP as CLI (redis already enabled)
- No Apache configuration needed
- Perfect for development

---

## Option 2: Restart Apache

Apache needs to be restarted to load the redis extension.

### Method A: XAMPP Control Panel

1. Open XAMPP Control Panel
2. Click "Stop" next to Apache
3. Wait for it to stop completely
4. Click "Start" next to Apache
5. Test: <http://127.0.0.1:8000>

### Method B: Command Line (as Administrator)

```powershell
# Right-click PowerShell → Run as Administrator
net stop Apache2.4
net start Apache2.4
```

## Method C: Windows Services

1. Press `Win + R`
2. Type: `services.msc` and press Enter
3. Find "Apache2.4" in the list
4. Right-click → Restart
5. Wait for status to show "Running"

---

## Verification

After restarting Apache or switching to PHP server:

1. **Access application:**

   ```text
   http://127.0.0.1:8000
   ```

2. **Should see:** Your application homepage (no errors)

3. **If still errors:** Use PHP built-in server (Option 1)

---

## Why This Happened

1. ✅ CLI PHP has redis extension enabled
2. ❌ Apache PHP didn't load the extension yet
3. 🔄 Apache needs restart to load new extensions

**Files are correct:**

- ✅ `C:\xampp\php\ext\php_redis.dll` exists
- ✅ `C:\xampp\php\php.ini` has `extension=redis`
- ✅ Just needs Apache restart

---

## Recommended: Switch to PHP Server

For development, PHP's built-in server is simpler:

```powershell
# Stop Apache (if running)
# Then start PHP server:
php artisan serve
```text

**Benefits:**

- No Apache configuration
- Uses CLI PHP (already working)
- Easier to manage
- Automatic reload on code changes

---

## Commands Summary

### Stop Apache (as Administrator)

```powershell
net stop Apache2.4
```

### Start PHP Server (no admin needed)

```powershell
php artisan serve
```text

### Access Application

```

<http://127.0.0.1:8000>

```text

---

## Status

**Current:**

- ❌ Apache PHP doesn't have redis loaded
- ✅ CLI PHP has redis working
- ✅ All tests passing

**After Fix:**

- ✅ Application works in browser
- ✅ Redis fully functional
- ✅ No errors

---

## Next Steps

1. **Choose Option 1 (PHP Server)** - Recommended

   ```powershell
   php artisan serve
   ```

1. **Or restart Apache** using XAMPP Control Panel

2. **Test application** at <http://127.0.0.1:8000>

3. **Verify no errors** ✅
