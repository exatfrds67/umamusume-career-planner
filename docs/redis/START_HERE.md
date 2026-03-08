# 🚀 Redis Setup - START HERE

## Quick Overview

Redis is installed in WSL and ready. You need to install the phpredis extension for Windows PHP to complete the setup.

**Estimated Time:** 37 minutes total

---

## ✅ What's Already Done

- Redis 7.0.15 running in WSL
- Accessible at 127.0.0.1:6379
- .env file configured
- 6 documentation files created

---

## 📋 What You Need to Do

### Step 1: Install phpredis Extension (10 min)

1. **Download the DLL:**
   - Go to: <https://pecl.php.net/package/redis>
   - Or: <https://windows.php.net/downloads/pecl/releases/redis/>
   - Find: PHP 8.4.11, NTS, x64, VS16 version

2. **Install:**

   ```text
   - Extract php_redis.dll
   - Copy to: C:\xampp\php\ext\php_redis.dll
   - Edit: C:\xampp\php\php.ini
   - Add line: extension=redis
   - Restart Apache
   ```

3. **Verify:**

   ```powershell
   php -m | Select-String -Pattern "redis"
   ```text

   Should show: `redis`

### Step 2: Test Connection (2 min)

```powershell
php artisan redis:health --detailed
```text

Should show: Connection successful

### Step 3: Update .env (2 min)

Edit `.env` file and change these lines:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```text

Then run:

```powershell
php artisan config:clear
```

### Step 4: Update Tests (10 min)

Follow instructions in: `UPDATE_REDIS_TESTS.md`

Update these files:

- `tests/Feature/FallbackRecoveryTest.php`
- `tests/Feature/CacheManagementTest.php`

### Step 5: Run Tests (10 min)

```powershell
php artisan test --filter=FallbackRecovery --compact
php artisan test --filter=CacheManagement --compact
php artisan test --compact
```text

All tests should pass.

### Step 6: Warm Cache (5 min)

```powershell
php artisan cache:warm
```text

---

## 📚 Documentation Guide

| File                            | When to Use            |
| ------------------------------- | ---------------------- |
| **START_HERE.md**               | Right now!             |
| **REDIS_SETUP_INSTRUCTIONS.md** | Installing phpredis    |
| **REDIS_SETUP_CHECKLIST.md**    | Step-by-step checklist |
| **UPDATE_REDIS_TESTS.md**       | Updating test files    |
| **REDIS_COMMANDS_REFERENCE.md** | Need Redis commands    |
| **REDIS_SETUP_SUMMARY.md**      | Overview/reference     |
| **REDIS_CURRENT_STATUS.md**     | Current state          |

---

## 🎯 Success Checklist

You're done when:

- [ ] `php -m | Select-String redis` shows "redis"
- [ ] `php artisan redis:health` succeeds
- [ ] `.env` uses Redis for cache/queue/session
- [ ] All tests pass
- [ ] Cache warming works
- [ ] No errors in logs

---

## ⚡ Quick Commands

### Check Redis Status

```powershell
wsl bash -c "redis-cli ping"
```text

### After phpredis Installation

```powershell
# Verify extension
php -m | Select-String redis

# Test connection
php artisan redis:health

# Clear config
php artisan config:clear

# Run tests
php artisan test --compact

# Warm cache
php artisan cache:warm
```

---

## 🆘 Need Help?

### Common Issues

**Extension not loading?**

- Check DLL is in C:\xampp\php\ext\
- Check php.ini has `extension=redis`
- Restart Apache completely

**Connection refused?**

- Check Redis: `wsl bash -c "redis-cli ping"`
- Restart Redis: `wsl bash -c "sudo service redis-server restart"`

**Tests failing?**

- Check .env has Redis configuration
- Run: `php artisan config:clear`
- Verify extension: `php -m | Select-String redis`

### Where to Look

1. **Installation issues** → `REDIS_SETUP_INSTRUCTIONS.md`
2. **Test updates** → `UPDATE_REDIS_TESTS.md`
3. **Commands** → `REDIS_COMMANDS_REFERENCE.md`
4. **Checklist** → `REDIS_SETUP_CHECKLIST.md`

---

## 🎉 Ready to Start?

**Begin with:** `REDIS_SETUP_INSTRUCTIONS.md`

**Follow along:** `REDIS_SETUP_CHECKLIST.md`

**Get help:** Check troubleshooting sections

Good luck! You've got this! 💪
